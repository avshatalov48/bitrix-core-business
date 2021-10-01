<?php
namespace Bitrix\Currency\Helpers;

use Bitrix\Currency;

class Editor
{
	protected static $listCurrencyCache;

	public static function getListCurrency()
	{
		if (static::$listCurrencyCache === null)
		{
			static::$listCurrencyCache = [];

			$separators = \CCurrencyLang::GetSeparators();
			$defaultFormat = \CCurrencyLang::GetDefaultValues();
			$defaultFormat['SEPARATOR'] = $separators[$defaultFormat['THOUSANDS_VARIANT']];

			$iterator = Currency\CurrencyTable::getList([
				'select' => [
					'CURRENCY',
					'BASE',
					'SORT',
				],
				'order' => [
					'SORT' => 'ASC',
					'CURRENCY' => 'ASC',
				],
			]);
			while ($row = $iterator->fetch())
			{
				unset($row['SORT']);
				$row['NAME'] = $row['CURRENCY'];
				static::$listCurrencyCache[$row['CURRENCY']] = array_merge($row, $defaultFormat);
			}
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
						if ($row['THOUSANDS_VARIANT'] == Currency\CurrencyClassifier::SEPARATOR_NBSPACE)
						{
							static::$listCurrencyCache[$currencyId]['SEPARATOR'] = ' ';
						}
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
}
