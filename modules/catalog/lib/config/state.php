<?php
namespace Bitrix\Catalog\Config;

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Landing;

/**
 * Class State
 * Provides methods for checking product restrictions and obtaining current settings based on constraints.
 *
 * @package Bitrix\Catalog\Config
 */
final class State
{
	/**
	 * Returns true if warehouse inventory management is allowed and enabled.
	 *
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function isUsedInventoryManagement()
	{
		if (!Feature::isInventoryManagementEnabled())
			return false;
		return ((string)Main\Config\Option::get('catalog', 'default_use_store_control') == 'Y');
	}

	/**
	 * Returns true if the limit on the number of price types is exceeded.
	 *
	 * @return bool
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function isExceededPriceTypeLimit()
	{
		if (Feature::isMultiPriceTypesEnabled())
			return false;

		//TODO: enable managed cache after blocked old api \CCatalogGroup
		return Catalog\GroupTable::getCount([]) > 1;
	}

	/**
	 * Returns true if it is allowed to add a new price type.
	 *
	 * @return bool
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function isAllowedNewPriceType()
	{
		if (Feature::isMultiPriceTypesEnabled())
			return true;

		//TODO: enable managed cache after blocked old api \CCatalogGroup
		return Catalog\GroupTable::getCount([]) == 0;
	}

	/**
	 * Returns true if the limit on the number of warehouses is exceeded.
	 *
	 * @return bool
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function isExceededStoreLimit()
	{
		if (Feature::isMultiStoresEnabled())
			return false;

		//TODO: enable managed cache after blocked old api \CCatalogStore
		return Catalog\StoreTable::getCount([]) > 1;
	}

	/**
	 * Returns true if it is allowed to add a new warehouse.
	 *
	 * @return bool
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function isAllowedNewStore()
	{
		if (Feature::isMultiStoresEnabled())
			return true;

		//TODO: enable managed cache after blocked old api \CCatalogStore
		return Catalog\StoreTable::getCount([]) == 0;
	}

	/**
	 * Returns information about exceeding the number of goods in the landing for the information block.
	 *
	 * @param int $iblockId		Iblock Id.
	 * @return array|false
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getExceedingProductLimit($iblockId)
	{
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			return false;

		$limit = (int)Main\Config\Option::get('catalog', 'landing_product_limit');
		if ($limit == 0)
			return false;

		$sections = self::getLandingSections();
		if (empty($sections))
			return false;

		$iblockSectionIds = [];
		$iterator = Iblock\SectionTable::getList([
			'select' => ['ID'],
			'filter' => ['=IBLOCK_ID' => $iblockId, '@ID' => $sections]
		]);
		while ($row = $iterator->fetch())
			$iblockSectionIds[] = (int)$row['ID'];
		unset($row, $iterator, $sections);

		if (empty($iblockSectionIds))
			return false;

		$count = \CIBlockElement::GetList(
			[],
			['IBLOCK_ID' => $iblockId, 'SECTION_ID' => $iblockSectionIds, 'INCLUDE_SUBSECTIONS' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
			[],
			false,
			['ID']
		);
		unset($iblockSectionIds);

		if ($count < $limit)
			return false;

		return [
			'COUNT' => $count,
			'LIMIT' => $limit
		];
	}

	/**
	 * Returns the sections Id used in landings.
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function getLandingSections()
	{
		$result = [];

		if (!Loader::includeModule('landing'))
			return $result;

		$iterator = Landing\Internals\HookDataTable::getList([
			'select' => ['VALUE'],
			'filter' => [
				'=ENTITY_TYPE' => Landing\Hook::ENTITY_TYPE_SITE,
				'=HOOK' => 'SETTINGS',
				'=CODE' => 'SECTION_ID'
			]
		]);
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['VALUE'];
			if ($id <= 0)
				continue;
			$result[$id] = $id;
		}
		unset($id, $row, $iterator);

		return (!empty($result) ? array_values($result) : []);
	}
}

class_alias('Bitrix\Catalog\Config\State', 'Bitrix\Catalog\Config\Configuration');