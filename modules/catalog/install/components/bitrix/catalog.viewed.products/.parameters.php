<?
use Bitrix\Main\Loader,
	Bitrix\Catalog,
	Bitrix\Currency;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arCurrentValues */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

if (!Loader::includeModule('catalog'))
	return;

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$iblockFilter = (
	!empty($arCurrentValues['IBLOCK_TYPE'])
	? array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
	: array('ACTIVE' => 'Y')
);
$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch())
	$arIBlock[$arr['ID']] = '['.$arr['ID'].'] '.$arr['NAME'];
unset($arr, $rsIBlock, $iblockFilter);

// Prices
$catalogGroups = CCatalogIBlockParameters::getPriceTypesList();

$arAscDesc = array(
	"asc" => GetMessage("CVP_SORT_ASC"),
	"desc" => GetMessage("CVP_SORT_DESC"),
);

$showFromSection = isset($arCurrentValues['SHOW_FROM_SECTION']) && $arCurrentValues['SHOW_FROM_SECTION'] == 'Y';

$arComponentParameters = array(
	"GROUPS" => array(
		"PRICES" => array(
			"NAME" => GetMessage("CVP_PRICES"),
		),
		"BASKET" => array(
			"NAME" => GetMessage("CVP_BASKET"),
		),
	),
	"PARAMETERS" => array(
		"DETAIL_URL" => CIBlockParameters::GetPathTemplateParam(
			"DETAIL",
			"DETAIL_URL",
			GetMessage("CVP_DETAIL_URL"),
			"",
			"URL_TEMPLATES"
		),
		"BASKET_URL" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("CVP_BASKET_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "/personal/basket.php",
		),
		"ACTION_VARIABLE" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("CVP_ACTION_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "action_cvp",
		),
		"PRODUCT_ID_VARIABLE" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("CVP_PRODUCT_ID_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "id",
		),
		"PRODUCT_QUANTITY_VARIABLE" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("CVP_PRODUCT_QUANTITY_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "quantity",
			"HIDDEN" => (isset($arCurrentValues['USE_PRODUCT_QUANTITY']) && $arCurrentValues['USE_PRODUCT_QUANTITY'] == 'Y' ? 'N' : 'Y')
		),
		"ADD_PROPERTIES_TO_BASKET" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("CVP_ADD_PROPERTIES_TO_BASKET"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y"
		),
		"PRODUCT_PROPS_VARIABLE" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("CVP_PRODUCT_PROPS_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "prop",
			"HIDDEN" => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
		),
		"PARTIAL_PRODUCT_PROPERTIES" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("CVP_PARTIAL_PRODUCT_PROPERTIES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"HIDDEN" => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
		),
		"SHOW_OLD_PRICE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CVP_SHOW_OLD_PRICE"),
			"TYPE" => "CHECKBOX",
			"VALUES" => "Y",
		),
		'SHOW_DISCOUNT_PERCENT' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CVP_SHOW_DISCOUNT_PERCENT'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y'
		),
		"PRICE_CODE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CVP_PRICE_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $catalogGroups,
		),
		"SHOW_PRICE_COUNT" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CVP_SHOW_PRICE_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "1",
		),
		'PRODUCT_SUBSCRIPTION' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CVP_PRODUCT_SUBSCRIPTION'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N'
		),
		"PRICE_VAT_INCLUDE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CVP_VAT_INCLUDE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"USE_PRODUCT_QUANTITY" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("CVP_USE_PRODUCT_QUANTITY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"SHOW_NAME" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("CVP_SHOW_NAME"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SHOW_IMAGE" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("CVP_SHOW_IMAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		'MESS_BTN_BUY' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CVP_MESS_BTN_BUY'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('CVP_MESS_BTN_BUY_DEFAULT')
		),
		'MESS_BTN_DETAIL' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CVP_MESS_BTN_DETAIL'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('CVP_MESS_BTN_DETAIL_DEFAULT')
		),
		'MESS_BTN_SUBSCRIBE' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CVP_MESS_BTN_SUBSCRIBE'),
			'TYPE' => 'STRING',
			'DEFAULT' => GetMessage('CVP_MESS_BTN_SUBSCRIBE_DEFAULT')
		),
		"PAGE_ELEMENT_COUNT" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("CVP_PAGE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "5",
		),
		"SHOW_FROM_SECTION" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CVP_SHOW_FROM_SECTION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => 'N',
			"REFRESH" => "Y",
		),
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CVP_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CVP_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"SECTION_ID" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CVP_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$GLOBALS["CATALOG_CURRENT_SECTION_ID"]}',
			"HIDDEN" => ($showFromSection ? "N" : "Y")
		),
		"SECTION_CODE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CVP_SECTION_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
			"HIDDEN" => ($showFromSection ? "N" : "Y")
		),
		"SECTION_ELEMENT_ID" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CVP_SECTION_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$GLOBALS["CATALOG_CURRENT_ELEMENT_ID"]}',
			"HIDDEN" => ($showFromSection ? "N" : "Y")
		),
		"SECTION_ELEMENT_CODE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CVP_SECTION_ELEMENT_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
			"HIDDEN" => ($showFromSection ? "N" : "Y")
		),
		"DEPTH" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CVP_DEPTH"),
			"TYPE" => "STRING",
			"DEFAULT" => "2",
			"HIDDEN" => ($showFromSection ? "N" : "Y")
		),
		"CACHE_TIME" => array("DEFAULT"=>36000000),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CVP_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	),
);

// Params groups
$iblockMap = array();
$catalogs = array();
$productsCatalogs = array();
$skuCatalogs = array();
$catalogFilter = array('=IBLOCK.ACTIVE' => 'Y');
if ($iblockExists)
	$catalogFilter[] = array(
		'LOGIC' => 'OR',
		'=IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'],
		'=PRODUCT_IBLOCK_ID' =>  $arCurrentValues['IBLOCK_ID']
	);
$iterator = Catalog\CatalogIblockTable::getList(array(
	'select' => array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID', 'IBLOCK_NAME' => 'IBLOCK.NAME'),
	'filter' => $catalogFilter,
	'order' => array('IBLOCK_ID' => 'ASC')
));
while ($row = $iterator->fetch())
{
	$iblockMap[$row['IBLOCK_ID']] = array(
		'ID' => $row['IBLOCK_ID'],
		'NAME' => $row['IBLOCK_NAME']
	);
	unset($row['IBLOCK_NAME']);
	if ((int)$row['PRODUCT_IBLOCK_ID'] > 0)
	{
		$skuCatalogs[$row['PRODUCT_IBLOCK_ID']] = $row;
		if (!isset($productsCatalogs[$row['PRODUCT_IBLOCK_ID']]))
			$productsCatalogs[$row['PRODUCT_IBLOCK_ID']] = $row;
	}
	else
	{
		$productsCatalogs[$row['IBLOCK_ID']] = $row;
	}
}
unset($row, $iterator, $catalogFilter);

foreach($productsCatalogs as $catalog)
{
	$catalog['VISIBLE'] = isset($arCurrentValues['SHOW_PRODUCTS_' . $catalog['IBLOCK_ID']]) &&	$arCurrentValues['SHOW_PRODUCTS_' . $catalog['IBLOCK_ID']] == "Y";
	$catalogs[] = $catalog;

	if(isset($skuCatalogs[$catalog['IBLOCK_ID']]))
	{
		$skuCatalogs[$catalog['IBLOCK_ID']]['VISIBLE'] = $catalog['VISIBLE'];
		$catalogs[] = $skuCatalogs[$catalog['IBLOCK_ID']];
	}
}

$defaultListValues = array("-" => getMessage("CVP_UNDEFINED"));
foreach ($catalogs as $catalog)
{
	$catalogs[$catalog['IBLOCK_ID']] = $catalog;
	$iblock = $iblockMap[$catalog['IBLOCK_ID']];
	if ((int)$catalog['SKU_PROPERTY_ID'] > 0) // sku
		$groupName = sprintf(getMessage("CVP_GROUP_OFFERS_CATALOG_PARAMS"), $iblock['NAME']);
	else
		$groupName = sprintf(getMessage("CVP_GROUP_PRODUCT_CATALOG_PARAMS"), $iblock['NAME']);

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

	$propertyIterator = CIBlockProperty::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array("IBLOCK_ID" => $iblock['ID'], "ACTIVE" => "Y"));
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
	if ((int)$catalog['SKU_PROPERTY_ID'] <= 0)
	{
		$arComponentParameters["PARAMETERS"]['SHOW_PRODUCTS_' . $iblock['ID']] = array(
			"PARENT" => $groupId,
			"NAME" => GetMessage("CVP_SHOW_PRODUCTS"),
			"TYPE" => "CHECKBOX",
			"REFRESH" => "Y",
			"DEFAULT" => "N"
		);
	}

	$arComponentParameters["PARAMETERS"]['PROPERTY_CODE_' . $iblock['ID']] = array(
		"PARENT" => $groupId,
		"NAME" => GetMessage("CVP_PROPERTY_DISPLAY"),
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
		"NAME" => GetMessage("CVP_PROPERTY_ADD_TO_BASKET"),
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
		"NAME" => GetMessage("CVP_ADDITIONAL_IMAGE"),
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
			"NAME" => GetMessage("CVP_PROPERTY_GROUP"),
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
			'NAME' => GetMessage('CVP_PROPERTY_LABEL'),
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
	'NAME' => GetMessage('CVP_HIDE_NOT_AVAILABLE'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
);

$arComponentParameters["PARAMETERS"]['CONVERT_CURRENCY'] = array(
	'PARENT' => 'PRICES',
	'NAME' => GetMessage('CVP_CONVERT_CURRENCY'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y',
);

if (isset($arCurrentValues['CONVERT_CURRENCY']) && 'Y' == $arCurrentValues['CONVERT_CURRENCY'])
{
	$arComponentParameters['PARAMETERS']['CURRENCY_ID'] = array(
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('CVP_CURRENCY_ID'),
		'TYPE' => 'LIST',
		'VALUES' => Currency\CurrencyManager::getCurrencyList(),
		'DEFAULT' => Currency\CurrencyManager::getBaseCurrency(),
		"ADDITIONAL_VALUES" => "Y",
	);
}