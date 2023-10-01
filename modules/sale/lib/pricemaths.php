<?php

namespace Bitrix\Sale;

use Bitrix\Main;

class PriceMaths
{
	private static ?int $valuePrecision = null;

	/**
	 * @param $value
	 *
	 * @return float
	 */
	public static function roundPrecision($value)
	{
		if (!isset(self::$valuePrecision))
		{
			self::$valuePrecision = (int)Main\Config\Option::get('sale', 'value_precision');
			if (self::$valuePrecision < 0)
			{
				self::$valuePrecision = 2;
			}
		}

		return round((float)$value, self::$valuePrecision);
	}

	/**
	 * @deprecated Use \Bitrix\Sale\PriceMaths::roundPrecision instead it
	 *
	 * @param $price
	 * @param $currency
	 *
	 * @return float
	 */
	public static function roundByFormatCurrency($price, $currency)
	{
		return (float)SaleFormatCurrency($price, $currency, false, true);
	}
}