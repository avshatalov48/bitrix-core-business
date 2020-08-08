<?php
namespace Bitrix\Bizproc\UserType;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Loader;

class Money extends UserFieldBase
{
	protected static function extractValue(FieldType $fieldType, array $field, array $request)
	{
		$value = parent::extractValue($fieldType, $field, $request);

		if ($value && mb_strpos($value, '|') !== false)
		{
			list($sum, $currency) = explode('|', $value);
			$value = doubleval($sum) . '|' . $currency;
		}

		return $value;
	}

	/** @inheritdoc */
	public static function compareValues($valueA, $valueB)
	{
		if (mb_strpos($valueA, '|') === false || mb_strpos($valueB, '|') === false)
		{
			return parent::compareValues($valueA, $valueB);
		}

		list($sumA, $currencyA) = explode('|', $valueA);
		list($sumB, $currencyB) = explode('|', $valueB);

		$sumA = (double) $sumA;
		$sumB = (double) $sumB;

		if (!$currencyA)
		{
			$currencyA = self::getDefaultCurrencyId();
		}
		if (!$currencyB)
		{
			$currencyB = self::getDefaultCurrencyId();
		}

		if ($currencyA !== $currencyB)
		{
			$sumB = self::convertMoney($sumB, $currencyB, $currencyA);
		}

		return parent::compareValues($sumA, $sumB);
	}

	protected static function formatValuePrintable(FieldType $fieldType, $value)
	{
		$formatted = parent::formatValuePrintable($fieldType, $value);
		$formatted = str_replace('&nbsp;', ' ', $formatted);

		return $formatted;
	}

	private static function getDefaultCurrencyId()
	{
		static $currencyId;

		if($currencyId !== null)
		{
			return $currencyId;
		}

		$currencyId = 'USD';

		$lang = \CLanguage::GetByID('ru');
		if($lang->Fetch())
		{
			$currencyId = 'RUB';
		}
		else
		{
			$lang = \CLanguage::GetByID('de');
			if($lang->Fetch())
			{
				$currencyId = 'EUR';
			}
		}

		return $currencyId;
	}

	private static function normalizeCurrencyID($currencyID)
	{
		return mb_strtoupper(trim(strval($currencyID)));
	}

	private static function convertMoney($sum, $srcCurrencyID, $dstCurrencyID, $srcExchRate = -1)
	{
		$sum = doubleval($sum);

		if (!Loader::includeModule('currency'))
		{
			return $sum;
		}

		$srcCurrencyID = self::normalizeCurrencyID($srcCurrencyID);
		$dstCurrencyID = self::normalizeCurrencyID($dstCurrencyID);
		$srcExchRate = doubleval($srcExchRate);

		if($sum === 0.0 || $srcCurrencyID === $dstCurrencyID)
		{
			return $sum;
		}

		if($srcExchRate <= 0)
		{
			$result = \CCurrencyRates::ConvertCurrency($sum, $srcCurrencyID, $dstCurrencyID);
		}
		else
		{
			$result = \CCurrencyRates::ConvertCurrency(
				doubleval($sum * $srcExchRate),
				\Bitrix\Currency\CurrencyManager::getBaseCurrency(),
				$dstCurrencyID
			);
		}

		$decimals = 2;
		$formatInfo = \CCurrencyLang::GetCurrencyFormat($dstCurrencyID);
		if(isset($formatInfo['DECIMALS']))
		{
			$decimals = intval($formatInfo['DECIMALS']);
		}

		$result = round($result, $decimals);
		return $result;
	}
}