<?php

namespace Bitrix\Catalog\Grid;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Crm;
use Bitrix\Main\ORM;
use Bitrix\Sale;

class ProductAction
{
	public const SET_FIELD = 'product_field';
	public const CHANGE_PRICE = 'change_price';
	public const CONVERT_PRODUCT_TO_SERVICE = 'convert_to_service';
	public const CONVERT_SERVICE_TO_PRODUCT = 'convert_to_product';

	public static function updateSectionList(int $iblockId, array $sections, array $fields): Main\Result
	{
		$result = new Main\Result();

		if ($iblockId <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_IBLOCK_ID')
			));
			return $result;
		}

		$catalog = \CCatalogSku::GetInfoByIBlock($iblockId);
		if (empty($catalog))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_CATALOG')
			));
			return $result;
		}

		if (empty($fields))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_EMPTY_FIELDS')
			));
			return $result;
		}

		$filter = [];
		$allowedTypes = static::getAllowedProductTypes($catalog, $fields);
		if (empty($allowedTypes))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_FIELDS')
			));
			return $result;
		}
		$filter['@TYPE'] = $allowedTypes;
		unset($allowedTypes);

		$sectionElements = self::getSectionProducts($iblockId, $sections, $filter);
		if (empty($sectionElements))
		{
			return $result;
		}

		$sectionIdList = array_keys($sectionElements);
		$sectionNames = [];
		$iterator = Iblock\SectionTable::getList([
			'select' => [
				'ID',
				'NAME',
			],
			'filter' => [
				'@ID' => $sectionIdList,
				'=IBLOCK_ID' => $iblockId,
			],
			'order' => ['ID' => 'ASC'],
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$sectionNames[$row['ID']] = $row['NAME'];
		}
		unset($row, $iterator);

		foreach ($sectionIdList as $sectionId)
		{
			$elementResult = static::updateElementList(
				$iblockId,
				$sectionElements[$sectionId],
				$fields
			);
			if (!$elementResult->isSuccess())
			{
				$result->addError(new Main\Error(
					Loc::getMessage(
						'BX_CATALOG_PRODUCT_ACTION_ERR_SECTION_PRODUCTS_UPDATE',
						['#ID#' => $sectionId, '#NAME#' => $sectionNames[$sectionId]]
					),
					$sectionId
				));
			}
		}
		unset($sectionId);
		unset($sectionNames, $sectionIdList, $sectionElements);

		return $result;
	}

	public static function updateElementList(int $iblockId, array $elementIds, array $fields): Main\Result
	{
		$result = new Main\Result();

		if ($iblockId <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_IBLOCK_ID')
			));
			return $result;
		}
		Main\Type\Collection::normalizeArrayValuesByInt($elementIds, true);
		if (empty($elementIds))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_EMPTY_ELEMENTS')
			));
			return $result;
		}
		if (empty($fields))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_EMPTY_FIELDS')
			));
			return $result;
		}
		$catalog = \CCatalogSku::GetInfoByIBlock($iblockId);
		if (empty($catalog))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_CATALOG')
			));
			return $result;
		}

		$allowedTypes = static::getAllowedProductTypes($catalog, $fields);
		if (empty($allowedTypes))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_FIELDS')
			));
			return $result;
		}

		$productResult = self::getProductListByTypeForModify($elementIds, $allowedTypes);
		$data = $productResult->getData();
		$newProducts = $data['NEW_PRODUCT_IDS'] ?? [];
		$existProducts = $data['EXIST_PRODUCT_IDS'] ?? [];
		unset($data);
		if (!$productResult->isSuccess())
		{
			$result->addErrors($productResult->getErrors());
		}
		unset($productResult);

		if (empty($newProducts) && empty($existProducts))
		{
			return $result;
		}

		$data = [
			'fields' => $fields,
			'external_fields' => [
				'IBLOCK_ID' => $iblockId
			]
		];
		foreach ($existProducts as $id)
		{
			$elementResult = Catalog\Model\Product::update($id, $data);
			if (!$elementResult->isSuccess())
			{
				$result->addError(new Main\Error(
					implode('; ', $elementResult->getErrorMessages()),
					$id
				));
			}
		}
		foreach ($newProducts as $id)
		{
			$data['fields']['ID'] = $id;
			$elementResult = Catalog\Model\Product::add($data);
			if (!$elementResult->isSuccess())
			{
				$result->addError(new Main\Error(
					implode('; ', $elementResult->getErrorMessages()),
					$id
				));
			}
		}
		unset($elementResult, $id, $data);
		unset($newProducts, $existProducts);
		unset($blackList, $catalog);

		return $result;
	}

	protected static function getSectionProducts(int $iblockId, array $sections, array $filter): ?array
	{
		global $USER;

		if (!(isset($USER) && $USER instanceof \CUser))
		{
			return null;
		}

		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_EDIT))
		{
			return null;
		}

		if ($iblockId <= 0)
		{
			return null;
		}
		Main\Type\Collection::normalizeArrayValuesByInt($sections, false);
		if (empty($sections))
		{
			return null;
		}

		$filter['IBLOCK_ID'] = $iblockId;
		$filter['INCLUDE_SUBSECTIONS'] = 'Y';
		$filter['CHECK_PERMISSIONS'] = 'Y';
		$filter['MIN_PERMISSION'] = 'R';

		$dublicates = [];
		$result = [];
		foreach ($sections as $sectionId)
		{
			$result[$sectionId] = [];
			$elements = [];
			$filter['SECTION_ID'] = $sectionId;
			$iterator = \CIBlockElement::GetList(
				['ID' => 'ASC'],
				$filter,
				false,
				false,
				['ID']
			);
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['ID'];
				if (isset($dublicates[$id]))
				{
					continue;
				}
				$dublicates[$id] = true;
				$elements[] = $id;
			}
			unset($id, $row, $iterator);

			if (!empty($elements))
			{
				$operations = \CIBlockElementRights::UserHasRightTo(
					$iblockId,
					$elements,
					'',
					\CIBlockRights::RETURN_OPERATIONS
				);
				foreach ($elements as $elementId)
				{
					if (
						isset($operations[$elementId]['element_edit'])
						&& isset($operations[$elementId]['element_edit_price'])
					)
					{
						$result[$sectionId][] = $elementId;
					}
				}
				unset($elementId);
				unset($operations);
			}
			unset($elements);

			if (empty($result[$sectionId]))
			{
				unset($result[$sectionId]);
			}
		}
		unset($sectionId);
		unset($dublicates);

		return (!empty($result) ? $result : null);
	}

	public static function getAllowedProductTypes(array $catalog, array $fields): array
	{
		static $list = null;

		if (empty($fields))
		{
			return [];
		}

		if ($list === null)
		{
			$list = [
				'WEIGHT',
				'QUANTITY',
				'QUANTITY_TRACE',
				'CAN_BUY_ZERO',
				'VAT_INCLUDED',
				'VAT_ID',
				'SUBSCRIBE',
				'MEASURE',
				'PURCHASING_PRICE',
				'PURCHASING_CURRENCY',
			];
			$baseTypes = [
				Catalog\ProductTable::TYPE_PRODUCT,
				Catalog\ProductTable::TYPE_OFFER,
				CATALOG\ProductTable::TYPE_FREE_OFFER,
			];
			if (
				$catalog['CATALOG_TYPE'] === \CCatalogSku::TYPE_FULL
				&& Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y'
			)
			{
				$baseTypes[] = Catalog\ProductTable::TYPE_SKU;
			}
			$list = array_fill_keys($list, $baseTypes);
			unset($baseTypes);

			$list['VAT_INCLUDED'][] = Catalog\ProductTable::TYPE_SET;
			$list['VAT_ID'][] = Catalog\ProductTable::TYPE_SET;
			$list['SUBSCRIBE'][] = Catalog\ProductTable::TYPE_SET;

			$list += Catalog\Product\SystemField::getAllowedProductTypes();
		}

		$result = [];
		foreach (array_keys($fields) as $fieldName)
		{
			if (!isset($list[$fieldName]))
			{
				$result = [];
				break;
			}
			$result[] = $list[$fieldName];
		}

		if (!empty($result))
		{
			if (count($result) === 1)
			{
				$result = reset($result);
			}
			else
			{
				$check = array_shift($result);
				foreach ($result as $row)
				{
					$check = array_intersect($check, $row);
				}
				unset($row);
				$result = array_values($check);
				unset($check);
			}
			sort($result);
		}

		return $result;
	}

	public static function convertToServiceSectionList(int $iblockId, array $sections): Main\Result
	{
		return self::convertTypeSectionList($iblockId, $sections, Catalog\ProductTable::TYPE_PRODUCT);
	}

	public static function convertToProductSectionList(int $iblockId, array $sections): Main\Result
	{
		return self::convertTypeSectionList($iblockId, $sections, Catalog\ProductTable::TYPE_SERVICE);
	}

	public static function convertToServiceElementList(int $iblockId, array $elementIds): Main\Result
	{
		return self::convertTypeElementList($iblockId, $elementIds, Catalog\ProductTable::TYPE_PRODUCT);
	}

	public static function convertToProductElementList(int $iblockId, array $elementIds): Main\Result
	{
		return self::convertTypeElementList($iblockId, $elementIds, Catalog\ProductTable::TYPE_SERVICE);
	}

	private static function convertTypeSectionList(int $iblockId, array $sections, int $type): Main\Result
	{
		$result = new Main\Result();

		if ($iblockId <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_IBLOCK_ID')
			));

			return $result;
		}

		$catalog = \CCatalogSku::GetInfoByIBlock($iblockId);
		if (empty($catalog))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_CATALOG')
			));

			return $result;
		}

		$filter = [
			'=TYPE' => $type,
		];

		$sectionElements = self::getSectionProducts($iblockId, $sections, $filter);
		if (empty($sectionElements))
		{
			return $result;
		}

		$sectionIdList = array_keys($sectionElements);
		$sectionNames = [];
		$iterator = Iblock\SectionTable::getList([
			'select' => [
				'ID',
				'NAME',
			],
			'filter' => [
				'@ID' => $sectionIdList,
				'=IBLOCK_ID' => $iblockId,
			],
			'order' => ['ID' => 'ASC'],
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$sectionNames[$row['ID']] = $row['NAME'];
		}
		unset($row, $iterator);

		foreach ($sectionIdList as $sectionId)
		{
			$elementResult = self::convertTypeElementList(
				$iblockId,
				$sectionElements[$sectionId],
				$type
			);
			if (!$elementResult->isSuccess())
			{
				$result->addError(new Main\Error(
					Loc::getMessage(
						$type === Catalog\ProductTable::TYPE_PRODUCT
							? 'BX_CATALOG_PRODUCT_ACTION_ERR_SECTION_PRODUCTS_CONVERT'
							: 'BX_CATALOG_PRODUCT_ACTION_ERR_SECTION_SERVICES_CONVERT'
						,
						[
							'#ID#' => $sectionId,
							'#NAME#' => $sectionNames[$sectionId],
						]
					),
					$sectionId
				));
			}
			$data = $elementResult->getData();
			if (isset($data['CONVERT_COMPLETE']))
			{
				$result->setData(['CONVERT_COMPLETE' => 'Y']);
			}
		}
		unset($sectionId);
		unset($sectionNames, $sectionIdList, $sectionElements);

		return $result;
	}

	private static function convertTypeElementList(int $iblockId, array $elementIds, int $type): Main\Result
	{
		$result = new Main\Result();

		if ($iblockId <= 0)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_IBLOCK_ID')
			));

			return $result;
		}

		$catalog = \CCatalogSku::GetInfoByIBlock($iblockId);
		if (empty($catalog))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_CATALOG')
			));

			return $result;
		}

		Main\Type\Collection::normalizeArrayValuesByInt($elementIds, true);
		if (empty($elementIds))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_EMPTY_ELEMENTS')
			));

			return $result;
		}

		$productResult = self::getProductListByTypeForConversion($elementIds, $type);
		$data = $productResult->getData();
		$products = $data['PRODUCT_IDS'] ?? [];
		unset($data);
		if (!$productResult->isSuccess())
		{
			$result->addErrors($productResult->getErrors());
		}
		unset($productResult);
		if (empty($products))
		{
			return $result;
		}

		$inventoryResult = self::checkInventoryDocumentByProducts($products);
		$data = $inventoryResult->getData();
		$products = $data['PRODUCT_IDS'] ?? [];
		unset($data);
		if (!$inventoryResult->isSuccess())
		{
			$result->addErrors($inventoryResult->getErrors());
		}
		unset($inventoryResult);
		if (empty($products))
		{
			return $result;
		}

		$convertResult = self::convertCatalogType($products, $type);
		if (!$convertResult->isSuccess())
		{
			$result->addErrors($convertResult->getErrors());
		}
		$data = $convertResult->getData();
		if (isset($data['CONVERT_COMPLETE']))
		{
			$result->setData(['CONVERT_COMPLETE' => 'Y']);
		}

		return $result;
	}

	private static function getProductListByTypeForModify(array $elementIds, array $types): Main\Result
	{
		$result = new Main\Result();

		$types = array_fill_keys($types, true);

		$existList = [];
		$newList = array_fill_keys($elementIds, true);
		$errorList = [];

		foreach (array_chunk($elementIds, 500) as $pageIds)
		{
			$iterator = Catalog\ProductTable::getList([
				'select' => [
					'ID',
					'TYPE',
				],
				'filter' => [
					'@ID' => $pageIds,
				]
			]);
			while ($row = $iterator->fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$row['TYPE'] = (int)$row['TYPE'];
				unset($newList[$row['ID']]);
				if (isset($types[$row['TYPE']]))
				{
					$existList[] = $row['ID'];
				}
				else
				{
					$errorList[] = $row['ID'];
				}
			}
			unset($row, $iterator);
		}

		$result->setData([
			'EXIST_PRODUCT_IDS' => $existList,
			'NEW_PRODUCT_IDS' => array_values($newList),
		]);
		unset($existList, $newList);

		if (!empty($errorList))
		{
			$names = [];
			$iterator = Iblock\ElementTable::getList([
				'select' => [
					'ID',
					'NAME',
				],
				'filter' => [
					'@ID' => $errorList,
				],
			]);
			while ($row = $iterator->fetch())
			{
				$names[] = '[' . $row['ID'] . '] ' . $row['NAME'];
			}
			unset($row, $iterator);
			$result->addError(new Main\Error(
				Loc::getMessage(
					'BX_CATALOG_PRODUCT_ACTION_ERR_CANNOT_SET_FIELD_BY_TYPE',
					[
						'#NAMES#' => implode(', ', $names),
					]
				)
			));
			unset($names);
		}
		unset($errorList);

		return $result;
	}

	private static function getProductListByTypeForConversion(array $elementIds, int $type): Main\Result
	{
		$result = new Main\Result();

		$validList = [];
		$errorList = [];

		foreach (array_chunk($elementIds, 500) as $pageIds)
		{
			$iterator = Catalog\ProductTable::getList([
				'select' => [
					'ID',
					'TYPE',
				],
				'filter' => [
					'@ID' => $pageIds,
				]
			]);
			while ($row = $iterator->fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$row['TYPE'] = (int)$row['TYPE'];
				if ($row['TYPE'] === $type)
				{
					$validList[] = $row['ID'];
				}
				else
				{
					$errorList[] = $row['ID'];
				}
			}
			unset($row, $iterator);
		}

		$result->setData(['PRODUCT_IDS' => $validList]);
		unset($validList);
		if (!empty($errorList))
		{
			$names = [];
			$iterator = Iblock\ElementTable::getList([
				'select' => [
					'ID',
					'NAME',
				],
				'filter' => [
					'@ID' => $errorList,
				],
			]);
			while ($row = $iterator->fetch())
			{
				$names[] = '[' . $row['ID'] . '] ' . $row['NAME'];
			}
			unset($row, $iterator);
			if ($type === Catalog\ProductTable::TYPE_PRODUCT)
			{
				$result->addError(new Main\Error(
					Loc::getMessage(
						'BX_CATALOG_PRODUCT_ACTION_ERR_SELECTED_NOT_SIMPLE_PRODUCT',
						['#NAMES#' => implode(', ', $names)]
					)
				));
			}
			else
			{
				$result->addError(new Main\Error(
					Loc::getMessage(
						'BX_CATALOG_PRODUCT_ACTION_ERR_SELECTED_NOT_SERVICE',
						['#NAMES#' => implode(', ', $names)]
					)
				));
			}
			unset($names);
		}
		unset($errorList);

		return $result;
	}

	private static function checkInventoryDocumentByProducts(array $elementIds): Main\Result
	{
		$result = new Main\Result();
		if (!Catalog\Config\State::isUsedInventoryManagement())
		{
			$result->setData(['PRODUCT_IDS' => $elementIds]);

			return $result;
		}

		$validList = array_fill_keys($elementIds, true);
		$errorList = [];

		$query = new ORM\Query\Query(Catalog\StoreDocumentElementTable::getEntity());
		$query->setDistinct(true);
		$query->setSelect([
			'ELEMENT_ID',
			'NAME' => 'ELEMENT.NAME'
		]);
		$query->setFilter(['=DOCUMENT.STATUS' => 'Y']);

		foreach (array_chunk($elementIds, 500) as $pageIds)
		{
			$query->addFilter('@ELEMENT_ID', $pageIds);
			$iterator = $query->exec();
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['ELEMENT_ID'];
				unset($validList[$id]);
				$errorList[] = '[' . $row['ELEMENT_ID'] . '] ' . $row['NAME'];
			}
			unset($row, $iterator);
		}
		unset($pagesIds);
		unset($query);

		$result->setData(['PRODUCT_IDS' => array_keys($validList)]);
		if (!empty($errorList))
		{
			$result->addError(new Main\Error(
				Loc::getMessage(
					'BX_CATALOG_PRODUCT_ACTION_ERR_SELECTED_INVENTORY_PRODUCTS',
					['#NAMES#' => implode(', ', $errorList)]
				)
			));
		}

		return $result;
	}

	private static function convertCatalogType(array $elementIds, int $type): Main\Result
	{
		$result = new Main\Result();

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$sqlMain = 'update ' . $helper->quote(Catalog\ProductTable::getTableName());

		if ($type === Catalog\ProductTable::TYPE_PRODUCT)
		{
			$sqlMain .= ' set ' . $helper->quote('TYPE') . ' = ' . Catalog\ProductTable::TYPE_SERVICE
				. ', ' . $helper->quote('QUANTITY') . ' = ('
				. 'CASE WHEN ' . $helper->quote('AVAILABLE') . ' = \'Y\' THEN 1 ELSE 0 END'
				. '), ' . $helper->quote('QUANTITY_RESERVED') . ' = 0'
				. ', ' . $helper->quote('QUANTITY_TRACE') . ' = \'N\''
				. ', ' . $helper->quote('CAN_BUY_ZERO') . ' = \'Y\''
				. ', ' . $helper->quote('NEGATIVE_AMOUNT_TRACE') . ' = \'Y\''
			;
		}
		else
		{
			$available = Catalog\ProductTable::calculateAvailable([
				'QUANTITY' => 0,
				'QUANTITY_TRACE' => Catalog\ProductTable::STATUS_DEFAULT,
				'CAN_BUY_ZERO' => Catalog\ProductTable::STATUS_DEFAULT,
			]);
			$sqlMain .= ' set ' . $helper->quote('TYPE') . ' = ' . Catalog\ProductTable::TYPE_PRODUCT
				. (Catalog\Config\State::isUsedInventoryManagement()
						? ', ' . $helper->quote('AVAILABLE') . ' = \'' . $helper->forSql($available) . '\', '
								. $helper->quote('QUANTITY') . ' = 0'
						: ''
					)
				. ', ' . $helper->quote('QUANTITY_TRACE') . ' = \'D\''
				. ', ' . $helper->quote('CAN_BUY_ZERO') . ' = \'D\''
				. ', ' . $helper->quote('NEGATIVE_AMOUNT_TRACE') . ' = \'D\''
			;
		}

		$sqlMain .= ', ' . $helper->quote('TIMESTAMP_X') . ' = ' . $helper->quote('TIMESTAMP_X')
			. ' where ID in ('
		;

		$sqlStores = 'delete from ' . $helper->quote(Catalog\StoreProductTable::getTableName())
			. ' where PRODUCT_ID in ('
		;

		foreach (array_chunk($elementIds, 500) as $pageIds)
		{
			$list = implode(',', $pageIds);
			$connection->query($sqlMain . $list . ')');

			if ($type === Catalog\ProductTable::TYPE_PRODUCT)
			{
				$connection->query($sqlStores . $list . ')');
				$result = self::convertToService($pageIds);
			}
			else
			{
				$result = self::convertToProduct($pageIds);
			}
			if (!$result->isSuccess())
			{
				return $result;
			}
		}

		$result->setData(['CONVERT_COMPLETE' => 'Y']);

		return $result;
	}

	/**
	 * Converts services to products
	 *
	 * @param array $productIdList
	 * @return Main\Result
	 */
	protected static function convertToProduct(array $productIdList): Main\Result
	{
		return self::convert($productIdList, Catalog\ProductTable::TYPE_PRODUCT);
	}

	/**
	 * Converts products to services
	 *
	 * @param array $productIdList
	 * @return Main\Result
	 */
	protected static function convertToService(array $productIdList): Main\Result
	{
		return self::convert($productIdList, Catalog\ProductTable::TYPE_SERVICE);
	}

	private static function convert(array $productIdList, int $catalogType): Main\Result
	{
		$result = new Main\Result();

		if (Main\Loader::includeModule('crm'))
		{
			$convertCrmProductResult = self::convertCrmProducts($productIdList, $catalogType);
			if (!$convertCrmProductResult->isSuccess())
			{
				$result->addErrors($convertCrmProductResult->getErrors());
			}
		}

		if (Main\Loader::includeModule('sale'))
		{
			$convertSaleProductResult = self::convertSaleProducts($productIdList, $catalogType);
			if (!$convertSaleProductResult->isSuccess())
			{
				$result->addErrors($convertSaleProductResult->getErrors());
			}
		}

		return $result;
	}

	private static function convertCrmProducts(array $productIdList, int $type): Main\Result
	{
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$productIdSql = $helper->forSql(implode(',', $productIdList));
		$sql = sprintf(
			'UPDATE %s SET TYPE = %d WHERE PRODUCT_ID IN (%s)',
			$helper->quote(Crm\ProductRowTable::getTableName()),
			$type,
			$productIdSql
		);

		try
		{
			$connection->query($sql);
		}
		catch (Main\DB\SqlQueryException $exception)
		{
			return (new Main\Result())->addError(new Main\Error($exception->getMessage()));
		}

		return new Main\Result();
	}

	private static function convertSaleProducts(array $productIdList, int $type): Main\Result
	{
		$saleType = Sale\Internals\Catalog\ProductTypeMapper::getType($type);

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$productIdSql = $helper->forSql(implode(',', $productIdList));
		$sql = sprintf(
			'UPDATE %s SET TYPE = %s WHERE PRODUCT_ID IN (%s)',
			$helper->quote(Sale\Internals\BasketTable::getTableName()),
			$saleType ?: $helper->convertToDb($saleType),
			$productIdSql
		);

		try
		{
			$connection->query($sql);
		}
		catch (Main\DB\SqlQueryException $exception)
		{
			return (new Main\Result())->addError(new Main\Error($exception->getMessage()));
		}

		return new Main\Result();
	}
}
