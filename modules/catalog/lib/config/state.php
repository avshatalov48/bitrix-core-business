<?php
namespace Bitrix\Catalog\Config;

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\ModuleManager,
	Bitrix\Main\Localization\Loc,
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
	/** @var array */
	private static $landingSections = null;
	/** @var array */
	private static $iblockSections = null;
	/** @var array */
	private static $fullIblockSections = null;
	/** @var int */
	private static $elementCount = null;
	/** @var array */
	private static $iblockList = [];

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

		return Catalog\GroupTable::getCount([], ['ttl' => 86400]) > 1;
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

		return Catalog\GroupTable::getCount([], ['ttl' => 86400]) == 0;
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

		return Catalog\StoreTable::getCount([], ['ttl' => 86400]) > 1;
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

		return Catalog\StoreTable::getCount([], ['ttl' => 86400]) == 0;
	}

	/**
	 * Returns information about exceeding the number of goods in the landing for the information block.
	 *
	 * @param int $iblockId		Iblock Id.
	 * @return array|false
	 */
	public static function getExceedingProductLimit($iblockId)
	{
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			return false;

		if (!ModuleManager::isModuleInstalled('bitrix24'))
			return false;

		if ($iblockId != self::getCrmCatalogId())
			return false;

		return self::checkIblockLimit($iblockId);
	}

	/**
	 * OnIBlockElementAdd event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return bool
	 */
	public static function handlerBeforeIblockElementAdd(array &$fields)
	{
		if (!self::checkIblockId($fields))
			return true;

		$limit = self::checkIblockLimit($fields['IBLOCK_ID']);
		if (empty($limit))
			return true;

		if (!isset($fields['IBLOCK_SECTION']) || !is_array($fields['IBLOCK_SECTION']))
			return true;
		$sections = $fields['IBLOCK_SECTION'];
		Main\Type\Collection::normalizeArrayValuesByInt($sections, true);
		if (empty($sections))
			return true;
		self::loadIblockSections($fields['IBLOCK_ID']);
		$sections = array_intersect($sections, self::$fullIblockSections);
		if (empty($sections))
			return true;
		unset($sections);

		self::setProductLimitError($limit);
		unset($limit);

		return false;
	}

	/**
	 * OnAfterIBlockElementAdd event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return void
	 */
	public static function handlerAfterIblockElementAdd(array &$fields)
	{
		if ($fields['RESULT'] === false)
			return;

		if (!self::checkIblockId($fields))
			return;

		$sections = $fields['IBLOCK_SECTION'];
		Main\Type\Collection::normalizeArrayValuesByInt($sections, true);
		if (empty($sections))
			return;
		self::loadIblockSections($fields['IBLOCK_ID']);
		$sections = array_intersect($sections, self::$fullIblockSections);
		if (empty($sections))
			return;

		self::$elementCount = null;
	}

	/**
	 * OnBeforeIBlockElementUpdate event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return bool
	 */
	public static function handlerBeforeIblockElementUpdate(array &$fields)
	{
		if (!self::checkIblockId($fields))
			return true;

		$limit = self::checkIblockLimit($fields['IBLOCK_ID']);
		if (empty($limit))
			return true;

		if (!isset($fields['IBLOCK_SECTION']) || !is_array($fields['IBLOCK_SECTION']))
			return true;
		$sections = $fields['IBLOCK_SECTION'];
		Main\Type\Collection::normalizeArrayValuesByInt($sections, true);
		if (empty($sections))
			return true;
		self::loadIblockSections($fields['IBLOCK_ID']);
		$sections = array_intersect($sections, self::$fullIblockSections);
		if (empty($sections))
			return true;
		unset($sections);

		$notMove = false;
		$iterator = Iblock\SectionElementTable::getList([
			'select' => ['IBLOCK_SECTION_ID'],
			'filter' => ['=IBLOCK_ELEMENT_ID' => $fields['ID'], '=ADDITIONAL_PROPERTY_ID' => null]
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			if (isset(self::$fullIblockSections[$row['ID']]))
			{
				$notMove = true;
				break;
			}
		}
		unset($row, $iterator);
		if ($notMove)
			return true;

		self::setProductLimitError($limit);
		unset($limit);

		return false;
	}

	/**
	 * OnAfterIBlockElementUpdate event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return void
	 * @throws Main\LoaderException
	 */
	public static function handlerAfterIblockElementUpdate(array &$fields)
	{
		if ($fields['RESULT'] === false)
			return;
		if (!self::checkIblockId($fields))
			return;
		if (!array_key_exists('IBLOCK_SECTION', $fields))
			return;

		self::$elementCount = null;
	}

	/**
	 * OnAfterIBlockElementDelete event handler. Do not use directly.
	 *
	 * @param array $fields
	 * @return void
	 */
	public static function handlerAfterIblockElementDelete(array $fields)
	{
		if (!self::checkIblockId($fields))
			return;

		self::$elementCount = null;
	}

	/**
	 * OnAfterIBlockSectionAdd event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return void
	 */
	public static function handlerAfterIblockSectionAdd(array &$fields)
	{
		if ($fields['RESULT'] === false)
			return;
		if (!self::checkIblockId($fields))
			return;

		self::$iblockSections = null;
		self::$fullIblockSections = null;
	}

	/**
	 * OnBeforeIBlockSectionUpdate event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return bool
	 */
	public static function handlerBeforeIblockSectionUpdate(array &$fields)
	{
		if (!self::checkIblockId($fields))
			return true;

		$limit = self::getIblockLimit($fields['IBLOCK_ID']);
		if ($limit['LIMIT'] == 0)
			return true;
		if (!array_key_exists('IBLOCK_SECTION_ID', $fields))
			return true;
		$parentId = (int)$fields['IBLOCK_SECTION_ID'];
		self::loadIblockSections($fields['IBLOCK_ID']);
		if (!isset(self::$fullIblockSections[$parentId]))
			return true;
		$iterator = Iblock\SectionTable::getList([
			'select' => ['IBLOCK_SECTION_ID'],
			'filter' => ['=ID' => $fields['ID'], '=IBLOCK_ID' => $fields['IBLOCK_ID']]
		]);
		$row = $iterator->fetch();
		unset($iterator);
		if (empty($row))
			return true;
		$oldParentId = (int)$row['IBLOCK_SECTION_ID'];
		if (isset(self::$fullIblockSections[$oldParentId]))
			return true;

		$count = (int)\CIBlockElement::GetList(
			[],
			[
				'IBLOCK_ID' => $fields['IBLOCK_ID'],
				'SECTION_ID' => $fields['ID'],
				'INCLUDE_SUBSECTIONS' => 'Y',
				'CHECK_PERMISSIONS' => 'N'
			],
			[],
			false,
			['ID']
		);
		if ($count == 0)
			return true;

		$limit = self::getIblockLimit($fields['IBLOCK_ID']);
		if (($count + $limit['COUNT']) <= $limit['LIMIT'])
			return true;

		$limit['COUNT'] += $count;

		self::setProductLimitError($limit, 'CATALOG_STATE_ERR_PRODUCT_IN_SECTION_LIMIT');
		unset($limit);

		return false;
	}

	/**
	 * OnAfterIBlockSectionUpdate event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return void
	 */
	public static function handlerAfterIblockSectionUpdate(array &$fields)
	{
		if ($fields['RESULT'] === false)
			return;
		if (!self::checkIblockId($fields))
			return;
		if (!array_key_exists('IBLOCK_SECTION_ID', $fields))
			return;

		self::$iblockSections = null;
		self::$fullIblockSections = null;
		self::$elementCount = null;
	}

	/**
	 * OnAfterIBlockSectionDelete event handler. Do not use directly.
	 *
	 * @param array $fields
	 * @return void
	 */
	public static function handlerAfterIblockSectionDelete(array $fields)
	{
		if (!self::checkIblockId($fields))
			return;

		self::$iblockSections = null;
		self::$fullIblockSections = null;
		self::$elementCount = null;
	}

	/**
	 * @param int $iblockId
	 * @return int
	 */
	private static function getElementCount($iblockId)
	{
		if (self::$elementCount === null)
		{
			self::$elementCount = 0;

			$iblockSectionIds = self::getIblockSections($iblockId);
			if (!empty($iblockSectionIds))
			{
				self::$elementCount = (int)\CIBlockElement::GetList(
					[],
					[
						'IBLOCK_ID' => $iblockId,
						'SECTION_ID' => $iblockSectionIds,
						'INCLUDE_SUBSECTIONS' => 'Y',
						'CHECK_PERMISSIONS' => 'N'
					],
					[],
					false,
					['ID']
				);
			}
			unset($iblockSectionIds);
		}
		return self::$elementCount;
	}

	/**
	 * @param int $iblockId
	 * @return array
	 */
	private static function getIblockSections($iblockId)
	{
		if (self::$iblockSections === null)
		{
			self::loadIblockSections($iblockId);
		}
		return self::$iblockSections;
	}

	/**
	 * @param int $iblockId
	 * @return void
	 */
	private static function loadIblockSections($iblockId)
	{
		if (self::$iblockSections === null)
		{
			self::$iblockSections = [];
			self::$fullIblockSections = [];
			$sections = self::getLandingSections();
			if (!empty($sections))
			{
				$iterator = Iblock\SectionTable::getList([
					'select' => ['ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'],
					'filter' => ['=IBLOCK_ID' => $iblockId, '@ID' => $sections]
				]);
				while ($row = $iterator->fetch())
				{
					$row['ID'] = (int)$row['ID'];
					self::$iblockSections[] = $row['ID'];
					self::$fullIblockSections[$row['ID']] = $row['ID'];
					$sublist = Iblock\SectionTable::getList([
						'select' => ['ID'],
						'filter' => ['=IBLOCK_ID' => $iblockId, '>LEFT_MARGIN' => $row['LEFT_MARGIN'], '<RIGHT_MARGIN' => $row['RIGHT_MARGIN']]
					]);
					while ($sub = $sublist->fetch())
					{
						$sub['ID'] = (int)$sub['ID'];
						self::$fullIblockSections[$sub['ID']] = $sub['ID'];
					}
				}
				unset($sub, $sublist, $row, $iterator);
			}
			unset($sections);
		}
	}

	/**
	 * Returns the sections Id used in landings.
	 *
	 * @return array
	 */
	private static function getLandingSections()
	{
		if (self::$landingSections === null)
		{
			self::$landingSections = [];

			if (!Loader::includeModule('landing'))
				return self::$landingSections;

			$iterator = Landing\Internals\HookDataTable::getList([
				'select' => ['VALUE'],
				'filter' => [
					'=ENTITY_TYPE' => Landing\Hook::ENTITY_TYPE_SITE,
					'=HOOK' => 'SETTINGS',
					'=CODE' => 'SECTION_ID'
				],
				'cache' => ['ttl' => 86400]
			]);
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['VALUE'];
				if ($id <= 0)
					continue;
				self::$landingSections[$id] = $id;
			}
			unset($id, $row, $iterator);

			if (!empty(self::$landingSections))
			{
				self::$landingSections = array_values(self::$landingSections);
			}
		}
		return self::$landingSections;
	}

	/**
	 * @return int
	 * @throws Main\LoaderException
	 */
	private static function getCrmCatalogId()
	{
		$result = 0;
		if (Loader::includeModule('crm'))
		{
			$result = \CCrmCatalog::GetDefaultID();
		}
		return $result;
	}

	/**
	 * @param array $fields
	 * @return bool
	 * @throws Main\LoaderException
	 */
	private static function checkIblockId(array $fields)
	{
		if (!isset($fields['IBLOCK_ID']))
			return false;
		$iblockId = (int)$fields['IBLOCK_ID'];
		if ($iblockId <= 0)
			return false;
		if (!isset(self::$iblockList[$iblockId]))
		{
			$result = true;
			if (!ModuleManager::isModuleInstalled('bitrix24'))
				$result = false;
			if ($iblockId != self::getCrmCatalogId())
				$result = false;
			self::$iblockList[$iblockId] = $result;
		}
		return self::$iblockList[$iblockId];
	}

	/**
	 * Returns products limit.
	 *
	 * @param int $iblockId
	 * @return array|false
	 */
	private static function checkIblockLimit($iblockId)
	{
		$result = self::getIblockLimit($iblockId);
		if ($result['LIMIT'] == 0)
			return false;
		if ($result['COUNT'] < $result['LIMIT'])
			return false;
		return $result;
	}

	/**
	 * @param $iblockId
	 * @return array
	 * 	keys are case sensitive:
	 *		<ul>
	 *		<li>int COUNT
	 * 		<li>int LIMIT
	 *		</ul>
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private static function getIblockLimit($iblockId)
	{
		$result = [
			'COUNT' => 0,
			'LIMIT' => (int)Main\Config\Option::get('catalog', 'landing_product_limit')
		];
		if ($result['LIMIT'] == 0)
			return $result;
		$result['COUNT'] = self::getElementCount($iblockId);
		return $result;
	}

	/**
	 * Send error.
	 *
	 * @param array $limit
	 * @param string $messageId
	 * @return void
	 */
	private static function setProductLimitError(array $limit, $messageId = '')
	{
		global $APPLICATION;

		if ($messageId == '')
			$messageId = 'CATALOG_STATE_ERR_PRODUCT_LIMIT';

		$oldMessages = [
			[
				'text' => Loc::getMessage(
					$messageId,
					[
						'#COUNT#' => $limit['COUNT'],
						'#LIMIT#' => $limit['LIMIT']
					]
				)
			]
		];

		$error = new \CAdminException($oldMessages);
		$APPLICATION->ThrowException($error);
		unset($error, $oldMessages);
	}

	/**
	 * Returns true if product card slider option is checked.
	 *
	 * @return bool
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function isProductCardSliderEnabled(): bool
	{
		if (!Feature::isCommonProductProcessingEnabled())
			return false;

		return Main\Config\Option::get('catalog', 'product_card_slider_enabled', 'Y') === 'Y';
	}
}