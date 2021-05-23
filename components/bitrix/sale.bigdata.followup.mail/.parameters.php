<?

use Bitrix\Main\Loader;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arCurrentValues */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

if (!Loader::includeModule("catalog"))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock=array();
$rsIBlock = CIBlock::GetList(array("sort" => "asc"), array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

// Prices
$catalogGroupIterator = CCatalogGroup::GetListEx(
	array("NAME" => "ASC", "SORT" => "ASC"),
	array(),
	false,
	false,
	array('ID', 'NAME', 'NAME_LANG')
);
$catalogGroups = array();
while ($catalogGroup = $catalogGroupIterator->Fetch())
{
	$catalogGroups[$catalogGroup['NAME']] = "[{$catalogGroup['NAME']}] {$catalogGroup['NAME_LANG']}";
}

$arAscDesc = array(
	"asc" => GetMessage("CVP_SORT_ASC"),
	"desc" => GetMessage("CVP_SORT_DESC"),
);

$showFromSection = isset($arCurrentValues['SHOW_FROM_SECTION']) && $arCurrentValues['SHOW_FROM_SECTION'] == 'Y';

$arComponentParameters = array(
	"PARAMETERS" => array(
		"ORDER_ID" => array(
			"NAME" => GetMessage('SBF_PARAM_ORDER_ID'),
			"TYPE" => "STRING",
			"DEFAULT" => "{#ORDER_ID#}",
		)
	)
);
