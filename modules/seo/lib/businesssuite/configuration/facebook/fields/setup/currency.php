<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Setup;

use Bitrix\Main\Loader;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields;

final class Currency implements Fields\IField, Fields\IAvailableFieldList
{
	/**
	 * get default currency value
	 * @return string|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getDefaultValue()
	{
		return in_array($current = self::getDefaultCurrency(),static::getCurrencyList())? $current : 'USD';
	}

	private static function getDefaultCurrency()
	{
		if (Loader::includeModule('bitrix24') && is_array($currencies = \CBitrix24::GetAvBillingCurrencies()))
		{
			$currentCurrency = current($currencies);
			return $currentCurrency !== 'RUR'? $currentCurrency : 'RUB';
		}

		if (Loader::includeModule('currency'))
		{
			return CurrencyManager::getBaseCurrency();
		}
		return false;
	}

	/**
	 * check currency code
	 *
	 * @param $value
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function checkValue($value): bool
	{
		if(is_string($value))
		{
			return in_array($value,static::getAvailableValues());
		}
		return false;
	}

	/**
	 * get available value
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getAvailableValues(): array
	{
		return static::getCurrencyList();
	}

	/**
	 * @inheritDoc
	 */
	public static function available(): bool
	{
		return true;

	}

	/**
	 * @inheritDoc
	 */
	public static function required(): bool
	{
		return true;
	}

	public static function getCurrencyList() : array
	{
		return [
			'DZD', 'ARS', 'AUD', 'BDT', 'BOB', 'BRL', 'GBP', 'CAD', 'CLP', 'CNY', 'COP', 'CRC', 'CZK', 'DKK', 'EGP', 'EUR',
			'GTQ', 'HNL', 'HKD', 'HUF', 'ISK', 'INR', 'IDR', 'ILS', 'JPY', 'KES', 'KRW', 'MOP', 'MYR', 'MXN', 'NZD', 'NIO',
			'NGN', 'NOK', 'PKR', 'PYG', 'PEN', 'PHP', 'PLN', 'QAR', 'RON', 'RUB', 'SAR', 'SGD', 'ZAR', 'SEK', 'CHF', 'TWD',
			'THB', 'TRY', 'AED', 'USD', 'UYU', 'VEF', 'VND'
		];
	}
}