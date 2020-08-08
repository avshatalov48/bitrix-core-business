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

if ($group !== "comp" && $group !== "type" && $group !== "dir" && $group !== "file")
	$group = "none";

$DOCUMENT_ROOT_LEN = mb_strlen($_SERVER["DOCUMENT_ROOT"]);
$sTableID = "tbl_perfmon_cache_list_".$group;
$oSort = new CAdminSorting($sTableID, "NN", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = array(
	"find",
	"find_type",
	"find_hit_id",
	"find_component_id",
	"find_component_name",
	"find_module_name",
	"find_op_mode",
	"find_base_dir",
	"find_init_dir",
	"find_file_name",
);

$lAdmin->InitFilter($FilterArr);

if ($group === "none")
{
	$arFilter = array(
		"COMPONENT_NAME" => ($find != "" && $find_type == "component_name"? $find: $find_component_name),
		"=HIT_ID" => ($find != "" && $find_type == "hit_id"? $find: $find_hit_id),
		"MODULE_NAME" => $find_module_name,
		"=COMPONENT_ID" => $find_component_id,
		"=OP_MODE" => $find_op_mode,
		"=BASE_DIR" => $find_base_dir,
		"=INIT_DIR" => $find_init_dir,
		"=FILE_NAME" => $find_file_name,
	);
}
else
{
	$arFilter = array();
}

foreach ($arFilter as $key => $value)
{
	if (!$value)
		unset($arFilter[$key]);
}

if ($group === "comp")
{
	$arHeaders = array(
		array(
			"id" => "COMPONENT_NAME",
			"content" => GetMessage("PERFMON_CACHE_COMPONENT_NAME"),
			"sort" => "COMPONENT_NAME",
			"default" => true,
		),
		array(
			"id" => "COUNT",
			"content" => GetMessage("PERFMON_CACHE_COUNT"),
			"sort" => "COUNT",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_R",
			"content" => GetMessage("PERFMON_CACHE_COUNT_R"),
			"sort" => "COUNT_R",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_W",
			"content" => GetMessage("PERFMON_CACHE_COUNT_W"),
			"sort" => "COUNT_W",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_C",
			"content" => GetMessage("PERFMON_CACHE_COUNT_C"),
			"sort" => "COUNT_C",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "SUM_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_SUM_CACHE_SIZE"),
			"sort" => "SUM_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "AVG_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_AVG_CACHE_SIZE"),
			"sort" => "AVG_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "MIN_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_MIN_CACHE_SIZE"),
			"sort" => "MIN_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "MAX_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_MAX_CACHE_SIZE"),
			"sort" => "MAX_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
	);
}
elseif ($group === "type")
{
	$arHeaders = array(
		array(
			"id" => "BASE_DIR",
			"content" => GetMessage("PERFMON_CACHE_BASE_DIR"),
			"sort" => "BASE_DIR",
			"default" => true,
		),
		array(
			"id" => "COUNT",
			"content" => GetMessage("PERFMON_CACHE_COUNT"),
			"sort" => "COUNT",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_R",
			"content" => GetMessage("PERFMON_CACHE_COUNT_R"),
			"sort" => "COUNT_R",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_W",
			"content" => GetMessage("PERFMON_CACHE_COUNT_W"),
			"sort" => "COUNT_W",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_C",
			"content" => GetMessage("PERFMON_CACHE_COUNT_C"),
			"sort" => "COUNT_C",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "SUM_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_SUM_CACHE_SIZE"),
			"sort" => "SUM_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "AVG_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_AVG_CACHE_SIZE"),
			"sort" => "AVG_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "MIN_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_MIN_CACHE_SIZE"),
			"sort" => "MIN_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "MAX_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_MAX_CACHE_SIZE"),
			"sort" => "MAX_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
	);
}
elseif ($group === "dir")
{
	$arHeaders = array(
		array(
			"id" => "BASE_DIR",
			"content" => GetMessage("PERFMON_CACHE_BASE_DIR"),
			"sort" => "INIT_DIR",
			"default" => true,
		),
		array(
			"id" => "INIT_DIR",
			"content" => GetMessage("PERFMON_CACHE_INIT_DIR"),
			"sort" => "INIT_DIR",
			"default" => true,
		),
		array(
			"id" => "COUNT",
			"content" => GetMessage("PERFMON_CACHE_COUNT"),
			"sort" => "COUNT",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_R",
			"content" => GetMessage("PERFMON_CACHE_COUNT_R"),
			"sort" => "COUNT_R",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_W",
			"content" => GetMessage("PERFMON_CACHE_COUNT_W"),
			"sort" => "COUNT_W",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_C",
			"content" => GetMessage("PERFMON_CACHE_COUNT_C"),
			"sort" => "COUNT_C",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "SUM_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_SUM_CACHE_SIZE"),
			"sort" => "SUM_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "AVG_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_AVG_CACHE_SIZE"),
			"sort" => "AVG_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "MIN_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_MIN_CACHE_SIZE"),
			"sort" => "MIN_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "MAX_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_MAX_CACHE_SIZE"),
			"sort" => "MAX_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
	);
}
elseif ($group === "file")
{
	$arHeaders = array(
		array(
			"id" => "BASE_DIR",
			"content" => GetMessage("PERFMON_CACHE_BASE_DIR"),
			"sort" => "INIT_DIR",
			"default" => true,
		),
		array(
			"id" => "INIT_DIR",
			"content" => GetMessage("PERFMON_CACHE_INIT_DIR"),
			"sort" => "INIT_DIR",
			"default" => true,
		),
		array(
			"id" => "FILE_NAME",
			"content" => GetMessage("PERFMON_CACHE_FILE_NAME"),
			"sort" => "FILE_NAME",
			"default" => true,
		),
		array(
			"id" => "HIT_RATIO",
			"content" => GetMessage("PERFMON_CACHE_HIT_RATIO"),
			"sort" => "HIT_RATIO",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT",
			"content" => GetMessage("PERFMON_CACHE_COUNT"),
			"sort" => "COUNT",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_R",
			"content" => GetMessage("PERFMON_CACHE_COUNT_R"),
			"sort" => "COUNT_R",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_W",
			"content" => GetMessage("PERFMON_CACHE_COUNT_W"),
			"sort" => "COUNT_W",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COUNT_C",
			"content" => GetMessage("PERFMON_CACHE_COUNT_C"),
			"sort" => "COUNT_C",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "SUM_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_SUM_CACHE_SIZE"),
			"sort" => "SUM_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "AVG_CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_AVG_CACHE_SIZE"),
			"sort" => "AVG_CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
	);
}
else
{
	$arHeaders = array(
		array(
			"id" => "ID",
			"content" => GetMessage("PERFMON_CACHE_ID"),
			"sort" => "ID",
			"align" => "right",
		),
		array(
			"id" => "HIT_ID",
			"content" => GetMessage("PERFMON_CACHE_HIT_ID"),
			"sort" => "HIT_ID",
			"align" => "right",
		),
		array(
			"id" => "NN",
			"content" => GetMessage("PERFMON_CACHE_NN"),
			"sort" => "NN",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "COMPONENT_NAME",
			"content" => GetMessage("PERFMON_CACHE_COMPONENT_NAME"),
			"sort" => "COMPONENT_NAME",
			"default" => true,
		),
		array(
			"id" => "MODULE_NAME",
			"content" => GetMessage("PERFMON_CACHE_MODULE_NAME"),
			"sort" => "MODULE_NAME",
			"default" => true,
		),
		array(
			"id" => "CACHE_SIZE",
			"content" => GetMessage("PERFMON_CACHE_CACHE_SIZE"),
			"sort" => "CACHE_SIZE",
			"align" => "right",
			"default" => true,
		),
		array(
			"id" => "OP_MODE",
			"content" => GetMessage("PERFMON_CACHE_OP_MODE"),
			"sort" => "OP_MODE",
			"default" => true,
		),
		array(
			"id" => "BASE_DIR",
			"content" => GetMessage("PERFMON_CACHE_BASE_DIR"),
			"sort" => "FILE_PATH",
			"default" => true,
		),
		array(
			"id" => "INIT_DIR",
			"content" => GetMessage("PERFMON_CACHE_INIT_DIR"),
			"sort" => "FILE_PATH",
			"default" => true,
		),
		array(
			"id" => "FILE_NAME",
			"content" => GetMessage("PERFMON_CACHE_FILE_NAME"),
			"sort" => "FILE_PATH",
			"default" => true,
		),
		array(
			"id" => "CACHE_PATH",
			"content" => GetMessage("PERFMON_CACHE_CACHE_PATH"),
		),
	);
}

$lAdmin->AddHeaders($arHeaders);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
{
	foreach ($arHeaders as $header => $info)
	{
		if ($info["default"])
			$arSelectedFields[] = $info["id"];
	}
}
if (in_array("FILE_NAME", $arSelectedFields))
	$arSelectedFields[] = "FILE_PATH";

$arNumCols = array(
	"CACHE_SIZE" => 0,
	"COUNT" => 0,
	"COUNT_R" => 0,
	"COUNT_W" => 0,
	"COUNT_C" => 0,
	"SUM_CACHE_SIZE" => 0,
	"AVG_CACHE_SIZE" => 0,
	"MIN_CACHE_SIZE" => 0,
	"MAX_CACHE_SIZE" => 0,
	"HIT_RATIO" => 2,
);

$cData = new CPerfomanceCache;
$rsData = $cData->GetList(
	array($by => $order),
	$arFilter,
	$group !== "none",
	array("nPageSize" => CAdminResult::GetNavSize($sTableID)),
	$arSelectedFields
);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PERFMON_CACHE_PAGE")));

$i = 0;
$max_display_url = COption::GetOptionInt("perfmon", "max_display_url");
while ($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow(++$i, $arRes);
	$numbers = array();
	foreach ($arNumCols as $column_name => $precision)
	{
		$numbers[$column_name] = perfmon_NumberFormat($arRes[$column_name], $precision);
		$row->AddViewField($column_name, $numbers[$column_name]);
	}
	$row->AddViewField("HIT_ID", '<a href="perfmon_hit_list.php?lang='.LANGUAGE_ID.'&amp;set_filter=Y&amp;find_id='.$f_HIT_ID.'">'.$f_HIT_ID.'</a>');
	if ($f_FILE_NAME != "")
	{
		if ($f_FILE_PATH == "")
			$f_FILE_PATH = $_SERVER["DOCUMENT_ROOT"].$f_BASE_DIR.$f_INIT_DIR.$f_FILE_NAME;
		if (
			file_exists($f_FILE_PATH)
			&& mb_substr($f_FILE_PATH, 0, $DOCUMENT_ROOT_LEN) === $_SERVER["DOCUMENT_ROOT"]
		)
			$row->AddViewField("FILE_NAME", '<a target="blank" href="/bitrix/admin/fileman_file_view.php?path='.urlencode(mb_substr($f_FILE_PATH, $DOCUMENT_ROOT_LEN)).'&lang='.LANGUAGE_ID.'">'.$f_FILE_NAME.'</a>');
	}
	if ($f_OP_MODE == "R")
		$row->AddViewField("OP_MODE", GetMessage("PERFMON_CACHE_OP_MODE_R"));
	elseif ($f_OP_MODE == "W")
		$row->AddViewField("OP_MODE", GetMessage("PERFMON_CACHE_OP_MODE_W"));
	elseif ($f_OP_MODE == "C")
		$row->AddViewField("OP_MODE", GetMessage("PERFMON_CACHE_OP_MODE_C"));
	if ($group === "comp")
	{
		if ($f_COUNT > 0 && $f_COMPONENT_NAME != "")
			$row->AddViewField("COUNT", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_component_name='.urlencode($f_COMPONENT_NAME).'">'.$numbers["COUNT"].'</a>');
		if ($f_COUNT_R > 0 && $f_COMPONENT_NAME != "")
			$row->AddViewField("COUNT_R", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_component_name='.urlencode($f_COMPONENT_NAME).'&amp;find_op_mode=R">'.$numbers["COUNT_R"].'</a>');
		if ($f_COUNT_W > 0 && $f_COMPONENT_NAME != "")
			$row->AddViewField("COUNT_W", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_component_name='.urlencode($f_COMPONENT_NAME).'&amp;find_op_mode=W">'.$numbers["COUNT_W"].'</a>');
		if ($f_COUNT_C > 0 && $f_COMPONENT_NAME != "")
			$row->AddViewField("COUNT_C", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_component_name='.urlencode($f_COMPONENT_NAME).'&amp;find_op_mode=C">'.$numbers["COUNT_C"].'</a>');
	}
	elseif ($group === "type")
	{
		if ($f_COUNT > 0)
			$row->AddViewField("COUNT", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'">'.$numbers["COUNT"].'</a>');
		if ($f_COUNT_R > 0)
			$row->AddViewField("COUNT_R", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_op_mode=R">'.$numbers["COUNT_R"].'</a>');
		if ($f_COUNT_W > 0)
			$row->AddViewField("COUNT_W", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_op_mode=W">'.$numbers["COUNT_W"].'</a>');
		if ($f_COUNT_C > 0)
			$row->AddViewField("COUNT_C", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_op_mode=C">'.$numbers["COUNT_C"].'</a>');
	}
	elseif ($group === "dir")
	{
		if ($f_COUNT > 0)
			$row->AddViewField("COUNT", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_init_dir='.urlencode($f_INIT_DIR).'">'.$numbers["COUNT"].'</a>');
		if ($f_COUNT_R > 0)
			$row->AddViewField("COUNT_R", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_init_dir='.urlencode($f_INIT_DIR).'&amp;find_op_mode=R">'.$numbers["COUNT_R"].'</a>');
		if ($f_COUNT_W > 0)
			$row->AddViewField("COUNT_W", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_init_dir='.urlencode($f_INIT_DIR).'&amp;find_op_mode=W">'.$numbers["COUNT_W"].'</a>');
		if ($f_COUNT_C > 0)
			$row->AddViewField("COUNT_C", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_init_dir='.urlencode($f_INIT_DIR).'&amp;find_op_mode=C">'.$numbers["COUNT_C"].'</a>');
	}
	elseif ($group === "file")
	{
		if ($f_COUNT > 0)
			$row->AddViewField("COUNT", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_init_dir='.urlencode($f_INIT_DIR).'&amp;find_file_name='.urlencode($f_FILE_NAME).'">'.$numbers["COUNT"].'</a>');
		if ($f_COUNT_R > 0)
			$row->AddViewField("COUNT_R", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_init_dir='.urlencode($f_INIT_DIR).'&amp;find_file_name='.urlencode($f_FILE_NAME).'&amp;find_op_mode=R">'.$numbers["COUNT_R"].'</a>');
		if ($f_COUNT_W > 0)
			$row->AddViewField("COUNT_W", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_init_dir='.urlencode($f_INIT_DIR).'&amp;find_file_name='.urlencode($f_FILE_NAME).'&amp;find_op_mode=W">'.$numbers["COUNT_W"].'</a>');
		if ($f_COUNT_C > 0)
			$row->AddViewField("COUNT_C", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_init_dir='.urlencode($f_INIT_DIR).'&amp;find_file_name='.urlencode($f_FILE_NAME).'&amp;find_op_mode=C">'.$numbers["COUNT_C"].'</a>');
	}
	if ($f_BASE_DIR === "/bitrix/managed_cache/")
		$BASE_DIR = GetMessage("PERFMON_CACHE_MANAGED");
	elseif ($f_BASE_DIR === "/bitrix/cache/")
		$BASE_DIR = GetMessage("PERFMON_CACHE_UNMANAGED");
	else
		$BASE_DIR = $f_BASE_DIR;
	if ($f_BASE_DIR != "")
		$row->AddViewField("BASE_DIR", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'">'.$BASE_DIR.'</a>');
	if ($f_INIT_DIR != "")
		$row->AddViewField("INIT_DIR", '<a href="perfmon_cache_list.php?lang='.LANGUAGE_ID.'&amp;group=none&amp;set_filter=Y&amp;find_base_dir='.urlencode($f_BASE_DIR).'&amp;find_init_dir='.urlencode($f_INIT_DIR).'">'.$f_INIT_DIR.'</a>');
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsData->SelectedRowsCount(),
		),
	)
);

if ($group == "comp")
	$group_title = GetMessage("PERFMON_CACHE_GROUP_COMP");
elseif ($group == "type")
	$group_title = GetMessage("PERFMON_CACHE_GROUP_BASE_DIR");
elseif ($group == "dir")
	$group_title = GetMessage("PERFMON_CACHE_GROUP_INIT_DIR");
elseif ($group == "file")
	$group_title = GetMessage("PERFMON_CACHE_GROUP_FILE_NAME");
else
	$group_title = GetMessage("PERFMON_CACHE_GROUP_NONE");

$aContext = array(
	array(
		"TEXT" => $group_title,
		"MENU" => array(
			array(
				"TEXT" => GetMessage("PERFMON_CACHE_GROUP_NONE"),
				"ACTION" => $lAdmin->ActionRedirect("perfmon_cache_list.php?lang=".LANGUAGE_ID."&group=none"),
				"ICON" => ($group == "none"? "checked": ""),
			),
			array(
				"TEXT" => GetMessage("PERFMON_CACHE_GROUP_COMP"),
				"ACTION" => $lAdmin->ActionRedirect("perfmon_cache_list.php?lang=".LANGUAGE_ID."&group=comp"),
				"ICON" => ($group == "comp"? "checked": ""),
			),
			array(
				"TEXT" => GetMessage("PERFMON_CACHE_GROUP_BASE_DIR"),
				"ACTION" => $lAdmin->ActionRedirect("perfmon_cache_list.php?lang=".LANGUAGE_ID."&group=type"),
				"ICON" => ($group == "type"? "checked": ""),
			),
			array(
				"TEXT" => GetMessage("PERFMON_CACHE_GROUP_INIT_DIR"),
				"ACTION" => $lAdmin->ActionRedirect("perfmon_cache_list.php?lang=".LANGUAGE_ID."&group=dir"),
				"ICON" => ($group == "dir"? "checked": ""),
			),
			array(
				"TEXT" => GetMessage("PERFMON_CACHE_GROUP_FILE_NAME"),
				"ACTION" => $lAdmin->ActionRedirect("perfmon_cache_list.php?lang=".LANGUAGE_ID."&group=file"),
				"ICON" => ($group == "file"? "checked": ""),
			),
		),
	),
);

$lAdmin->AddAdminContextMenu($aContext, false, $group === "none");

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PERFMON_CACHE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($group == "none")
{
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
			"find_component_name" => GetMessage("PERFMON_CACHE_COMPONENT_NAME"),
			"find_module_name" => GetMessage("PERFMON_CACHE_MODULE_NAME"),
			"find_hit_id" => GetMessage("PERFMON_CACHE_HIT_ID"),
			"find_component_id" => GetMessage("PERFMON_CACHE_COMPONENT_ID"),
			"find_op_mode" => GetMessage("PERFMON_CACHE_OP_MODE"),
			"find_base_dir" => GetMessage("PERFMON_CACHE_BASE_DIR"),
			"find_init_dir" => GetMessage("PERFMON_CACHE_INIT_DIR"),
			"find_file_name" => GetMessage("PERFMON_CACHE_FILE_NAME"),
		)
	);
	?>

	<form name="find_form" method="get" action="<? echo $APPLICATION->GetCurPage(); ?>">
		<? $oFilter->Begin(); ?>
		<tr>
			<td><b><?=GetMessage("PERFMON_CACHE_FIND")?>:</b></td>
			<td>
				<input type="text" size="25" name="find" value="<? echo htmlspecialcharsbx($find) ?>"
					title="<?=GetMessage("PERFMON_CACHE_FIND")?>">
				<?
				$arr = array(
					"reference" => array(
						GetMessage("PERFMON_CACHE_COMPONENT_NAME"),
						GetMessage("PERFMON_CACHE_HIT_ID"),
					),
					"reference_id" => array(
						"component_name",
						"hit_id",
					)
				);
				echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
				?>
			</td>
		</tr>
		<tr>
			<td><?=GetMessage("PERFMON_CACHE_COMPONENT_NAME")?></td>
			<td><input type="text" name="find_component_name" size="47"
				value="<? echo htmlspecialcharsbx($find_component_name) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage("PERFMON_CACHE_MODULE_NAME")?></td>
			<td><input type="text" name="find_module_name" size="47"
				value="<? echo htmlspecialcharsbx($find_module_name) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage("PERFMON_CACHE_HIT_ID")?></td>
			<td><input type="text" name="find_hit_id" size="47"
				value="<? echo htmlspecialcharsbx($find_hit_id) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage("PERFMON_CACHE_COMPONENT_ID")?></td>
			<td><input type="text" name="find_component_id" size="47"
				value="<? echo htmlspecialcharsbx($find_component_id) ?>"></td>
		</tr>
		<tr>
			<td><? echo GetMessage("PERFMON_CACHE_OP_MODE") ?>:</td>
			<td><?
				$arr = array(
					"reference" => array(
						GetMessage("PERFMON_CACHE_OP_MODE_R"),
						GetMessage("PERFMON_CACHE_OP_MODE_W"),
						GetMessage("PERFMON_CACHE_OP_MODE_C"),
					),
					"reference_id" => array(
						"R",
						"W",
						"C",
					),
				);
				echo SelectBoxFromArray("find_op_mode", $arr, htmlspecialcharsbx($find_op_mode), GetMessage("MAIN_ALL"));
				?></td>
		</tr>
		<tr>
			<td><?=GetMessage("PERFMON_CACHE_BASE_DIR")?></td>
			<td><input type="text" name="find_base_dir" size="47"
				value="<? echo htmlspecialcharsbx($find_base_dir) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage("PERFMON_CACHE_INIT_DIR")?></td>
			<td><input type="text" name="find_init_dir" size="47"
				value="<? echo htmlspecialcharsbx($find_init_dir) ?>"></td>
		</tr>
		<tr>
			<td><?=GetMessage("PERFMON_CACHE_FILE_NAME")?></td>
			<td><input type="text" name="find_file_name" size="47"
				value="<? echo htmlspecialcharsbx($find_file_name) ?>"></td>
		</tr>
		<?
		$oFilter->Buttons(array(
			"table_id" => $sTableID,
			"url" => $APPLICATION->GetCurPage(),
			"form" => "find_form",
		));
		$oFilter->End();
		?>
	</form>
<?
}

$lAdmin->DisplayList();?>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>
