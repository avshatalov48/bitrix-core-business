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
if ($RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_perfmon_history";
$lAdmin = new CAdminList($sTableID);

if (($arID = $lAdmin->GroupAction()) && $RIGHT >= "W")
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$cData = new CPerfomanceHistory;
		$rsData = $cData->GetList(array("ID" => "ASC"));
		while ($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;
		$ID = intval($ID);
		switch ($_REQUEST['action'])
		{
			case "delete":
				CPerfomanceHistory::Delete($ID);
				break;
		}
	}
}

$lAdmin->AddHeaders(array(
	array(
		"id" => "ID",
		"content" => GetMessage("PERFMON_HIST_ID"),
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage("PERFMON_HIST_TIMESTAMP_X"),
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "TOTAL_MARK",
		"content" => GetMessage("PERFMON_HIST_TOTAL_MARK"),
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "ACCELERATOR_ENABLED",
		"content" => GetMessage("PERFMON_HIST_ACCELERATOR_ENABLED"),
		"align" => "right",
		"default" => true,
	),
));

$cData = new CPerfomanceHistory;
$rsData = $cData->GetList(array("ID" => "DESC"));

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PERFMON_HIST_PAGE")));

while ($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("TOTAL_MARK", perfmon_NumberFormat($f_TOTAL_MARK, 2));
	$row->AddCheckField("ACCELERATOR_ENABLED", false);

	$arActions = array();
	if ($RIGHT >= "W")
	{
		$arActions[] = array(
			"ICON" => "delete",
			"DEFAULT" => "Y",
			"TEXT" => GetMessage("PERFMON_HIST_DELETE"),
			"ACTION" => "if(confirm('".GetMessageJS('PERFMON_HIST_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
		);
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

$aContext = array();
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsData->SelectedRowsCount(),
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0",
		),
	)
);

$aGroupActions = array();
if ($RIGHT >= "W")
{
	$aGroupActions["delete"] = GetMessage("MAIN_ADMIN_LIST_DELETE");
}
$lAdmin->AddGroupActionTable($aGroupActions);

$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage("PERFMON_HIST_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>