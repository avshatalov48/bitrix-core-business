<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$defCountry = "Россия";
$defCity = "c213"; //Moscow
$url = 'ts='.time();

$http = new \Bitrix\Main\Web\HttpClient();
$http->setTimeout(10);
$res = $http->get("https://export.yandex.ru/bar/reginfo.xml?".$url);

if($res !== false)
{
	$res = str_replace("\xE2\x88\x92", "-", $res);
	$res = $GLOBALS["APPLICATION"]->ConvertCharset($res, 'UTF-8', SITE_CHARSET);

	$xml = new CDataXML();
	$xml->LoadString($res);

	$node = $xml->SelectNodes('/info/weather/day/country');
	if(is_object($node))
	{
		$defCountry = $node->textContent();
	}

	$node = $xml->SelectNodes('/info/region');
	if(is_object($node))
	{
		$attrId = $node->getAttribute("id");
		if($attrId > 0)
			$defCity = "c".$attrId;
	}
}

$arCities = array();

$cache = new CPHPCache;
if($cache->StartDataCache(60*60*24*7, "gadget_yadex_weather"))
{
	$http = new \Bitrix\Main\Web\HttpClient();
	$http->setTimeout(20);
	$res = $http->get("https://pogoda.yandex.ru/static/cities.xml");
	if($res !== false)
	{
		$res = \Bitrix\Main\Text\Encoding::convertEncoding($res, 'UTF-8', SITE_CHARSET);
		$xml = new CDataXML();
		$xml->LoadString($res);
		$allCities = $xml->GetArray();
		if(is_array($allCities["cities"]["#"]["country"]))
		{
			foreach($allCities["cities"]["#"]["country"] as $country)
			{
				$countryCities = array();
				foreach($country["#"]["city"] as $cities)
				{
					$cityId = "c".$cities["@"]["region"];
					$cityName = $cities["#"];
					$countryCities[$cityId] = $cityName;
				}
				asort($countryCities);

				$countryName = $country["@"]["name"];
				$arCities[$countryName] = $countryCities;
			}

			$cache->EndDataCache($arCities);
		}
	}
}
else
{
	$arCities = $cache->GetVars();
}

$keys = array_keys($arCities);
$arCountries = array_combine($keys, $keys);

if(isset($_REQUEST["GP_COUNTRY"]))
{
	//refresh
	$currentCountry = $arCountries[$_REQUEST["GP_COUNTRY"]];
}
elseif(isset($arAllCurrentValues["COUNTRY"]["VALUE"]))
{
	$currentCountry = $arAllCurrentValues["COUNTRY"]["VALUE"];
}
else
{
	$currentCountry = $defCountry;
}

$arCity = $arCities[$currentCountry];

$arParameters = Array(
	"PARAMETERS"=> Array(
		"CACHE_TIME" => array(
			"NAME" => "Время кеширования, сек (0-не кешировать)",
			"TYPE" => "STRING",
			"DEFAULT" => "3600"
			),
		"SHOW_URL" => Array(
				"NAME" => "Показывать ссылку на подробную информацию",
				"TYPE" => "CHECKBOX",
				"MULTIPLE" => "N",
				"DEFAULT" => "N",
			),
	),
	"USER_PARAMETERS"=> Array(
		"COUNTRY"=>Array(
			"NAME" => "Страна",
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"REFRESH" => "Y",
			"DEFAULT" => $defCountry,
			"VALUES"=>$arCountries,
		),
		"CITY"=>Array(
			"NAME" => "Город",
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => $defCity,
			"VALUES"=>$arCity,
		),
	),
);
