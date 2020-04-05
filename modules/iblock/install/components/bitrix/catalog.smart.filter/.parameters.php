<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Loader;
use Bitrix\Currency;

if (!Loader::includeModule('iblock'))
	return;

$catalogIncluded = Loader::includeModule('catalog');
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

$arPrice = array();
if ($catalogIncluded)
{
	$arPrice = CCatalogIBlockParameters::getPriceTypesList();
}

$arProperty_UF = array();
$arSProperty_LNS = array();
if ($iblockExists)
{
	$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arCurrentValues["IBLOCK_ID"]."_SECTION");
	foreach($arUserFields as $FIELD_NAME=>$arUserField)
	{
		$arProperty_UF[$FIELD_NAME] = $arUserField["LIST_COLUMN_LABEL"]? $arUserField["LIST_COLUMN_LABEL"]: $FIELD_NAME;
		if($arUserField["USER_TYPE"]["BASE_TYPE"]=="string")
			$arSProperty_LNS[$FIELD_NAME] = $arProperty_UF[$FIELD_NAME];
	}
	unset($arUserFields, $FIELD_NAME, $arUserField);
}

$arComponentParameters = array(
	"GROUPS" => array(
		"PRICES" => array(
			"NAME" => GetMessage("CP_BCSF_PRICES"),
		),
		"XML_EXPORT" => array(
			"NAME" => GetMessage("CP_BCSF_GROUP_XML_EXPORT"),
		),
	),
	"PARAMETERS" => array(
		"SEF_MODE" => array(),
		"SEF_RULE" => array(
			"VALUES" => array(
				"SECTION_ID" => array(
					"TEXT" => GetMessage("CP_BCSF_SECTION_ID"),
					"TEMPLATE" => "#SECTION_ID#",
					"PARAMETER_LINK" => "SECTION_ID",
					"PARAMETER_VALUE" => '={$_REQUEST["SECTION_ID"]}',
				),
				"SECTION_CODE" => array(
					"TEXT" => GetMessage("CP_BCSF_SECTION_CODE"),
					"TEMPLATE" => "#SECTION_CODE#",
					"PARAMETER_LINK" => "SECTION_CODE",
					"PARAMETER_VALUE" => '={$_REQUEST["SECTION_CODE"]}',
				),
				"SECTION_CODE_PATH" => array(
					"TEXT" => GetMessage("CP_BCSF_SECTION_CODE_PATH"),
					"TEMPLATE" => "#SECTION_CODE_PATH#",
					"PARAMETER_LINK" => "SECTION_CODE_PATH",
					"PARAMETER_VALUE" => '={$_REQUEST["SECTION_CODE_PATH"]}',
				),
				"SMART_FILTER_PATH" => array(
					"TEXT" => GetMessage("CP_BCSF_SMART_FILTER_PATH"),
					"TEMPLATE" => "#SMART_FILTER_PATH#",
					"PARAMETER_LINK" => "SMART_FILTER_PATH",
					"PARAMETER_VALUE" => '={$_REQUEST["SMART_FILTER_PATH"]}',
				),
			),
		),
		"IBLOCK_TYPE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSF_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSF_IBLOCK_ID"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"SECTION_ID" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSF_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SECTION_ID"]}',
		),
		"SECTION_CODE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSF_SECTION_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"PREFILTER_NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSF_PREFILTER_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "smartPreFilter",
		),
		"FILTER_NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSF_FILTER_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "arrFilter",
		),
		"PRICE_CODE" => array(
			"PARENT" => "PRICES",
			"NAME" => GetMessage("CP_BCSF_PRICE_CODE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arPrice,
		),
		"CACHE_TIME" => array(
			"DEFAULT" => 36000000,
		),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BCSF_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SAVE_IN_SESSION" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CP_BCSF_SAVE_IN_SESSION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"PAGER_PARAMS_NAME" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CP_BCSF_PAGER_PARAMS_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "arrPager"
		),
		"XML_EXPORT" => array(
			"PARENT" => "XML_EXPORT",
			"NAME" => GetMessage("CP_BCSF_XML_EXPORT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"SECTION_TITLE" => array(
			"PARENT" => "XML_EXPORT",
			"NAME" => GetMessage("CP_BCSF_SECTION_TITLE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => "-",
			"VALUES" => array_merge(
				array(
					"-" => " ",
					"NAME" => GetMessage("IBLOCK_FIELD_NAME"),
				), $arSProperty_LNS
			),
		),
		"SECTION_DESCRIPTION" => array(
			"PARENT" => "XML_EXPORT",
			"NAME" => GetMessage("CP_BCSF_SECTION_DESCRIPTION"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => "-",
			"VALUES" => array_merge(
				array(
					"-" => " ",
					"NAME" => GetMessage("IBLOCK_FIELD_NAME"),
					"DESCRIPTION" => GetMessage("IBLOCK_FIELD_DESCRIPTION"),
				), $arSProperty_LNS
			),
		),
	),
);

if ($arCurrentValues["SEF_MODE"] == "Y")
{
	$arComponentParameters["PARAMETERS"]["SECTION_CODE_PATH"] = array(
		"NAME" => GetMessage("CP_BCSF_SECTION_CODE_PATH"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	);
	$arComponentParameters["PARAMETERS"]["SMART_FILTER_PATH"] = array(
		"NAME" => GetMessage("CP_BCSF_SMART_FILTER_PATH"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	);
}

if ($catalogIncluded)
{
	$arComponentParameters["PARAMETERS"]['HIDE_NOT_AVAILABLE'] = array(
		'PARENT' => 'DATA_SOURCE',
		'NAME' => GetMessage('CP_BCSF_HIDE_NOT_AVAILABLE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	);

	$arComponentParameters["PARAMETERS"]['CONVERT_CURRENCY'] = array(
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('CP_BCSF_CONVERT_CURRENCY'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y',
	);

	if (isset($arCurrentValues['CONVERT_CURRENCY']) && $arCurrentValues['CONVERT_CURRENCY'] == 'Y')
	{
		$arComponentParameters['PARAMETERS']['CURRENCY_ID'] = array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('CP_BCSF_CURRENCY_ID'),
			'TYPE' => 'LIST',
			'VALUES' => Currency\CurrencyManager::getCurrencyList(),
			'DEFAULT' => Currency\CurrencyManager::getBaseCurrency(),
			"ADDITIONAL_VALUES" => "Y",
		);
	}
}

if (empty($arPrice))
{
	unset($arComponentParameters["PARAMETERS"]["PRICE_CODE"]);
}