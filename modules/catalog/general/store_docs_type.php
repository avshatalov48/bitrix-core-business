<?php

use Bitrix\Catalog\Document\Action\Store\MoveStoreBatchAction;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\Document\Action;
use Bitrix\Catalog\Document\Action\Barcode\AddStoreBarcodeAction;
use Bitrix\Catalog\Document\Action\Barcode\DeleteStoreBarcodeAction;
use Bitrix\Catalog\Document\Action\Reserve\ReserveStoreProductAction;
use Bitrix\Catalog\Document\Action\Reserve\UnReserveStoreProductAction;
use Bitrix\Catalog\Document\Action\Store\DecreaseStoreQuantityAction;
use Bitrix\Catalog\Document\Action\Store\IncreaseStoreQuantityAction;
use Bitrix\Catalog\Document\Action\Price\UpdateProductPricesAction;
use Bitrix\Catalog\Document\Action\Store\ReduceStoreBatchAmountAction;
use Bitrix\Catalog\Document\Action\Store\UpsertStoreBatchAction;
use Bitrix\Catalog\Document\Action\Store\ReturnStoreBatchAction;
use Bitrix\Catalog\Document\Action\Store\WriteOffStoreBatchAction;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Iblock;
use Bitrix\Catalog\v2\Contractor\Provider\Manager;

/** @global CMain $APPLICATION */

/*
	Document structure
		ID int
		DOC_TYPE char(1)
		SITE_ID char(2)
		STATUS char(1) Y/N
		CONTRACTOR_ID
		TOTAL
		CURRENCY

		ELEMENTS => [
			product_id => [
				PRODUCT_ID int
				TYPE int
				QUANTITY float
				QUANTITY_RESERVED float
				QUANTITY_TRACE char(1) Y/N
				CAN_BUY_ZERO char(1) Y/N
				SUBSCRIBE char(1) Y/N
				BARCODE_MULTI char(1) Y/N

				OLD_QUANTITY float (=QUANTITY after load)
				QUANTITY_LIMIT bool (QUANTITY_TRACE === 'Y' && CAN_BUY_ZERO === 'N')

				IBLOCK_ID int
				ELEMENT_NAME string

				POSITIONS => [
					document_row_id => [
						ROW_ID int
						PRODUCT_ID int
						STORE_TO ?int
						STORE_FROM ?int
						AMOUNT float
						PURCHASING_PRICE ?float
						PURCHASING_CURRENCY ?string
						PURCHASING_INFO => [ // deprecated
							PURCHASING_PRICE ?float
							PURCHASING_CURRENCY ?string
						]
					]
				]
				BARCODES => [
					barcode => [
						ROW_ID int
						DOCUMENT_ROW_ID int
						PRODUCT_ID int
						BARCODE string
						STORE_FROM ?int
						STORE_TO ?int
					]
				]
				CURRENT_STORES => [
					store_id => [
						ID int
						PRODUCT_ID int
						STORE_ID int
						AMOUNT float
						QUANTITY_RESERVED float
					]
				]
				CURRENT_BARCODES => [
					barcode => [
						ID ?int
						BARCODE string
						PRODUCT_ID ?int
						ORDER_ID ?int
						STORE_ID ?int
					]
				]
			]
		]
*/

abstract class CCatalogDocsTypes
{
	/** @deprecated  */
	public const TYPE_ARRIVAL = StoreDocumentTable::TYPE_ARRIVAL;
	/** @deprecated  */
	public const TYPE_STORE_ADJUSTMENT = StoreDocumentTable::TYPE_STORE_ADJUSTMENT;
	/** @deprecated  */
	public const TYPE_MOVING = StoreDocumentTable::TYPE_MOVING;
	/** @deprecated  */
	public const TYPE_RETURN = StoreDocumentTable::TYPE_RETURN;
	/** @deprecated  */
	public const TYPE_DEDUCT = StoreDocumentTable::TYPE_DEDUCT;
	/** @deprecated  */
	public const TYPE_UNDO_RESERVE = StoreDocumentTable::TYPE_UNDO_RESERVE;
	public const TYPE_INVENTORY = 'I';

	protected const ACTION_CONDUCTION = 'Y';
	protected const ACTION_CANCEL = 'N';

	protected const ALL_STORES = 'ALL';
	protected const DISABLED_STORES = 'DISABLE';
	protected const ACTIVE_STORES = 'ACTIVE';

	protected const ERR_STORE_ABSENT = 'ABSENT';
	protected const ERR_STORE_UNKNOWN = 'UNKNOWN';
	protected const ERR_STORE_DISABLED = 'DISABLED';

	public static function getFields(): array
	{
		$result = static::getDocumentFields();
		foreach (static::getElementFields() as $field => $description)
		{
			$result[$field] = $description;
		}

		return $result;
	}

	public static function getDocumentFields(): array
	{
		return array_merge(
			static::getDocumentCommonFields(),
			static::getDocumentSpecificFields()
		);
	}

	protected static function getDocumentCommonFields(): array
	{
		return [
			'ID' => ['required' => 'Y'],
			'TITLE' => ['required' => 'N'],
			'DOC_TYPE' => ['required' => 'Y'],
			'SITE_ID' => ['required' => 'Y'],
			'DATE_MODIFY' => ['required' => 'Y'],
			'DATE_CREATE' => ['required' => 'Y'],
			'CREATED_BY' => ['required' => 'Y'],
			'MODIFIED_BY' => ['required' => 'Y'],
			'STATUS' => ['required' => 'Y'],
			'WAS_CANCELLED' => ['required' => 'Y'],
			'DATE_STATUS' => ['required' => 'Y'],
			'STATUS_BY' => ['required' => 'Y'],
			'COMMENTARY' => ['required' => 'N'],
			'RESPONSIBLE_ID' => ['required' => 'Y'],
		];
	}

	protected static function getDocumentSpecificFields(): array
	{
		return [];
	}

	public static function getElementFields(): array
	{
		return [];
	}

	/**
	 * The method of conducting a document, distributes products to warehouses, according to the document type.
	 *
	 * @param $documentId
	 * @param $userId
	 * @param $currency
	 * @param $contractorId
	 *
	 * @return array|bool
	 */
	public static function conductDocument($documentId, $userId, $currency, $contractorId)
	{
		$documentId = (int)$documentId;
		$userId = (int)$userId;
		if ($currency !== null)
		{
			$currency = (string)$currency;
		}
		$contractorId = (int)$contractorId;
		if (!static::checkParamsForConduction($documentId, $userId, $currency, $contractorId))
		{
			return false;
		}

		$document = static::load($documentId, ['CURRENCY' => $currency]);
		if ($document === null || !static::checkConductDocument($document))
		{
			return false;
		}

		$actions = static::getDocumentActions(self::ACTION_CONDUCTION, $document, $userId);
		if (empty($actions) || !static::checkDocumentActions($actions))
		{
			return false;
		}

		return static::executeActions($actions);
	}

	/**
	 * Method cancels an instrument and perform the reverse action of conducting a document.
	 *
	 * @param $documentId
	 * @param $userId
	 *
	 * @return array|bool
	 */
	public static function cancellationDocument($documentId, $userId)
	{
		$documentId = (int)$documentId;
		$userId = (int)$userId;

		if (!static::checkParamsForCancellation($documentId, $userId))
		{
			return false;
		}

		$document = static::load($documentId);
		if ($document === null || !static::checkCancellationDocument($document))
		{
			return false;
		}

		$actions = static::getDocumentActions(self::ACTION_CANCEL, $document, $userId);
		if (empty($actions) || !static::checkDocumentActions($actions))
		{
			return false;
		}

		return static::executeActions($actions);
	}

	protected static function checkConductDocument(array $document): bool
	{
		return true;
	}

	protected static function checkCancellationDocument(array $document): bool
	{
		return true;
	}

	protected static function setErrors(array $errors): void
	{
		global $APPLICATION;

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode('; ', $errors));
		}
	}

	abstract public static function getTypeId(): string;

	protected static function load(int $documentId, array $options = []): ?array
	{
		$result = static::loadDocument($documentId, $options);
		if ($result === null)
		{
			return null;
		}

		$result = static::loadProducts($result);
		if (!static::checkDocumentProducts($result))
		{
			return null;
		}

		$result = static::loadProductStores($result);

		$result = static::loadDocumentBarcodes($result);
		if (!static::checkDocumentBarcodes($result))
		{
			return null;
		}

		$result = static::loadBarcodes($result);
		if (!static::checkBarcodes($result))
		{
			return null;
		}

		return $result;
	}

	protected static function loadDocument(int $documentId, array $options = []): ?array
	{
		$select = [
			'ID',
			'DOC_TYPE',
			'SITE_ID',
			'CONTRACTOR_ID',
			'CURRENCY',
			'STATUS',
			'TOTAL',
		];
		$iterator = Catalog\StoreDocumentTable::getList([
			'select' => $select,
			'filter' => [
				'=ID' => $documentId,
				'=DOC_TYPE' => static::getTypeId(),
			],
		]);
		$result = $iterator->Fetch();
		unset($iterator);
		if (empty($result))
		{
			$iterator = Catalog\StoreDocumentTable::getList([
				'select' => $select,
				'filter' => ['=ID' => $documentId],
			]);
			$result = $iterator->Fetch();
			unset($iterator);
			if (empty($result))
			{
				static::setErrors([
					Loc::getMessage(
						'CATALOG_STORE_DOCS_ERR_BAD_DOCUMENT_ID',
						['#ID#' => $documentId]
					)
				]);
			}
			else
			{
				static::setErrors([
					Loc::getMessage(
						'CATALOG_STORE_DOCS_ERR_BAD_DOCUMENT_TYPE_ID',
						['#ID#' => $documentId]
					)
				]);
			}
			return null;
		}
		$result['ID'] = (int)$result['ID'];
		$currency = $result['CURRENCY'] ?? '';
		if ($currency === '')
		{
			$currency = $options['CURRENCY'] ?? '';
		}

		$result['ELEMENTS'] = [];
		$productList = &$result['ELEMENTS'];
		$found = false;

		$iterator = Catalog\StoreDocumentElementTable::getList([
			'select' => [
				'ID',
				'DOC_ID',
				'STORE_FROM',
				'STORE_TO',
				'ELEMENT_ID',
				'AMOUNT',
				'PURCHASING_PRICE',
				'BASE_PRICE',
			],
			'filter' => ['=DOC_ID' => $documentId],
		]);
		while ($row = $iterator->fetch())
		{
			$found = true;
			$row['ID'] = (int)$row['ID'];
			$row['ELEMENT_ID'] = (int)$row['ELEMENT_ID'];
			$row['AMOUNT'] = (float)$row['AMOUNT'];
			if ($row['STORE_TO'] !== null)
			{
				$row['STORE_TO'] = (int)$row['STORE_TO'];
				if ($row['STORE_TO'] <= 0)
				{
					$row['STORE_TO'] = null;
				}
			}
			if ($row['STORE_FROM'] !== null)
			{
				$row['STORE_FROM'] = (int)$row['STORE_FROM'];
				if ($row['STORE_FROM'] <= 0)
				{
					$row['STORE_FROM'] = null;
				}
			}

			$elementId = $row['ELEMENT_ID'];
			$rowId = $row['ID'];

			if (!isset($productList[$elementId]))
			{
				$productList[$elementId] = [
					'PRODUCT_ID' => $elementId,
					'POSITIONS' => [],
					'BARCODES' => [],
					'CURRENT_STORES' => [],
					'CURRENT_BARCODES' => [],
				];
			}

			$documentRow = [
				'ROW_ID' => $rowId,
				'PRODUCT_ID' => $elementId,
				'STORE_TO' => $row['STORE_TO'],
				'STORE_FROM' => $row['STORE_FROM'],
				'AMOUNT' => $row['AMOUNT'],
				'PURCHASING_PRICE' => $row['PURCHASING_PRICE'],
				'PURCHASING_CURRENCY' => $currency,
				'PURCHASING_INFO' => [ // deprecated
					'PURCHASING_PRICE' => $row['PURCHASING_PRICE'],
					'PURCHASING_CURRENCY' => $currency,
				],
			];

			if (isset($row['BASE_PRICE']))
			{
				$documentRow['BASE_PRICE_INFO'] = [
					'BASE_PRICE' => (float)$row['BASE_PRICE'],
					'BASE_PRICE_CURRENCY' => $currency,
				];
			}

			$productList[$elementId]['POSITIONS'][$rowId] = $documentRow;
		}
		unset($row, $iterator);

		unset($productList);

		if (!$found)
		{
			static::setErrors([
				Loc::getMessage(
					'CATALOG_STORE_DOCS_ERR_ABSENT_DOCUMENT_ELEMENTS',
					['#ID#' => $documentId]
				)
			]);
			return null;
		}

		return $result;
	}

	protected static function loadProducts(array $document): array
	{
		$productList = &$document['ELEMENTS'];
		$elements = array_keys($productList);
		sort($elements);
		foreach (array_chunk($elements, 500) as $pageIds)
		{
			$iterator = Catalog\Model\Product::getList([
				'select' => [
					'ID',
					'TYPE',
					'QUANTITY',
					'QUANTITY_RESERVED',
					'QUANTITY_TRACE',
					'CAN_BUY_ZERO',
					'SUBSCRIBE',
					'BARCODE_MULTI',
				],
				'filter' => ['@ID' => $pageIds]
			]);
			while ($row = $iterator->Fetch())
			{
				$id = (int)$row['ID'];
				$productList[$id]['TYPE'] = (int)$row['TYPE'];
				$productList[$id]['OLD_QUANTITY'] = (float)$row['QUANTITY'];
				$productList[$id]['QUANTITY_TRACE'] = $row['QUANTITY_TRACE'];
				$productList[$id]['CAN_BUY_ZERO'] = $row['CAN_BUY_ZERO'];
				$productList[$id]['SUBSCRIBE'] = $row['SUBSCRIBE'];
				$productList[$id]['BARCODE_MULTI'] = $row['BARCODE_MULTI'];

				$productList[$id]['QUANTITY'] = (float)$row['QUANTITY'];
				$productList[$id]['QUANTITY_RESERVED'] = (float)$row['QUANTITY_RESERVED'];

				$productList[$id]['QUANTITY_LIMIT'] = ($row['QUANTITY_TRACE'] === 'Y' && $row['CAN_BUY_ZERO'] === 'N');
			}
			unset($row, $iterator);

			$iterator = Iblock\ElementTable::getList([
				'select' => [
					'ID',
					'IBLOCK_ID',
					'NAME',
				],
				'filter' => ['@ID' => $pageIds],
			]);
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['ID'];
				$productList[$id]['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];
				$productList[$id]['ELEMENT_NAME'] = $row['NAME'];
			}
			unset($row, $iterator);
		}
		unset($pageIds, $elements);

		unset($productList);

		return $document;
	}

	protected static function checkDocumentProducts(array $document): bool
	{
		$errors = [];

		foreach ($document['ELEMENTS'] as $productId => $data)
		{
			if (!isset($data['IBLOCK_ID']))
			{
				$errors[] = Loc::getMessage(
					'CATALOG_STORE_DOCS_ERR_BAD_ELEMENT_ID',
					['#ID#' => $productId]
				);
				continue;
			}
			$description = [
				'#ID#' => $productId,
				'#NAME#' => $data['ELEMENT_NAME'],
			];
			if (!isset($data['TYPE']))
			{
				$errors[] = Loc::getMessage(
					'CATALOG_STORE_DOCS_ERR_BAD_PRODUCT_ID',
					$description
				);
				continue;
			}
			if (
				$data['TYPE'] !== Catalog\ProductTable::TYPE_PRODUCT
				&& $data['TYPE'] !== Catalog\ProductTable::TYPE_OFFER
			)
			{
				$errors[] = Loc::getMessage(
					'CATALOG_STORE_DOCS_ERR_BAD_PRODUCT_TYPE',
					$description
				);
				continue;
			}
			foreach ($data['POSITIONS'] as $row)
			{
				if ($row['AMOUNT'] <= 0)
				{
					$errors[] = Loc::getMessage(
						'CATALOG_STORE_DOCS_ERR_BAD_POSITION_AMOUNT',
						$description
					);
				}
			}
		}

		if (!empty($errors))
		{
			static::setErrors($errors);
			return false;
		}

		return true;
	}

	protected static function loadProductStores(array $document): array
	{
		$stores = static::getStoreList();
		$activeStores = array_keys($stores[self::ACTIVE_STORES]);
		if (empty($activeStores))
		{
			return $document;
		}

		$productList = &$document['ELEMENTS'];
		$elements = array_keys($productList);
		sort($elements);
		foreach (array_chunk($elements, 500) as $pageIds)
		{
			$iterator = Catalog\StoreProductTable::getList([
				'select' => [
					'ID',
					'PRODUCT_ID',
					'STORE_ID',
					'AMOUNT',
					'QUANTITY_RESERVED',
				],
				'filter' => [
					'@PRODUCT_ID' => $pageIds,
					'@STORE_ID' => $activeStores,
				],
			]);
			while ($row = $iterator->fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$row['PRODUCT_ID'] = (int)$row['PRODUCT_ID'];
				$row['STORE_ID'] = (int)$row['STORE_ID'];
				$row['AMOUNT'] = (float)$row['AMOUNT'];
				$row['QUANTITY_RESERVED'] = (float)$row['QUANTITY_RESERVED'];

				$productId = $row['PRODUCT_ID'];
				$storeId = $row['STORE_ID'];

				$productList[$productId]['CURRENT_STORES'][$storeId] = $row;
			}
			unset($row, $iterator);
		}
		unset($pageIds, $elements);

		unset($productList);

		return $document;
	}

	protected static function loadDocumentBarcodes(array $document): array
	{
		$productList = &$document['ELEMENTS'];

		$elements = [];
		foreach (array_keys($productList) as $elementId)
		{
			foreach (array_keys($productList[$elementId]['POSITIONS']) as $rowId)
			{
				$elements[$rowId] = $elementId;
			}
		}

		$iterator = Catalog\StoreDocumentBarcodeTable::getList([
			'select' => ['*'],
			'filter' => [
				'=DOC_ID' => $document['ID'],
			],
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$rowId = $row['ID'];
			$row['DOC_ELEMENT_ID'] = (int)$row['DOC_ELEMENT_ID'];
			$documentRowId = $row['DOC_ELEMENT_ID'];
			$elementId = $elements[$documentRowId];

			$position = $productList[$elementId]['POSITIONS'][$documentRowId];

			$productList[$elementId]['BARCODES'][$row['BARCODE']] = [
				'ROW_ID' => $rowId,
				'DOCUMENT_ROW_ID' => $documentRowId,
				'PRODUCT_ID' => $elementId,
				'BARCODE' => $row['BARCODE'],
				'STORE_FROM' => $position['STORE_FROM'],
				'STORE_TO' => $position['STORE_TO'],
			];
		}
		unset($row, $iterator);
		unset($productList);

		return $document;
	}

	protected static function checkDocumentBarcodes(array $document): bool
	{
		$errors = [];

		foreach ($document['ELEMENTS'] as $data)
		{
			if ($data['BARCODE_MULTI'] !== 'Y')
			{
				continue;
			}
			$description = [
				'#ID#' => $data['PRODUCT_ID'],
				'#NAME#' => $data['ELEMENT_NAME'],
			];
			if (static::getProductCount($data['POSITIONS']) !== count($data['BARCODES']))
			{
				$errors[] = Loc::getMessage(
					'CATALOG_STORE_DOCS_ERR_BAD_BARCODE_COUNT',
					$description
				);
				continue;
			}
			foreach (array_keys($data['POSITIONS']) as $rowId)
			{
				if ($data['POSITIONS'][$rowId]['AMOUNT'] != static::getRowBarcodeCount($rowId, $data['BARCODES']))
				{
					$errors[] = Loc::getMessage(
						'CATALOG_STORE_DOCS_ERR_WRONG_POSITION_BARCODES_COUNT',
						$description
					);
				}
			}
			unset($rowId);
		}
		unset($data);

		if (!empty($errors))
		{
			static::setErrors($errors);
			return false;
		}

		return true;
	}

	protected static function getDocumentBarcodes(array $document): array
	{
		$result = [];

		foreach ($document['ELEMENTS'] as $product)
		{
			foreach (array_keys($product['BARCODES']) as $barcode)
			{
				$result[$barcode] = $product['PRODUCT_ID'];
			}
			unset($barcode);
		}
		unset($product);

		return $result;
	}

	protected static function loadBarcodes(array $document): array
	{
		$list = static::getDocumentBarcodes($document);
		if (empty($list))
		{
			return $document;
		}

		$productList = &$document['ELEMENTS'];

		foreach ($list as $barcode => $productId)
		{
			$productList[$productId]['CURRENT_BARCODES'][$barcode] = [
				'ID' => null,
				'PRODUCT_ID' => $productId,
				'BARCODE' => $barcode,
				'STORE_ID' => null,
				'ORDER_ID' => null,
			];
		}
		unset($barcode, $productId);

		$iterator = Catalog\StoreBarcodeTable::getList([
			'select' => [
				'ID',
				'PRODUCT_ID',
				'BARCODE',
				'STORE_ID',
				'ORDER_ID',
			],
			'filter' => [
				'@BARCODE' => array_keys($list),
			],
		]);
		while ($row = $iterator->fetch())
		{
			$productId = $list[$row['BARCODE']];
			$row['ID'] = (int)$row['ID'];
			$row['PRODUCT_ID'] = (int)$row['PRODUCT_ID'];
			$row['STORE_ID'] = (int)$row['STORE_ID'];
			if ($row['STORE_ID'] <= 0)
			{
				$row['STORE_ID'] = null;
			}
			$row['ORDER_ID'] = (int)$row['ORDER_ID'];
			if ($row['ORDER_ID'] <= 0)
			{
				$row['ORDER_ID'] = null;
			}
			$productList[$productId]['CURRENT_BARCODES'][$row['BARCODE']] = $row;
		}
		unset($productId, $row, $iterator);
		unset($list);
		unset($productList);

		return $document;
	}

	protected static function checkBarcodes(array $document): bool
	{
		$errors = [];

		foreach ($document['ELEMENTS'] as $data)
		{
			foreach ($data['CURRENT_BARCODES'] as $barcode)
			{
				if ($barcode['PRODUCT_ID'] !== $data['PRODUCT_ID'])
				{
					$errors[] = Loc::getMessage(
						'CATALOG_STORE_DOCS_ERR_WRONG_BARCODES_PRODUCT_ID',
						[
							'#ID#' => $data['PRODUCT_ID'],
							'#NAME#' => $data['ELEMENT_NAME'],
							'#BARCODE#' => $barcode['BARCODE'],
							'#OTHER_ID#' => $barcode['PRODUCT_ID']
						]
					);
				}
			}
		}

		if (!empty($errors))
		{
			static::setErrors($errors);
			return false;
		}

		return true;
	}

	protected static function getProductCount(array $positions): ?int
	{
		$amount = 0;
		foreach ($positions as $row)
		{
			$amount += $row['AMOUNT'];
		}

		return  ($amount > (int)$amount ? null : (int)$amount);
	}

	protected static function getRowBarcodeCount(int $rowId, array $barcodes): int
	{
		$count = 0;
		foreach ($barcodes as $row)
		{
			if ($row['DOCUMENT_ROW_ID'] === $rowId)
			{
				$count++;
			}
		}
		unset($row);

		return $count;
	}

	protected static function getStoreList(): array
	{
		$result = [
			self::ALL_STORES => [],
			self::DISABLED_STORES => [],
			self::ACTIVE_STORES => [],
		];
		$iterator = Catalog\StoreTable::getList([
			'select' => [
				'ID',
				'ACTIVE'
			],
			'order' => [
				'ID' => 'ASC',
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['ID'];
			$result[self::ALL_STORES][$id] = true;
			if ($row['ACTIVE'] !== 'Y')
			{
				$result[self::DISABLED_STORES][$id] = true;
			}
			else
			{
				$result[self::ACTIVE_STORES][$id] = true;
			}
		}
		unset($row, $iterator);

		return $result;
	}

	protected static function checkDocumentStores(array $document, array $options): bool
	{
		$errorList = [
			self::ERR_STORE_ABSENT => [],
			self::ERR_STORE_UNKNOWN => [],
			self::ERR_STORE_DISABLED => [],
		];

		$storeList = static::getStoreList();

		$storeField = $options['STORE_FIELD'];

		foreach ($document['ELEMENTS'] as $product)
		{
			$id = $product['PRODUCT_ID'];
			$name = $product['ELEMENT_NAME'];
			foreach ($product['POSITIONS'] as $position)
			{
				if (!isset($position[$storeField]))
				{
					$errorList[self::ERR_STORE_ABSENT][$id] = $name;
					break;
				}
				$storeId = $position[$storeField];
				if (!isset($storeList[self::ALL_STORES][$storeId]))
				{
					$errorList[self::ERR_STORE_UNKNOWN][$id] = $name;
					break;
				}
				if (isset($storeList[self::DISABLED_STORES][$storeId]))
				{
					$errorList[self::ERR_STORE_DISABLED][$id] = $name;
					break;
				}
			}
			unset($position, $name, $id);
		}
		unset($product);

		if (
			!empty($errorList[self::ERR_STORE_ABSENT])
			|| !empty($errorList[self::ERR_STORE_UNKNOWN])
			|| !empty($errorList[self::ERR_STORE_DISABLED])
		)
		{
			$list = [];
			if (!empty($errorList[self::ERR_STORE_ABSENT]))
			{
				$list[] = static::getProductListError(
					$options['ERR_ABSENT'],
					$errorList[self::ERR_STORE_ABSENT]
				);
			}
			if (!empty($errorList[self::ERR_STORE_UNKNOWN]))
			{
				$list[] = static::getProductListError(
					$options['ERR_UNKNOWN'],
					$errorList[self::ERR_STORE_UNKNOWN]
				);
			}
			if (!empty($errorList[self::ERR_STORE_DISABLED]))
			{
				$list[] = static::getProductListError(
					$options['ERR_DISABLED'],
					$errorList[self::ERR_STORE_DISABLED]
				);
			}

			static::setErrors($list);
			unset($list);

			return false;
		}

		return true;
	}

	protected static function getProductListError(array $errors, array $products): string
	{
		$list = [];
		foreach ($products as $id => $name)
		{
			$list[] = Loc::getMessage(
				'CATALOG_STORE_DOCS_TPL_PDODUCT',
				[
					'#ID#' => $id,
					'#NAME#' => $name,
				]
			);
		}

		$errorCode = (count($list) > 1 && isset($errors[1]) ? $errors[1] : $errors[0]);

		return Loc::getMessage(
			$errorCode,
			['#PRODUCTS#' => implode(', ', $list)]
		);
	}

	protected static function getStoreDestinationErrors(): array
	{
		return [
			'ERR_ABSENT' => [],
			'ERR_UNKNOWN' => [],
			'ERR_DISABLED' => [],
		];
	}

	protected static function getStoreSourceErrors(): array
	{
		return [
			'ERR_ABSENT' => [],
			'ERR_UNKNOWN' => [],
			'ERR_DISABLED' => [],
		];
	}

	protected static function checkStoreDestination(array $document): bool
	{
		return static::checkDocumentStores(
			$document,
			['STORE_FIELD' => 'STORE_TO'] + static::getStoreDestinationErrors()
		);
	}

	protected static function checkStoreSource(array $document): bool
	{
		return static::checkDocumentStores(
			$document,
			['STORE_FIELD' => 'STORE_FROM'] + static::getStoreSourceErrors()
		);
	}

	/**
	 * @param string $action
	 * @param array $document
	 * @param int $userId
	 * @return array|null
	 */
	protected static function getDocumentActions(string $action, array $document, int $userId): ?array
	{
		if (
			$action !== self::ACTION_CONDUCTION
			&& $action !== self::ACTION_CANCEL
		)
		{
			static::setErrors([
				Loc::getMessage('CATALOG_STORE_DOCS_ERR_BAD_ACTION')
			]);

			return null;
		}

		return [];
	}

	/**
	 * Check document actions.
	 *
	 * @param Action[] $actions
	 *
	 * @return bool
	 */
	protected static function checkDocumentActions(array $actions): bool
	{
		foreach ($actions as $action)
		{
			$result = $action->canExecute();
			if (!$result->isSuccess())
			{
				static::setErrors(
					$result->getErrorMessages()
				);
				return false;
			}
		}

		return true;
	}

	/**
	 * Execute document actions.
	 *
	 * @param Action[] $actions
	 *
	 * @return bool
	 */
	protected static function executeActions(array $actions): bool
	{
		foreach ($actions as $action)
		{
			$result = $action->execute();
			if (!$result->isSuccess())
			{
				static::setErrors(
					$result->getErrorMessages()
				);
				return false;
			}
		}

		return true;
	}

	/**
	 * @deprecated use actions.
	 *
	 * The method checks the correctness of the data warehouse. If successful, enrolling \ debits to the storage required amount of product.
	 *
	 * @param $arFields
	 * @param $userId
	 *
	 * @return bool
	 */
	protected static function distributeElementsToStores($arFields, $userId): bool
	{
		trigger_error("Wrong API usage of use inventory documents", E_USER_WARNING);

		return false;
	}

	private static function isExistPriceRanges(int $productId, int $basePriceId): bool
	{
		return Catalog\PriceTable::getCount([
			'=PRODUCT_ID' => $productId,
			'=CATALOG_GROUP_ID' => $basePriceId,
			[
				'LOGIC' => 'OR',
				'!=QUANTITY_FROM' => null,
				'!=QUANTITY_TO' => null,
			],
		]) > 0;
	}

	/** The method works with barcodes. If necessary, check the uniqueness of multiple barcodes.
	 * @param $arFields
	 * @param $userId
	 * @return bool|int
	 */
	protected static function applyBarCode($arFields, $userId)
	{
		global $APPLICATION;

		$barCode = $arFields["BARCODE"];
		$elementId = $arFields["PRODUCT_ID"];
		$storeToId = (isset($arFields["STORE_ID"])) ? $arFields["STORE_ID"] : 0;
		$storeFromId = (isset($arFields["STORE_FROM"])) ? $arFields["STORE_FROM"] : 0;
		$newStore = 0;
		$userId = (int)$userId;
		$result = false;
		$rsProps = CCatalogStoreBarCode::GetList(array(), array("BARCODE" => $barCode), false, false, array('ID', 'STORE_ID', 'PRODUCT_ID'));
		if($arBarCode = $rsProps->Fetch())
		{
			if($storeFromId > 0) // deduct or moving
			{
				if($storeToId > 0) // moving
				{
					if($arBarCode["STORE_ID"] == $storeFromId && $arBarCode["PRODUCT_ID"] == $elementId)
						$newStore = $storeToId;
					else
					{
						$storeName = CCatalogStoreControlUtil::getStoreName($storeFromId);
						$APPLICATION->ThrowException(Loc::getMessage("CATALOG_STORE_DOCS_ERR_WRONG_STORE_BARCODE", array("#STORE#" => '"'.$storeName.'"', "#PRODUCT#" => '"'.$arFields["ELEMENT_NAME"].'"', "#BARCODE#" => '"'.$barCode.'"')));
						return false;
					}
				}
			}
			else
			{
				$APPLICATION->ThrowException(Loc::getMessage("CATALOG_STORE_DOCS_ERR_BARCODE_ALREADY_EXIST", array("#PRODUCT#" => '"'.$arFields["ELEMENT_NAME"].'"', "#BARCODE#" => '"'.$barCode.'"')));
				return false;
			}
			if($newStore > 0)
				$result = CCatalogStoreBarCode::update($arBarCode["ID"], array("STORE_ID" => $storeToId, "MODIFIED_BY" => $userId));
			else
				$result = CCatalogStoreBarCode::delete($arBarCode["ID"]);
		}
		else
		{
			if($storeFromId > 0)
			{
				$storeName = CCatalogStoreControlUtil::getStoreName($storeFromId);
				$APPLICATION->ThrowException(Loc::getMessage("CATALOG_STORE_DOCS_ERR_WRONG_STORE_BARCODE", array("#STORE#" => '"'.$storeName.'"', "#PRODUCT#" => '"'.$arFields["ELEMENT_NAME"].'"', "#BARCODE#" => '"'.$barCode.'"')));
				return false;
			}
			elseif($storeToId > 0)
				$result = CCatalogStoreBarCode::Add(array("PRODUCT_ID" => $elementId, "STORE_ID" => $storeToId, "BARCODE" => $barCode, "MODIFIED_BY" => $userId, "CREATED_BY" => $userId));
		}

		return $result;
	}

	/**
	 * @deprecated
	 */
	protected static function checkTotalAmount($elementId, $name = ''): array
	{
		return [];
	}

	/**
	 * @deprecated
	 * @see \CCatalogDocsTypes::checkDocumentProducts
	 */
	protected static function checkAmountField($arDocElement, $name = '')
	{
		global $APPLICATION;
		$name = (string)$name;
		if(doubleval($arDocElement["AMOUNT"]) <= 0)
		{
			if ($name == '')
			{
				$dbProduct = CIBlockElement::GetList(
					array(),
					array("ID" => $arDocElement["ELEMENT_ID"]),
					false,
					false,
					array('ID', 'NAME')
				);
				if ($arProduct = $dbProduct->Fetch())
				{
					$name = $arProduct['NAME'];
				}
			}
			if ($name == '')
				$name = $arDocElement["ELEMENT_ID"];
			$APPLICATION->ThrowException(Loc::getMessage("CATALOG_STORE_DOCS_ERR_WRONG_AMOUNT", array("#PRODUCT#" => '"'.$name.'"')));
			return false;
		}
		return true;
	}

	protected static function checkParamsForConduction(int $documentId, int $userId, ?string $currency, int $contractorId): bool
	{
		if ($documentId <= 0)
		{
			static::setErrors([
				Loc::getMessage('CATALOG_STORE_DOCS_ERR_WRONG_DOCUMENT_ID')
			]);
			return false;
		}

		return true;
	}

	protected static function checkParamsForCancellation(int $documentId, int $userId): bool
	{
		if ($documentId <= 0)
		{
			static::setErrors([
				Loc::getMessage('CATALOG_STORE_DOCS_ERR_WRONG_DOCUMENT_ID')
			]);
			return false;
		}

		return true;
	}
}

class CCatalogArrivalDocs extends CCatalogDocsTypes
{
	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getDocumentSpecificFields(): array
	{
		return [
			'DOC_NUMBER' => ['required' => 'N'],
			'DATE_DOCUMENT' => ['required' => 'N'],
			'ITEMS_ORDER_DATE' => ['required' => 'N'],
			'ITEMS_RECEIVED_DATE' => ['required' => 'N'],
			'DOCUMENT_FILES' => ['required' => 'N'],
			'CONTRACTOR_ID' => ['required' => 'Y'],
			'CURRENCY' => ['required' => 'Y'],
			'TOTAL' => ['required' => 'N'],
		];
	}

	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getElementFields(): array
	{
		return [
			'AMOUNT' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_ARRIVAL_AMOUNT'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_ARRIVAL_AMOUNT'),
			],
			'NET_PRICE' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_PURCHASING_PRICE'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_PURCHASING_PRICE'),
			],
			'BASE_PRICE' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_PRICE'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_PRICE'),
			],
			'STORE_TO' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_STORE_TO'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_STORE_TO'),
			],
			'BAR_CODE' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_BARCODE'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_BARCODE'),
			],
			'TOTAL' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_TOTAL'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_TOTAL'),
			],
		];
	}

	public static function getTypeId(): string
	{
		return StoreDocumentTable::TYPE_ARRIVAL;
	}

	protected static function checkParamsForConduction(int $documentId, int $userId, ?string $currency, int $contractorId): bool
	{
		if (!parent::checkParamsForConduction($documentId, $userId, $currency, $contractorId))
		{
			return false;
		}

		if (Manager::getActiveProvider(Manager::PROVIDER_STORE_DOCUMENT))
		{
			$contractor = Manager::getActiveProvider(Manager::PROVIDER_STORE_DOCUMENT)::getContractorByDocumentId($documentId);
			$isContractorSpecified = !is_null($contractor);
		}
		else
		{
			$isContractorSpecified = $contractorId > 0;
		}

		if (!$isContractorSpecified)
		{
			static::setErrors([
				Loc::getMessage('CATALOG_STORE_DOCS_ERR_WRONG_CONTRACTOR')
			]);
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected static function getDocumentActions(string $action, array $document, int $userId): ?array
	{
		$actions = parent::getDocumentActions($action, $document, $userId);
		if ($actions === null)
		{
			return null;
		}

		$isBatchMetodSelected = State::isProductBatchMethodSelected();

		$elements = $document['ELEMENTS'] ?? [];
		foreach ($elements as $productId => $element)
		{
			$positions = (array)($element['POSITIONS'] ?? []);
			foreach ($positions as $item)
			{
				if ($action === self::ACTION_CONDUCTION)
				{
					$actions[] = new IncreaseStoreQuantityAction(
						$item['STORE_TO'],
						$item['PRODUCT_ID'],
						$item['AMOUNT'],
					);

					if ($isBatchMetodSelected)
					{
						$actions[] = new UpsertStoreBatchAction(
							$item['STORE_TO'],
							$item['PRODUCT_ID'],
							$item['AMOUNT'],
							$item['ROW_ID'],
							$item['PURCHASING_PRICE'] ?? null,
							$item['PURCHASING_CURRENCY'] ?? null,
						);
					}

					$actions[] = new UpdateProductPricesAction(
						$item['PRODUCT_ID'],
						$item['PURCHASING_PRICE'] ?? null,
						$item['PURCHASING_CURRENCY'] ?? null,
						$item['BASE_PRICE_INFO']['BASE_PRICE'] ?? null,
						$item['BASE_PRICE_INFO']['BASE_PRICE_CURRENCY'] ?? null,
					);
				}
				elseif ($action === self::ACTION_CANCEL)
				{
					if (State::isProductBatchMethodSelected())
					{
						$actions[] = new ReduceStoreBatchAmountAction($item['ROW_ID']);
					}

					$actions[] = new DecreaseStoreQuantityAction(
						$item['STORE_TO'],
						$item['PRODUCT_ID'],
						$item['AMOUNT'],
						$document['DOC_TYPE'],
					);
				}
			}

			$barcodes = (array)($element['BARCODES'] ?? []);
			foreach ($barcodes as $item)
			{
				$storeId = $item['STORE_TO'];
				if (!$storeId)
				{
					$rowId = $item['DOCUMENT_ROW_ID'];
					$storeId = $positions[$rowId]['STORE_TO'] ?? null;
				}

				if ($element['BARCODE_MULTI'] === 'N')
				{
					$storeId = 0;
				}

				if ($action === self::ACTION_CONDUCTION)
				{
					$actions[] = new AddStoreBarcodeAction(
						$storeId,
						$productId,
						$item['BARCODE'],
						$userId
					);
				}
				elseif ($action === self::ACTION_CANCEL)
				{
					if ($element['BARCODE_MULTI'] === 'Y')
					{
						$actions[] = new DeleteStoreBarcodeAction(
							$storeId,
							$productId,
							$item['BARCODE'],
							$userId
						);
					}
				}
			}
		}

		return $actions;
	}

	protected static function checkConductDocument(array $document): bool
	{
		if (!parent::checkConductDocument($document))
		{
			return false;
		}

		if (!static::checkStoreDestination($document))
		{
			return false;
		}

		return true;
	}

	protected static function checkCancellationDocument(array $document): bool
	{
		if (!parent::checkCancellationDocument($document))
		{
			return false;
		}

		if (!static::checkStoreDestination($document))
		{
			return false;
		}

		return true;
	}

	protected static function getStoreDestinationErrors(): array
	{
		return [
			'ERR_ABSENT' => [
				'CATALOG_STORE_DOCS_ERR_ARRIVAL_STORE_DESTINATION_IS_ABSENT_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_ARRIVAL_STORE_DESTINATION_IS_ABSENT_PRODUCT_LIST_EXT',
			],
			'ERR_UNKNOWN' => [
				'CATALOG_STORE_DOCS_ERR_ARRIVAL_STORE_DESTINATION_IS_UNKNOWN_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_ARRIVAL_STORE_DESTINATION_IS_UNKNOWN_PRODUCT_LIST',
			],
			'ERR_DISABLED' => [
				'CATALOG_STORE_DOCS_ERR_ARRIVAL_STORE_DESTINATION_IS_DISABLE_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_ARRIVAL_STORE_DESTINATION_IS_DISABLE_PRODUCT_LIST',
			],
		];
	}
}

class CCatalogStoreAdjustmentDocs extends CCatalogArrivalDocs
{
	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getDocumentSpecificFields(): array
	{
		return [
			'CURRENCY' => ['required' => 'Y'],
			'TOTAL' => ['required' => 'N'],
		];
	}

	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getElementFields(): array
	{
		return [
			'AMOUNT' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_ARRIVAL_AMOUNT'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_ARRIVAL_AMOUNT'),
			],
			'NET_PRICE' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_PURCHASING_PRICE'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_PURCHASING_PRICE'),
			],
			'BASE_PRICE' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_PRICE'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_PRICE'),
			],
			'STORE_TO' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_STORE_TO'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_STORE_TO'),
			],
			'BAR_CODE' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_BARCODE'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_BARCODE'),
			],
			'TOTAL' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_TOTAL'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_TOTAL'),
			],
		];
	}

	public static function getTypeId(): string
	{
		return StoreDocumentTable::TYPE_STORE_ADJUSTMENT;
	}

	protected static function checkParamsForConduction(int $documentId, int $userId, ?string $currency, int $contractorId): bool
	{
		if (!CCatalogDocsTypes::checkParamsForConduction($documentId, $userId, $currency, $contractorId))
		{
			return false;
		}

		return true;
	}

	protected static function getStoreDestinationErrors(): array
	{
		return [
			'ERR_ABSENT' => [
				'CATALOG_STORE_DOCS_ERR_ADJUSTMENT_STORE_DESTINATION_IS_ABSENT_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_ADJUSTMENT_STORE_DESTINATION_IS_ABSENT_PRODUCT_LIST_EXT',
			],
			'ERR_UNKNOWN' => [
				'CATALOG_STORE_DOCS_ERR_ADJUSTMENT_STORE_DESTINATION_IS_UNKNOWN_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_ADJUSTMENT_STORE_DESTINATION_IS_UNKNOWN_PRODUCT_LIST',
			],
			'ERR_DISABLED' => [
				'CATALOG_STORE_DOCS_ERR_ADJUSTMENT_STORE_DESTINATION_IS_DISABLE_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_ADJUSTMENT_STORE_DESTINATION_IS_DISABLE_PRODUCT_LIST',
			],
		];
	}
}

class CCatalogMovingDocs extends CCatalogDocsTypes
{
	public static function getDocumentSpecificFields(): array
	{
		return [
			'DOC_NUMBER' => ['required' => 'N'],
			'DATE_DOCUMENT' => ['required' => 'N'],
		];
	}

	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getElementFields(): array
	{
		return [
			'AMOUNT' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_AMOUNT'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_AMOUNT'),
			],
			'STORE_TO' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_MOVING_STORE_TO'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_MOVING_STORE_TO'),
			],
			'BAR_CODE' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_BARCODE'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_BARCODE'),
			],
			'STORE_FROM' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_MOVING_STORE_FROM'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_MOVING_STORE_FROM'),
			],
		];
	}

	public static function getTypeId(): string
	{
		return StoreDocumentTable::TYPE_MOVING;
	}

	/**
	 * @inheritDoc
	 */
	protected static function getDocumentActions(string $action, array $document, int $userId): ?array
	{
		$actions = parent::getDocumentActions($action, $document, $userId);
		if ($actions === null)
		{
			return null;
		}

		$elements = $document['ELEMENTS'] ?? [];
		foreach ($elements as $productId => $element)
		{
			$positions = (array)($element['POSITIONS'] ?? []);
			foreach ($positions as $item)
			{
				if ($action === self::ACTION_CONDUCTION)
				{
					if (State::isProductBatchMethodSelected())
					{
						$actions[] = new MoveStoreBatchAction(
							$item['STORE_FROM'],
							$item['STORE_TO'],
							$item['PRODUCT_ID'],
							$item['AMOUNT'],
							$item['ROW_ID']
						);
					}

					$actions[] = new DecreaseStoreQuantityAction(
						$item['STORE_FROM'],
						$item['PRODUCT_ID'],
						$item['AMOUNT'],
						$document['DOC_TYPE']
					);
					$actions[] = new IncreaseStoreQuantityAction(
						$item['STORE_TO'],
						$item['PRODUCT_ID'],
						$item['AMOUNT']
					);
				}
				elseif ($action === self::ACTION_CANCEL)
				{
					if (State::isProductBatchMethodSelected())
					{
						$actions[] = new ReduceStoreBatchAmountAction($item['ROW_ID']);
						$actions[] = new ReturnStoreBatchAction($item['ROW_ID']);
					}

					$actions[] = new IncreaseStoreQuantityAction(
						$item['STORE_FROM'],
						$item['PRODUCT_ID'],
						$item['AMOUNT']
					);
					$actions[] = new DecreaseStoreQuantityAction(
						$item['STORE_TO'],
						$item['PRODUCT_ID'],
						$item['AMOUNT'],
						$document['DOC_TYPE']
					);
				}
			}

			$barcodes = (array)($element['BARCODES'] ?? []);
			foreach ($barcodes as $item)
			{
				if ($action === self::ACTION_CONDUCTION)
				{
					if ($element['BARCODE_MULTI'] === 'N')
					{
						$storeId = 0;
					}
					else
					{
						$storeId = $item['STORE_TO'];
						if (!$storeId)
						{
							$rowId = $item['DOCUMENT_ROW_ID'];
							$storeId = $positions[$rowId]['STORE_TO'] ?? null;
						}
					}

					$actions[] = new AddStoreBarcodeAction(
						$storeId,
						$productId,
						$item['BARCODE'],
						$userId
					);
				}
				elseif ($action === self::ACTION_CANCEL)
				{
					if ($element['BARCODE_MULTI'] === 'N')
					{
						$storeId = 0;
					}
					else
					{
						$storeId = $item['STORE_FROM'];
						if (!$storeId)
						{
							$rowId = $item['DOCUMENT_ROW_ID'];
							$storeId = $positions[$rowId]['STORE_FROM'] ?? null;
						}
					}

					$actions[] = new AddStoreBarcodeAction(
						$storeId,
						$productId,
						$item['BARCODE'],
						$userId
					);
				}
			}
		}

		return $actions;
	}

	protected static function checkConductDocument(array $document): bool
	{
		if (!parent::checkConductDocument($document))
		{
			return false;
		}

		if (!static::checkStoreSource($document))
		{
			return false;
		}

		if (!static::checkStoreDestination($document))
		{
			return false;
		}

		// check dest and source stores
		$elements = (array)($document['ELEMENTS'] ?? []);
		foreach ($elements as $element)
		{
			$positions = (array)($element['POSITIONS'] ?? []);
			foreach ($positions as $position)
			{
				if ((int)$position['STORE_TO'] === (int)$position['STORE_FROM'])
				{
					self::setErrors([
						Loc::getMessage('CATALOG_STORE_DOCS_ERR_STORE_FROM_EQUALS_STORE_TO')
					]);
					return false;
				}
			}
		}

		return true;
	}

	protected static function checkCancellationDocument(array $document): bool
	{
		if (!parent::checkConductDocument($document))
		{
			return false;
		}

		if (!static::checkStoreSource($document))
		{
			return false;
		}

		if (!static::checkStoreDestination($document))
		{
			return false;
		}

		return true;
	}

	protected static function getStoreDestinationErrors(): array
	{
		return [
			'ERR_ABSENT' => [
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_DESTINATION_IS_ABSENT_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_DESTINATION_IS_ABSENT_PRODUCT_LIST_EXT',
			],
			'ERR_UNKNOWN' => [
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_DESTINATION_IS_UNKNOWN_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_DESTINATION_IS_UNKNOWN_PRODUCT_LIST',
			],
			'ERR_DISABLED' => [
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_DESTINATION_IS_DISABLE_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_DESTINATION_IS_DISABLE_PRODUCT_LIST',
			],
		];
	}

	protected static function getStoreSourceErrors(): array
	{
		return [
			'ERR_ABSENT' => [
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_FROM_IS_ABSENT_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_FROM_IS_ABSENT_PRODUCT_LIST',
			],
			'ERR_UNKNOWN' => [
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_FROM_IS_UNKNOWN_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_FROM_IS_UNKNOWN_PRODUCT_LIST',
			],
			'ERR_DISABLED' => [
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_FROM_IS_DISABLE_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_MOVING_STORE_FROM_IS_DISABLE_PRODUCT_LIST',
			],
		];
	}
}

class CCatalogReturnsDocs extends CCatalogDocsTypes
{
	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getElementFields(): array
	{
		return [
			'AMOUNT' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_AMOUNT'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_AMOUNT'),
			],
			'STORE_TO' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_STORE_TO'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_STORE_TO'),
			],
			'BAR_CODE' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_BARCODE'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_BARCODE'),
			],
		];
	}

	public static function getTypeId(): string
	{
		return StoreDocumentTable::TYPE_RETURN;
	}

	/**
	 * @inheritDoc
	 */
	protected static function getDocumentActions(string $action, array $document, int $userId): ?array
	{
		$actions = parent::getDocumentActions($action, $document, $userId);
		if ($actions === null)
		{
			return null;
		}

		$elements = $document['ELEMENTS'] ?? [];
		foreach ($elements as $productId => $element)
		{
			$positions = (array)($element['POSITIONS'] ?? []);
			foreach ($positions as $item)
			{
				if ($action === self::ACTION_CONDUCTION)
				{
					$actions[] = new IncreaseStoreQuantityAction(
						$item['STORE_TO'],
						$item['PRODUCT_ID'],
						$item['AMOUNT']
					);
					if (State::isProductBatchMethodSelected())
					{
						$actions[] = new UpsertStoreBatchAction(
							$item['STORE_TO'],
							$item['PRODUCT_ID'],
							$item['AMOUNT'],
							$item['ROW_ID'],
							$item['PURCHASING_PRICE'],
							$item['PURCHASING_CURRENCY'],
						);
					}
				}
				elseif ($action === self::ACTION_CANCEL)
				{
					if (State::isProductBatchMethodSelected())
					{
						$actions[] = new ReduceStoreBatchAmountAction($item['ROW_ID']);
					}

					$actions[] = new DecreaseStoreQuantityAction(
						$item['STORE_TO'],
						$item['PRODUCT_ID'],
						$item['AMOUNT'],
						$document['DOC_TYPE']
					);
				}
			}

			$barcodes = (array)($element['BARCODES'] ?? []);
			foreach ($barcodes as $item)
			{
				$storeId = $item['STORE_TO'];
				if (!$storeId)
				{
					$rowId = $item['DOCUMENT_ROW_ID'];
					$storeId = $positions[$rowId]['STORE_TO'] ?? null;
				}

				if ($element['BARCODE_MULTI'] === 'N')
				{
					$storeId = 0;
				}

				if ($action === self::ACTION_CONDUCTION)
				{
					$actions[] = new AddStoreBarcodeAction(
						$storeId,
						$productId,
						$item['BARCODE'],
						$userId
					);
				}
				elseif ($action === self::ACTION_CANCEL)
				{
					$actions[] = new DeleteStoreBarcodeAction(
						$storeId,
						$productId,
						$item['BARCODE'],
						$userId
					);
				}
			}
		}

		return $actions;
	}

	protected static function checkConductDocument(array $document): bool
	{
		if (!parent::checkConductDocument($document))
		{
			return false;
		}

		if (!static::checkStoreDestination($document))
		{
			return false;
		}

		return true;
	}

	protected static function checkCancellationDocument(array $document): bool
	{
		if (!parent::checkConductDocument($document))
		{
			return false;
		}

		if (!static::checkStoreDestination($document))
		{
			return false;
		}

		return true;
	}

	protected static function getStoreDestinationErrors(): array
	{
		return [
			'ERR_ABSENT' => [
				'CATALOG_STORE_DOCS_ERR_RETURNS_STORE_DESTINATION_IS_ABSENT_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_RETURNS_STORE_DESTINATION_IS_ABSENT_PRODUCT_LIST_EXT',
			],
			'ERR_UNKNOWN' => [
				'CATALOG_STORE_DOCS_ERR_RETURNS_STORE_DESTINATION_IS_UNKNOWN_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_RETURNS_STORE_DESTINATION_IS_UNKNOWN_PRODUCT_LIST',
			],
			'ERR_DISABLED' => [
				'CATALOG_STORE_DOCS_ERR_RETURNS_STORE_DESTINATION_IS_DISABLE_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_RETURNS_STORE_DESTINATION_IS_DISABLE_PRODUCT_LIST',
			],
		];
	}
}

class CCatalogDeductDocs extends CCatalogDocsTypes
{
	public static function getDocumentSpecificFields(): array
	{
		return [
			'DOC_NUMBER' => ['required' => 'N'],
			'DATE_DOCUMENT' => ['required' => 'N'],
		];
	}

	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getElementFields(): array
	{
		return [
			'AMOUNT' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_AMOUNT'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_AMOUNT'),
			],
			'BAR_CODE' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_BARCODE'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_BARCODE'),
			],
			'STORE_FROM' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_DEDUCT_STORE_FROM'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_DEDUCT_STORE_FROM'),
			],
		];
	}

	public static function getTypeId(): string
	{
		return StoreDocumentTable::TYPE_DEDUCT;
	}

	/**
	 * @inheritDoc
	 */
	protected static function getDocumentActions(string $action, array $document, int $userId): ?array
	{
		$actions = parent::getDocumentActions($action, $document, $userId);
		if ($actions === null)
		{
			return null;
		}

		$elements = $document['ELEMENTS'] ?? [];
		foreach ($elements as $productId => $element)
		{
			$positions = (array)($element['POSITIONS'] ?? []);
			foreach ($positions as $item)
			{
				if ($action === self::ACTION_CONDUCTION)
				{
					if (State::isProductBatchMethodSelected())
					{
						$actions[] = new WriteOffStoreBatchAction(
							$item['ROW_ID'],
							$item['PRODUCT_ID'],
							$item['AMOUNT']
						);
					}
					$actions[] = new DecreaseStoreQuantityAction(
						$item['STORE_FROM'],
						$item['PRODUCT_ID'],
						$item['AMOUNT'],
						$document['DOC_TYPE']
					);
				}
				elseif ($action === self::ACTION_CANCEL)
				{
					$actions[] = new IncreaseStoreQuantityAction(
						$item['STORE_FROM'],
						$item['PRODUCT_ID'],
						$item['AMOUNT']
					);
					if (State::isProductBatchMethodSelected())
					{
						$actions[] = new ReturnStoreBatchAction($item['ROW_ID']);
					}
				}
			}

			$barcodes = (array)($element['BARCODES'] ?? []);
			foreach ($barcodes as $item)
			{
				$storeId = $item['STORE_TO'];
				if (!$storeId)
				{
					$rowId = $item['DOCUMENT_ROW_ID'];
					$storeId = $positions[$rowId]['STORE_TO'] ?? null;
				}

				if ($element['BARCODE_MULTI'] === 'N')
				{
					$storeId = 0;
				}

				if ($action === self::ACTION_CONDUCTION)
				{
					$actions[] = new DeleteStoreBarcodeAction(
						$storeId,
						$productId,
						$item['BARCODE'],
						$userId
					);
				}
				elseif ($action === self::ACTION_CANCEL)
				{
					$actions[] = new AddStoreBarcodeAction(
						$storeId,
						$productId,
						$item['BARCODE'],
						$userId
					);
				}
			}
		}

		return $actions;
	}

	protected static function checkConductDocument(array $document): bool
	{
		if (!parent::checkConductDocument($document))
		{
			return false;
		}

		if (!static::checkStoreSource($document))
		{
			return false;
		}

		return true;
	}

	protected static function checkCancellationDocument(array $document): bool
	{
		if (!parent::checkConductDocument($document))
		{
			return false;
		}

		if (!static::checkStoreSource($document))
		{
			return false;
		}

		return true;
	}

	protected static function getStoreSourceErrors(): array
	{
		return [
			'ERR_ABSENT' => [
				'CATALOG_STORE_DOCS_ERR_DEDUCT_STORE_SOURCE_IS_ABSENT_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_DEDUCT_STORE_SOURCE_IS_ABSENT_PRODUCT_LIST_EXT',
			],
			'ERR_UNKNOWN' => [
				'CATALOG_STORE_DOCS_ERR_DEDUCT_STORE_SOURCE_IS_UNKNOWN_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_DEDUCT_STORE_SOURCE_IS_UNKNOWN_PRODUCT_LIST',
			],
			'ERR_DISABLED' => [
				'CATALOG_STORE_DOCS_ERR_DEDUCT_STORE_SOURCE_IS_DISABLE_PRODUCT',
				'CATALOG_STORE_DOCS_ERR_DEDUCT_STORE_SOURCE_IS_DISABLE_PRODUCT_LIST',
			],
		];
	}
}

class CCatalogUnReservedDocs extends CCatalogDocsTypes
{
	/** The method returns an array of fields needed for this type of document.
	 * @return array
	 */
	public static function getElementFields(): array
	{
		return [
			'AMOUNT' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_AMOUNT'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_AMOUNT'),
			],
			'RESERVED' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_RESERVED'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_COMMON_RESERVED'),
			],
			'STORE_FROM' => [
				'required' => 'Y',
				'name' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_UNRESERVED_STORE_FROM'),
				'title' => Loc::getMessage('CATALOG_STORE_DOCS_ELEMENT_FIELD_UNRESERVED_STORE_FROM'),
			],
		];
	}

	public static function getTypeId(): string
	{
		return StoreDocumentTable::TYPE_UNDO_RESERVE;
	}

	/**
	 * @inheritDoc
	 */
	protected static function getDocumentActions(string $action, array $document, int $userId): ?array
	{
		$actions = parent::getDocumentActions($action, $document, $userId);
		if ($actions === null)
		{
			return null;
		}

		$elements = $document['ELEMENTS'] ?? [];
		foreach ($elements as $productId => $element)
		{
			$positions = (array)($element['POSITIONS'] ?? []);
			foreach ($positions as $item)
			{
				if ($action === self::ACTION_CONDUCTION)
				{
					$actions[] = new UnReserveStoreProductAction(
						$item['STORE_FROM'],
						$item['PRODUCT_ID'],
						$item['AMOUNT']
					);
				}
				elseif ($action === self::ACTION_CANCEL)
				{
					$actions[] = new ReserveStoreProductAction(
						$item['STORE_FROM'],
						$item['PRODUCT_ID'],
						$item['AMOUNT']
					);
				}
			}
		}

		return $actions;
	}
}
