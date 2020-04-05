<?php

namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PriceMaths;

Loc::loadMessages(__FILE__);

class PercentPrice extends Price
{
	/**
	 * @param $entityParams
	 * @param $paramValue
	 * @return float
	 */
	protected static function getPrice($entityParams, $paramValue)
	{
		$percent = (float)$paramValue / 100;
		$price = (float)$entityParams['PRICE_ORDER'] * $percent;

		return PriceMaths::roundPrecision($price);
	}

	/**
	 * @return mixed
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_PS_RESTRICTIONS_BY_PERCENT_PRICE');
	}

	/**
	 * @return mixed
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage('SALE_PS_RESTRICTIONS_BY_PERCENT_PRICE_DESC');
	}

	/**
	 * @param $entityId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getParamsStructure($entityId = 0)
	{
		return array(
			"MIN_VALUE" => array(
				'TYPE' => 'NUMBER',
				'DEFAULT' => 0,
				'LABEL' => Loc::getMessage("SALE_PS_RESTRICTIONS_BY_PRICE_PERCENT_TYPE_MORE")
			),
			"MAX_VALUE" => array(
				'TYPE' => 'NUMBER',
				'DEFAULT' => 0,
				'LABEL' => Loc::getMessage("SALE_PS_RESTRICTIONS_BY_PRICE_PERCENT_TYPE_LESS")
			)
		);
	}
}