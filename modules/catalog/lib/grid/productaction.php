<?php

namespace Bitrix\Catalog\Grid;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Catalog;

class ProductAction
{
	public const SET_FIELD = 'product_field';
	public const CHANGE_PRICE = 'change_price';

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

		$products = [];
		foreach (array_chunk($elementIds, 500) as $pageIds)
		{
			$filter['@ID'] = $pageIds;
			$iterator = Catalog\Model\Product::getList([
				'select' => ['ID'],
				'filter' => $filter,
			]);
			while ($row = $iterator->fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$products[$row['ID']] = $row;
			}
			unset($row, $iterator);
		}
		unset($pageIds);

		$data = [
			'fields' => $fields,
			'external_fields' => [
				'IBLOCK_ID' => $iblockId
			]
		];
		$newData = $data;

		foreach ($elementIds as $id)
		{
			if (!isset($products[$id]))
			{
				$newData['fields']['ID'] = $id;
				$elementResult = Catalog\Model\Product::add($newData);
				if (!$elementResult->isSuccess())
				{
					$result->addError(new Main\Error(
						implode('; ', $elementResult->getErrorMessages()),
						$id
					));
				}
			}
			else
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
		}
		unset($elementResult, $id);
		unset($newData, $data);
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

		if (!$USER->CanDoOperation('catalog_price'))
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
				reset($result);
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
}
