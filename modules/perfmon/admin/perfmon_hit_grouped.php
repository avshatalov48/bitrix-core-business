<?
define("ADMIN_MODULE_NAME", "perfmon");
define("PERFMON_STOP", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_perfmon_hit_grouped";
$oSort = new CAdminSorting($sTableID, "SUM_PAGE_TIME", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = array(
	"find_script_name",
	"find_is_admin",
	"find_count_from", "find_count_to",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = array(
	"SCRIPT_NAME" => $find_script_name,
	"=IS_ADMIN" => $find_is_admin,
	">=COUNT" => $find_count_from,
	"<=COUNT" => $find_count_to,
);
foreach ($arFilter as $key => $value)
	if (!$value)
		unset($arFilter[$key]);

$arHeaders = array(
	array(
		"id" => "IS_ADMIN",
		"content" => GetMessage("PERFMON_HIT_IS_ADMIN"),
		"sort" => "IS_ADMIN",
	),
	array(
		"id" => "REQUEST_METHOD",
		"content" => GetMessage("PERFMON_HIT_REQUEST_METHOD"),
		"sort" => "REQUEST_METHOD",
	),
	array(
		"id" => "SERVER_NAME",
		"content" => GetMessage("PERFMON_HIT_SERVER_NAME"),
		"sort" => "SERVER_NAME",
	),
	array(
		"id" => "SERVER_PORT",
		"content" => GetMessage("PERFMON_HIT_SERVER_PORT"),
		"sort" => "SERVER_PORT",
	),
	array(
		"id" => "SCRIPT_NAME",
		"content" => GetMessage("PERFMON_HIT_PAGE"),
		"sort" => "SCRIPT_NAME",
		"default" => true,
	),
	array(
		"id" => "PERCENT",
		"content" => GetMessage("PERFMON_HIT_PERCENT"),
		"sort" => "SUM_PAGE_TIME",
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "REQUEST_URI",
		"content" => GetMessage("PERFMON_HIT_REQUEST_URI"),
		"sort" => "REQUEST_URI",
	),
	array(
		"id" => "COUNT",
		"content" => GetMessage("PERFMON_HIT_COUNT"),
		"sort" => "COUNT",
		"align" => "right",
		"default" => true,
	),
);

$arGrpFuncs = array("MAX", "MIN", "SUM", "AVG");
$arGrpCols = array(
	"INCLUDED_FILES" => 0,
	"MEMORY_PEAK_USAGE" => 0,
	"CACHE_SIZE" => 0,
	"CACHE_COUNT" => 0,
	"CACHE_COUNT_R" => 0,
	"CACHE_COUNT_W" => 0,
	"CACHE_COUNT_C" => 0,
	"PAGE_TIME" => 4,
	"PROLOG_TIME" => 4,
	"AGENTS_TIME" => 4,
	"WORK_AREA_TIME" => 4,
	"EPILOG_TIME" => 4,
	"EVENTS_TIME" => 4,
	"COMPONENTS" => 0,
	"COMPONENTS_TIME" => 4,
	"QUERIES" => 0,
	"QUERIES_TIME" => 4,
);

$arDefColumns = array("SUM_PAGE_TIME", "AVG_PAGE_TIME", "AVG_COMPONENTS", "AVG_QUERIES");
foreach ($arGrpCols as $col => $prec)
{
	foreach ($arGrpFuncs as $func)
	{
		$arHeaders [] = array(
			"id" => $func."_".$col,
			"content" => GetMessage("PERFMON_HIT_".$func."_".$col),
			"sort" => $func."_".$col,
			"align" => "right",
			"default" => in_array($func."_".$col, $arDefColumns),
		);
	}
}

$lAdmin->AddHeaders($arHeaders);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
	$arSelectedFields = array(
		"SCRIPT_NAME",
		"COUNT",
		"MAX_INCLUDED_FILES",
		"MAX_MEMORY_PEAK_USAGE",
		"AVG_QUERIES",
	);


$cData = new CPerfomanceHit;

$rsTotal = $cData->GetList(array("COUNT" => "ASC"), $arFilter, true, false, array("COUNT", "SUM_PAGE_TIME"));
$arTotal = $rsTotal->Fetch();

$rsData = $cData->GetList(
	array($by => $order),
	$arFilter,
	true,
	array("nPageSize" => CAdminResult::GetNavSize($sTableID)),
	$arSelectedFields
);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PERFMON_HIT_PAGE2")));

$max_display_url = COption::GetOptionInt("perfmon", "max_display_url");
while ($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_NAME, $arRes);

	$row->AddViewField("SCRIPT_NAME", '<a href="perfmon_hit_list.php?lang='.LANGUAGE_ID.'&amp;set_filter=Y&amp;find_script_name='.$f_SCRIPT_NAME.'">'.$f_SCRIPT_NAME.'</a>');

	$page_time = doubleval($arTotal["SUM_PAGE_TIME"]);
	if ($page_time > 0)
		$row->AddViewField("PERCENT", perfmon_NumberFormat(($arRes["SUM_PAGE_TIME"] / $page_time) * 100, 2)."%");
	else
		$row->AddViewField("PERCENT", "&nbsp;");

	$row->AddViewField("COUNT", perfmon_NumberFormat($f_COUNT, 0));

	foreach ($arGrpFuncs as $func)
	{
		foreach ($arGrpCols as $col => $prec)
		{
			if (strlen($arRes[$func."_".$col]))
			{
				$row->AddViewField($func."_".$col, perfmon_NumberFormat($arRes[$func."_".$col], $prec));
			}
		}
	}
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsData->SelectedRowsCount(),
		),
		array(
			"title" => GetMessage("PERFMON_HIT_TOTAL_HITS").":",
			"value" => $arTotal["COUNT"],
		),
	)
);

$aContext = array();
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PERFMON_HIT_TITLE2"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"find_is_admin" => GetMessage("PERFMON_HIT_IS_ADMIN"),
		"find_count" => GetMessage("PERFMON_HIT_COUNT"),
	)
);
?>

<form name="find_form" method="get" action="<? echo $APPLICATION->GetCurPage(); ?>">
	<? $oFilter->Begin(); ?>
	<tr>
		<td><?echo GetMessage("PERFMON_HIT_PAGE")?>:</td>
		<td><input type="text" name="find_script_name" size="47"
			value="<? echo htmlspecialcharsbx($find_script_name) ?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("PERFMON_HIT_IS_ADMIN")?>:</td>
		<td><?
			$arr = array(
				"reference" => array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")),
				"reference_id" => array("Y", "N")
			);
			echo SelectBoxFromArray("find_is_admin", $arr, htmlspecialcharsbx($find_is_admin), GetMessage("MAIN_ALL"));
			?></td>
	</tr>
	<tr>
		<td><?=GetMessage("PERFMON_HIT_COUNT")?>:</td>
		<td><input type="text" name="find_count_from" size="7" value="<?echo htmlspecialcharsbx($find_count_from)?>">...<input type="text" name="find_count_to" size="7" value="<? echo htmlspecialcharsbx($find_count_to) ?>"></td>
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
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
