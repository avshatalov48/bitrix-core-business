<?php

use Bitrix\Highloadblock as HL;

const ADMIN_MODULE_NAME = 'highloadblock';

/** @global \CUser $USER */
/** @global \CMain $APPLICATION */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile(__DIR__.'/menu.php');

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->SetTitle(GetMessage('HLBLOCK_ADMIN_MENU_TITLE'));

$sTableID = "tbl_hlblock_entity";
$oSort = new CAdminSorting($sTableID, "NAME", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('HLBLOCK_ADMIN_ENTITY_TITLE'), "sort"=>"NAME", "default"=>true)
);

$lAdmin->AddHeaders($arHeaders);

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$getListOrder = [
	$by => $order,
];
if ($by !== 'ID')
{
	$getListOrder['ID'] = 'ASC';
}
// select data
$rsData = HL\HighloadBlockTable::getList([
	"select" => $lAdmin->GetVisibleHeaderColumns(),
	"order" => $getListOrder,
]);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// build list
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PAGES")));
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row = $lAdmin->AddRow($f_ID, $arRes);

	$can_edit = true;

	$arActions = Array();

	$arActions[] = array(
		"ICON" => "list",
		"TEXT" => GetMessage('HLBLOCK_ADMIN_ROWS_LIST'),
		"ACTION" => $lAdmin->ActionRedirect("highloadblock_rows_list.php?ENTITY_ID=".$f_ID),
		"DEFAULT" => true
	);

	$arActions[] = array(
		"ICON"=>"list",
		"TEXT" => GetMessage('HLBLOCK_ADMIN_FIELDS_LIST'),
		"ACTION" => $lAdmin->ActionRedirect(
			"userfield_admin.php?lang=".LANGUAGE_ID."&set_filter=Y&find=HLBLOCK_".intval($f_ID)."&find_type=ENTITY_ID&back_url=".urlencode($APPLICATION->GetCurPageParam())
		)
	);

	$arActions[] = array(
		"ICON"=>"edit",
		"TEXT"=>GetMessage($can_edit ? "MAIN_ADMIN_MENU_EDIT" : "MAIN_ADMIN_MENU_VIEW"),
		"ACTION"=>$lAdmin->ActionRedirect("highloadblock_entity_edit.php?ID=".$f_ID)
	);

	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION" => "if(confirm('".GetMessageJS('HLBLOCK_ADMIN_DELETE_ENTITY_CONFIRM')."')) ".
			$lAdmin->ActionRedirect("highloadblock_entity_edit.php?action=delete&ID=".$f_ID.'&'.bitrix_sessid_get())
	);

	$row->AddActions($arActions);
}


// view

if ($lAdmin->isListMode())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
}
else
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	// menu
	$aMenu = [];
	$aMenu[] = [
		"TEXT" => GetMessage('HLBLOCK_ADMIN_ADD_ENTITY_BUTTON'),
		"TITLE" => GetMessage('HLBLOCK_ADMIN_ADD_ENTITY_BUTTON'),
		"LINK" => "highloadblock_entity_edit.php?lang=" . LANGUAGE_ID,
		"ICON" => "btn_new",
	];

	$adminContextMenu = new CAdminContextMenu($aMenu);
	$adminContextMenu->Show();
}

$lAdmin->CheckListMode();

$lAdmin->DisplayList();


if ($lAdmin->isListMode())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}
else
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
