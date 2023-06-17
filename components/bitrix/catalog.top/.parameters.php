<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var string $componentPath
 * @var string $componentName
 */

use Bitrix\Main\Loader,
	Bitrix\Main\Web\Json,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Currency;

if (!Loader::includeModule('iblock'))
	return;

$catalogIncluded = Loader::includeModule('catalog');
CBitrixComponent::includeComponentClass($componentName);

$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$offersIblock = array();
if ($catalogIncluded)
{
	$iterator = Catalog\CatalogIblockTable::getList(array(
		'select' => array('IBLOCK_ID'),
		'filter' => array('!=PRODUCT_IBLOCK_ID' => 0)
	));
	while ($row = $iterator->fetch())
		$offersIblock[$row['IBLOCK_ID']] = true;
	unset($row, $iterator);
}

$arIBlock = array();
$iblockFilter = [
	'ACTIVE' => 'Y',
];
if (!empty($arCurrentValues['IBLOCK_TYPE']))
{
	$iblockFilter['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch())
{
	$id = (int)$arr['ID'];
	if (isset($offersIblock[$id]))
		continue;
	$arIBlock[$id] = '['.$id.'] '.$arr['NAME'];
}
unset($id, $arr, $rsIBlock, $iblockFilter);
unset($offersIblock);

$arProperty = array();
$arProperty_N = array();
$arProperty_X = array();
if ($iblockExists)
{
	$propertyIterator = Iblock\PropertyTable::getList(array(
		'select' => array('ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE'),
		'filter' => array('=IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'], '=ACTIVE' => 'Y'),
		'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
	));
	while ($property = $propertyIterator->fetch())
	{
		$propertyCode = (string)$property['CODE'];
		if ($propertyCode == '')
		{
			$propertyCode = $property['ID'];
		}

		$propertyName = '['.$propertyCode.'] '.$property['NAME'];

		if ($property['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_FILE)
		{
			$arProperty[$propertyCode] = $propertyName;

			if ($property['MULTIPLE'] == 'Y')
			{
				$arProperty_X[$propertyCode] = $propertyName;
			}
			elseif ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST)
			{
				$arProperty_X[$propertyCode] = $propertyName;
			}
			elseif ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_ELEMENT && (int)$property['LINK_IBLOCK_ID'] > 0)
			{
				$arProperty_X[$propertyCode] = $propertyName;
			}
		}

		if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_NUMBER)
		{
			$arProperty_N[$propertyCode] = $propertyName;
		}
	}
	unset($propertyCode, $propertyName, $property, $propertyIterator);
}

$offers = false;
$filterDataValues = array();
$arProperty_Offers = array();
$arProperty_OffersWithoutFile = array();

if ($catalogIncluded && $iblockExists)
{
	$filterDataValues['iblockId'] = (int)$arCurrentValues['IBLOCK_ID'];
	$offers = CCatalogSku::GetInfoByProductIBlock($arCurrentValues['IBLOCK_ID']);
	if (!empty($offers))
	{
		$filterDataValues['offersIblockId'] = $offers['IBLOCK_ID'];
		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => array('ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE', 'SORT'),
			'filter' => array('=IBLOCK_ID' => $offers['IBLOCK_ID'], '=ACTIVE' => 'Y', '!=ID' => $offers['SKU_PROPERTY_ID']),
			'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
		));
		while ($property = $propertyIterator->fetch())
		{
			$propertyCode = (string)$property['CODE'];
			if ($propertyCode == '')
				$propertyCode = $property['ID'];
			$propertyName = '['.$propertyCode.'] '.$property['NAME'];

			$arProperty_Offers[$propertyCode] = $propertyName;
			if ($property['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_FILE)
				$arProperty_OffersWithoutFile[$propertyCode] = $propertyName;
		}
		unset($propertyCode, $propertyName, $property, $propertyIterator);
	}
}

$arSort = CIBlockParameters::GetElementSortFields(
	array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'),
	array('KEY_LOWERCASE' => 'Y')
);

$arPrice = array();
if ($catalogIncluded)
{
	$arSort = array_merge($arSort, CCatalogIBlockParameters::GetCatalogSortFields());
	if (isset($arSort['CATALOG_AVAILABLE']))
		unset($arSort['CATALOG_AVAILABLE']);
	$arPrice = CCatalogIBlockParameters::getPriceTypesList();
}
else
{
	$arPrice = $arProperty_N;
}

$arAscDesc = array(
	'asc' => GetMessage('IBLOCK_SORT_ASC'),
	'desc' => GetMessage('IBLOCK_SORT_DESC'),
);

$arComponentParameters = array(
	'GROUPS' => array(
		'SORT_SETTINGS' => array(
			'NAME' => GetMessage('SORT_SETTINGS'),
			'SORT' => 210
		),
		'ACTION_SETTINGS' => array(
			'NAME' => GetMessage('IBLOCK_ACTIONS')
		),
		'PRICES' => array(
			'NAME' => GetMessage('IBLOCK_PRICES'),
		),
		'BASKET' => array(
			'NAME' => GetMessage('IBLOCK_BASKET'),
		),
		'COMPARE' => array(
			'NAME' => GetMessage('IBLOCK_COMPARE')
		),
		"ANALYTICS_SETTINGS" => array(
			"NAME" => GetMessage("ANALYTICS_SETTINGS")
		),
		"EXTENDED_SETTINGS" => array(
			"NAME" => GetMessage("IBLOCK_EXTENDED_SETTINGS"),
			"SORT" => 10000
		)
	),
	'PARAMETERS' => array(
		'SEF_MODE' => array(),
		'SEF_RULE' => array(
			'VALUES' => array(
			),
		),
		'IBLOCK_TYPE' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('IBLOCK_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $arIBlockType,
			'REFRESH' => 'Y',
		),
		'IBLOCK_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('IBLOCK_IBLOCK'),
			'TYPE' => 'LIST',
			'ADDITIONAL_VALUES' => 'Y',
			'VALUES' => $arIBlock,
			'REFRESH' => 'Y',
		),
		'ELEMENT_SORT_FIELD' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('IBLOCK_ELEMENT_SORT_FIELD'),
			'TYPE' => 'LIST',
			'VALUES' => $arSort,
			'ADDITIONAL_VALUES' => 'Y',
			'DEFAULT' => 'sort',
		),
		'ELEMENT_SORT_ORDER' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('IBLOCK_ELEMENT_SORT_ORDER'),
			'TYPE' => 'LIST',
			'VALUES' => $arAscDesc,
			'DEFAULT' => 'asc',
			'ADDITIONAL_VALUES' => 'Y',
		),
		'ELEMENT_SORT_FIELD2' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('IBLOCK_ELEMENT_SORT_FIELD2'),
			'TYPE' => 'LIST',
			'VALUES' => $arSort,
			'ADDITIONAL_VALUES' => 'Y',
			'DEFAULT' => 'id',
		),
		'ELEMENT_SORT_ORDER2' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('IBLOCK_ELEMENT_SORT_ORDER2'),
			'TYPE' => 'LIST',
			'VALUES' => $arAscDesc,
			'DEFAULT' => 'desc',
			'ADDITIONAL_VALUES' => 'Y',
		),
		'FILTER_NAME' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IBLOCK_FILTER_NAME_IN'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		'SECTION_URL' => CIBlockParameters::GetPathTemplateParam(
			'SECTION',
			'SECTION_URL',
			GetMessage('IBLOCK_SECTION_URL'),
			'',
			'URL_TEMPLATES'
		),
		'DETAIL_URL' => CIBlockParameters::GetPathTemplateParam(
			'DETAIL',
			'DETAIL_URL',
			GetMessage('IBLOCK_DETAIL_URL'),
			'',
			'URL_TEMPLATES'
		),
		'DISPLAY_COMPARE' => Array(
			'PARENT' => 'COMPARE',
			'NAME' => GetMessage('CP_BCT_DISPLAY_COMPARE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y'
		),
		'ELEMENT_COUNT' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('IBLOCK_ELEMENT_COUNT'),
			'TYPE' => 'STRING',
			'HIDDEN' => isset($templateProperties['PRODUCT_ROW_VARIANTS']) ? 'Y' : 'N',
			'DEFAULT' => '9',
		),
		'LINE_ELEMENT_COUNT' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('IBLOCK_LINE_ELEMENT_COUNT'),
			'TYPE' => 'STRING',
			'HIDDEN' => isset($templateProperties['PRODUCT_ROW_VARIANTS']) ? 'Y' : 'N',
			'DEFAULT' => '3',
		),
		'PROPERTY_CODE' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('IBLOCK_PROPERTY'),
			'TYPE' => 'LIST',
			'REFRESH' => 'Y',
			'MULTIPLE' => 'Y',
			'VALUES' => $arProperty,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'PROPERTY_CODE_MOBILE' => array(),
		'OFFERS_FIELD_CODE' => CIBlockParameters::GetFieldCode(GetMessage('CP_BCT_OFFERS_FIELD_CODE'), 'VISUAL'),
		'OFFERS_PROPERTY_CODE' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCT_OFFERS_PROPERTY_CODE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $arProperty_Offers,
			'ADDITIONAL_VALUES' => 'Y',
		),
		'OFFERS_SORT_FIELD' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('CP_BCT_OFFERS_SORT_FIELD'),
			'TYPE' => 'LIST',
			'VALUES' => $arSort,
			'ADDITIONAL_VALUES' => 'Y',
			'DEFAULT' => 'sort',
		),
		'OFFERS_SORT_ORDER' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('CP_BCT_OFFERS_SORT_ORDER'),
			'TYPE' => 'LIST',
			'VALUES' => $arAscDesc,
			'DEFAULT' => 'asc',
			'ADDITIONAL_VALUES' => 'Y',
		),
		'OFFERS_SORT_FIELD2' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('CP_BCT_OFFERS_SORT_FIELD2'),
			'TYPE' => 'LIST',
			'VALUES' => $arSort,
			'ADDITIONAL_VALUES' => 'Y',
			'DEFAULT' => 'id',
		),
		'OFFERS_SORT_ORDER2' => array(
			'PARENT' => 'SORT_SETTINGS',
			'NAME' => GetMessage('CP_BCT_OFFERS_SORT_ORDER2'),
			'TYPE' => 'LIST',
			'VALUES' => $arAscDesc,
			'DEFAULT' => 'desc',
			'ADDITIONAL_VALUES' => 'Y',
		),
		'OFFERS_LIMIT' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCT_OFFERS_LIMIT'),
			'TYPE' => 'STRING',
			'DEFAULT' => 5
		),
		'PRICE_CODE' => array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_PRICE_CODE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $arPrice,
		),
		'USE_PRICE_COUNT' => array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_USE_PRICE_COUNT'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'SHOW_PRICE_COUNT' => array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_SHOW_PRICE_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '1'
		),
		'PRICE_VAT_INCLUDE' => array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_VAT_INCLUDE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'BASKET_URL' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('IBLOCK_BASKET_URL'),
			'TYPE' => 'STRING',
			'DEFAULT' => '/personal/basket.php',
		),
		'ACTION_VARIABLE' => array(
			'PARENT' => 'ACTION_SETTINGS',
			'NAME' => GetMessage('IBLOCK_ACTION_VARIABLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'action'
		),
		'PRODUCT_ID_VARIABLE' => array(
			'PARENT' => 'ACTION_SETTINGS',
			'NAME' => GetMessage('IBLOCK_PRODUCT_ID_VARIABLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'id'
		),
		'USE_PRODUCT_QUANTITY' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_BCT_USE_PRODUCT_QUANTITY'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		),
		'PRODUCT_QUANTITY_VARIABLE' => array(
			'PARENT' => 'URL_TEMPLATES',
			'NAME' => GetMessage('CP_BCT_PRODUCT_QUANTITY_VARIABLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'quantity',
			'HIDDEN' => (isset($arCurrentValues['USE_PRODUCT_QUANTITY']) && $arCurrentValues['USE_PRODUCT_QUANTITY'] == 'Y' ? 'N' : 'Y')
		),
		'ADD_PROPERTIES_TO_BASKET' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_BCT_ADD_PROPERTIES_TO_BASKET'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
			'REFRESH' => 'Y'
		),
		'PRODUCT_PROPS_VARIABLE' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_BCT_PRODUCT_PROPS_VARIABLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'prop',
			'HIDDEN' => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
		),
		'PARTIAL_PRODUCT_PROPERTIES' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_BCT_PARTIAL_PRODUCT_PROPERTIES'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'HIDDEN' => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
		),
		'PRODUCT_PROPERTIES' => array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_BCT_PRODUCT_PROPERTIES'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $arProperty_X,
			'HIDDEN' => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
		),
		'CACHE_TIME'  =>  Array('DEFAULT'=>36000000),
		'CACHE_FILTER' => array(
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('IBLOCK_CACHE_FILTER'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'CACHE_GROUPS' => array(
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => GetMessage('CP_BCT_CACHE_GROUPS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'COMPATIBLE_MODE' => array(
			'PARENT' => 'EXTENDED_SETTINGS',
			'NAME' => GetMessage('CP_BCT_COMPATIBLE_MODE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y'
		),
	),
);

if (isset($arCurrentValues['COMPATIBLE_MODE']) && $arCurrentValues['COMPATIBLE_MODE'] === 'N')
{
	unset($arComponentParameters['PARAMETERS']['OFFERS_LIMIT']);
}

if ($usePropertyFeatures)
{
	unset($arComponentParameters['PARAMETERS']['PROPERTY_CODE']);
	unset($arComponentParameters['PARAMETERS']['OFFERS_PROPERTY_CODE']);
	if (isset($arComponentParameters['PARAMETERS']['PRODUCT_PROPERTIES']))
		unset($arComponentParameters['PARAMETERS']['PRODUCT_PROPERTIES']);
}

// hack for correct sort
if (isset($templateProperties['PROPERTY_CODE_MOBILE']))
{
	$arComponentParameters['PARAMETERS']['PROPERTY_CODE_MOBILE'] = $templateProperties['PROPERTY_CODE_MOBILE'];
	unset($templateProperties['PROPERTY_CODE_MOBILE']);
}
else
{
	unset($arComponentParameters['PARAMETERS']['PROPERTY_CODE_MOBILE']);
}

if ($catalogIncluded)
{
	if (!empty($filterDataValues))
	{
		$arComponentParameters['PARAMETERS']['CUSTOM_FILTER'] = array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_BCT_CUSTOM_FILTER'),
			'TYPE' => 'CUSTOM',
			'JS_FILE' => CatalogTopComponent::getSettingsScript($componentPath, 'filter_conditions'),
			'JS_EVENT' => 'initFilterConditionsControl',
			'JS_MESSAGES' => Json::encode(array(
				'invalid' => GetMessage('CP_BCT_TPL_SETTINGS_INVALID_CONDITION')
			)),
			'JS_DATA' => Json::encode($filterDataValues),
			'DEFAULT' => ''
		);
	}

	$arComponentParameters['PARAMETERS']['HIDE_NOT_AVAILABLE'] = array(
		'PARENT' => 'DATA_SOURCE',
		'NAME' => GetMessage('CP_BCT_HIDE_NOT_AVAILABLE'),
		'TYPE' => 'LIST',
		'DEFAULT' => 'N',
		'VALUES' => array(
			'Y' => GetMessage('CP_BCT_HIDE_NOT_AVAILABLE_HIDE'),
			'L' => GetMessage('CP_BCT_HIDE_NOT_AVAILABLE_LAST'),
			'N' => GetMessage('CP_BCT_HIDE_NOT_AVAILABLE_SHOW')
		),
		'ADDITIONAL_VALUES' => 'N'
	);
	$arComponentParameters['PARAMETERS']['HIDE_NOT_AVAILABLE_OFFERS'] = array(
		'PARENT' => 'DATA_SOURCE',
		'NAME' => GetMessage('CP_BCT_HIDE_NOT_AVAILABLE_OFFERS'),
		'TYPE' => 'LIST',
		'DEFAULT' => 'N',
		'VALUES' => array(
			'Y' => GetMessage('CP_BCT_HIDE_NOT_AVAILABLE_OFFERS_HIDE'),
			'L' => GetMessage('CP_BCT_HIDE_NOT_AVAILABLE_OFFERS_SUBSCRIBE'),
			'N' => GetMessage('CP_BCT_HIDE_NOT_AVAILABLE_OFFERS_SHOW')
		)
	);
	$arComponentParameters['PARAMETERS']['CONVERT_CURRENCY'] = array(
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('CP_BCT_CONVERT_CURRENCY'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y',
	);

	if (isset($arCurrentValues['CONVERT_CURRENCY']) && $arCurrentValues['CONVERT_CURRENCY'] == 'Y')
	{
		$arComponentParameters['PARAMETERS']['CURRENCY_ID'] = array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('CP_BCT_CURRENCY_ID'),
			'TYPE' => 'LIST',
			'VALUES' => Currency\CurrencyManager::getCurrencyList(),
			'DEFAULT' => Currency\CurrencyManager::getBaseCurrency(),
			'ADDITIONAL_VALUES' => 'Y',
		);
	}
}

if (empty($offers))
{
	unset($arComponentParameters['PARAMETERS']['OFFERS_FIELD_CODE']);
	if (isset($arComponentParameters['PARAMETERS']['OFFERS_PROPERTY_CODE']))
		unset($arComponentParameters['PARAMETERS']['OFFERS_PROPERTY_CODE']);
	unset($arComponentParameters['PARAMETERS']['OFFERS_SORT_FIELD']);
	unset($arComponentParameters['PARAMETERS']['OFFERS_SORT_ORDER']);
	unset($arComponentParameters['PARAMETERS']['OFFERS_SORT_FIELD2']);
	unset($arComponentParameters['PARAMETERS']['OFFERS_SORT_ORDER2']);
}
else
{
	if (!$usePropertyFeatures)
	{
		$arComponentParameters['PARAMETERS']['OFFERS_CART_PROPERTIES'] = array(
			'PARENT' => 'BASKET',
			'NAME' => GetMessage('CP_BCT_OFFERS_CART_PROPERTIES'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $arProperty_OffersWithoutFile,
			'HIDDEN' => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
		);
	}
}

if (isset($arCurrentValues['DISPLAY_COMPARE']) && $arCurrentValues['DISPLAY_COMPARE'] == 'Y')
{
	$arComponentParameters['PARAMETERS']['COMPARE_PATH'] = array(
		'PARENT' => 'COMPARE',
		'NAME' => GetMessage('CP_BCT_COMPARE_PATH'),
		'TYPE' => 'STRING',
		'DEFAULT' => ''
	);
}