<?
use \Bitrix\Main\Loader as Loader;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!Loader::includeModule("sale") || !Loader::includeModule("iblock") || !Loader::includeModule("catalog"))
{
	ShowError(GetMessage("SRP_NEED_REQUIRED_MODULES"));
	die();
}
// Prices
$catalogGroupIterator = CCatalogGroup::getList(array("NAME" => "ASC", "SORT" => "ASC"));
$catalogGroups = array();
while ($catalogGroup = $catalogGroupIterator->fetch())
{
	$catalogGroups[$catalogGroup['NAME']] = "[{$catalogGroup['NAME']}] {$catalogGroup['NAME_LANG']}";
}

$arAscDesc = array(
	"asc" => GetMessage("SRP_SORT_ASC"),
	"desc" => GetMessage("SRP_SORT_DESC"),
);
// iblockTypes
$iblockTypes = CIBlockParameters::getIBlockTypes();

$iblockNames = array();
$iblockIterator = CIBlock::GetList(array("SORT" => "ASC"), array("TYPE" => ($arCurrentValues["IBLOCK_TYPE"] != "-" ? $arCurrentValues["IBLOCK_TYPE"] : "")));
while ($iblock = $iblockIterator->fetch())
{
	$iblockNames[$iblock['ID']] = "[{$iblock['CODE']}] {$iblock['NAME']}";
}


$arComponentParameters = array(
	"GROUPS" => array(
		"PRICES" => array(
			"NAME" => GetMessage("SRP_PRICES"),
		),
		"BASKET" => array(
			"NAME" => GetMessage("SRP_BASKET"),
		),
	),
	"PARAMETERS" => array(

		"IBLOCK_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SRP_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $iblockTypes,
			"DEFAULT" => "catalog",
			"REFRESH" => "Y",
		),

		"IBLOCK_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SRP_IBLOCK"),
			"TYPE" => "LIST",
			"VALUES" => $iblockNames,
			"DEFAULT" => '={$_REQUEST["IBLOCK_ID"]}',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SRP_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["PRODUCT_ID"]}',
		),

		"CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SRP_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["PRODUCT_CODE"]}',
		),

		"MIN_BUYES" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SRP_MIN_BUYES"),
			"TYPE" => "STRING",
			"DEFAULT" => "1",
		),

		"CACHE_TIME" => array("DEFAULT"=>86400),

		"DETAIL_URL" => CIBlockParameters::GetPathTemplateParam(
				"DETAIL",
				"DETAIL_URL",
				GetMessage("SRP_DETAIL_URL"),
				"",
				"URL_TEMPLATES"
			),
		"BASKET_URL" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("SRP_BASKET_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "/personal/basket.php",
		),

		"ACTION_VARIABLE" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("SRP_ACTION_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "action",
		),
		"PRODUCT_ID_VARIABLE" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("SRP_PRODUCT_ID_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "id",
		),
		"PRODUCT_QUANTITY_VARIABLE" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("SRP_PRODUCT_QUANTITY_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "quantity",
		),
		"ADD_PROPERTIES_TO_BASKET" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("SRP_ADD_PROPERTIES_TO_BASKET"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y"
		),
		"PRODUCT_PROPS_VARIABLE" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("SRP_PRODUCT_PROPS_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "prop",
			"HIDDEN" => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
		),

		"PARTIAL_PRODUCT_PROPERTIES" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("SRP_PARTIAL_PRODUCT_PROPERTIES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"HIDDEN" => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
		),



		"SHOW_OLD_PRICE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("SRP_SHOW_OLD_PRICE"),
			"TYPE" => "CHECKBOX",
			"VALUES" => "N",
		),
		'SHOW_DISCOUNT_PERCENT' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('SRP_SHOW_DISCOUNT_PERCENT'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N'
		),
		"PRICE_CODE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("SRP_PRICE_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $catalogGroups,
		),

		"SHOW_PRICE_COUNT" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("SRP_SHOW_PRICE_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "1",
		),
		'PRODUCT_SUBSCRIPTION' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('SRP_PRODUCT_SUBSCRIPTION'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N'
		),

		"PRICE_VAT_INCLUDE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("SRP_VAT_INCLUDE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"USE_PRODUCT_QUANTITY" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("SRP_USE_PRODUCT_QUANTITY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"SHOW_NAME" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("SRP_SHOW_NAME"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SHOW_IMAGE" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("SRP_SHOW_IMAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		'MESS_BTN_BUY' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('SRP_MESS_BTN_BUY'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('SRP_MESS_BTN_BUY_DEFAULT')
		),

		'MESS_BTN_DETAIL' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('SRP_MESS_BTN_DETAIL'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('SRP_MESS_BTN_DETAIL_DEFAULT')
		),
		'MESS_NOT_AVAILABLE' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('SRP_MESS_NOT_AVAILABLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('SRP_MESS_NOT_AVAILABLE_DEFAULT')
		),
		'MESS_BTN_SUBSCRIBE' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('SRP_MESS_BTN_SUBSCRIBE'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('SRP_MESS_BTN_SUBSCRIBE_DEFAULT')
		),

		"PAGE_ELEMENT_COUNT" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("SRP_PAGE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "30",
		),
	),
);



// Params groups
$iblockMap = array();
$iblockIterator = CIBlock::GetList(array("SORT" => "ASC"), array("ACTIVE" => "Y"));
while ($iblock = $iblockIterator->fetch())
{
	$iblockMap[$iblock['ID']] = $iblock;
}

$catalogs = array();
$productsCatalogs = array();
$skuCatalogs = array();
$catalogIterator = CCatalog::GetList(
	array("IBLOCK_ID" => "ASC"),
	array("@IBLOCK_ID" => array_keys($iblockMap)),
	false,
	false,
	array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID')
);
while($catalog = $catalogIterator->fetch())
{
	$isOffersCatalog = (int)$catalog['PRODUCT_IBLOCK_ID'] > 0;
	if($isOffersCatalog)
	{
		$skuCatalogs[$catalog['PRODUCT_IBLOCK_ID']] = $catalog;
		if (!isset($productsCatalogs[$catalog['PRODUCT_IBLOCK_ID']]))
			$productsCatalogs[$catalog['PRODUCT_IBLOCK_ID']] = $catalog;
	}
	else
	{
		$productsCatalogs[$catalog['IBLOCK_ID']] = $catalog;
	}
}

foreach($productsCatalogs as $catalog)
{
	//$catalog['VISIBLE'] = isset($arCurrentValues['SHOW_PRODUCTS_' . $catalog['IBLOCK_ID']]) &&	$arCurrentValues['SHOW_PRODUCTS_' . $catalog['IBLOCK_ID']] == "Y";
	$catalog['VISIBLE'] = true;
	$catalogs[] = $catalog;

	if(isset($skuCatalogs[$catalog['IBLOCK_ID']]))
	{
		$skuCatalogs[$catalog['IBLOCK_ID']]['VISIBLE'] = $catalog['VISIBLE'];
		$catalogs[] = $skuCatalogs[$catalog['IBLOCK_ID']];
	}
}

$defaultListValues = array("-" => getMessage("SRP_UNDEFINED"));
foreach ($catalogs as $catalog)
{
	$catalogs[$catalog['IBLOCK_ID']] = $catalog;
	$iblock = $iblockMap[$catalog['IBLOCK_ID']];
	if ((int)$catalog['SKU_PROPERTY_ID'] > 0) // sku
		$groupName = sprintf(getMessage("SRP_GROUP_OFFERS_CATALOG_PARAMS"), $iblock['NAME']);
	else
		$groupName = sprintf(getMessage("SRP_GROUP_PRODUCT_CATALOG_PARAMS"), $iblock['NAME']);

	$groupId = 'CATALOG_PPARAMS_' . $iblock['ID'];
	$arComponentParameters['GROUPS'][$groupId] = array(
		"NAME" => $groupName
	);

	// Params in group
	// 1. Display Properties
	$listProperties = array();
	$allProperties = array();
	$fileProperties = array();
	$treeProperties = array();

	$propertyIterator = CIBlockProperty::getList(array("SORT" => "ASC", "NAME" => "ASC"), array("IBLOCK_ID" => $iblock['ID'], "ACTIVE" => "Y"));
	while ($property = $propertyIterator->fetch())
	{
		$property['ID'] = (int)$property['ID'];
		$propertyName = '[' . $property['ID'] . ']' . ('' != $property['CODE'] ? '[' . $property['CODE'] . ']' : '') . ' ' . $property['NAME'];
		if ('' == $property['CODE'])
			$property['CODE'] = $property['ID'];

		$allProperties[$property['CODE']] = $propertyName;

		if ('F' == $property['PROPERTY_TYPE'])
			$fileProperties[$property['CODE']] = $propertyName;

		if ('L' == $property['PROPERTY_TYPE'])
			$listProperties[$property['CODE']] = $propertyName;

		// skip property id
		if ($property['ID'] == $catalog['SKU_PROPERTY_ID'])
			continue;

		if ('L' == $property['PROPERTY_TYPE'] ||
			'E' == $property['PROPERTY_TYPE'] ||
			('S' == $property['PROPERTY_TYPE'] && 'directory' == $property['USER_TYPE'])
		)
			$treeProperties[$property['CODE']] = $propertyName;
	}

	// Properties
	// Common Catalog options
	/*if ((int)$catalog['SKU_PROPERTY_ID'] <= 0)
	{
		$arComponentParameters["PARAMETERS"]['SHOW_PRODUCTS_' . $iblock['ID']] = array(
			"PARENT" => $groupId,
			"NAME" => GetMessage("SRP_SHOW_PRODUCTS"),
			"TYPE" => "HIDDEN",
			"REFRESH" => "Y",
			"DEFAULT" => "Y"
		);
	}*/

	$arComponentParameters["PARAMETERS"]['PROPERTY_CODE_' . $iblock['ID']] = array(
		"PARENT" => $groupId,
		"NAME" => GetMessage("SRP_PROPERTY_DISPLAY"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $allProperties,
		"ADDITIONAL_VALUES" => "Y",
		"DEFAULT" => "",
		"HIDDEN" => (!$catalog['VISIBLE'] ? 'Y' : 'N')
	);

	// 3. Cart properties
	$arComponentParameters["PARAMETERS"]['CART_PROPERTIES_' . $iblock['ID']] = array(
		"PARENT" => $groupId,
		"NAME" => GetMessage("SRP_PROPERTY_ADD_TO_BASKET"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $treeProperties,
		"ADDITIONAL_VALUES" => "Y",
		"HIDDEN" => ((isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N') ||
			!$catalog['VISIBLE'] ? 'Y' : 'N')
	);

	// 2. Additional Image
	$arComponentParameters["PARAMETERS"]['ADDITIONAL_PICT_PROP_' . $iblock['ID']] = array(
		"PARENT" => $groupId,
		"NAME" => GetMessage("SRP_ADDITIONAL_IMAGE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" =>  $fileProperties,
		"ADDITIONAL_VALUES" => "N",
		"DEFAULT" => "-",
		"HIDDEN" => (!$catalog['VISIBLE'] ? 'Y' : 'N')
	);

	if ((int)$catalog['SKU_PROPERTY_ID'] > 0)
	{
		$arComponentParameters["PARAMETERS"]['OFFER_TREE_PROPS_' . $iblock['ID']] = array(
			"PARENT" => $groupId,
			"NAME" => GetMessage("SRP_PROPERTY_GROUP"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => array_merge($defaultListValues, $treeProperties),
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "-",
			"HIDDEN" => (!$catalog['VISIBLE'] ? 'Y' : 'N')
		);
	}
	else
	{
		$arComponentParameters['PARAMETERS']['LABEL_PROP_' . $iblock['ID']] = array(
			'PARENT' => $groupId,
			'NAME' => GetMessage('SRP_PROPERTY_LABEL'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'DEFAULT' => '-',
			'VALUES' => array_merge($defaultListValues, $listProperties),
			"HIDDEN" => (!$catalog['VISIBLE'] ? 'Y' : 'N')
		);
	}
}





$arComponentParameters["PARAMETERS"]['HIDE_NOT_AVAILABLE'] = array(
	'PARENT' => 'DATA_SOURCE',
	'NAME' => GetMessage('SRP_HIDE_NOT_AVAILABLE'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
);
if (Loader::includeModule('currency'))
{
	$arComponentParameters["PARAMETERS"]['CONVERT_CURRENCY'] = array(
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('SRP_CONVERT_CURRENCY'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y',
	);

	if (isset($arCurrentValues['CONVERT_CURRENCY']) && 'Y' == $arCurrentValues['CONVERT_CURRENCY'])
	{
		$arCurrencyList = array();
		$by = 'SORT';
		$order = 'ASC';
		$rsCurrencies = CCurrency::GetList($by, $order);
		while ($arCurrency = $rsCurrencies->Fetch())
		{
			$arCurrencyList[$arCurrency['CURRENCY']] = $arCurrency['CURRENCY'];
		}
		$arComponentParameters['PARAMETERS']['CURRENCY_ID'] = array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('SRP_CURRENCY_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arCurrencyList,
			'DEFAULT' => CCurrency::GetBaseCurrency(),
			"ADDITIONAL_VALUES" => "Y",
		);
	}
}
?>
