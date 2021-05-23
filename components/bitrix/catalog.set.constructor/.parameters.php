<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader,
	Bitrix\Catalog,
	Bitrix\Currency;

if (!Loader::includeModule('catalog'))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$filterIBlock = (
	isset($arCurrentValues["IBLOCK_TYPE_ID"]) && !empty($arCurrentValues["IBLOCK_TYPE_ID"])
	? array("TYPE" => $arCurrentValues["IBLOCK_TYPE_ID"], "ACTIVE"=>"Y")
	: array("ACTIVE"=>"Y")
);
$rsIBlock = CIBlock::GetList(array("sort" => "asc"), $filterIBlock);
while ($arr = $rsIBlock->Fetch())
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
unset($arr, $rsIBlock, $filterIBlock);

$arPrice = array();
$priceTypeIterator = Catalog\GroupTable::getList(array(
	'select' => array('ID', 'NAME', 'NAME_LANG' => 'CURRENT_LANG.NAME'),
	'order' => array('SORT' => 'ASC')
));
while ($priceType = $priceTypeIterator->fetch())
{
	$priceType['NAME_LANG'] = (string)$priceType['NAME_LANG'];
	$arPrice[$priceType['NAME']] = '['.$priceType['NAME'].']'.($priceType['NAME_LANG'] != '' ? ' '.$priceType['NAME_LANG'] : '');
}
unset($priceType, $priceTypeIterator);

$arProperty_LNS = array();
$arProperty_N = array();
$arProperty_X = array();
$arProperty_L = array();

$rsProp = CIBlockProperty::GetList(array("sort"=>"asc", "name"=>"asc"), array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arCurrentValues['IBLOCK_ID']));
while ($arr = $rsProp->Fetch())
{
	$arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];

	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S")))
	{
		$arProperty_LNS[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}

	if ($arr["PROPERTY_TYPE"]=="L" && in_array($arr["CODE"], array("SPECIALOFFER","NEWPRODUCT","SALELEADER")))
	{
		$arProperty_L[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}

	if ($arr["PROPERTY_TYPE"]=="N")
	{
		$arProperty_N[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}

	if ($arr["PROPERTY_TYPE"]!="F")
	{
		if($arr["MULTIPLE"] == "Y")
			$arProperty_X[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
		elseif($arr["PROPERTY_TYPE"] == "L")
			$arProperty_X[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
		elseif($arr["PROPERTY_TYPE"] == "E" && $arr["LINK_IBLOCK_ID"] > 0)
			$arProperty_X[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}

$arOffers = CIBlockPriceTools::GetOffersIBlock($arCurrentValues["IBLOCK_ID"]);
$OFFERS_IBLOCK_ID = is_array($arOffers)? $arOffers["OFFERS_IBLOCK_ID"]: 0;
$arProperty_Offers = array();
if($OFFERS_IBLOCK_ID)
{
	$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("IBLOCK_ID"=>$OFFERS_IBLOCK_ID, "ACTIVE"=>"Y"));
	while($arr=$rsProp->Fetch())
	{
		if($arr["PROPERTY_TYPE"] != "F")
			$arProperty_Offers[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}

$arComponentParameters = array(
	"GROUPS" => array(
		"PRICES" => array(
			"NAME" => GetMessage("IBLOCK_PRICES"),
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CATALOG_SET_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CATALOG_SET_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
		),
		"ELEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CATALOG_SET_IBLOCK_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => ""
		),
		"BASKET_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_BASKET_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "/personal/basket.php",
		),
		"PRICE_CODE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CATALOG_SET_IBLOCK_PRICE_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arPrice,
		),
		"PRICE_VAT_INCLUDE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("IBLOCK_VAT_INCLUDE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CACHE_TIME" => array("DEFAULT"=>36000000),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BCT_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"BUNDLE_ITEMS_COUNT" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("CP_CSC_PARAM_TITLE_BUNDLE_ITEMS_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "3"
		)
	),
);

$arComponentParameters["PARAMETERS"]['CONVERT_CURRENCY'] = array(
	'PARENT' => 'PRICES',
	'NAME' => GetMessage('CP_BCT_CONVERT_CURRENCY'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y',
);

if (isset($arCurrentValues['CONVERT_CURRENCY']) && 'Y' == $arCurrentValues['CONVERT_CURRENCY'])
{
	$arComponentParameters['PARAMETERS']['CURRENCY_ID'] = array(
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('CP_BCT_CURRENCY_ID'),
		'TYPE' => 'LIST',
		'VALUES' => Currency\CurrencyManager::getCurrencyList(),
		'DEFAULT' => Currency\CurrencyManager::getBaseCurrency(),
		"ADDITIONAL_VALUES" => "Y",
	);
}

if($OFFERS_IBLOCK_ID)
{
	$arComponentParameters["PARAMETERS"]["OFFERS_CART_PROPERTIES"] = array(
		"PARENT" => "PRICES",
		"NAME" => GetMessage("CP_BCT_OFFERS_CART_PROPERTIES"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arProperty_Offers,
	);
}