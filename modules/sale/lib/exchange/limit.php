<?php


namespace Bitrix\Sale\Exchange;


use Bitrix\Bitrix24\Feature;

class Limit
{
	/**
	 * @return bool
	 */
	public static function isExchangeAvailable(): bool
	{
		$result = true;
		if (\CModule::IncludeModule('bitrix24'))
		{
			$result = Feature::isFeatureEnabled('limit_shop_1c');
		}

		return $result;
	}
}