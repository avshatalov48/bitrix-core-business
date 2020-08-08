<?php

namespace Bitrix\Currency\UserField\Types;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Currency\Helpers\Editor;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;
use Bitrix\Main\UserField\Types\BaseType;
use CUserTypeManager;

Loc::loadLanguageFile(__FILE__);

class MoneyType extends BaseType
{
	public const
		USER_TYPE_ID = 'money',
		DB_SEPARATOR = '|',
		RENDER_COMPONENT = 'bitrix:currency.field.money';

	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_MONEY_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING,
		];
	}

	/**
	 * @return string
	 */
	public static function getDbColumnType(): string
	{
		return 'varchar(200)';
	}

	/**
	 * @param array $userField
	 * @param $value
	 * @return string
	 */
	public static function onBeforeSave(array $userField, $value)
	{
		list($value, $currency) = static::unFormatFromDb($value);

		if($value !== '')
		{
			if(!$currency)
			{
				$currency = CurrencyManager::getBaseCurrency();
			}
			return static::formatToDB($value, $currency);
		}

		return '';
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		list($value, $currency) = static::unFormatFromDb($userField['SETTINGS']['DEFAULT_VALUE']);
		if($value !== '')
		{
			if($currency === '')
			{
				$currency = CurrencyManager::getBaseCurrency();
			}
			$value = static::formatToDB($value, $currency);
		}

		return [
			'DEFAULT_VALUE' => $value
		];
	}

	/**
	 * @param string $value
	 * @param string|null $currency
	 * @return string
	 */
	public static function formatToDb(string $value, ?string $currency): string
	{
		if($value === '')
		{
			return '';
		}

		$value = (string)((float)$value);

		return $value . static::DB_SEPARATOR . trim($currency);
	}

	/**
	 * @param string|null $value
	 * @return array
	 */
	public static function unFormatFromDb(?string $value): array
	{
		return explode(static::DB_SEPARATOR, $value);
	}
}