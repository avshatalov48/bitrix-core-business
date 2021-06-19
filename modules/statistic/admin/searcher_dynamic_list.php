<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_searcher_dynamic_list";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

InitSorting();

$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","searcher_list.php");

if($set_default=="Y")
{
	$find_date1_DAYS_TO_BACK=90;
	$set_filter = "Y";
}

$arFilterFields = array(
	"find_searcher_id",
	"find_date1",
	"find_date2"
	);
$lAdmin->InitFilter($arFilterFields);

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2
);

global $by, $order;

$rsData = CSearcher::GetDynamicList($find_searcher_id, $by, $order, $arMaxMin, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_SEARCHER_PAGES")));

$lAdmin->AddHeaders(array(
	array("id"=>"DATE_STAT", "content"=>GetMessage("STAT_DATE"), "sort"=>"s_date", "default"=>true),
	array("id"=>"HITS", "content"=>GetMessage("STAT_HITS"), "align"=>"right", "default"=>true),
	)
);

$sumDays=0;
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("HITS","<a title=\"".GetMessage("STAT_HITS_LIST_OPEN")."\" href=\"hit_searcher_list.php?lang=".LANGUAGE_ID."&find_searcher_id=$find_searcher_id&find_date1=$f_DATE_STAT&find_date2=$f_DATE_STAT&set_filter=Y\">".intval($f_TOTAL_HITS)."</a>");
	$sumDays+=$f_TOTAL_HITS;
}

$lAdmin->AddFooter(array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("title"=>GetMessage("STAT_TOTAL_HITS"), "value"=>$sumDays),
		)
	);

$aMenu = array();
$aMenu[] = array(
	"TEXT"	=> GetMessage("STAT_LIST"),
	"TITLE"=>GetMessage("STAT_LIST_TITLE"),
	"LINK"=>"searcher_list.php?lang=".LANG,
	"ICON" => "btn_list"
);
$aMenu[] = array("SEPARATOR"=>true);
$aMenu[] = array(
	"TEXT"	=> GetMessage("STAT_GRAPH"),
	"TITLE"=>GetMessage("STAT_GRAPH_TITLE"),
	"LINK"=>"searcher_graph_list.php?lang=".LANGUAGE_ID."&find_searchers[]=$find_searcher_id&find_date1=$arFilter[DATE1]&find_date2=$arFilter[DATE2]&set_filter=Y",
);

$lAdmin->AddAdminContextMenu($aMenu);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#"=>$STORED_DAYS)));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/***************************************************************************
				HTML form
***************************************************************************/
?>
<a name="tb"></a>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("STAT_F_PERIOD"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td><?echo GetMessage("STAT_F_SEARCHER_ID")?></td>
	<td><?echo SelectBox("find_searcher_id", CSearcher::GetDropDownList(), "", htmlspecialcharsbx($find_searcher_id));?></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()));
$oFilter->End();
?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
