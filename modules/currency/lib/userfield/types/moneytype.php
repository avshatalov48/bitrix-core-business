<?php

namespace Bitrix\Currency\UserField\Types;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Currency\Helpers\Editor;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BaseType;
use CUserTypeManager;

Loc::loadLanguageFile(__FILE__);

class MoneyType extends BaseType
{
	public const
		USER_TYPE_ID = 'money',
		DB_SEPARATOR = '|',
		RENDER_COMPONENT = 'bitrix:currency.field.money';

	private const STRICT_FIELD_FORMAT = '/^\-?[0-9]+\.?[0-9]*\|[A-Z]{3}$/';
	private const LIGHT_FIELD_FORMAT = '/^\-?[0-9]+\.?[0-9]*(\|[A-Z]{3})?$/';

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

	public static function checkFields(array $userField, $value): array
	{
		$fieldName = HtmlFilter::encode(
			$userField['EDIT_FORM_LABEL'] ?: $userField['FIELD_NAME']
		);

		$result = [];

		if ($userField['MULTIPLE'] === 'N')
		{
			if (!self::checkValueFormat($userField, $value))
			{
				$result[] = [
					'id' => $userField['FIELD_NAME'],
					'text' => Loc::getMessage('USER_TYPE_MONEY_ERR_BAD_SINGLE_FORMAT',
						[
							'#FIELD_NAME#' => $fieldName,
						]
					),
				];
			}
		}
		else
		{
			if (is_array($value))
			{
				foreach ($value as $row)
				{
					if (
						!is_string($row)
						|| !self::checkValueFormat($userField, $row)
					)
					{
						$result[] = [
							'id' => $userField['FIELD_NAME'],
							'text' => Loc::getMessage('USER_TYPE_MONEY_ERR_BAD_ROW_FORMAT',
								[
									'#FIELD_NAME#' => $fieldName,
								]
							),
						];
						break;
					}
				}
			}
			else
			{
				if (!self::checkValueFormat($userField, $value))
				{
					$result[] = [
						'id' => $userField['FIELD_NAME'],
						'text' => Loc::getMessage('USER_TYPE_MONEY_ERR_BAD_SINGLE_FORMAT',
							[
								'#FIELD_NAME#' => $fieldName,
							]
						),
					];
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $userField
	 * @param $value
	 * @return string
	 */
	public static function onBeforeSave(array $userField, $value): string
	{
		if ($value === '' || $value === null)
		{
			return '';
		}

		if (!self::checkValueFormat($userField, $value))
		{
			return '';
		}

		[$value, $currency] = static::unFormatFromDb($value);

		if ($value !== '')
		{
			if (!$currency)
			{
				if (self::isStrictFormat($userField))
				{
					return '';
				}
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
		[$value, $currency] = static::unFormatFromDb($userField['SETTINGS']['DEFAULT_VALUE'] ?? null);
		if ($value !== '')
		{
			if($currency === '')
			{
				$currency = CurrencyManager::getBaseCurrency();
			}
			$value = static::formatToDB($value, $currency);
		}

		return [
			'DEFAULT_VALUE' => $value,
		];
	}

	/**
	 * @param string $value
	 * @param string|null $currency
	 * @return string
	 */
	public static function formatToDb(string $value, ?string $currency): string
	{
		$value = trim($value);
		if ($value === '')
		{
			return '';
		}

		$value = (string)Editor::prepareValue($value);

		$currency = trim((string)$currency);

		return $value . static::DB_SEPARATOR . $currency;
	}

	/**
	 * @param string|null $value
	 * @return array
	 */
	public static function unFormatFromDb(?string $value): array
	{
		if ($value === null || $value === '')
		{
			return [
				'',
				'',
			];
		}

		$result = explode(static::DB_SEPARATOR, $value);
		if (count($result) === 1)
		{
			$result[] = '';
		}

		return $result;
	}

	private static function checkValueFormat(array $userField, $value): bool
	{
		if ($value === '' || $value === null)
		{
			return true;
		}

		$isStrictFormat = self::isStrictFormat($userField);

		if (!$isStrictFormat)
		{
			if (
				is_int($value)
				|| is_float($value)
			)
			{
				$value = (string)$value;
			}
		}

		if (!is_string($value))
		{
			return false;
		}

		$format = $isStrictFormat
			? self::STRICT_FIELD_FORMAT
			: self::LIGHT_FIELD_FORMAT
		;
		$prepared = [];
		if (!preg_match($format, $value, $prepared))
		{
			return false;
		}

		return true;
	}

	private static function isStrictFormat(array $userField): bool
	{
		return ($userField['SETTINGS']['STRICT_FORMAT'] ?? 'N') === 'Y';
	}
}
