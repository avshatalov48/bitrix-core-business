<?php

namespace Bitrix\UI\Currency;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Config\Option;

class CurrencyFormat
{
	public static function convertByDefault(float $price, string $currency, string $languageId = ''): string
	{
		if (empty($languageId))
		{
			$languageId = LANGUAGE_ID;
		}

		if ($languageId === 'en' && $currency === "USD")
		{
			return "$".number_format($price, 2, ".", ",");
		}
		if ($languageId === 'en' && $currency === "EUR")
		{
			return number_format($price, 2, ".", ",")." &euro;";
		}
		if ($languageId === 'de' && $currency === "EUR")
		{
			return number_format($price, 2, ",", ".")." &euro;";
		}
		if ($languageId === 'la' && $currency === "USD")
		{
			return "$".number_format($price, 2, ".", ",");
		}
		if ($languageId === 'br' && $currency === "BRL")
		{
			return "R$".number_format($price, 2, ",", ".");
		}
		if ($languageId === 'fr' && $currency === "EUR")
		{
			return number_format($price, 2, ".", ",")." &euro;";
		}
		if ($languageId === 'it' && $currency === "EUR")
		{
			return number_format($price, 2, ",", ".")." &euro;";
		}
		if ($languageId === 'pl' && $currency === "PLN")
		{
			return number_format($price, 2, ",", " ")." &#164;";
		}
		if ($languageId === 'tr' && $currency === "TRY")
		{
			return number_format($price, 2, ",", ".")."&#8378;";
		}
		if ($languageId === 'sc' && $currency === "CNY")
		{
			return "&#x00A5;".number_format($price, 2, ".", "");
		}
		if ($languageId === 'tc' && $currency === "TWD")
		{
			return "NT$;".number_format($price, 2, ".", ",");
		}
		if ($languageId === 'ja' && $currency === "JPY")
		{
			return number_format($price, 2, "", ",")."&#165;";
		}
		if ($languageId === 'vn' && $currency === "VND")
		{
			return number_format($price, 2, ",", ".")." &#8363;";
		}
		if ($languageId === 'id' && $currency === "IDR")
		{
			return "Rs. ".number_format($price, 2, ",", ".");
		}
		if ($languageId === 'ms' && $currency === "MYR")
		{
			return "RM ".number_format($price, 2, ".", ",");
		}
		if ($languageId === 'th' && $currency === "THB")
		{
			return "&#3647; ".number_format($price, 2, ".", ",");
		}
		if ($languageId === 'en' && $currency === "IDR")
		{
			return "Rs. ".number_format($price, 2, ".", ",");
		}
		if ($languageId === 'hi' && $currency === "IDR")
		{
			return "Rs. ".number_format($price, 2, ".", ",");
		}
		if ($languageId === 'en' && $currency === "GBP")
		{
			return "&#163;".number_format($price, 2, ".", ",");
		}
		if ($languageId === 'la' && $currency === "MXN")
		{
			return "$".number_format($price, 2, ",", ".");
		}
		if ($languageId === 'la' && $currency === "COP")
		{
			return "$".number_format($price, 2, ",", ".");
		}

		return "$".number_format($price, 2, ",", ".");
	}

	public static function convertBySettings(float $price, string $currency): string
	{
		$formatSetting = [];
		$langSetting = [];
		if (Loader::includeModule('currency') && Loader::includeModule('bitrix24'))
		{
			$currentAreaConfig = \CBitrix24::getCurrentAreaConfig();
			$currentAreaConfig['CURRENCY'] = $currency;
			$formatSetting = self::getFormatFromApi($currentAreaConfig);
			$langSetting = \CCurrencyLang::GetByID($currentAreaConfig['CURRENCY'], $currentAreaConfig["LANGUAGE_ID"]);
		}

		if (
			isset($formatSetting["DECIMALS"])
			&& isset($formatSetting["DECIMAL_SEPARATOR"])
			&& isset($formatSetting["THOUSANDS_SEPARATOR"])
			&& isset($formatSetting["FORMAT_STRING"])
		)
		{
			$formatSetting["THOUSANDS_SEP"] = $formatSetting["THOUSANDS_SEPARATOR"];
			$formatSetting["DEC_POINT"] = $formatSetting["DECIMAL_SEPARATOR"];
			$formatSetting['CURRENCY'] = $currency;

			return \CCurrencyLang::formatValue($price, $formatSetting);
		}
		elseif (
			isset($langSetting["DECIMALS"])
			&& isset($langSetting["DEC_POINT"])
			&& isset($langSetting["THOUSANDS_SEP"])
			&& isset($langSetting["FORMAT_STRING"])
		)
		{
			$langSetting['CURRENCY'] = $currency;
			return \CCurrencyLang::formatValue($price, $langSetting);
		}
		else
		{
			return self::convertByDefault($price, $currency);
		}

	}

	public static function getFormatFromApi(array $langSetting): array
	{
		$result = [];
		$apiCurrencyFormat = Option::get('bitrix24', 'currency_patterns_from_api', '');

		if ($apiCurrencyFormat !== '')
		{
			$resultOption = Json::decode($apiCurrencyFormat);
			if (
				isset($resultOption['time'])
				&& isset($resultOption['currencyFormat'])
				&& (((int)$resultOption['time'] + 60*60) > time())
			)
			{
				return $resultOption['currencyFormat'];
			}
		}

		$httpClient = new HttpClient();
		if (isset($langSetting['ID']) && isset($langSetting['LANGUAGE_ID']) && isset($langSetting['CURRENCY']))
		{
			$locationAreaId = $langSetting['ID'];
			$languageId = $langSetting['LANGUAGE_ID'];
			$currencyCode = $langSetting['CURRENCY'];
			$url = 'https://util.1c-bitrix.ru/b24/catalog/get.php?currencyCode=' . $currencyCode . '&productType=CLOUD'
				   . '&locationAreaId=' . $locationAreaId . '&languageId=' . $languageId . '&requestData=formatting|patterns'
			;

			$resultRequest = $httpClient->get($url);
			if ($resultRequest)
			{
				if ($httpClient->getStatus() === 200)
				{
					try
					{
						$resultDecode = Json::decode($resultRequest);
					}
					catch (ArgumentException $e)
					{
					}

					if (
						!empty($resultDecode["result"]["formatting"])
						&& is_array($resultDecode["result"]["formatting"])
					)
					{
						$result = $resultDecode["result"]["formatting"]["separators"];
						$result['FORMAT_STRING'] = $resultDecode["result"]["patterns"]["price"]['per_period'];
						$result['FORMAT_STRING'] = str_replace('#PRICE#', '#', $result['FORMAT_STRING']);
						$resultToOption = ['time' => time(), 'currencyFormat' => $result];
						Option::set('bitrix24', 'currency_patterns_from_api', JSON::encode($resultToOption));
					}
				}
			}
		}

		return $result;
	}

}