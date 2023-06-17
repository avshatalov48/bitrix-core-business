<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */
use Bitrix\Main\Loader;

if (!Loader::includeModule("iblock"))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

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
	$arIBlock[$arr['ID']] = '['.$arr['ID'].'] '.$arr['NAME'];
unset($arr, $rsIBlock, $iblockFilter);

$arComponentParameters = array(
	"GROUPS" => array(
		"ACTION_SETTINGS" => array(
			"NAME" => GetMessage('IBLOCK_ACTIONS')
		)
	),
	"PARAMETERS" => array(
		"AJAX_MODE" => array(),
		"IBLOCK_TYPE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"DETAIL_URL" => CIBlockParameters::GetPathTemplateParam(
			"DETAIL",
			"DETAIL_URL",
			GetMessage("IBLOCK_DETAIL_URL"),
			"",
			"ADDITIONAL_SETTINGS"
		),
		"COMPARE_URL" => array(
			"NAME" => GetMessage("IBLOCK_COMPARE_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "compare.php"
		),
		"NAME" => array(
			"NAME" => GetMessage("IBLOCK_COMPARE_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "CATALOG_COMPARE_LIST"
		),
		"ACTION_VARIABLE" => array(
			"PARENT" => "ACTION_SETTINGS",
			"NAME" => GetMessage("IBLOCK_ACTION_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "action",
		),
		"PRODUCT_ID_VARIABLE" => array(
			"PARENT" => "ACTION_SETTINGS",
			"NAME" => GetMessage("IBLOCK_PRODUCT_ID_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "id",
		),
	),
);