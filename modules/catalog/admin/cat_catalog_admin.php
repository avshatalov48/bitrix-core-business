<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

/** @global CMain $APPLICATION */

IncludeModuleLangFile(__FILE__);

if(!CModule::IncludeModule("catalog"))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("CAT_CADM_CATALOG_MODULE_IS_MISSING"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

$sTableID = "tbl_catalog_admin";
$lAdmin = new CAdminList($sTableID);

$arHeader = array(
	array(
		"id" => "NAME",
		"content" => GetMessage("CAT_CADM_NAME"),
		"default" => true,
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("CAT_CADM_SORT"),
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "ACTIVE",
		"content" => GetMessage("CAT_CADM_ACTIVE"),
		"default" => true,
		"align" => "center",
	),
	array(
		"id" => "LID",
		"content" => GetMessage("CAT_CADM_LANG"),
		"default" => true,
		"align" => "left",
	),
	array(
		"id" => "ID",
		"content" => GetMessage("CAT_CADM_ID"),
		"sort" => "id",
		"default" => true,
		"align" => "right",
	),
);

$lAdmin->AddHeaders($arHeader);

$arCatalogs = array();
$rsCatalog = CCatalog::GetList(
	array(),
	array(),
	false,
	false,
	array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID')
);
while($ar = $rsCatalog->Fetch())
{
	if(!$ar["PRODUCT_IBLOCK_ID"])
		$arCatalogs[$ar["IBLOCK_ID"]] = 1;
}

$rsIBlocks = CIBlock::GetList(array("SORT"=>"asc", "NAME"=>"ASC"), array('ID' => array_keys($arCatalogs), "MIN_PERMISSION" => "U"));
$rsIBlocks = new CAdminResult($rsIBlocks, $sTableID);

while($dbrs = $rsIBlocks->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $dbrs, 'cat_catalog_edit.php?IBLOCK_ID='.$f_ID.'&lang='.LANGUAGE_ID);

	$f_LID = '';
	$db_LID = CIBlock::GetSite($f_ID);
	while($ar_LID = $db_LID->Fetch())
		$f_LID .= ($f_LID!=""?" / ":"").htmlspecialcharsbx($ar_LID["LID"]);

	$row->AddViewField("LID", $f_LID);
	$row->AddViewField("NAME", '<a href="'.htmlspecialcharsbx('cat_catalog_edit.php?IBLOCK_ID='.$f_ID.'&lang='.LANGUAGE_ID).'">'.$f_NAME.'</a>');
	$row->AddCheckField("ACTIVE", false);

	$arActions = array();

	if(CIBlockRights::UserHasRightTo($f_ID, $f_ID, "iblock_edit"))
	{
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => GetMessage("MAIN_ADMIN_MENU_EDIT"),
			"DEFAULT" => $_REQUEST["admin"] == "Y",
			"ACTION" => "window.location='".CUtil::JSEscape('cat_catalog_edit.php?IBLOCK_ID='.$f_ID.'&lang='.LANGUAGE_ID)."';",
		);
	}

	if(!empty($arActions))
		$row->AddActions($arActions);
}

$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage("CAT_CADM_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
