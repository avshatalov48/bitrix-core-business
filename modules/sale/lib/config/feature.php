<?php
namespace Bitrix\Sale\Config;

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Bitrix24;

Loc::loadMessages(__FILE__);

final class Feature
{
	private const DISCOUNT_CONSTRUCTOR = 'sale_discount_constructor';
	private const CUMULATIVE_DISCOUNTS = 'sale_cumulative_discounts';

	private static $bitrix24Included = null;

	private static $featureList = [];

	private static $tranferList = [
		self::CUMULATIVE_DISCOUNTS => 'CatDiscountSave'
	];

	private static $retailExist = [
		self::CUMULATIVE_DISCOUNTS => true
	];

	private static $bitrix24exist = [
		self::CUMULATIVE_DISCOUNTS => true,
		self::DISCOUNT_CONSTRUCTOR => true
	];

	/** @var array bitrix24 articles about tarif features */
	private static $bitrix24helpCodes = [
		self::DISCOUNT_CONSTRUCTOR => 'limit_shop_discount_builder',
		self::CUMULATIVE_DISCOUNTS => 'limit_shop_cumulative_discounts'
	];

	private static $helpCodesCounter = 0;
	private static $initUi = false;

	public static function isCumulativeDiscountsEnabled()
	{
		return self::isFeatureEnabled(self::CUMULATIVE_DISCOUNTS);
	}

	public static function isDiscountConstructorEnabled()
	{
		return self::isFeatureEnabled(self::DISCOUNT_CONSTRUCTOR);
	}

	/**
	 * Returns url description for help article about cumulative discounts.
	 *
	 * @return array|null
	 */
	public static function getCumulativeDiscountsHelpLink(): ?array
	{
		return self::getHelpLink(self::CUMULATIVE_DISCOUNTS);
	}

	/**
	 * Returns url description for help article about cumulative discounts.
	 *
	 * @return array|null
	 */
	public static function getDiscountConstructorHelpLink(): ?array
	{
		return self::getHelpLink(self::DISCOUNT_CONSTRUCTOR);
	}

	/**
	 * Init ui scope for show help links on internal pages.
	 *
	 * @return void
	 */
	public static function initUiHelpScope(): void
	{
		global $APPLICATION;
		if (!self::isBitrix24())
		{
			return;
		}
		if (self::$helpCodesCounter <= 0 || self::$initUi)
		{
			return;
		}
		self::$initUi = true;
		$APPLICATION->IncludeComponent(
			'bitrix:ui.info.helper',
			'',
			[]
		);
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
			'LINK' => 'BX.UI.InfoHelper.show(\''.self::$bitrix24helpCodes[$featureId].'\');'
		];
	}

	private static function isBitrix24()
	{
		if (self::$bitrix24Included === null)
			self::$bitrix24Included = Loader::includeModule('bitrix24');
		return self::$bitrix24Included;
	}
}