<?php
namespace Bitrix\Catalog\Config;

use Bitrix\Bitrix24;
use Bitrix\Catalog\Store\EnableWizard;
use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

/**
 * Class Feature
 * Provides unified methods for check B24 tariff limits and Bitrix edition limits.
 *
 * @package Bitrix\Catalog\Config
 */
final class Feature
{
	private const PRODUCT_SETS = 'catalog_product_sets';
	private const MULTI_PRICE_TYPES = 'catalog_multi_price_types';
	private const CUMULATIVE_DISCOUNTS = 'catalog_cumulative_discounts';
	private const MULTI_WARENHOUSES = 'catalog_multi_warenhouses';
	private const EXTENDED_PRICES = 'catalog_price_quantity_ranges';
	private const INVENTORY_MANAGEMENT = 'catalog_inventory_management';
	private const INVENTORY_MANAGEMENT_1C = 'catalog_inventory_management_1c';
	private const COMMON_PRODUCT_PROCESSING = 'catalog_common_product_processing';
	private const PRODUCT_LIMIT = 'catalog_product_limit';
	private const CATALOG_PERMISSIONS = 'catalog_permissions';
	private const CATALOG_SERVICES = 'catalog_services';

	private const LANDING_PRODUCT_LIMIT_VARIABLE = 'landing_product_limit';

	/** @var null|bool sign of the presence of Bitrix24 */
	private static ?bool $bitrix24Included = null;

	/** @var array map of compliance with tariff and edition restrictions */
	private static array $tranferList = [
		self::PRODUCT_SETS => 'CatCompleteSet',
		self::MULTI_PRICE_TYPES => 'CatMultiPrice',
		self::CUMULATIVE_DISCOUNTS => 'CatDiscountSave',
		self::MULTI_WARENHOUSES => 'CatMultiStore'
	];

	/** @var array edition restrictions */
	private static array $retailExist = [
		self::PRODUCT_SETS => true,
		self::MULTI_PRICE_TYPES => true,
		self::CUMULATIVE_DISCOUNTS => true,
		self::MULTI_WARENHOUSES => true
	];

	/** @var array bitrix24 restrictions */
	private static array $bitrix24exist = [
		self::PRODUCT_SETS => true,
		self::EXTENDED_PRICES => true,
		self::MULTI_PRICE_TYPES => true,
		self::MULTI_WARENHOUSES => true,
		self::INVENTORY_MANAGEMENT => true,
		self::INVENTORY_MANAGEMENT_1C => true,
		self::COMMON_PRODUCT_PROCESSING => true,
		self::CATALOG_PERMISSIONS => true,
		self::CATALOG_SERVICES => true,
	];

	/** @var array bitrix24 articles about tarif features */
	private static array $bitrix24helpCodes = [
		self::PRODUCT_SETS => 'limit_shop_bundles',
		self::MULTI_PRICE_TYPES => 'limit_shop_variable_prices',
		self::EXTENDED_PRICES => 'limit_shop_variable_prices',
		self::MULTI_WARENHOUSES => 'limit_shop_stocks',
		self::INVENTORY_MANAGEMENT => 'limit_store_inventory_management',
		self::PRODUCT_LIMIT => 'limit_shop_products',
		self::CATALOG_PERMISSIONS => 'limit_crm_catalog_access_permissions',
		self::CATALOG_SERVICES => 'limit_crm_catalog_services',
	];

	private static int $helpCodesCounter = 0;
	private static bool $initUi = false;

	/**
	 * @return int
	 */
	public static function getLandingProductLimit(): int
	{
		$result = 0;
		if (self::isBitrix24())
		{
			$result = (int)Bitrix24\Feature::getVariable(self::LANDING_PRODUCT_LIMIT_VARIABLE);
		}

		return $result;
	}

	/**
	 * Returns true if product sets are allowed.
	 *
	 * @return bool
	 */
	public static function isProductSetsEnabled(): bool
	{
		return self::isFeatureEnabled(self::PRODUCT_SETS);
	}

	/**
	 * Returns true if price quantity ranges are allowed.
	 *
	 * @return bool
	 */
	public static function isPriceQuantityRangesEnabled(): bool
	{
		return self::isFeatureEnabled(self::EXTENDED_PRICES);
	}

	/**
	 * Returns true if multi types of prices are allowed.
	 *
	 * @return bool
	 */
	public static function isMultiPriceTypesEnabled(): bool
	{
		return self::isFeatureEnabled(self::MULTI_PRICE_TYPES);
	}

	/**
	 * Return true if cumulative discounts are allowed.
	 *
	 * @return bool
	 */
	public static function isCumulativeDiscountsEnabled(): bool
	{
		return self::isFeatureEnabled(self::CUMULATIVE_DISCOUNTS);
	}

	/**
	 * Returns true if multiple warehouses are allowed.
	 *
	 * @return bool
	 */
	public static function isMultiStoresEnabled(): bool
	{
		return self::isFeatureEnabled(self::MULTI_WARENHOUSES);
	}

	/**
	 * Returns true if warehouse inventory management is allowed.
	 *
	 * @return bool
	 */
	public static function isInventoryManagementEnabled(): bool
	{
		return self::isFeatureEnabled(self::INVENTORY_MANAGEMENT);
	}

	public static function checkInventoryManagementFeatureByCurrentMode(): bool
	{
		$currentMode = EnableWizard\Manager::getCurrentMode();

		if ($currentMode === EnableWizard\ModeList::ONEC)
		{
			return !EnableWizard\TariffChecker::isOnecInventoryManagementRestricted();
		}

		return self::isInventoryManagementEnabled();
	}

	/**
	 * Returns true if common product processing is enabled.
	 *
	 * @return bool
	 */
	public static function isCommonProductProcessingEnabled(): bool
	{
		if (!self::isBitrix24())
		{
			return Option::get('catalog', 'catalog_common_product_processing') === 'Y';
		}

		return self::isFeatureEnabled(self::COMMON_PRODUCT_PROCESSING);
	}

	/**
	 * Returns true if catalog rights editor is enabled.
	 *
	 * @return bool
	 */
	public static function isAccessControllerCheckingEnabled(): bool
	{
		return self::isFeatureEnabled(self::CATALOG_PERMISSIONS);
	}

	/**
	 * Returns true if can exporting to Yandex.Market.
	 *
	 * @return bool
	 */
	public static function isCanUseYandexExport(): bool
	{
		$lang = LANGUAGE_ID;

		if (self::isBitrix24())
		{
			$lang = \CBitrix24::getLicensePrefix();
		}
		elseif (Loader::includeModule('intranet'))
		{
			$lang = \CIntranetUtils::getPortalZone();
		}
		elseif (Option::get('main', 'vendor') === '1c_bitrix')
		{
			$lang = 'ru';
		}

		return in_array($lang, ['ru', 'by', 'kz'], true);
	}

	/**
	 * Returns true if can use services.
	 *
	 * @return bool
	 */
	public static function isCatalogServicesEnabled(): bool
	{
		return self::isFeatureEnabled(self::CATALOG_SERVICES);
	}

	/**
	 * Returns true if can use product batches.
	 *
	 * @return bool
	 */
	public static function isStoreBatchEnabled(): bool
	{
		return self::isInventoryManagementEnabled();
	}

	public static function getLandingLimitVariable(): string
	{
		return self::LANDING_PRODUCT_LIMIT_VARIABLE;
	}

	/**
	 * Returns url description for help article about sets and bunles.
	 *
	 * @return array|null
	 */
	public static function getProductSetsHelpLink(): ?array
	{
		return self::getHelpLink(self::PRODUCT_SETS);
	}

	/**
	 * Returns url description for help article about price quantity ranges.
	 *
	 * @return array|null
	 */
	public static function getPriceQuantityRangesHelpLink(): ?array
	{
		return self::getHelpLink(self::EXTENDED_PRICES);
	}

	/**
	 * Returns url description for help article about multi price types.
	 *
	 * @return array|null
	 */
	public static function getMultiPriceTypesHelpLink(): ?array
	{
		return self::getHelpLink(self::MULTI_PRICE_TYPES);
	}

	/**
	 * Returns url description for help article about multi stores.
	 *
	 * @return array|null
	 */
	public static function getMultiStoresHelpLink(): ?array
	{
		return self::getHelpLink(self::MULTI_WARENHOUSES);
	}

	/**
	 * Returns url description for help article about inventory managment.
	 *
	 * @return array|null
	 */
	public static function getInventoryManagementHelpLink(): ?array
	{
		return self::getHelpLink(self::INVENTORY_MANAGEMENT);
	}

	/**
	 * Returns url description for help article about product limits.
	 *
	 * @return array|null
	 */
	public static function getProductLimitHelpLink(): ?array
	{
		return self::getHelpLink(self::PRODUCT_LIMIT);
	}

	/**
	 * Returns url description for help article about catalog right editor.
	 *
	 * @return array|null
	 */
	public static function getAccessControllerHelpLink(): ?array
	{
		return self::getHelpLink(self::CATALOG_PERMISSIONS);
	}

	/**
	 * Returns url description for help article about services in catalog.
	 *
	 * @return array|null
	 */
	public static function getCatalogServicesHelpLink(): ?array
	{
		return self::getHelpLink(self::CATALOG_SERVICES);
	}

	/**
	 * Init ui scope for show help links on internal pages.
	 *
	 * @return void
	 */
	public static function initUiHelpScope(): void
	{
		if (!self::isBitrix24())
		{
			return;
		}
		if (self::$helpCodesCounter <= 0 || self::$initUi)
		{
			return;
		}
		if (Loader::includeModule('ui'))
		{
			self::$initUi = true;
			Main\UI\Extension::load('ui.info-helper');
		}
	}

	/**
	 * Check restriction.
	 *
	 * @param string $featureId		Restriction name.
	 * @return bool
	 */
	private static function isFeatureEnabled(string $featureId): bool
	{
		if ($featureId === '')
		{
			return false;
		}

		$result = true;
		if (self::isBitrix24())
		{
			if (isset(self::$bitrix24exist[$featureId]))
			{
				$result = Bitrix24\Feature::isFeatureEnabled($featureId);
			}
		}
		else
		{
			if (isset(self::$retailExist[$featureId]))
			{
				$result = \CBXFeatures::IsFeatureEnabled(self::$tranferList[$featureId]);
			}
		}

		return $result;
	}

	/**
	 * Returns javascript link to bitrx24 feature help article.
	 *
	 * @param string $featureId
	 * @return array|null
	 */
	private static function getHelpLink(string $featureId): ?array
	{
		if (!self::isBitrix24())
		{
			return null;
		}
		if (!isset(self::$bitrix24helpCodes[$featureId]))
		{
			return null;
		}
		self::$helpCodesCounter++;
		return [
			'TYPE' => 'ONCLICK',
			'LINK' => 'top.BX.UI.InfoHelper.show(\''.self::$bitrix24helpCodes[$featureId].'\');',
			'FEATURE_CODE' => self::$bitrix24helpCodes[$featureId],
		];
	}

	/**
	 * Return true if Bitrix24 is exists.
	 *
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	private static function isBitrix24(): bool
	{
		if (self::$bitrix24Included === null)
			self::$bitrix24Included = Loader::includeModule('bitrix24');
		return self::$bitrix24Included;
	}
}
