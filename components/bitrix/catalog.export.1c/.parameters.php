<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("catalog"))
	return;

$rsCatalog = CCatalog::GetList(Array("sort" => "asc"));
while($arr = $rsCatalog->Fetch())
{
	if(!$arr["PRODUCT_IBLOCK_ID"])
		$arIBlock[$arr["IBLOCK_ID"]] = "[".$arr["IBLOCK_ID"]."] ".CIBlock::GetArrayByID($arr["IBLOCK_ID"], "NAME");
}

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
while($arUGroups = $dbUGroups -> Fetch())
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCE1_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
		),
		"INTERVAL" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCE1_INTERVAL"),
			"TYPE" => "STRING",
			"DEFAULT" => 30,
		),
		"ELEMENTS_PER_STEP" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCE1_ELEMENTS_PER_STEP"),
			"TYPE" => "STRING",
			"DEFAULT" => 100,
		),
		"GROUP_PERMISSIONS" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCE1_GROUP_PERMISSIONS"),
			"TYPE" => "LIST",
			"VALUES" => $arUGroupsEx,
			"DEFAULT" => array(1),
			"MULTIPLE" => "Y",
		),
		"USE_ZIP" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCE1_USE_ZIP"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	),
);
?>
