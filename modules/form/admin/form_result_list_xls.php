<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");

CModule::IncludeModule("form");

ClearVars();

$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$WEB_FORM_ID = intval($WEB_FORM_ID);
$z = CForm::GetByID($WEB_FORM_ID);
if ($form=$z->Fetch())
{
	$SHOW_ADDITIONAL = "Y";
	$SHOW_ANSWER_VALUE = "Y";
	$SHOW_STATUS = "Y";
	$WEB_FORM_NAME = $form["SID"];
	IncludeModuleLangFile(__FILE__);
	$s = dirname($APPLICATION->GetCurPage())."/".basename($APPLICATION->GetCurPage(),"_xls.php").".php";
	InitSorting($s);
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/body/form_result_list_handler.php");
	header("Content-Type: application/vnd.ms-excel");
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_excel_after.php");
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/body/form_result_list_table_excel.php");
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_excel.php");
}