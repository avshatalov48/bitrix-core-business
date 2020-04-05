<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Currency;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

Loc::loadMessages(__FILE__);

$result = array(
	'STATUS' => '',
	'MESSAGE' => '',
	'RATE_CNT' => '',
	'RATE' => ''
);

if (!check_bitrix_sessid())
{
	$result['STATUS'] = 'ERROR';
	$result['MESSAGE'] = Loc::getMessage('BX_CURRENCY_GET_RATE_ERR_SESSION');
}
else
{
	if (!Loader::includeModule('currency'))
	{
		$result['STATUS'] = 'ERROR';
		$result['MESSAGE'] = Loc::getMessage('BX_CURRENCY_GET_RATE_ERR_MODULE_ABSENT');
	}
	else
	{
		$baseCurrency = Currency\CurrencyManager::getBaseCurrency();
		$baseCurrency = '';
		$date = '';
		$currency = '';
		if (isset($_REQUEST['BASE_CURRENCY']))
			$baseCurrency = (string)$_REQUEST['BASE_CURRENCY'];
		if ($baseCurrency == '')
			$baseCurrency = Currency\CurrencyManager::getBaseCurrency();
		if (isset($_REQUEST['DATE_RATE']))
			$date = (string)$_REQUEST['DATE_RATE'];
		if (isset($_REQUEST['CURRENCY']))
			$currency = (string)$_REQUEST['CURRENCY'];

		if ($baseCurrency == '')
		{
			$result['STATUS'] = 'ERROR';
			$result['MESSAGE'] = Loc::getMessage('BX_CURRENCY_GET_RATE_ERR_BASE_CURRENCY_ABSENT');
		}
		elseif ($date == '' || !$DB->IsDate($date))
		{
			$result['STATUS'] = 'ERROR';
			$result['MESSAGE'] = Loc::getMessage('BX_CURRENCY_GET_RATE_ERR_DATE_RATE');
		}
		elseif ($currency == '')
		{
			$result['STATUS'] = 'ERROR';
			$result['MESSAGE'] = Loc::getMessage('BX_CURRENCY_GET_RATE_ERR_CURRENCY');
		}
		else
		{
			$url = '';
			switch ($baseCurrency)
			{
				case 'UAH':
					$url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange/?date='.$DB->FormatDate($date, CLang::GetDateFormat('SHORT', LANGUAGE_ID), 'YMD');
					break;
				case 'BYR':
				case 'BYN':
					$url = 'http://www.nbrb.by/Services/XmlExRates.aspx?ondate='.$DB->FormatDate($date, CLang::GetDateFormat('SHORT', LANGUAGE_ID), 'Y-M-D');
					break;
				case 'RUB':
				case 'RUR':
					$url = 'https://www.cbr.ru/scripts/XML_daily.asp?date_req='.$DB->FormatDate($date, CLang::GetDateFormat('SHORT', LANGUAGE_ID), 'D.M.Y');
					break;
			}
			$http = new Main\Web\HttpClient();
			$http->setRedirect(true);
			$data = $http->get($url);

			$charset = 'windows-1251';
			$matches = array();
			if (preg_match("/<"."\?XML[^>]{1,}encoding=[\"']([^>\"']{1,})[\"'][^>]{0,}\?".">/i", $data, $matches))
			{
				$charset = trim($matches[1]);
			}
			$data = preg_replace("#<!DOCTYPE[^>]+?>#i", '', $data);
			$data = preg_replace("#<"."\\?XML[^>]+?\\?".">#i", '', $data);
			$data = $APPLICATION->ConvertCharset($data, $charset, SITE_CHARSET);

			$objXML = new CDataXML();
			$res = $objXML->LoadString($data);
			if ($res !== false)
				$data = $objXML->GetArray();
			else
				$data = false;

			if (!empty($data) && is_array($data))
			{
				switch ($baseCurrency)
				{
					case 'UAH':
						if (!empty($data["exchange"]["#"]['currency']) && is_array($data["exchange"]["#"]['currency']))
						{
							$currencyList = $data['exchange']['#']['currency'];
							foreach ($currencyList as $currencyRate)
							{
								if ($currencyRate['#']['cc'][0]['#'] == $currency)
								{

									$result['STATUS'] = 'OK';
									$result['RATE_CNT'] = 1;
									$result['RATE'] = (float)str_replace(",", ".", $currencyRate['#']['rate'][0]['#']);
									break;
								}
							}
							unset($currencyRate, $currencyList);
						}
						break;
					case 'BYR':
					case 'BYN':
						if (!empty($data["DailyExRates"]["#"]["Currency"]) && is_array($data["DailyExRates"]["#"]["Currency"]))
						{
							$currencyList = $data['DailyExRates']['#']['Currency'];
							foreach ($currencyList as $currencyRate)
							{
								if ($currencyRate["#"]["CharCode"][0]["#"] == $currency)
								{
									$result['STATUS'] = 'OK';
									$result['RATE_CNT'] = (int)$currencyRate["#"]["Scale"][0]["#"];
									$result['RATE'] = (float)str_replace(",", ".", $currencyRate["#"]["Rate"][0]["#"]);
									break;
								}
							}
							unset($currencyRate, $currencyList);
						}
						break;
					case 'RUB':
					case 'RUR':
						if (!empty($data["ValCurs"]["#"]["Valute"]) && is_array($data["ValCurs"]["#"]["Valute"]))
						{
							$currencyList = $data["ValCurs"]["#"]["Valute"];
							foreach ($currencyList as $currencyRate)
							{
								if ($currencyRate["#"]["CharCode"][0]["#"] == $currency)
								{
									$result['STATUS'] = 'OK';
									$result['RATE_CNT'] = (int)$currencyRate["#"]["Nominal"][0]["#"];
									$result['RATE'] = (float)str_replace(",", ".", $currencyRate["#"]["Value"][0]["#"]);
									break;
								}
							}
							unset($currencyRate, $currencyList);
						}
						break;
				}
			}
		}
		if ($result['STATUS'] != 'OK')
		{
			$result['STATUS'] = 'ERROR';
			$result['MESSAGE'] = Loc::getMessage('BX_CURRENCY_GET_RATE_ERR_RESULT_ABSENT');
		}
	}
}
$APPLICATION->RestartBuffer();
header('Content-Type: application/json');
echo Main\Web\Json::encode($result);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');