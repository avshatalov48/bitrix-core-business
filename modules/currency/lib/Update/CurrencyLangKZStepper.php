<?php

namespace Bitrix\Currency\Update;

use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;
use Bitrix\Currency\CurrencyClassifier;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Currency\CurrencyLangTable;
use Bitrix\Main\Localization\LanguageTable;

class CurrencyLangKZStepper extends Stepper
{
	private const CURRENCY_LIMIT = 100;
	private const LANGUAGE_FOR_UPDATE = 'kz';

	protected static $moduleId = 'currency';

	public function execute(array &$option): bool
	{
		if (
			!Loader::includeModule('currency')
			|| !Loader::includeModule('bitrix24')
		)
		{
			return self::FINISH_EXECUTION;
		}

		$languageTableResult = LanguageTable::getRow([
			'select' => ['ID'],
			'filter' => ['=ID' => self::LANGUAGE_FOR_UPDATE],
		]);
		if (!$languageTableResult)
		{
			return self::FINISH_EXECUTION;
		}

		$option['steps'] = (int)($option['steps'] ?? 0);
		$option['count'] = (int)($option['count'] ?? CurrencyTable::getCount());

		$currencyIterator = CurrencyTable::getList([
			'select' => ['CURRENCY'],
			'offset' => $option['steps'],
			'limit' => self::CURRENCY_LIMIT,
			'order' => ['CURRENCY' => 'ASC'],
		]);
		while ($currency = $currencyIterator->fetch())
		{
			$option['steps']++;

			$currencyLangData = CurrencyLangTable::getRow(
				[
					'select' => ['CURRENCY', 'LID'],
					'filter' => [
						'=CURRENCY' => $currency['CURRENCY'],
						'=LID' => self::LANGUAGE_FOR_UPDATE,
					],
				],
			);
			if ($currencyLangData)
			{
				continue;
			}

			$currencyClassifierData = CurrencyClassifier::getCurrency(
				$currency['CURRENCY'],
				[self::LANGUAGE_FOR_UPDATE]
			);
			if (!$currencyClassifierData)
			{
				continue;
			}
			$languageData = $currencyClassifierData[mb_strtoupper(self::LANGUAGE_FOR_UPDATE)];

			$datetimeEntity = new \Bitrix\Main\DB\SqlExpression(
				\Bitrix\Main\Application::getConnection()->getSqlHelper()->getCurrentDateTimeFunction()
			);
			CurrencyLangTable::add([
					'CURRENCY' => $currency['CURRENCY'],
					'LID' => self::LANGUAGE_FOR_UPDATE,
					'FORMAT_STRING' => str_replace('#VALUE#', '#', $languageData['FORMAT_STRING']),
					'FULL_NAME' => $languageData['FULL_NAME'],
					'DEC_POINT' => $languageData['DEC_POINT'],
					'THOUSANDS_SEP' => null,
					'DECIMALS' => $languageData['DECIMALS'],
					'THOUSANDS_VARIANT' => $languageData['THOUSANDS_VARIANT'],
					'HIDE_ZERO' => 'Y',
					'CREATED_BY' => null,
					'DATE_CREATE' => $datetimeEntity,
					'MODIFIED_BY' => null,
					'TIMESTAMP_X' => $datetimeEntity,
			]);
			\Bitrix\Currency\CurrencyManager::clearCurrencyCache();
		}

		return $option['steps'] < $option['count'] ? self::CONTINUE_EXECUTION : self::FINISH_EXECUTION;
	}
}
