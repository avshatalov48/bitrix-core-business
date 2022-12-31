<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\Config;
use Bitrix\Catalog\StoreTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Main\Component\ParameterSigner;

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\Entity;

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/catalog/install/components/bitrix/catalog.productcard.store.amount/class.php');

class StoreAmount
{
	protected $entityId;
	protected $variationIds;

	protected $storesCount;
	protected $storeTotal;

	protected $measures = [];
	protected $defaultMeasure;

	protected const GRID_NAME = 'productcard_store_amount';

	public function __construct(int $entityId)
	{
		$this->entityId = $entityId;
	}

	public function getEntityId(): int
	{
		return $this->entityId;
	}

	public function getVariationIds(): array
	{
		if (!isset($this->variationIds))
		{
			$productId = $this->getEntityId();
			$variations = \CCatalogSku::getOffersList($productId);
			if (isset($variations[$productId]))
			{
				$this->variationIds = array_column($variations[$productId], 'ID');
			}
			else
			{
				$this->variationIds = [$productId];
			}
		}

		return $this->variationIds;
	}

	public function getStoreAmountGridId(): string
	{
		return self::GRID_NAME . '_' . $this->getEntityId();
	}

	public function getStoresCount(): int
	{
		if (!isset($this->storesCount))
		{
			$filter = [
				'=PRODUCT_ID' => $this->getVariationIds(),
				[
					'LOGIC' => 'OR',
					'!=AMOUNT' => 0,
					'!=QUANTITY_RESERVED' => 0,
				],
				'=STORE.ACTIVE' => 'Y',
			];

			$filter = array_merge(
				$filter,
				AccessController::getCurrent()->getEntityFilter(
					ActionDictionary::ACTION_STORE_VIEW,
					StoreProductTable::class
				)
			);

			$this->storesCount = StoreProductTable::getList([
				'select' => ['CNT'],
				'filter' => $filter,
				'runtime' => [
					new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(DISTINCT(STORE_ID))')
				],
			])->fetch()['CNT'];
		}

		return $this->storesCount;
	}

	public function getProductStores(array $params = []): array
	{
		$stores = [];

		$offset = $params['offset'] ?? 0;
		$limit = $params['limit'] ?? null;

		$variationIds = $this->getVariationIds();
		$filter = [
			'=PRODUCT_ID' => $variationIds,
			'=STORE.ACTIVE' => 'Y',
		];

		$filter = array_merge(
			$filter,
			AccessController::getCurrent()->getEntityFilter(
				ActionDictionary::ACTION_STORE_VIEW,
				StoreProductTable::class
			)
		);

		$storeProductData = StoreProductTable::getList([
			'select' => ['SID_DISTINCT'],
			'filter' => $filter,
			'runtime' => [
				new \Bitrix\Main\Entity\ExpressionField('SID_DISTINCT', 'DISTINCT(STORE_ID)'),
			],
		])->fetchAll();

		$storeIds = array_column($storeProductData, 'SID_DISTINCT');
		$storeIds = array_slice($storeIds, $offset, $limit);

		$storeProductData = StoreProductTable::getList([
			'select' => [
				'STORE_ID',
				'PRODUCT_ID',
				'AMOUNT',
				'QUANTITY_RESERVED'
			],
			'filter' => [
				'=STORE_ID' => $storeIds,
				'=PRODUCT_ID' => $variationIds,
				'=STORE.ACTIVE' => 'Y',
			],
		])->fetchAll();

		$fetchedVariationIds = array_column($storeProductData, 'PRODUCT_ID');
		$variationsData = $this->getVariationsData($fetchedVariationIds);

		$variations = [];
		foreach ($variationsData as $variationData)
		{
			$variations[$variationData['ID']] = $variationData;
		}

		$storeIds = [];
		foreach ($storeProductData as $storeProduct)
		{
			$productData = $variations[$storeProduct['PRODUCT_ID']];

			if ((float)$storeProduct['QUANTITY_RESERVED'] === 0.0 && (float)$storeProduct['AMOUNT'] === 0.0)
			{
				continue;
			}

			$storeId = (int)$storeProduct['STORE_ID'];
			$amount = (float)$productData['PURCHASING_PRICE'] * (float)$storeProduct['AMOUNT'];

			$measureId = $productData['MEASURE'] ?? (int)$this->getDefaultMeasure()['ID'];
			$currency = $productData['PURCHASING_CURRENCY'];

			if (!isset($stores[$storeId]))
			{
				$storeIds[] = $storeId;

				$stores[$storeId] = [
					'ID' => $storeId,
					'QUANTITY' => [],
					'AMOUNT' => [],
				];
			}

			// MEASURE EXIST CHECK
			if (!isset($stores[$storeId]['QUANTITY'][$measureId]))
			{
				$stores[$storeId]['QUANTITY'][$measureId] = [
					'QUANTITY_COMMON' => (float)$storeProduct['AMOUNT'],
					'QUANTITY_RESERVED' => (float)$storeProduct['QUANTITY_RESERVED'],
					'MEASURE_ID' => $measureId,
				];
			}
			else
			{
				$stores[$storeId]['QUANTITY'][$measureId]['QUANTITY_COMMON'] += (float)$storeProduct['AMOUNT'];
				$stores[$storeId]['QUANTITY'][$measureId]['QUANTITY_RESERVED'] += (float)$storeProduct['QUANTITY_RESERVED'];
			}

			// CURRENCY EXIST CHECK
			if (!isset($stores[$storeId]['AMOUNT'][$currency]))
			{
				$stores[$storeId]['AMOUNT'][$currency] = [
					'AMOUNT' => $amount,
					'CURRENCY' => $currency,
				];
			}
			else
			{
				$stores[$storeId]['AMOUNT'][$currency]['AMOUNT'] += $amount;
			}
		}

		$storesInfo = $this->getStoresInfo($storeIds);

		foreach ($storesInfo as $storeInfo)
		{
			if (isset($stores[(int)$storeInfo['ID']]))
			{
				$stores[(int)$storeInfo['ID']]['TITLE'] = $storeInfo['TITLE'];
			}
		}

		return $stores;
	}

	/**
	 * Return array of information of a stores
	 * @param array $storeIds IDs of stores
	 * @return array
	 */
	protected function getStoresInfo(array $storeIds): array
	{
		if (count($storeIds) > 0)
		{
			return StoreTable::getList([
					'select' => ['ID', 'TITLE'],
					'filter' => [
						'=ID' => $storeIds,
						'ACTIVE' => 'Y',
					],
				])
				->fetchAll()
			;
		}

		return [];
	}

	protected function getVariationsData(array $variationIds): array
	{
		return ProductTable::getList([
			'select' => [
				'ID',
				'PURCHASING_PRICE',
				'PURCHASING_CURRENCY',
				'MEASURE',
			],

			'filter' => ['=ID' => $variationIds],
		])->fetchAll();
	}

	/**
	 * Form and prepare data to render in total info page block
	 * @return array
	 */
	public function getTotalData(): array
	{
		$storeTotal = $this->getStoreTotal();

		$quantity = '';
		$quantityCommon = '';
		$quantityReserved = '';

		foreach ($storeTotal['QUANTITY'] as $storeQuantity)
		{
			$measureSymbol = htmlspecialcharsbx($this->getMeasure($storeQuantity['MEASURE_ID']));

			$measureInfo =
				"<span class='total-info'>
					<span class='total-info-value'>{$storeQuantity['QUANTITY']}</span>
					{$measureSymbol}
				</span>";

			$measureCommonInfo =
				"<span class='total-info'>
					<span class='total-info-value'>{$storeQuantity['QUANTITY_COMMON']}</span>
					{$measureSymbol}
				</span>";

			$measureReservedInfo =
				"<span class='total-info'>
					<span class='total-info-value'>{$storeQuantity['QUANTITY_RESERVED']}</span>
					{$measureSymbol}
				</span>";

			$quantity .= $measureInfo . '<br>';
			$quantityCommon .= $measureCommonInfo . '<br>';
			$quantityReserved .= $measureReservedInfo . '<br>';
		}

		$amount = '';

		foreach ($storeTotal['AMOUNT'] as $storeAmount)
		{
			if (isset($storeAmount['CURRENCY']))
			{
				$amountValue =
					"<span class='total-info-value'>"
					. \CCurrencyLang::CurrencyFormat($storeAmount['AMOUNT'], $storeAmount['CURRENCY'], false)
					. "</span>";

				$amountBlock =
					"<span class='total-info'>"
					. \CCurrencyLang::getPriceControl(
						$amountValue,
						$storeAmount['CURRENCY']
					)
					. "</span>";

				$amount .= $amountBlock.'<br>';
			}

		}

		return [
			"QUANTITY" => $quantity,
			"QUANTITY_COMMON" => $quantityCommon,
			"QUANTITY_RESERVED" => $quantityReserved,
			"AMOUNT" => $amount,
		];
	}

	/**
	 *  Check exists and return array of total information about product stores
	 * @return array
	 */
	protected function getStoreTotal(): array
	{
		if (!isset($this->storeTotal))
		{
			$this->fillStoreTotal();
		}

		return $this->storeTotal;
	}

	/**
	 * Combine Amount and Quantity info from all stores to one array
	 */
	protected function fillStoreTotal(): void
	{
		$storeTotalData = [
			'QUANTITY' => [],
			'AMOUNT' => [],
		];

		if ($this->getStoresCount() > 0)
		{
			$variationsTotalData = $this->getVariationsTotalData($this->getVariationIds());
			foreach ($variationsTotalData as $variationData)
			{
				$variationData['MEASURE_ID'] = $variationData['MEASURE_ID'] ?? (int)$this->getDefaultMeasure()['ID'];

				if (!isset($storeTotalData['QUANTITY'][$variationData['MEASURE_ID']]))
				{
					$storeTotalData['QUANTITY'][$variationData['MEASURE_ID']] = [
						'QUANTITY' => 0.0,
						'QUANTITY_COMMON' => 0.0,
						'QUANTITY_RESERVED' => 0.0,
						'MEASURE_ID' => $variationData['MEASURE_ID'],
					];
				}

				$quantityValue = (float)$variationData['QUANTITY_COMMON'] - (float)$variationData['QUANTITY_RESERVED'];

				$storeTotalData['QUANTITY'][$variationData['MEASURE_ID']]['QUANTITY'] += $quantityValue;
				$storeTotalData['QUANTITY'][$variationData['MEASURE_ID']]['QUANTITY_COMMON'] += (float)$variationData['QUANTITY_COMMON'];
				$storeTotalData['QUANTITY'][$variationData['MEASURE_ID']]['QUANTITY_RESERVED'] += (float)$variationData['QUANTITY_RESERVED'];

				if (!isset($storeTotalData['AMOUNT'][$variationData['CURRENCY']]))
				{
					$storeTotalData['AMOUNT'][$variationData['CURRENCY']] = [
						'AMOUNT' => 0.0,
						'CURRENCY' => $variationData['CURRENCY'],
					];
				}
				$commonPrice = (float)$variationData['QUANTITY_COMMON'] * (float)$variationData['PRICE'];
				$storeTotalData['AMOUNT'][$variationData['CURRENCY']]['AMOUNT'] += $commonPrice;
			}
		}

		$this->storeTotal = $storeTotalData;
	}

	/**
	 * Get array of variations with a total stores data of it
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getVariationsTotalData(array $variationIds): array
	{
		$products = [];

		$filter = [
			'=PRODUCT_ID' => $variationIds,
			'=STORE.ACTIVE' => 'Y',
		];

		$filter = array_merge(
			$filter,
			AccessController::getCurrent()->getEntityFilter(
				ActionDictionary::ACTION_STORE_VIEW,
				StoreProductTable::class
			)
		);

		$commonProductData = StoreProductTable::getList([
			'select' => [
				'PRODUCT_ID',
				'MEASURE_ID' => 'PRODUCT.MEASURE',
				'PRICE' => 'PRODUCT.PURCHASING_PRICE',
				'CURRENCY' => 'PRODUCT.PURCHASING_CURRENCY',
				'QUANTITY_COMMON',
			],
			'filter' => $filter,
			'group' => ['PRODUCT_ID'],
			'runtime' => [
				new Entity\ExpressionField('QUANTITY_COMMON', 'SUM(AMOUNT)'),
			],
		])->fetchAll();

		foreach ($commonProductData as $productData)
		{
			$products[$productData['PRODUCT_ID']] = $productData;
		}

		$productQuantityReservedData = StoreProductTable::getList([
			'select' => [
				'PRODUCT_ID',
				'RESERVED_AMOUNT',
			],
			'filter' => [
				'=PRODUCT_ID' => $variationIds,
				'=STORE.ACTIVE' => 'Y',
			],
			'group' => ['PRODUCT_ID'],
			'runtime' => [
				new Entity\ExpressionField('RESERVED_AMOUNT', 'SUM(QUANTITY_RESERVED)'),
			],
		])->fetchAll();

		foreach ($productQuantityReservedData as $productData)
		{
			$products[$productData['PRODUCT_ID']]['QUANTITY_RESERVED'] = $productData['RESERVED_AMOUNT'];
		}

		return $products;
	}

	/**
	 * Return measure symbol from ID
	 *
	 * @param $measureId
	 * @return string
	 */
	public function getMeasure(?int $measureId): string
	{
		if ($measureId === null)
		{
			return $this->getDefaultMeasure()['SYMBOL'];
		}

		if (!isset($this->measures[$measureId]))
		{
			$measure = \CCatalogMeasure::getList([], ['=ID' => $measureId])->Fetch();

			if ($measure)
			{
				$this->measures[$measureId] = $measure['SYMBOL'];
			}
			else
			{
				$this->measures[$measureId] = '';
			}
		}

		return $this->measures[$measureId];
	}

	/**
	 * Return default measure value
	 * @return array
	 */
	protected function getDefaultMeasure(): array
	{
		if (!isset($this->defaultMeasure))
		{
			$fetchedMeasure = \CCatalogMeasure::getList([], ['=IS_DEFAULT' => 'Y'])->Fetch();
			if ($fetchedMeasure)
			{
				$this->defaultMeasure = $fetchedMeasure;
			}
			else
			{
				// default symbol not exist catch
				$this->defaultMeasure = [
					'ID' => 0,
					'SYMBOL' => '',
				];
			}
		}

		return $this->defaultMeasure;
	}

	public function getStoreAmountSignedParameters(): string
	{
		return ParameterSigner::signParameters(
			$this->getStoreAmountComponentName(),
			$this->getStoreAmountParameters()
		);
	}

	protected function getStoreAmountComponentName(): string
	{
		return 'bitrix:catalog.productcard.store.amount';
	}

	protected function getStoreAmountParameters(): array
	{
		return [
			'ENTITY_ID' => $this->getEntityId(),
		];
	}
}