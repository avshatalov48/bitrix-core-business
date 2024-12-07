<?php
namespace Bitrix\Currency\Helpers;

use Bitrix\Currency;

class Editor
{
	protected const VALUE_MASK = '/^([+-]?)([0-9]*)(\.[0-9]*)?$/';

	protected static array $listCurrencyCache;

	/**
	 * Returns currency list for money editor.
	 *
	 * @return array
	 */
	public static function getListCurrency(): array
	{
		if (!isset(static::$listCurrencyCache))
		{
			static::$listCurrencyCache = [];

			$separators = \CCurrencyLang::GetSeparators();
			$separators[Currency\CurrencyClassifier::SEPARATOR_NBSPACE] = $separators[Currency\CurrencyClassifier::SEPARATOR_SPACE];

			$defaultFormat = \CCurrencyLang::GetDefaultValues();
			$defaultFormat['SEPARATOR'] = $separators[$defaultFormat['THOUSANDS_VARIANT']];

			$iterator = Currency\CurrencyTable::getList([
				'select' => [
					'CURRENCY',
					'NAME' => 'CURRENCY',
					'BASE',
					'SORT',
				],
				'order' => [
					'SORT' => 'ASC',
					'CURRENCY' => 'ASC',
				],
				'cache' => [
					'ttl' => 86400,
				],
			]);
			while ($row = $iterator->fetch())
			{
				unset($row['SORT']);
				static::$listCurrencyCache[$row['CURRENCY']] = array_merge($row, $defaultFormat);
			}
			unset($row, $iterator);

			if (!empty(static::$listCurrencyCache))
			{
				$iterator = Currency\CurrencyLangTable::getList([
					'select' => [
						'CURRENCY',
						'FULL_NAME',
						'FORMAT_STRING',
						'DEC_POINT',
						'THOUSANDS_VARIANT',
						'DECIMALS',
						'THOUSANDS_SEP',
						'HIDE_ZERO',
					],
					'filter' => [
						'@CURRENCY' => array_keys(static::$listCurrencyCache),
						'=LID' => LANGUAGE_ID,
					],
					'cache' => [
						'ttl' => 86400,
					],
				]);
				while ($row = $iterator->fetch())
				{
					$currencyId = $row['CURRENCY'];
					$row['FULL_NAME'] = (string)$row['FULL_NAME'];
					if ($row['FULL_NAME'] !== '')
					{
						static::$listCurrencyCache[$currencyId]['NAME'] = $row['FULL_NAME'];
					}

					unset($row['FULL_NAME'], $row['CURRENCY']);
					static::$listCurrencyCache[$currencyId] = array_merge(
						static::$listCurrencyCache[$currencyId],
						$row
					);

					if ($row['THOUSANDS_VARIANT'] !== null && isset($separators[$row['THOUSANDS_VARIANT']]))
					{
						static::$listCurrencyCache[$currencyId]['SEPARATOR'] = $separators[$row['THOUSANDS_VARIANT']];
					}
					else
					{
						static::$listCurrencyCache[$currencyId]['SEPARATOR'] = $row['THOUSANDS_SEP'];
					}
				}
			}
			unset($row, $iterator);
		}

		return static::$listCurrencyCache;
	}

	/**
	 * Parse money value in bcmath format.
	 *
	 * @param $value
	 * @return array|null
	 */
	public static function parseValue($value): ?array
	{
		if (!is_string($value))
		{
			return null;
		}

		$parsedValue = [];
		if (preg_match(self::VALUE_MASK, $value, $parsedValue))
		{
			$parsedValue[3] ??= '';
			return $parsedValue;
		}

		return null;
	}

	/**
	 * Check money value before save.
	 *
	 * @param $value
	 * @return string|int|float
	 */
	public static function prepareValue($value): string|int|float
	{
		if (is_int($value) || is_float($value))
		{
			return $value;
		}

		if (!is_string($value))
		{
			return '';
		}
		$value = trim($value);
		if ($value === '')
		{
			return '';
		}

		$parsedValue = static::parseValue($value);
		if ($parsedValue === null)
		{
			return (float)$value;
		}

		$result =
			($parsedValue[1] === '-' ? '-' : '')
			. ($parsedValue[2] === '' ? '0' : $parsedValue[2])
		;

		if ($parsedValue[3] !== '' && $parsedValue[3] !== '.')
		{
			$fraction = rtrim($parsedValue[3], '0');
			if ($fraction !== '.')
			{
				$result .= $fraction;
			}
		}

		return $result;
	}
}
