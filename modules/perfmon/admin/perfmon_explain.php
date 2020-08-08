<?
use Bitrix\Main\Loader;

define("ADMIN_MODULE_NAME", "perfmon");
define("PERFMON_STOP", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global string $DBType */
Loader::includeModule('perfmon');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");
IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(GetMessage("PERFMON_EXPLAIN_TITLE"));

$ID = intval($ID);
$sTableID = "tbl_perfmon_explain";
$lAdmin = new CAdminList($sTableID);

if ($DBType == "mysql")
{
	$arHeader = array(
		array(
			"id" => "select_type",
			"content" => GetMessage("PERFMON_EXPLAIN_F_SELECT_TYPE"),
			"align" => "left",
			"default" => true,
		),
		array(
			"id" => "table",
			"content" => GetMessage("PERFMON_EXPLAIN_F_TABLE"),
			"align" => "left",
			"default" => true,
		),
		array(
			"id" => "type",
			"content" => GetMessage("PERFMON_EXPLAIN_F_TYPE"),
			"align" => "left",
			"default" => true,
		),
		array(
			"id" => "possible_keys",
			"content" => GetMessage("PERFMON_EXPLAIN_F_POSSIBLE_KEYS"),
			"align" => "left",
			"default" => true,
		),
		array(
			"id" => "key",
			"content" => GetMessage("PERFMON_EXPLAIN_F_KEY"),
			"align" => "left",
			"default" => true,
		),
		array(
			"id" => "key_len",
			"content" => GetMessage("PERFMON_EXPLAIN_F_KEY_LEN"),
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "ref",
			"content" => GetMessage("PERFMON_EXPLAIN_F_REF"),
			"align" => "left",
			"default" => true,
		),
		array(
			"id" => "rows",
			"content" => GetMessage("PERFMON_EXPLAIN_F_ROWS"),
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "Extra",
			"content" => GetMessage("PERFMON_EXPLAIN_F_EXTRA"),
			"align" => "left",
			"default" => true,
		),
	);
}
elseif ($DBType == "oracle")
{
	$arHeader = array(
		array(
			"id" => "OPERATION",
			"content" => GetMessage("PERFMON_EXPLAIN_F_OPERATION"),
			"align" => "left",
			"default" => true,
		),
		array(
			"id" => "OBJECT_NAME",
			"content" => GetMessage("PERFMON_EXPLAIN_F_OBJECT_NAME"),
			"align" => "left",
			"default" => true,
		),
		array(
			"id" => "OBJECT_TYPE",
			"content" => GetMessage("PERFMON_EXPLAIN_F_OBJECT_TYPE"),
			"align" => "left",
			"default" => true,
		),
		array(
			"id" => "OPTIONS",
			"content" => GetMessage("PERFMON_EXPLAIN_F_EXTRA"),
			"align" => "left",
			"default" => true,
		),
		array(
			"id" => "CARDINALITY",
			"content" => GetMessage("PERFMON_EXPLAIN_F_ROWS"),
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COST",
			"content" => GetMessage("PERFMON_EXPLAIN_F_COST"),
			"align" => "right",
			"default" => true,
		),
	);
}
else
{
	$arHeader = array();
}
$lAdmin->AddHeaders($arHeader);

$arPlan = false;
$cData = new CPerfomanceSQL;
$rsSQL = $cData->GetList(array("ID", "SQL_TEXT"), array("=ID" => $ID), array(), false);
$arSQL = $rsSQL->Fetch();
$strSQL = CPerfQuery::transform2select($arSQL["SQL_TEXT"]);
if ($strSQL)
{
	if ($DBType == "mysql")
	{
		$rsData = $DB->Query("explain ".$strSQL, true);
	}
	elseif ($DBType == "oracle")
	{
		$rsData = $DB->Query("explain plan for ".$strSQL, true);
		if ($rsData)
		{
			$rsData = $DB->Query("select * from plan_table order by ID");
			$arPlan = $rsData->Fetch();
		}
	}
	else
	{
		$rsData = false;
	}
}
else
{
	$rsData = false;
}

if ($rsData)
{
	$SQL_TEXT = CPerfomanceSQL::Format($strSQL);
	$lAdmin->BeginPrologContent();
	if (class_exists("geshi"))
	{
		$obGeSHi = new GeSHi(CSqlFormat::reformatSql($SQL_TEXT), 'sql');
		echo $obGeSHi->parse_code();
	}
	else
	{
		echo "<p>".str_replace(
				array(" ", "\t", "\n"),
				array(" ", "&nbsp;&nbsp;&nbsp;", "<br>"),
				htmlspecialcharsbx(CSqlFormat::reformatSql($SQL_TEXT))
			)."</p>";
	}
	if ($arPlan["OPTIMIZER"])
	{
		echo "<p>".GetMessage("PERFMON_EXPLAIN_F_OPTIMIZER").": ".$arPlan["OPTIMIZER"]."</p>";
		echo "<p>".GetMessage("PERFMON_EXPLAIN_F_COST").": ".$arPlan["POSITION"]."</p>";
	}
	$lAdmin->EndPrologContent();
}
else
{
	$rsData = new CDBResult;
	$rsData->InitFromArray(array());
	$lAdmin->BeginPrologContent();
	$message = new CAdminMessage(array(
		"MESSAGE" => GetMessage("PERFMON_EXPLAIN_SQL_ERROR"),
		"TYPE" => "ERROR",
	));
	echo $message->Show();
	$lAdmin->EndPrologContent();
}

$Comment = "";
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
while ($arRes = $rsData->GetNext(true, "f_"))
{
	if (array_key_exists("DEPTH", $arRes))
		$arRes["OPERATION"] = str_repeat("&nbsp;&nbsp;", $arRes["DEPTH"]).$arRes["OPERATION"];
	if (array_key_exists("select_type", $arRes))
		$arRes["select_type"] = $arRes["id"]." ".$arRes["select_type"];
	$row = $lAdmin->AddRow($arRes["ID"], $arRes);
	if (array_key_exists("Comment", $arRes))
		$Comment .= $arRes["Comment"]."\n";
}

if ($Comment)
{
	$lAdmin->BeginEpilogContent();
	$message = new CAdminMessage(array(
		"MESSAGE" => $Comment,
		"TYPE" => "OK",
	));
	echo $message->Show();
	$lAdmin->EndEpilogContent();
}

$lAdmin->AddFooter(array());
$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
?>