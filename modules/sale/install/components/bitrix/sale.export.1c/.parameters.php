<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("sale"))
	return;

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList();
while($arUGroups = $dbUGroups -> Fetch())
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}

$rsSite = CSite::GetList("sort", "asc", array("ACTIVE" => "Y"));
$arSites = array("" => GetMessage("CP_BCI1_ALL_SITES"));
while ($arSite = $rsSite->GetNext())
{
	$arSites[$arSite["LID"]] = $arSite["NAME"];
}

$arStatuses = Array("" => GetMessage("CP_BCI1_NO"));
$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID));
while ($arStatus = $dbStatus->GetNext())
{
	$arStatuses[$arStatus["ID"]] = "[".$arStatus["ID"]."] ".$arStatus["NAME"];
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SITE_LIST" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_SITE_LIST"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => $arSites,
		),
		"IMPORT_NEW_ORDERS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SALE_1C_IMPORT_NEW_ORDERS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"1C_SITE_NEW_ORDERS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SALE_1C_SITE_NEW_ORDERS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => $arSites,
		),
		"EXPORT_PAYED_ORDERS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_EXPORT_PAYED_ORDERS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"EXPORT_ALLOW_DELIVERY_ORDERS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_EXPORT_ALLOW_DELIVERY_ORDERS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"CHANGE_STATUS_FROM_1C" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_CHANGE_STATUS_FROM_1C"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"EXPORT_FINAL_ORDERS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_EXPORT_FINAL_ORDERS"),
			"TYPE" => "LIST",
			"DEFAULT" => "",
			"VALUES" => $arStatuses,
		),
/*		"FINAL_STATUS_ON_DELIVERY" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_FINAL_STATUS_ON_DELIVERY"),
			"TYPE" => "LIST",
			"DEFAULT" => "F",
			"VALUES" => $arStatuses,
		),		
*/		"REPLACE_CURRENCY" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_REPLACE_CURRENCY"),
			"TYPE" => "TEXT",
			"DEFAULT" => GetMessage("CP_BCI1_REPLACE_CURRENCY_VALUE"),
		),		
		"GROUP_PERMISSIONS" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("CP_BCI1_GROUP_PERMISSIONS"),
			"TYPE" => "LIST",
			"VALUES" => $arUGroupsEx,
			"DEFAULT" => array(1),
			"MULTIPLE" => "Y",
		),
		"USE_ZIP" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("CP_BCI1_USE_ZIP"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"INTERVAL" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("SALE_1C_INTERVAL"),
			"TYPE" => "TEXT",
			"DEFAULT" => 30,
		),
		"FILE_SIZE_LIMIT" => array(
			"PARENT" => "ADDITIONAL",
			"NAME" => GetMessage("SALE_1C_FILE_SIZE_LIMIT"),
			"TYPE" => "TEXT",
			"DEFAULT" => 200*1024,
		),
	),
);
?>
