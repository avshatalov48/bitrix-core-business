<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
use Bitrix\Main\Loader;

global $USER_FIELD_MANAGER;

if(!Loader::includeModule("iblock"))
	return;

$catalogIncluded = Loader::includeModule("catalog");

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$iblockFilter = [
	'ACTIVE' => 'Y',
];
if (!empty($arCurrentValues['IBLOCK_TYPE']))
{
	$iblockFilter['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
$rsIBlock = CIBlock::GetList(array("SORT" => "ASC"), $iblockFilter);
while($arr=$rsIBlock->Fetch())
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];

$arProperty_UF = array();
$arUserFields =
	$iblockExists
		? $USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$arCurrentValues["IBLOCK_ID"]."_SECTION", 0, LANGUAGE_ID)
		: []
;
foreach($arUserFields as $FIELD_NAME=>$arUserField)
{
	$arUserField['LIST_COLUMN_LABEL'] = (string)$arUserField['LIST_COLUMN_LABEL'];
	$arProperty_UF[$FIELD_NAME] = $arUserField['LIST_COLUMN_LABEL'] ? '['.$FIELD_NAME.']'.$arUserField['LIST_COLUMN_LABEL'] : $FIELD_NAME;
}

$isProducts = false;
if ($catalogIncluded && (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0))
{
	$catalog = CCatalogSku::GetInfoByIBlock($arCurrentValues['IBLOCK_ID']);
	$isProducts = (!empty($catalog));
	unset($catalog);
}

$countFilterList = array(
	"CNT_ALL" => GetMessage("CP_BCSL_COUNT_ELEMENTS_ALL")
);
$countFilterList["CNT_ACTIVE"] = ($isProducts
	? GetMessage("CP_BCSL_COUNT_PRODUCTS_ACTIVE")
	: GetMessage("CP_BCSL_COUNT_ELEMENTS_ACTIVE")
);
if ($isProducts)
{
	$countFilterList["CNT_AVAILABLE"] = GetMessage("CP_BCSL_COUNT_PRODUCTS_AVAILABLE");
}

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCSL_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCSL_IBLOCK_ID"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"SECTION_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCSL_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SECTION_ID"]}',
		),
		"SECTION_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCSL_SECTION_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"SECTION_URL" => CIBlockParameters::GetPathTemplateParam(
			"SECTION",
			"SECTION_URL",
			GetMessage("CP_BCSL_SECTION_URL"),
			"",
			"URL_TEMPLATES"
		),
		"COUNT_ELEMENTS" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSL_COUNT_ELEMENTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => 'Y',
			"REFRESH" => "Y"
		),
		"COUNT_ELEMENTS_FILTER" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSL_COUNT_ELEMENTS_FILTER"),
			"TYPE" => "LIST",
			"VALUES" => $countFilterList,
			"DEFAULT" => "CNT_ACTIVE",
			"HIDDEN" => (isset($arCurrentValues['COUNT_ELEMENTS']) && $arCurrentValues['COUNT_ELEMENTS'] == 'N' ? 'Y' : 'N')
		),
		"ADDITIONAL_COUNT_ELEMENTS_FILTER" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSL_ADDITIONAL_COUNT_ELEMENTS_FILTER"),
			"TYPE" => "STRING",
			"DEFAULT" => "additionalCountFilter",
			"HIDDEN" => (isset($arCurrentValues['COUNT_ELEMENTS']) && $arCurrentValues['COUNT_ELEMENTS'] == 'N' ? 'Y' : 'N'),
		),
		"HIDE_SECTIONS_WITH_ZERO_COUNT_ELEMENTS" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSL_HIDE_SECTIONS_WITH_ZERO_COUNT_ELEMENTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"HIDDEN" => (isset($arCurrentValues['COUNT_ELEMENTS']) && $arCurrentValues['COUNT_ELEMENTS'] == 'N' ? 'Y' : 'N'),
		),
		"TOP_DEPTH" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSL_TOP_DEPTH"),
			"TYPE" => "STRING",
			"DEFAULT" => '2',
		),
		"SECTION_FIELDS" => CIBlockParameters::GetSectionFieldCode(
			GetMessage("CP_BCSL_SECTION_FIELDS"),
			"DATA_SOURCE",
			array()
		),
		"SECTION_USER_FIELDS" =>array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("CP_BCSL_SECTION_USER_FIELDS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arProperty_UF,
		),
		"FILTER_NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage('CP_BCSL_FILTER_NAME_IN'),
			"TYPE" => "STRING",
			"DEFAULT" => "sectionsFilter",
		),
		"ADD_SECTIONS_CHAIN" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CP_BCSL_ADD_SECTIONS_CHAIN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>36000000),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BCSL_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CACHE_FILTER" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BCSL_CACHE_FILTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
	),
);
