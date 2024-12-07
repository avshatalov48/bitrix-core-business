<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Currency;

/**
 * @var array $arParams
 * @var array $arResult
 */

$arParams['arrCURRENCY_FROM'] ??= [];
if (!is_array($arParams['arrCURRENCY_FROM']))
{
	$arParams['arrCURRENCY_FROM'] = [];
}

$arParams['arrCURRENCY_FROM'] = array_filter($arParams['arrCURRENCY_FROM']);

$arParams['CURRENCY_BASE'] = trim((string)($arParams['CURRENCY_BASE'] ?? ''));

$arParams['RATE_DAY'] = trim((string)($arParams['RATE_DAY'] ?? ''));

$arParams['SHOW_CB'] = ($arParams['SHOW_CB'] ?? null) == 'Y' ? 'Y' : 'N';
if ($arParams['CURRENCY_BASE'] !== 'RUB' && $arParams['CURRENCY_BASE'] !== 'RUR')
{
	$arParams['SHOW_CB'] = 'N';
}

$arParams['CACHE_TIME'] = (int)($arParams['CACHE_TIME'] ?? 86400);

if ($this->StartResultCache())
{
	if (!Loader::includeModule('currency'))
	{
		$this->AbortResultCache();
		ShowError(Loc::getMessage('CURRENCY_MODULE_NOT_INSTALLED'));

		return;
	}

	global $CACHE_MANAGER;

	$arResult = [];
	$arResult['CURRENCY'] = [];

	if ($arParams['CURRENCY_BASE'] === '')
	{
		$arParams['CURRENCY_BASE'] = Option::get('sale', 'default_currency');
	}

	if ($arParams['CURRENCY_BASE'] === '')
	{
		$arParams['CURRENCY_BASE'] = Currency\CurrencyManager::getBaseCurrency();
	}

	if ($arParams['CURRENCY_BASE'] !== '')
	{
		if ($arParams['RATE_DAY'] === '')
		{
			$arResult['RATE_DAY_TIMESTAMP'] = time();
		}
		else
		{
			$arRATE_DAY_PARSED = [
				'YYYY' => 0,
				'MM' => 0,
				'DD' => 0,
			];
			$parsed = [];
			if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $arParams['RATE_DAY'], $parsed))
			{
				$arRATE_DAY_PARSED = [
					'YYYY' => (int)$parsed[1],
					'MM' => (int)$parsed[2],
					'DD' => (int)$parsed[3],
				];
			}
			if (1901 > $arRATE_DAY_PARSED['YYYY'] || 2038 < $arRATE_DAY_PARSED['YYYY'])
			{
				$arResult['RATE_DAY_TIMESTAMP'] = time();
			}
			else
			{
				$arResult['RATE_DAY_TIMESTAMP'] = mktime(
					0,
					0,
					0,
					$arRATE_DAY_PARSED['MM'],
					$arRATE_DAY_PARSED['DD'],
					$arRATE_DAY_PARSED['YYYY']
				);
			}
		}
		$arResult['RATE_DAY_SHOW'] = ConvertTimeStamp($arResult['RATE_DAY_TIMESTAMP'], 'SHORT');

		if (!empty($arParams['arrCURRENCY_FROM']))
		{
			if ($arParams['SHOW_CB'] === 'Y')
			{
				$http = new Main\Web\HttpClient();
				$http->setRedirect(true);
				$strQueryText = $http->get(
					'https://www.cbr.ru/scripts/XML_daily.asp?date_req=' . date('d.m.Y', $arResult['RATE_DAY_TIMESTAMP'])
				);

				if (!empty($strQueryText))
				{
					$strQueryText = Main\Text\Encoding::convertEncoding($strQueryText, 'windows-1251', SITE_CHARSET);

					$strQueryText = preg_replace("#<!DOCTYPE[^>]+?>#i", "", $strQueryText);
					$strQueryText = preg_replace("#<"."\\?XML[^>]+?\\?".">#i", "", $strQueryText);

					$objXML = new CDataXML();
					$objXML->LoadString($strQueryText);
					$arData = $objXML->GetArray();

					$arFields = [];
					$arResult['CURRENCY_CBRF'] = [];

					if (!empty($arData) && is_array($arData))
					{
						if (!empty($arData["ValCurs"]) && is_array($arData["ValCurs"]))
						{
							if (!empty($arData["ValCurs"]["#"]) && is_array($arData["ValCurs"]["#"]))
							{
								if (!empty($arData["ValCurs"]["#"]["Valute"]) && is_array($arData["ValCurs"]["#"]["Valute"]))
								{
									foreach($arData["ValCurs"]["#"]["Valute"] as $arOneCBVal)
									{
										if (in_array($arOneCBVal["#"]["CharCode"][0]["#"], $arParams["arrCURRENCY_FROM"]))
										{
											$arCurrency = [
												"CURRENCY" => $arOneCBVal["#"]["CharCode"][0]["#"],
												"RATE_CNT" => (int)($arOneCBVal["#"]["Nominal"][0]["#"]),
												"RATE" => (float)(str_replace(",", ".", $arOneCBVal["#"]["Value"][0]["#"]))
											];

											$arResult["CURRENCY_CBRF"][] = [
												"FROM" => CCurrencyLang::CurrencyFormat($arCurrency["RATE_CNT"], $arCurrency["CURRENCY"], true),
												"BASE" => CCurrencyLang::CurrencyFormat($arCurrency["RATE"], $arParams["CURRENCY_BASE"], true),
											];
										}
									}
									unset($arOneCBVal);
								}
							}
						}
					}
				}
			}

			$currencyList = [];
			$iterator = Currency\CurrencyTable::getList([
				'select' => ['CURRENCY', 'AMOUNT_CNT'],
				'filter' => ['@CURRENCY' => $arParams["arrCURRENCY_FROM"]],
				'order' => ['CURRENCY' => 'ASC']
			]);
			while ($row = $iterator->fetch())
			{
				$currencyList[$row['CURRENCY']] = $row['CURRENCY'];
				$rate = CCurrencyRates::ConvertCurrency(
					$row['AMOUNT_CNT'],
					$row['CURRENCY'],
					$arParams['CURRENCY_BASE'],
					$arParams['RATE_DAY']
				);
				$arResult['CURRENCY'][] = [
					'FROM' => CCurrencyLang::CurrencyFormat($row['AMOUNT_CNT'], $row['CURRENCY'], true),
					'BASE' => CCurrencyLang::CurrencyFormat($rate, $arParams['CURRENCY_BASE'], true),
				];
				unset($rate);
			}
			unset($row, $iterator);

			if (!empty($currencyList) && defined('BX_COMP_MANAGED_CACHE'))
			{
				$currencyList[$arParams['CURRENCY_BASE']] = $arParams['CURRENCY_BASE'];

				$CACHE_MANAGER->StartTagCache($this->GetCachePath());
				foreach ($currencyList as $currency)
				{
					$CACHE_MANAGER->RegisterTag('currency_id_'.$currency);
				}
				unset($currency);
				$CACHE_MANAGER->EndTagCache();
			}
		}
	}

	$this->IncludeComponentTemplate();
}
