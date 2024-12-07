<?
/*********************************************************************************
Delivery services for EMS Russian Post Service (http://www.emspost.ru/)
It uses on-line api. Delivery only from Russia.
Files:
- ems/city.php - list of EMS city ids
- ems/country.php - list of EMS country ids
*********************************************************************************/

CModule::IncludeModule("sale");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/delivery/delivery_ems.php');

define('DELIVERY_EMS_CACHE_LIFETIME', 2592000); // cache lifetime - 30 days (60*60*24*30)
define('DELIVERY_EMS_PRICE_TARIFF', 0.004956); // declared value koeff - 0,42% + VAT. https://www.pochta.ru/support/post-rules/valuable-departure
define('DELIVERY_EMS_WRITE_LOG', 0); // flag 'write to log'. use CDeliveryEMS::__WriteToLog() for logging.

class CDeliveryEMS
{
	public static function Init()
	{
		if (\Bitrix\Main\Loader::includeModule('currency') && $arCurrency = CCurrency::GetByID('RUR'))
		{
			$base_currency = 'RUR';
		}
		else
		{
			$base_currency = 'RUB';
		}

		return array(
			/* Basic description */
			"SID" => "ems",
			"NAME" => GetMessage('SALE_DH_EMS_NAME'),
			"DESCRIPTION" => GetMessage('SALE_DH_EMS_DESCRIPTION'),
			"DESCRIPTION_INNER" => GetMessage('SALE_DH_EMS_DESCRIPTION_INNER'),
			"BASE_CURRENCY" => $base_currency,

			"HANDLER" => __FILE__,

			/* Handler methods */
			"DBGETSETTINGS" => array("CDeliveryEMS", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliveryEMS", "SetSettings"),
			"GETCONFIG" => array("CDeliveryEMS", "GetConfig"),

			"COMPABILITY" => array("CDeliveryEMS", "Compability"),
			"CALCULATOR" => array("CDeliveryEMS", "Calculate"),

			"DEPRECATED" => "Y",

			/* List of delivery profiles */
			"PROFILES" => array(
				"delivery" => array(
					"TITLE" => GetMessage("SALE_DH_EMS_DELIVERY_TITLE"),
					"DESCRIPTION" => '', //GetMessage("SALE_DH_EMS_DELIVERY_DESCRIPTION"),

					"RESTRICTIONS_WEIGHT" => array(0, 31500),
					"RESTRICTIONS_SUM" => array(0),
				),
			)
		);
	}

	public static function GetConfig()
	{
		$arConfig = array(
			"CONFIG_GROUPS" => array(
				"all" => GetMessage('SALE_DH_EMS_CONFIG_TITLE'),
			),

			"CONFIG" => array(
				"category" => array(
					"TYPE" => "DROPDOWN",
					"DEFAULT" => 'att',
					"TITLE" => GetMessage('SALE_DH_EMS_CONFIG_CATEGORY'),
					"GROUP" => "all",
					"VALUES" => array(
						'att' => GetMessage('SALE_DH_EMS_CONFIG_CATEGORY_att'),
						'doc' => GetMessage('SALE_DH_EMS_CONFIG_CATEGORY_doc'),
					),
				),
			),
		);

		return $arConfig;
	}

	public static function GetSettings($strSettings)
	{
		return array(
			"category" => $strSettings == 'doc' ? 'doc' : 'att'
		);
	}

	public static function SetSettings($arSettings)
	{
		return ($arSettings["category"] == 'doc' ? 'doc' : 'att');
	}

	public static function JsObjectToPhp($data)
	{
		// json_decode recognize only UTF strings
		$arResult = json_decode($data, true);

		return $arResult;
	}

	public static function __EMSQuery($method, $arParams = array())
	{
		$arQuery = array('method='.$method);

		foreach ($arParams as $key => $value)
			$arQuery[] = $key.'='.urlencode($value);

		$error_number = 0;
		$error_text = "";
		$data = QueryGetData(
			'www.emspost.ru',
			80,
			'/api/rest',
			implode("&", $arQuery),
			$error_number,
			$error_text,
			'GET'
		);

		if (($pos = mb_strpos($data, "\n")) !== false)
		{
			$data = trim(mb_substr($data, 0, $pos));
		}

		CDeliveryEMS::__Write2Log($error_number.": ".$error_text);
		CDeliveryEMS::__Write2Log($data);

		$arResult = CDeliveryEMS::JsObjectToPhp($data);

		return $arResult;
	}

	public static function __GetLocation($location)
	{
		$arLocation = CSaleHelper::getLocationByIdHitCached($location);
		$arLocation["IS_RUSSIAN"] = CDeliveryEMS::__IsRussian($arLocation) ? "Y" : "N";

		if ($arLocation["IS_RUSSIAN"] == 'Y')
		{
			// Cities
			if(!empty($arLocation['CITY_NAME_ORIG']) || !empty($arLocation['CITY_SHORT_NAME']) || !empty($arLocation['CITY_NAME_LANG']) || !empty($arLocation['CITY_NAME']))
			{
				static $arEMSCityList;

				if (!is_array($arEMSCityList))
				{
					if (file_exists(__DIR__.'/ems/city.php'))
						require_once(__DIR__.'/ems/city.php');
				}

				$arLocation['CITY_NAME_ORIG'] = mb_strtoupper($arLocation['CITY_NAME_ORIG']);
				$arLocation['CITY_SHORT_NAME'] = mb_strtoupper($arLocation['CITY_SHORT_NAME']);
				$arLocation['CITY_NAME_LANG'] = mb_strtoupper($arLocation['CITY_NAME_LANG']);
				$arLocation['CITY_NAME'] = mb_strtoupper($arLocation['CITY_NAME']);

				if (is_array($arEMSCityList))
				{
					$arLocation['EMS_ID'] =
						$arEMSCityList[$arLocation['CITY_NAME_ORIG']] ? $arEMSCityList[$arLocation['CITY_NAME_ORIG']] :	(
							$arEMSCityList[$arLocation['CITY_SHORT_NAME']] ? $arEMSCityList[$arLocation['CITY_SHORT_NAME']] : (
								$arEMSCityList[$arLocation['CITY_NAME_LANG']] ? $arEMSCityList[$arLocation['CITY_NAME_LANG']] : (
									$arEMSCityList[$arLocation['CITY_NAME']] ? $arEMSCityList[$arLocation['CITY_NAME']] : (
										$arEMSCityList[mb_strtoupper($arLocation['CITY_NAME'])] ? $arEMSCityList[mb_strtoupper($arLocation['CITY_NAME'])] : ''
									)
								)
							)
						);

					$arLocation['EMS_TYPE'] = 'city';
				}
				else
				{
					$arLocation['EMS_CITIES_NOT_LOADED'] = true;
				}
			}


			if(empty($arLocation['EMS_ID']) && (!empty($arLocation['REGION_NAME_ORIG']) || !empty($arLocation['REGION_SHORT_NAME']) || !empty($arLocation['REGION_NAME_LANG']) || !empty($arLocation['REGION_NAME'])))
			{
				// Regions
				static $arEMSRegionList;

				if (!is_array($arEMSRegionList))
				{
					if (file_exists(__DIR__.'/ems/region.php'))
						require_once(__DIR__.'/ems/region.php');
				}

				if($arLocation['REGION_NAME_ORIG'] == 'Саха /Якутия/ Респ' || $arLocation['REGION_NAME_ORIG'] == 'Республика Саха (Якутия)')
					$arLocation['REGION_NAME_ORIG']  = 'САХА (ЯКУТИЯ) РЕСПУБЛИКА';
				elseif($arLocation['REGION_NAME_ORIG'] == 'Еврейская Аобл')
					$arLocation['REGION_NAME_ORIG']  = 'ЕВРЕЙСКАЯ АВТОНОМНАЯ ОБЛАСТЬ';
				elseif($arLocation['REGION_NAME_ORIG'] == 'Ненецкий АО')
					$arLocation['REGION_NAME_ORIG']  = 'НЕНЕЦКИЙ АВТОНОМНЫЙ ОКРУГ';
				elseif($arLocation['REGION_NAME_ORIG'] == 'Северная Осетия - Алания Респ')
					$arLocation['REGION_NAME_ORIG']  = 'СЕВЕРНАЯ ОСЕТИЯ-АЛАНИЯ РЕСПУБЛИКА';
				elseif($arLocation['REGION_NAME_ORIG'] == 'Ханты-Мансийский Автономный округ - Югра АО' || $arLocation['REGION_NAME_ORIG'] == 'Ханты-Мансийский автономный округ')
					$arLocation['REGION_NAME_ORIG']  = 'ХАНТЫ-МАНСИЙСКИЙ-ЮГРА АВТОНОМНЫЙ ОКРУГ';
				elseif($arLocation['REGION_NAME_ORIG'] == 'Чукотский АО')
					$arLocation['REGION_NAME_ORIG']  = 'ЧУКОТСКИЙ АВТОНОМНЫЙ ОКРУГ';
				elseif($arLocation['REGION_NAME_ORIG'] == 'Ямало-Ненецкий АО')
					$arLocation['REGION_NAME_ORIG']  = 'ЯМАЛО-НЕНЕЦКИЙ АВТОНОМНЫЙ ОКРУГ';
				elseif($arLocation['REGION_NAME_ORIG'] == 'Крым')
					$arLocation['REGION_NAME_ORIG']  = 'КРЫМ РЕСПУБЛИКА';

				$arLocation['REGION_NAME_ORIG'] = preg_replace('/\sОБЛ$/iu', ' ОБЛАСТЬ', mb_strtoupper($arLocation['REGION_NAME_ORIG']));
				$arLocation['REGION_NAME_ORIG'] = preg_replace('/\sРЕСП$/u', ' РЕСПУБЛИКА', mb_strtoupper($arLocation['REGION_NAME_ORIG']));
				$arLocation['REGION_NAME_ORIG'] = preg_replace('/^(РЕСПУБЛИКА)\s*(.*)$/u', '$2 $1', mb_strtoupper($arLocation['REGION_NAME_ORIG']));

				$arLocation['REGION_NAME_ORIG'] = mb_strtoupper($arLocation['REGION_NAME_ORIG']);
				$arLocation['REGION_SHORT_NAME'] = mb_strtoupper($arLocation['REGION_SHORT_NAME']);
				$arLocation['REGION_NAME_LANG'] = mb_strtoupper($arLocation['REGION_NAME_LANG']);
				$arLocation['REGION_NAME'] = mb_strtoupper($arLocation['REGION_NAME']);

				if (is_array($arEMSRegionList))
				{
					$arLocation['EMS_ID'] =
						$arEMSRegionList[$arLocation['REGION_NAME_ORIG']] ? $arEMSRegionList[$arLocation['REGION_NAME_ORIG']] :	(
						$arEMSRegionList[$arLocation['REGION_SHORT_NAME']] ? $arEMSRegionList[$arLocation['REGION_SHORT_NAME']] : (
						$arEMSRegionList[$arLocation['REGION_NAME_LANG']] ? $arEMSRegionList[$arLocation['REGION_NAME_LANG']] : (
						$arEMSRegionList[$arLocation['REGION_NAME']] ? $arEMSRegionList[$arLocation['REGION_NAME']] : (
						$arEMSRegionList[mb_strtoupper($arLocation['REGION_NAME'])] ? $arEMSRegionList[mb_strtoupper($arLocation['REGION_NAME'])] : ''
						)
						)
						)
						);

					$arLocation['EMS_TYPE'] = 'region';
				}
				else
				{
					$arLocation['EMS_REGIONS_NOT_LOADED'] = true;
				}
			}
		}
		else
		{
			static $arEMSCountryList;

			if (!is_array($arEMSCountryList))
			{
				if (file_exists(__DIR__.'/ems/country.php'))
					require_once(__DIR__.'/ems/country.php');
			}

			if (is_array($arEMSCountryList))
			{				
				$arLocation['EMS_ID'] =
					$arEMSCountryList[$arLocation['COUNTRY_NAME_ORIG']] ? $arEMSCountryList[$arLocation['COUNTRY_NAME_ORIG']] :	(
						$arEMSCountryList[$arLocation['COUNTRY_SHORT_NAME']] ? $arEMSCountryList[$arLocation['COUNTRY_SHORT_NAME']] : (
							$arEMSCountryList[$arLocation['COUNTRY_NAME_LANG']] ? $arEMSCountryList[$arLocation['COUNTRY_NAME_LANG']] : (
								$arEMSCountryList[$arLocation['COUNTRY_NAME']] ? $arEMSCountryList[$arLocation['COUNTRY_NAME']] : (
									$arEMSCountryList[mb_strtoupper($arLocation['COUNTRY_NAME'])] ? $arEMSCountryList[mb_strtoupper($arLocation['COUNTRY_NAME'])] : ''
								)
							)
						)
					);
			}
			else
			{
				$arLocation['EMS_COUNTRIES_NOT_LOADED'] = true;
			}
		}

		return $arLocation;
	}

	public static function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
	{
		//echo '<pre style="text-align: left;">'; print_r($arOrder); print_r($arConfig); echo '</pre>';

		if ($STEP >= 4)
			return array(
				"RESULT" => "ERROR",
				"TEXT" => GetMessage('SALE_DH_EMS_ERROR_CONNECT'),
			);

		if ($arOrder["WEIGHT"] <= 0) $arOrder["WEIGHT"] = 1;

		$arLocationTo = CDeliveryEMS::__GetLocation($arOrder["LOCATION_TO"]);

		if ($arLocationTo['IS_RUSSIAN'] == 'Y')
			$arLocationFrom = CDeliveryEMS::__GetLocation($arOrder["LOCATION_FROM"]);

		if (isset($arLocationTo['EMS_CITIES_NOT_LOADED']))
		{
			// get cities and proceed to next step

			$data = CDeliveryEMS::__EMSQuery('ems.get.locations', array('type' => 'cities', 'plain' => 'true'));

			if (!is_array($data) || $data['rsp']['stat'] != 'ok' || !is_array($data['rsp']['locations']))
			{
				return array(
					"RESULT" => "ERROR",
					"TEXT" => GetMessage('SALE_DH_EMS_ERROR_CONNECT'),
				);
			}

			$arCitiesList = array();
			foreach ($data['rsp']['locations'] as $arLocation)
			{
				$arCitiesList[$arLocation['name']] = $arLocation['value'];
			}

			CheckDirPath(__DIR__."/ems/");
			if ($fp = fopen(__DIR__."/ems/city.php", "w"))
			{
				fwrite($fp, '<'."?\r\n");
				fwrite($fp, '$'."arEMSCityList = array();\r\n");
				foreach ($arCitiesList as $key => $value)
				{
					fwrite($fp, '$'."arEMSCityList['".addslashes($key)."'] = '".htmlspecialcharsbx(trim($value))."';\r\n");
				}
				fwrite($fp, '?'.'>');
				fclose($fp);
			}

			return array(
				"RESULT" => "NEXT_STEP",
				"TEXT" => GetMessage('SALE_DH_EMS_CORRECT_CITIES'),
			);
		}

		if (isset($arLocationTo['EMS_REGIONS_NOT_LOADED']))
		{
			// get cities and proceed to next step

			$data = CDeliveryEMS::__EMSQuery('ems.get.locations', array('type' => 'regions', 'plain' => 'true'));

			if (!is_array($data) || $data['rsp']['stat'] != 'ok' || !is_array($data['rsp']['locations']))
			{
				return array(
					"RESULT" => "ERROR",
					"TEXT" => GetMessage('SALE_DH_EMS_ERROR_CONNECT'),
				);
			}

			$arEMSRegionList = array();
			foreach ($data['rsp']['locations'] as $arLocation)
			{
				$arEMSRegionList[$arLocation['name']] = $arLocation['value'];
			}

			$path = __DIR__;
			CheckDirPath($path."/ems/");
			if ($fp = fopen($path."/ems/region.php", "w"))
			{
				fwrite($fp, '<'."?\r\n");
				fwrite($fp, '$'."arEMSRegionList = array();\r\n");
				foreach ($arEMSRegionList as $key => $value)
				{
					fwrite($fp, '$'."arEMSRegionList['".addslashes($key)."'] = '".htmlspecialcharsbx(trim($value))."';\r\n");
				}
				fwrite($fp, '?'.'>');
				fclose($fp);
			}

			return array(
				"RESULT" => "NEXT_STEP",
				"TEXT" => GetMessage('SALE_DH_EMS_CORRECT_REGIONS'),
			);
		}

		if (isset($arLocationTo['EMS_COUNTRIES_NOT_LOADED']))
		{
			// get cities and proceed to next step

			$data = CDeliveryEMS::__EMSQuery('ems.get.locations', array('type' => 'countries', 'plain' => 'true'));

			if (!is_array($data) || $data['rsp']['stat'] != 'ok' || !is_array($data['rsp']['locations']))
			{
				return array(
					"RESULT" => "ERROR",
					"TEXT" => GetMessage('SALE_DH_EMS_ERROR_CONNECT'),
				);
			}

			$arCountriesList = array();
			foreach ($data['rsp']['locations'] as $arLocation)
			{
				$arCountriesList[$arLocation['name']] = $arLocation['value'];
			}

			CheckDirPath(__DIR__."/ems/");
			if ($fp = fopen(__DIR__."/ems/country.php", "w"))
			{
				fwrite($fp, '<'."?\r\n");
				fwrite($fp, '$'."arEMSCountryList = array();\r\n");
				foreach ($arCountriesList as $key => $value)
				{
					fwrite($fp, '$'."arEMSCountryList['".addslashes($key)."'] = '".htmlspecialcharsbx(trim($value))."';\r\n");
				}
				fwrite($fp, '?'.'>');
				fclose($fp);
			}

			return array(
				"RESULT" => "NEXT_STEP",
				"TEXT" => GetMessage('SALE_DH_EMS_CORRECT_COUNTRIES'),
			);
		}

		// echo '<pre style="text-align: left">';
		// print_r($arLocationFrom);
		// print_r($arLocationTo);
		// echo '</pre>';

		if (!$arLocationTo['EMS_ID'])
		{
			if ($arLocationTo['IS_RUSSIAN'] == 'Y')
				$text = GetMessage('SALE_DH_EMS_ERROR_NO_LOCATION_TO');
			else
				$text = str_replace('#COUNTRY#', $arLocationTo['COUNTRY_NAME_ORIG'], GetMessage('SALE_DH_EMS_ERROR_NO_COUNTRY_TO'));

			return array(
				"RESULT" => "ERROR",
				"TEXT" => $text,
			);
		}

		if ($arLocationTo['IS_RUSSIAN'] == 'Y' && !$arLocationFrom['EMS_ID'])
		{
			$text = str_replace('#CITY#', $arLocationFrom['CITY_NAME_ORIG'], GetMessage('SALE_DH_EMS_ERROR_NO_CITY_FROM'));

			return array(
				"RESULT" => "ERROR",
				"TEXT" => $text,
			);
		}


		$cache_id = "sale|8.0.3|ems|".$profile."|".$arConfig["category"]["VALUE"]."|".$arOrder["LOCATION_FROM"]."|".$arOrder["LOCATION_TO"];

		// 0-0.1,0.1-0.5,0.5-1,1-1.5,1.5-2,2-3....30-31,31-31.5

		if ($arOrder['WEIGHT'] < 100)
			$cache_id .= '|weight_0';
		elseif ($arOrder['WEIGHT'] < 2000)
			$cache_id .= '|weight_half_'.(ceil($arOrder['WEIGHT']/1000) * 2);
		elseif ($arOrder['WEIGHT'] < 31000)
			$cache_id .= '|weight_'.(ceil($arOrder['WEIGHT']/1000));
		else
			$cache_id .= '|weight_max';

		$obCache = new CPHPCache();
		/*if ($obCache->InitCache(DELIVERY_EMS_CACHE_LIFETIME, $cache_id, "/"))
		{
			$vars = $obCache->GetVars();
			$result = $vars["RESULT"];
			$transit = $vars["TRANSIT"];

			if ($arLocationTo['IS_RUSSIAN'] == 'Y')
				$result += $arOrder["PRICE"] * DELIVERY_EMS_PRICE_TARIFF;

			return array(
				"RESULT" => "OK",
				"VALUE" => $result,
				'TRANSIT' => $transit,
			);
		}*/

		$arParams = array();

		if ($arLocationTo['IS_RUSSIAN'] != 'Y')
			$arParams['type'] = $arConfig["category"]["VALUE"];
		else
			$arParams['from'] = $arLocationFrom['EMS_ID'];

		$arParams['to'] = $arLocationTo['EMS_ID'];
		$arParams['weight'] = $arOrder['WEIGHT'] / 1000;
		$arParams['plain'] = 'true';
		$data = CDeliveryEMS::__EMSQuery('ems.calculate', $arParams);

		if (is_array($data) && $data['rsp']['stat'] == 'ok')
		{
			$obCache->StartDataCache();

			$result = doubleval($data['rsp']['price']);
			$transit = '';
			if ($data['rsp']['term'])
				$transit = $data['rsp']['term']['min'].'-'.$data['rsp']['term']['max'];

			$obCache->EndDataCache(
				array(
					"RESULT" => $result,
					"TRANSIT" => $transit." ".GetMessage("SALE_DH_EMS_DAYS"),
				)
			);

			if ($arLocationTo['IS_RUSSIAN'] == 'Y')
				$result += $arOrder["PRICE"] * DELIVERY_EMS_PRICE_TARIFF;

			return array(
				"RESULT" => "OK",
				"VALUE" => $result,
				'TRANSIT' => $data['rsp']['term']['min'].'-'.$data['rsp']['term']['max']." ".GetMessage("SALE_DH_EMS_DAYS")
			);
		}

		return array(
			"RESULT" => "ERROR",
			"TEXT" => GetMessage('SALE_DH_EMS_ERROR_RESPONSE').(is_array($data) ? ' ('.$data['rsp']['err']['msg'].')' : ''),
		);
	}

	public static function Compability($arOrder, $arConfig)
	{
		//It will work never.
		return array();

		$arLocationFrom = CSaleHelper::getLocationByIdHitCached($arOrder["LOCATION_FROM"]);
		$arLocationTo = CDeliveryEMS::__GetLocation($arOrder["LOCATION_TO"]);

		if ($arConfig['category']['VALUE'] == 'doc' && $arOrder['WEIGHT'] > 2000)
			return array();
		elseif (CDeliveryEMS::__IsRussian($arLocationFrom) && $arOrder['WEIGHT'] <= 31500 && $arLocationTo['EMS_ID'])
			return array('delivery');
		else
			return array();
	}

	public static function __IsRussian($arLocation)
	{
		return
			(mb_strtoupper($arLocation["COUNTRY_NAME_ORIG"]) == "РОССИЯ"
			|| mb_strtoupper($arLocation["COUNTRY_SHORT_NAME"]) == "РОССИЯ"
			|| mb_strtoupper($arLocation["COUNTRY_NAME_LANG"]) == "РОССИЯ"
			|| mb_strtoupper($arLocation["COUNTRY_NAME_ORIG"]) == "RUSSIA"
			|| mb_strtoupper($arLocation["COUNTRY_SHORT_NAME"]) == "RUSSIA"
			|| mb_strtoupper($arLocation["COUNTRY_NAME_LANG"]) == "RUSSIA"
			|| mb_strtoupper($arLocation["COUNTRY_NAME_ORIG"]) == "РОССИЙСКАЯ ФЕДЕРАЦИЯ"
			|| mb_strtoupper($arLocation["COUNTRY_SHORT_NAME"]) == "РОССИЙСКАЯ ФЕДЕРАЦИЯ"
			|| mb_strtoupper($arLocation["COUNTRY_NAME_LANG"]) == "РОССИЙСКАЯ ФЕДЕРАЦИЯ"
			|| mb_strtoupper($arLocation["COUNTRY_NAME_ORIG"]) == "RUSSIAN FEDERATION"
			|| mb_strtoupper($arLocation["COUNTRY_SHORT_NAME"]) == "RUSSIAN FEDERATION"
			|| mb_strtoupper($arLocation["COUNTRY_NAME_LANG"]) == "RUSSIAN FEDERATION"
			|| ($arLocation["COUNTRY_NAME_LANG"] === null && $arLocation["COUNTRY_NAME_ORIG"] === null)
		);
	}

	public static function __Write2Log($data)
	{
		if (defined('DELIVERY_EMS_WRITE_LOG') && DELIVERY_EMS_WRITE_LOG === 1)
		{
			$fp = fopen(__DIR__."/ems.log", "a");
			fwrite($fp, "\r\n==========================================\r\n");
			fwrite($fp, $data);
			fclose($fp);
		}
	}
}

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryEMS', 'Init'));
?>