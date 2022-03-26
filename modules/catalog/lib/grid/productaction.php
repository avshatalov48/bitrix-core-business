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
		if (empty($catalog) || $catalog['CATALOG_TYPE'] === \CCatalogSku::TYPE_PRODUCT)
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
		$blockedTypes = self::getBlockedProductTypes($catalog, $fields);
		if (!empty($blockedTypes))
		{
			$filter['!=TYPE'] = $blockedTypes;
		}
		unset($blockedTypes);

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
		if (empty($catalog) || $catalog['CATALOG_TYPE'] === \CCatalogSku::TYPE_PRODUCT)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_BAD_CATALOG')
			));
			return $result;
		}
		$blockedTypes = self::getBlockedProductTypes($catalog, $fields);
		if (!empty($blockedTypes))
		{
			$blockedTypes = array_fill_keys($blockedTypes, true);
		}

		$products = [];
		foreach (array_chunk($elementIds, 500) as $pageIds)
		{
			$iterator = Catalog\Model\Product::getList([
				'select' => [
					'ID',
					'TYPE',
				],
				'filter' => ['@ID' => $pageIds],
			]);
			while ($row = $iterator->fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$row['TYPE'] = (int)$row['TYPE'];
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
				$type = $products[$id]['TYPE'];
				if (isset($blockedTypes[$type]))
				{
					switch ($type)
					{
						case Catalog\ProductTable::TYPE_SKU:
							$result->addError(new Main\Error(
								Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_CANNOT_MODIFY_SKU'),
								$id
							));
							break;
						case Catalog\ProductTable::TYPE_SET:
							$result->addError(new Main\Error(
								Loc::getMessage('BX_CATALOG_PRODUCT_ACTION_ERR_CANNOT_MODIFY_SET'),
								$id
							));
							break;
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
				unset($type);
			}
		}
		unset($elementResult, $id);
		unset($newData, $data);
		unset($blackList, $catalog);

		return $result;
	}

	public static function updateProductField(int $iblockId, int $elementId, array $fields)
	{

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

	protected static function getBlockedProductTypes(array $catalog, array $fields): array
	{
		$result = [];

		$setFields = [
			'WEIGHT' => true,
			'QUANTITY' => true,
			'QUANTITY_TRACE' => true,
			'CAN_BUY_ZERO' => true,
			'MEASURE' => true,
		];
		$blackList = array_intersect_key($fields, $setFields);
		if (!empty($blackList))
		{
			$result[] = Catalog\ProductTable::TYPE_SET;
		}
		unset($blackList, $setFields);

		if (
			$catalog['CATALOG_TYPE'] === \CCatalogSku::TYPE_FULL
			&& Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') !== 'Y'
		)
		{
			$result[] = Catalog\ProductTable::TYPE_SKU;
		}

		return $result;
	}
}
