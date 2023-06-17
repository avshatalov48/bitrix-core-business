<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

require_once(__DIR__."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/lang_admin.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_language";

$oSort = new CAdminSorting($sTableID, "sort", "asc");

$lAdmin = new CAdminList($sTableID, $oSort);

if($lAdmin->EditAction() && $isAdmin)
{
	foreach($_REQUEST["FIELDS"] as $ID => $arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;

		$ob = new CLanguage;
		if(!$ob->Update($ID, $arFields))
		{
			if($arFields["DEF"]!="Y")
				$arFields["DEF"] = "N";
			$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$ob->LAST_ERROR, $ID);
		}
	}
}

if(($arID = $lAdmin->GroupAction()) && $isAdmin)
{
	if (isset($_REQUEST['action_target']) && $_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$rsData = CLanguage::GetList();
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;

		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			if(!CLanguage::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
			}
			else
			{
				$DB->Commit();
			}
			break;
		case "activate":
		case "deactivate":
			$ob = new CLanguage;
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$ob->Update($ID, $arFields))
				$lAdmin->AddGroupError(GetMessage("EDIT_ERROR").$ob->LAST_ERROR, $ID);
			break;
		}
	}
}

$APPLICATION->SetTitle(GetMessage("TITLE"));

global $by, $order;

$langs = CLanguage::GetList($by, $order, Array());
$rsData = new CAdminResult($langs, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PAGES"), false));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"lid", "default"=>true),
	array("id"=>"ACTIVE","content"=>GetMessage('ACTIVE'), "sort"=>"active", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('SORT'), "sort"=>"sort", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("NAME"), "sort"=>"name", "default"=>true),
	array("id"=>"CODE", "content"=>GetMessage('lang_admin_code'), "sort"=>"code", "default"=>true),
	array("id"=>"DEF", "content"=>GetMessage("DEF"), "sort"=>"def", "default"=>true),
));

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes, "lang_edit.php?LID=".$f_ID."&lang=".LANGUAGE_ID, GetMessage("LANG_EDIT_TITLE"));
	$row->AddViewField("ID", '<a href="lang_edit.php?lang='.LANGUAGE_ID.'&amp;LID='.$f_ID.'" title="'.GetMessage("LANG_EDIT_TITLE").'">'.$f_ID.'</a>');
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("SORT");
	$row->AddInputField("NAME");
	$row->AddInputField("CODE");
	$row->AddCheckField("DEF");
	$arActions = Array();

	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("CHANGE"), "ACTION"=>$lAdmin->ActionRedirect("lang_edit.php?LID=".$f_ID));

	if($isAdmin)
	{
		$arActions[] = array("ICON"=>"copy", "TEXT"=>GetMessage("COPY"), "ACTION"=>$lAdmin->ActionRedirect("lang_edit.php?COPY_ID=".$f_ID));
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("DELETE"), "ACTION"=>"if(confirm('".GetMessage('CONFIRM_DEL')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(Array(
	"delete"=>true,
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
));

$aContext = array(
	array(
		"TEXT"	=> GetMessage("ADD_LANG"),
		"LINK"	=> "lang_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("ADD_LANG_TITLE"),
		"ICON"	=> "btn_new"
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
