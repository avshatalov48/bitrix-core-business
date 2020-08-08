<?
use Bitrix\Main\Loader;

define("ADMIN_MODULE_NAME", "perfmon");
define("PERFMON_STOP", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
Loader::includeModule('perfmon');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT == "D" || $DB->type !== "MYSQL")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

$sTableID = "tbl_perfmon_index_complete";
$lAdmin = new CAdminList($sTableID, $oSort);

if (($arID = $lAdmin->GroupAction()) && $RIGHT >= "W")
{
	switch ($_REQUEST['action'])
	{
	case "delete_ban":
		foreach ($arID as $ID)
			CPerfomanceIndexComplete::Delete($ID);
		break;
	case "delete_index":
		if ($isAdmin)
		{
			foreach ($arID as $ID)
			{
				$rs = CPerfomanceIndexComplete::GetList(array("=ID" => $ID));
				while ($ar = $rs->Fetch())
				{
					if ($DB->Query("ALTER TABLE ".$ar["TABLE_NAME"]." DROP INDEX ".$ar["INDEX_NAME"]))
						CPerfomanceIndexComplete::Delete($ID);
				}
			}
		}
		break;
	}
}

$lAdmin->AddHeaders(array(
	array(
		"id" => "BANNED",
		"content" => GetMessage("PERFMON_ICOMPLETE_STATUS"),
		"align" => "center",
		"default" => true,
	),
	array(
		"id" => "TABLE_NAME",
		"content" => GetMessage("PERFMON_ICOMPLETE_TABLE_NAME"),
		"default" => true,
	),
	array(
		"id" => "COLUMN_NAMES",
		"content" => GetMessage("PERFMON_ICOMPLETE_COLUMN_NAMES"),
		"default" => true,
	),
	array(
		"id" => "INDEX_NAME",
		"content" => GetMessage("PERFMON_ICOMPLETE_INDEX_NAME"),
		"default" => true,
	),
));

$cData = new CPerfomanceIndexComplete;
$rsData = $cData->GetList();

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PERFMON_ICOMPLETE_PAGE")));

while ($arRes = $rsData->NavNext(true, "f_"))
{
	$row = $lAdmin->AddRow($f_NAME, $arRes);
	$arActions = array();

	$row->AddViewField("COLUMN_NAMES", str_replace(",", "<br>", $f_COLUMN_NAMES));

	if ($f_BANNED == "N")
	{
		$row->AddViewField("BANNED", '<span class="adm-lamp adm-lamp-in-list adm-lamp-green" title="'.htmlspecialcharsbx(GetMessage("PERFMON_ICOMPLETE_GREEN_ALT")).'"></span>');
		if ($isAdmin)
		{
			$arActions[] = array(
				"TEXT" => GetMessage("PERFMON_ICOMPLETE_DELETE_INDEX"),
				"ACTION" => $lAdmin->ActionDoGroup($f_ID, 'delete_index'),
			);
		}
	}
	elseif ($f_BANNED == "Y")
	{
		$row->AddViewField("BANNED", '<span class="adm-lamp adm-lamp-in-list adm-lamp-red" title="'.htmlspecialcharsbx(GetMessage("PERFMON_ICOMPLETE_RED_ALT")).'"></span>');
		$row->AddViewField("INDEX_NAME", GetMessage("PERFMON_ICOMPLETE_NO_INDEX"));
		$arActions[] = array(
			"TEXT" => GetMessage("PERFMON_ICOMPLETE_DELETE_BAN"),
			"ACTION" => $lAdmin->ActionDoGroup($f_ID, 'delete_ban'),
		);
	}
	else
	{
		$row->AddViewField("BANNED", '<span class="adm-lamp adm-lamp-in-list adm-lamp-yellow" title="'.htmlspecialcharsbx(GetMessage("PERFMON_ICOMPLETE_YELLOW_ALT")).'"></span>');
	}

	if (!empty($arActions))
		$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsData->SelectedRowsCount(),
		),
	)
);

$lAdmin->AddAdminContextMenu(array());

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PERFMON_ICOMPLETE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>