<?php
namespace Bitrix\Sale\Config;

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Bitrix24;

Loc::loadMessages(__FILE__);

final class Feature
{
	private static $bitrix24Included = null;

	private static $featureList = [];

	private static $tranferList = [
		'sale_cumulative_discounts' => 'CatDiscountSave'
	];

	private static $retailExist = [
		'sale_cumulative_discounts' => true
	];

	private static $bitrix24exist = [
		'sale_cumulative_discounts' => true,
		'sale_discount_constructor' => true
	];

	public static function isCumulativeDiscountsEnabled()
	{
		return self::isFeatureEnabled('sale_cumulative_discounts');
	}

	public static function isDiscountConstructorEnabled()
	{
		return self::isFeatureEnabled('sale_discount_constructor');
	}

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

	private static function isBitrix24()
	{
		if (self::$bitrix24Included === null)
			self::$bitrix24Included = Loader::includeModule('bitrix24');
		return self::$bitrix24Included;
	}
}