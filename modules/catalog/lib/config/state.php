<?php

namespace Bitrix\Catalog\Config;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Catalog;
use Bitrix\Landing;
use Bitrix\Crm;

/**
 * Class State
 * Provides methods for checking product restrictions and obtaining current settings based on constraints.
 *
 * @package Bitrix\Catalog\Config
 */
final class State
{
	/** @var array */
	private static $landingSections;
	/** @var array */
	private static $iblockSections;
	/** @var array */
	private static $fullIblockSections;
	/** @var int */
	private static $elementCount;
	/** @var array */
	private static $iblockList = [];
	/** @var bool */
	private static $crmIncluded;

	/**
	 * Returns true if warehouse inventory management is allowed and enabled.
	 *
	 * @return bool
	 */
	public static function isUsedInventoryManagement(): bool
	{
		if (!Feature::isInventoryManagementEnabled())
		{
			return false;
		}

		return (Main\Config\Option::get('catalog', 'default_use_store_control') === 'Y');
	}

	/**
	 * Returns true if used store quantity reserve.
	 *
	 * @return bool
	 */
	public static function isShowedStoreReserve(): bool
	{
		if (!self::isUsedInventoryManagement())
		{
			return false;
		}
		if (
			Main\Config\Option::get('catalog', 'enable_reservation') === 'Y'
			&& Main\Config\Option::get('catalog', 'show_store_reserve') === 'Y'
		)
		{
			return true;
		}
		if (self::isCrmIncluded())
		{
			return (Main\Config\Option::get('crm', 'enable_order_deal_create') === 'Y');
		}

		return false;
	}

	/**
	 * Returns true if the limit on the number of price types is exceeded.
	 *
	 * @return bool
	 */
	public static function isExceededPriceTypeLimit(): bool
	{
		if (Feature::isMultiPriceTypesEnabled())
		{
			return false;
		}

		return Catalog\GroupTable::getCount([], ['ttl' => 86400]) > 1;
	}

	/**
	 * Returns true if it is allowed to add a new price type.
	 *
	 * @return bool
	 */
	public static function isAllowedNewPriceType(): bool
	{
		if (Feature::isMultiPriceTypesEnabled())
		{
			return true;
		}

		return Catalog\GroupTable::getCount([], ['ttl' => 86400]) === 0;
	}

	/**
	 * Returns true if the limit on the number of warehouses is exceeded.
	 *
	 * @return bool
	 */
	public static function isExceededStoreLimit(): bool
	{
		if (Feature::isMultiStoresEnabled())
		{
			return false;
		}

		return Catalog\StoreTable::getCount([], ['ttl' => 86400]) > 1;
	}

	/**
	 * Returns true if it is allowed to add a new warehouse.
	 *
	 * @return bool
	 */
	public static function isAllowedNewStore(): bool
	{
		if (Feature::isMultiStoresEnabled())
		{
			return true;
		}

		return Catalog\StoreTable::getCount([], ['ttl' => 86400]) === 0;
	}

	/**
	 * Returns information about exceeding the number of goods in the landing for the information block.
	 *
	 * @param int $iblockId		Iblock Id.
	 * @param int|null $sectionId Current section (can be absent).
	 * @return array|null
	 */
	public static function getExceedingProductLimit(int $iblockId, ?int $sectionId = null): ?array
	{
		if ($iblockId <= 0)
		{
			return null;
		}

		if (!ModuleManager::isModuleInstalled('bitrix24'))
		{
			return null;
		}

		if ($iblockId !== self::getCrmCatalogId())
		{
			return null;
		}

		$result = self::checkIblockLimit($iblockId);
		if ($result !== null && $sectionId !== null)
		{
			self::loadIblockSections($iblockId);
			if (!isset(self::$fullIblockSections[$sectionId]))
			{
				$result = null;
			}
		}
		if ($result === null)
		{
			$result = self::getCrmCatalogLimit($iblockId);
		}

		return $result;
	}

	/**
	 * Returns information about exceeding the number of goods for crm catalog.
	 *
	 * @return array|null
	 */
	public static function getCrmExceedingProductLimit(): ?array
	{
		$crmCatalogId = self::getCrmCatalogId();
		if ($crmCatalogId > 0)
		{
			return self::getExceedingProductLimit($crmCatalogId);
		}

		return null;
	}

	/**
	 * OnIBlockElementAdd event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return bool
	 */
	public static function handlerBeforeIblockElementAdd(array &$fields): bool
	{
		if (!self::checkIblockId($fields))
		{
			return true;
		}

		$limit = self::checkIblockLimit((int)$fields['IBLOCK_ID']);
		if (empty($limit))
		{
			return true;
		}

		if (!isset($fields['IBLOCK_SECTION']) || !is_array($fields['IBLOCK_SECTION']))
		{
			return true;
		}
		$sections = $fields['IBLOCK_SECTION'];
		Main\Type\Collection::normalizeArrayValuesByInt($sections, true);
		if (empty($sections))
		{
			return true;
		}
		self::loadIblockSections((int)$fields['IBLOCK_ID']);
		$sections = array_intersect($sections, self::$fullIblockSections);
		if (empty($sections))
		{
			return true;
		}
		unset($sections);

		self::setProductLimitError($limit['MESSAGE']);
		unset($limit);

		return false;
	}

	/**
	 * OnAfterIBlockElementAdd event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return void
	 */
	public static function handlerAfterIblockElementAdd(array &$fields): void
	{
		if ($fields['RESULT'] === false)
			return;

		if (!self::checkIblockId($fields))
			return;

		$sections = $fields['IBLOCK_SECTION'];
		Main\Type\Collection::normalizeArrayValuesByInt($sections, true);
		if (empty($sections))
			return;
		self::loadIblockSections((int)$fields['IBLOCK_ID']);
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
	public static function handlerBeforeIblockElementUpdate(array &$fields): bool
	{
		if (!self::checkIblockId($fields))
		{
			return true;
		}

		$limit = self::checkIblockLimit((int)$fields['IBLOCK_ID']);
		if (empty($limit))
		{
			return true;
		}

		if (!isset($fields['IBLOCK_SECTION']) || !is_array($fields['IBLOCK_SECTION']))
		{
			return true;
		}
		$sections = $fields['IBLOCK_SECTION'];
		Main\Type\Collection::normalizeArrayValuesByInt($sections, true);
		if (empty($sections))
		{
			return true;
		}
		self::loadIblockSections((int)$fields['IBLOCK_ID']);
		$sections = array_intersect($sections, self::$fullIblockSections);
		if (empty($sections))
		{
			return true;
		}
		unset($sections);

		$notMove = false;
		$iterator = Iblock\SectionElementTable::getList([
			'select' => ['IBLOCK_SECTION_ID'],
			'filter' => [
				'=IBLOCK_ELEMENT_ID' => $fields['ID'],
				'=ADDITIONAL_PROPERTY_ID' => null,
			],
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
		{
			return true;
		}

		self::setProductLimitError($limit['MESSAGE']);
		unset($limit);

		return false;
	}

	/**
	 * OnAfterIBlockElementUpdate event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return void
	 */
	public static function handlerAfterIblockElementUpdate(array &$fields): void
	{
		if ($fields['RESULT'] === false)
		{
			return;
		}
		if (!self::checkIblockId($fields))
		{
			return;
		}
		if (!array_key_exists('IBLOCK_SECTION', $fields))
		{
			return;
		}

		self::$elementCount = null;
	}

	/**
	 * OnAfterIBlockElementDelete event handler. Do not use directly.
	 *
	 * @param array $fields
	 * @return void
	 */
	public static function handlerAfterIblockElementDelete(array $fields): void
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
	public static function handlerAfterIblockSectionAdd(array &$fields): void
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
	public static function handlerBeforeIblockSectionUpdate(array &$fields): bool
	{
		if (!self::checkIblockId($fields))
		{
			return true;
		}

		$limit = self::getIblockLimit((int)$fields['IBLOCK_ID']);
		if ($limit['LIMIT'] === 0)
		{
			return true;
		}
		if (!array_key_exists('IBLOCK_SECTION_ID', $fields))
		{
			return true;
		}
		$parentId = (int)$fields['IBLOCK_SECTION_ID'];
		self::loadIblockSections((int)$fields['IBLOCK_ID']);
		if (!isset(self::$fullIblockSections[$parentId]))
		{
			return true;
		}
		$iterator = Iblock\SectionTable::getList([
			'select' => ['IBLOCK_SECTION_ID'],
			'filter' => [
				'=ID' => $fields['ID'],
				'=IBLOCK_ID' => $fields['IBLOCK_ID'],
			],
		]);
		$row = $iterator->fetch();
		unset($iterator);
		if (empty($row))
		{
			return true;
		}
		$oldParentId = (int)$row['IBLOCK_SECTION_ID'];
		if (isset(self::$fullIblockSections[$oldParentId]))
		{
			return true;
		}

		$count = (int)\CIBlockElement::GetList(
			[],
			[
				'IBLOCK_ID' => $fields['IBLOCK_ID'],
				'SECTION_ID' => $fields['ID'],
				'INCLUDE_SUBSECTIONS' => 'Y',
				'CHECK_PERMISSIONS' => 'N',
			],
			[],
			false,
			['ID']
		);
		if ($count === 0)
		{
			return true;
		}
		$limit['COUNT'] += $count;
		if ($limit['COUNT'] <= $limit['LIMIT'])
		{
			return true;
		}

		$limit['MESSAGE_ID'] = 'CATALOG_STATE_ERR_PRODUCT_IN_SECTION_LIMIT';

		self::setProductLimitError(self::getProductLimitError($limit));
		unset($limit);

		return false;
	}

	/**
	 * OnAfterIBlockSectionUpdate event handler. Do not use directly.
	 *
	 * @param array &$fields
	 * @return void
	 */
	public static function handlerAfterIblockSectionUpdate(array &$fields): void
	{
		if ($fields['RESULT'] === false)
		{
			return;
		}
		if (!self::checkIblockId($fields))
		{
			return;
		}
		if (!array_key_exists('IBLOCK_SECTION_ID', $fields))
		{
			return;
		}

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
	private static function getElementCount(int $iblockId): int
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
						'CHECK_PERMISSIONS' => 'N',
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
	private static function getIblockSections(int $iblockId): array
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
	private static function loadIblockSections(int $iblockId): void
	{
		if (self::$iblockSections === null)
		{
			self::$iblockSections = [];
			self::$fullIblockSections = [];
			$sections = self::getLandingSections();
			if (!empty($sections))
			{
				$iterator = Iblock\SectionTable::getList([
					'select' => [
						'ID',
						'LEFT_MARGIN',
						'RIGHT_MARGIN',
					],
					'filter' => [
						'=IBLOCK_ID' => $iblockId,
						'@ID' => $sections,
					]
				]);
				while ($row = $iterator->fetch())
				{
					$row['ID'] = (int)$row['ID'];
					self::$iblockSections[] = $row['ID'];
					self::$fullIblockSections[$row['ID']] = $row['ID'];
					$sublist = Iblock\SectionTable::getList([
						'select' => ['ID'],
						'filter' => [
							'=IBLOCK_ID' => $iblockId,
							'>LEFT_MARGIN' => $row['LEFT_MARGIN'],
							'<RIGHT_MARGIN' => $row['RIGHT_MARGIN'],
						]
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
	private static function getLandingSections(): array
	{
		if (self::$landingSections === null)
		{
			self::$landingSections = [];

			if (!Loader::includeModule('landing'))
			{
				return self::$landingSections;
			}

			$iterator = Landing\Internals\HookDataTable::getList([
				'runtime' => [
					new Main\ORM\Fields\Relations\Reference(
						'TMP_LANDING_SITE',
						'Bitrix\Landing\Internals\SiteTable',
						['=this.ENTITY_ID' => 'ref.ID']
					)
				],
				'select' => ['VALUE'],
				'filter' => [
					'=ENTITY_TYPE' => Landing\Hook::ENTITY_TYPE_SITE,
					'=HOOK' => 'SETTINGS',
					'=CODE' => 'SECTION_ID',
					'=TMP_LANDING_SITE.DELETED' => 'N',
				],
				'cache' => ['ttl' => 86400],
			]);
			while ($row = $iterator->fetch())
			{
				$id = (int)$row['VALUE'];
				if ($id <= 0)
				{
					continue;
				}
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
	 * Returns crm catalog id, if exists.
	 *
	 * @return int|null
	 */
	private static function getCrmCatalogId(): ?int
	{
		$result = null;
		if (self::isCrmIncluded())
		{
			$result = Crm\Product\Catalog::getDefaultId();
		}

		return $result;
	}

	/**
	 * @param int $iblockId
	 * @return array|null
	 */
	private static function getCrmCatalogLimit(int $iblockId): ?array
	{
		if (!self::isCrmIncluded())
		{
			return null;
		}

		return Crm\Config\State::getExceedingProductLimit($iblockId);
	}

	/**
	 * Returns true if crm exists.
	 *
	 * @return bool
	 */
	private static function isCrmIncluded(): bool
	{
		if (self::$crmIncluded === null)
		{
			self::$crmIncluded = Loader::includeModule('crm');
		}

		return self::$crmIncluded;
	}

	/**
	 * Check crm catalog id.
	 *
	 * @param array $fields
	 * @return bool
	 */
	private static function checkIblockId(array $fields): bool
	{
		if (!isset($fields['IBLOCK_ID']))
		{
			return false;
		}
		$iblockId = (int)$fields['IBLOCK_ID'];
		if ($iblockId <= 0)
		{
			return false;
		}
		if (!isset(self::$iblockList[$iblockId]))
		{
			$result = true;
			if (!ModuleManager::isModuleInstalled('bitrix24'))
			{
				$result = false;
			}
			if ($iblockId !== self::getCrmCatalogId())
			{
				$result = false;
			}
			self::$iblockList[$iblockId] = $result;
		}

		return self::$iblockList[$iblockId];
	}

	/**
	 * Check products limit.
	 *
	 * @param int $iblockId
	 * @return array|null
	 * 	keys are case sensitive:
	 * 		<ul>
	 * 		<li>int COUNT
	 * 		<li>int LIMIT
	 * 		<li>array|null HELP_ACTION
	 * 		<li>string MESSAGE
	 * 		</ul>
	 */
	private static function checkIblockLimit(int $iblockId): ?array
	{
		$result = self::getIblockLimit($iblockId);
		if (
			$result['LIMIT'] === 0
			|| $result['COUNT'] < $result['LIMIT']
		)
		{
			return null;
		}
		$result['MESSAGE'] = self::getProductLimitError($result);
		unset($result['MESSAGE_ID']);
		$result['HELP_MESSAGE'] = Feature::getProductLimitHelpLink();

		return $result;
	}

	/**
	 * Returns products limit.
	 *
	 * @param int $iblockId
	 * @return array
	 * 	keys are case sensitive:
	 * 		<ul>
	 * 		<li>int COUNT
	 * 		<li>int LIMIT
	 * 		<li>string MESSAGE_ID
	 * 		</ul>
	 */
	private static function getIblockLimit(int $iblockId): array
	{
		$result = [
			'COUNT' => 0,
			'LIMIT' => (int)Main\Config\Option::get('catalog', 'landing_product_limit'),
			'MESSAGE_ID' => 'CATALOG_STATE_ERR_PRODUCT_LIMIT'
		];
		if ($result['LIMIT'] === 0)
		{
			return $result;
		}
		$result['COUNT'] = self::getElementCount($iblockId);

		return $result;
	}

	/**
	 * Returns message with error description.
	 *
	 * @param array $limit
	 * @return string|null
	 */
	private static function getProductLimitError(array $limit): ?string
	{
		if (!isset($limit['COUNT']) || !isset($limit['LIMIT']) || !isset($limit['MESSAGE_ID']))
		{
			return null;
		}

		return Loc::getMessage(
			$limit['MESSAGE_ID'],
			[
				'#COUNT#' => $limit['COUNT'],
				'#LIMIT#' => $limit['LIMIT']
			]
		);
	}

	/**
	 * Send error.
	 *
	 * @param string $errorMessage
	 * @return void
	 */
	private static function setProductLimitError(string $errorMessage): void
	{
		global $APPLICATION;

		$error = new \CAdminException([
			[
				'text' => $errorMessage,
			]
		]);
		$APPLICATION->ThrowException($error);
	}

	/**
	 * Returns true if product card slider option is checked.
	 *
	 * @return bool
	 */
	public static function isProductCardSliderEnabled(): bool
	{
		if (!Feature::isCommonProductProcessingEnabled())
		{
			return false;
		}

		return Main\Config\Option::get('catalog', 'product_card_slider_enabled', 'Y') === 'Y';
	}
}
