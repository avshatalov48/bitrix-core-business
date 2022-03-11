<?php

namespace Bitrix\Conversion;

use CCurrencyLang;
use Bitrix\Main\Loader;

final class Utils
{
	public static function convertToBaseCurrency($value, $currency)
	{
		static $module, $baseCurrency;

		if (! $module)
		{
			$module = Loader::includeModule('currency');
			$baseCurrency = Config::getBaseCurrency();
		}

		if ($module && $currency != $baseCurrency)
		{
			$value = \CCurrencyRates::ConvertCurrency($value, $currency, $baseCurrency);
		}

		return $value;
	}

	public static function formatToBaseCurrency($value, $format = null)
	{
		static $module, $baseCurrency;

		if (! $module)
		{
			$module = Loader::includeModule('currency');
			$baseCurrency = Config::getBaseCurrency();
		}

		if ($module)
		{
			$value = \CCurrencyLang::CurrencyFormat($value, $baseCurrency);
		}

		return $value;
	}

	/** @deprecated */
	public static function getBaseCurrencyUnit() // TODO remove from sale
	{
		static $unit;

		if (!$unit)
		{
			$unit = Config::getBaseCurrency();
			if (Loader::includeModule('currency'))
			{
				$unit = trim(
					CCurrencyLang::getPriceControl(' ', $unit)
				);
			}
		}

		return $unit;
	}
}