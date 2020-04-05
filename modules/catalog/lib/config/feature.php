<?php
namespace Bitrix\Catalog\Config;

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Bitrix24;

Loc::loadMessages(__FILE__);

/**
 * Class Feature
 * Provides unified methods for check B24 tariff limits and Bitrix edition limits.
 *
 * @package Bitrix\Catalog\Config
 */
final class Feature
{
	/** @var null|bool sign of the presence of Bitrix24 */
	private static $bitrix24Included = null;

	/** @var array features hit cache */
	private static $featureList = [];

	/** @var array map of compliance with tariff and edition restrictions */
	private static $tranferList = [
		'catalog_product_sets' => 'CatCompleteSet',
		'catalog_multi_price_types' => 'CatMultiPrice',
		'catalog_cumulative_discounts' => 'CatDiscountSave',
		'catalog_multi_warenhouses' => 'CatMultiStore'
	];

	/** @var array edition restrictions */
	private static $retailExist = [
		'catalog_product_sets' => true,
		'catalog_multi_price_types' => true,
		'catalog_cumulative_discounts' => true,
		'catalog_multi_warenhouses' => true
	];

	/** @var array bitrix24 restrictions */
	private static $bitrix24exist = [
		'catalog_product_sets' => true,
		'catalog_price_quantity_ranges' => true,
		'catalog_multi_price_types' => true,
		'catalog_multi_warenhouses' => true,
		'catalog_inventory_management' => true
	];

	/**
	 * Returns true if product sets are allowed.
	 *
	 * @return bool
	 */
	public static function isProductSetsEnabled()
	{
		return self::isFeatureEnabled('catalog_product_sets');
	}

	/**
	 * Returns true if price quantity ranges are allowed.
	 *
	 * @return bool
	 */
	public static function isPriceQuantityRangesEnabled()
	{
		return self::isFeatureEnabled('catalog_price_quantity_ranges');
	}

	/**
	 * Returns true if multi types of prices are allowed.
	 *
	 * @return bool
	 */
	public static function isMultiPriceTypesEnabled()
	{
		return self::isFeatureEnabled('catalog_multi_price_types');
	}

	/**
	 * Return true if cumulative discounts are allowed.
	 *
	 * @return bool
	 */
	public static function isCumulativeDiscountsEnabled()
	{
		return self::isFeatureEnabled('catalog_cumulative_discounts');
	}

	/**
	 * Returns true if multiple warehouses are allowed.
	 *
	 * @return bool
	 */
	public static function isMultiStoresEnabled()
	{
		return self::isFeatureEnabled('catalog_multi_warenhouses');
	}

	/**
	 * Returns true if warehouse inventory management is allowed.
	 *
	 * @return bool
	 */
	public static function isInventoryManagementEnabled()
	{
		return self::isFeatureEnabled('catalog_inventory_management');
	}

	/**
	 * Check restriction.
	 *
	 * @param string $featureId		Restriction name.
	 * @return bool
	 */
	private static function isFeatureEnabled($featureId)
	{
		$featureId = (string)$featureId;
		if ($featureId === '')
			return false;
		if (!isset(self::$featureList[$featureId]))
		{
			if (self::isBitrix24())
			{
				if (isset(self::$bitrix24exist[$featureId]))
					self::$featureList[$featureId] = Bitrix24\Feature::isFeatureEnabled($featureId);
				else
					self::$featureList[$featureId] = true;
			}
			else
			{
				if (isset(self::$retailExist[$featureId]))
					self::$featureList[$featureId] = \CBXFeatures::IsFeatureEnabled(self::$tranferList[$featureId]);
				else
					self::$featureList[$featureId] = true;
			}
		}
		return self::$featureList[$featureId];
	}

	/**
	 * Return true if Bitrix24 is exists.
	 *
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	private static function isBitrix24()
	{
		if (self::$bitrix24Included === null)
			self::$bitrix24Included = Loader::includeModule('bitrix24');
		return self::$bitrix24Included;
	}
}