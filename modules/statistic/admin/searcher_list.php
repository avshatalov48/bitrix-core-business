<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$strError = "";
$sTableID = "tbl_searcher_list";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);
$statDB = CDatabase::GetModuleConnection('statistic');

$arFilterFields = Array(
	"find_id",
	"find_active",
	"find_save_statistic",
	"find_hits1",
	"find_hits2",
	"find_date1",
	"find_date2",
	"find_date1_period",
	"find_date2_period",
	"find_name",
	"find_user_agent",
	"find_diagram_default",
	"find_id_exact_match",
	"find_name_exact_match",
	"find_user_agent_exact_match",
	);

$lAdmin->InitFilter($arFilterFields);
/***************************************************************************
			Functions
***************************************************************************/

function CheckFilter()
{
	global $strError, $arFilterFields, $statDB;
	global $find_date1, $find_date2, $find_date1_period, $find_date2_period;
	global $find_hits1, $find_hits2;
	$str = "";
	$arr = array();

	$arr[] = array(
		"date1" => $find_date1,
		"date2" => $find_date2,
		"mess1" => GetMessage("STAT_WRONG_DATE_FROM"),
		"mess2" => GetMessage("STAT_WRONG_DATE_TILL"),
		"mess3" => GetMessage("STAT_FROM_TILL_DATE")
		);

	$arr[] = array(
		"date1" => $find_date1_period,
		"date2" => $find_date2_period,
		"mess1" => GetMessage("STAT_WRONG_DATE_PERIOD_FROM"),
		"mess2" => GetMessage("STAT_WRONG_DATE_PERIOD_TILL"),
		"mess3" => GetMessage("STAT_FROM_TILL_DATE_PERIOD")
		);

	foreach($arr as $ar)
	{
		if ($ar["date1"] <> '' && !CheckDateTime($ar["date1"])) $str.= $ar["mess1"]."<br>";
		elseif ($ar["date2"] <> '' && !CheckDateTime($ar["date2"])) $str.= $ar["mess2"]."<br>";
		elseif ($ar["date1"] <> '' && $ar["date2"] <> '' &&
		$statDB->CompareDates($ar["date1"], $ar["date2"])==1) $str.= $ar["mess3"]."<br>";
	}

	if (intval($find_hits1)>0 && intval($find_hits2)>0 && $find_hits1>$find_hits2)
		$str.= GetMessage("STAT_FROM_TILL_HITS")."<br>";

	$strError .= $str;
	if ($str <> '') return false; else return true;
}

if (CheckFilter())
{
	$arFilter = Array(
		"ID"				=> $find_id,
		"ID_EXACT_MATCH"		=> $find_id_exact_match,
		"ACTIVE"			=> $find_active,
		"SAVE_STATISTIC"	=> $find_save_statistic,
		"HITS1"				=> $find_hits1,
		"HITS2"				=> $find_hits2,
		"DATE1"				=> $find_date1,
		"DATE2"				=> $find_date2,
		"DATE1_PERIOD"		=> $find_date1_period,
		"DATE2_PERIOD"		=> $find_date2_period,
		"NAME"				=> $find_name,
		"NAME_EXACT_MATCH"	=> $find_name_exact_match,
		"USER_AGENT"		=> $find_user_agent,
		"USER_AGENT_EXACT_MATCH"	=> $find_user_agent_exact_match,
		"DIAGRAM_DEFAULT"	=> $find_diagram_default
		);
}
else $lAdmin->AddFilterError($strError);

if(($arID = $lAdmin->GroupAction()) && $STAT_RIGHT=="W" && check_bitrix_sessid())
{
	if($_REQUEST['action_target']=="selected")
	{
		$arID = Array();
		$rsData = CSearcher::GetList('', '', $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$statDB->StartTransaction();
			$statDB->Query("DELETE FROM b_stat_searcher WHERE ID = ".$ID);
			$statDB->Query("DELETE FROM b_stat_searcher_params WHERE SEARCHER_ID = ".$ID);
			$statDB->Query("DELETE FROM b_stat_searcher_day WHERE SEARCHER_ID = ".$ID);
			$statDB->Commit();
			break;
		}
	}
}

global $by, $order;

$rsData = CSearcher::GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_SEARCHER_PAGES")));

$today = GetTime(time());
$yesterday = GetTime(time()-86400);
$b_yesterday = GetTime(time()-172800);

$headers=array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("STAT_NAME"), "sort"=>"s_name", "default"=>true),
	array("id"=>"USER_AGENT", "content"=>"UserAgent", "sort"=>"s_user_agent", "default"=>true),
	array("id"=>"TODAY_HITS", "content"=>GetMessage("STAT_TODAY_HITS")." $today", "sort"=>"s_today_hits", "default"=>true, "align"=>"right"),
	array("id"=>"YESTERDAY_HITS", "content"=>GetMessage("STAT_YESTERDAY_HITS")." $yesterday", "sort"=>"s_yesterday_hits", "default"=>true, "align"=>"right"),
	array("id"=>"B_YESTERDAY_HITS", "content"=>GetMessage("STAT_B_YESTERDAY_HITS")." $b_yesterday", "sort"=>"s_b_yesterday_hits", "default"=>true, "align"=>"right"),
	);
if ($arFilter["DATE1_PERIOD"] <> '')
	$headers[]=array("id"=>"PERIOD_HITS", "content"=>GetMessage("STAT_PERIOD_HITS")." ".htmlspecialcharsEx($arFilter["DATE1_PERIOD"])." ".htmlspecialcharsEx($arFilter["DATE2_PERIOD"]), "sort"=>"s_period_hits", "default"=>true, "align"=>"right");

$headers[]=array("id"=>"TOTAL_HITS", "content"=>GetMessage("STAT_TOTAL_HITS"), "sort"=>"s_total_hits", "default"=>true, "align"=>"right");
$headers[]=array("id"=>"DATE_LAST", "content"=>GetMessage("STAT_LAST_DATE"), "sort"=>"s_date_last", "default"=>true);

$lAdmin->AddHeaders($headers);

$total_TODAY_COUNTER = 0;
$total_YESTERDAY_COUNTER = 0;
$total_B_YESTERDAY_COUNTER = 0;
$total_PERIOD_COUNTER = 0;
$total_TOTAL_COUNTER = 0;
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$dynamic_days = CSearcher::DynamicDays($f_ID);
	$total_TODAY_COUNTER += intval($f_TODAY_HITS);
	$total_YESTERDAY_COUNTER += intval($f_YESTERDAY_HITS);
	$total_B_YESTERDAY_COUNTER += intval($f_B_YESTERDAY_HITS);
	$total_PERIOD_COUNTER += intval($f_PERIOD_HITS);
	$total_TOTAL_COUNTER += intval($f_TOTAL_HITS);

	if ($dynamic_days>=2 && function_exists("ImageCreate"))
	{
		$txt="<a title=\"".GetMessage("STAT_GRAPH_ALT")."\" href=\"searcher_graph_list.php?lang=".LANGUAGE_ID."&find_searchers[]=$f_ID&set_filter=Y\">$f_NAME</a>";
		$row->AddViewField("NAME", $txt);
	}

	if ($f_TODAY_HITS>0)
	{
		$txt="<a title=\"".GetMessage("STAT_HITS_SHOW")."\" href=\"hit_searcher_list.php?lang=".LANGUAGE_ID."&find_searcher_id=$f_ID&find_searcher_id_exact_match=Y&find_date1=$today&set_filter=Y\">$f_TODAY_HITS</a>";
		$row->AddViewField("TODAY_HITS", $txt);
	}

	if ($f_YESTERDAY_HITS>0)
	{
		$txt="<a title=\"".GetMessage("STAT_HITS_SHOW")."\" href=\"hit_searcher_list.php?lang=".LANGUAGE_ID."&find_searcher_id=$f_ID&find_searcher_id_exact_match=Y&find_date1=$yesterday&find_date2=$yesterday&set_filter=Y\">$f_YESTERDAY_HITS</a>";
		$row->AddViewField("YESTERDAY_HITS", $txt);
	}

	if ($f_B_YESTERDAY_HITS>0)
	{
		$txt="<a title=\"".GetMessage("STAT_HITS_SHOW")."\" href=\"hit_searcher_list.php?lang=".LANGUAGE_ID."&find_searcher_id=$f_ID&find_searcher_id_exact_match=Y&find_date1=$b_yesterday&find_date2=$b_yesterday&set_filter=Y\">$f_B_YESTERDAY_HITS</a>";
		$row->AddViewField("B_YESTERDAY_HITS", $txt);
	}

	if ($f_PERIOD_HITS>0)
	{
		$txt="<a title=\"".GetMessage("STAT_HITS_SHOW")."\" href=\"".htmlspecialcharsbx("hit_searcher_list.php?lang=".urlencode(LANGUAGE_ID)."&find_searcher_id=".urlencode($f_ID)."&find_searcher_id_exact_match=Y&find_date1=".urlencode($arFilter["DATE1_PERIOD"])."&find_date2=".urlencode($arFilter["DATE2_PERIOD"])."&set_filter=Y")."\">$f_PERIOD_HITS</a>";
		$row->AddViewField("PERIOD_HITS", $txt);
	}

	if ($f_TOTAL_HITS>0)
	{
		$txt="<a title=\"".GetMessage("STAT_HITS_SHOW")."\" href=\"hit_searcher_list.php?lang=".LANGUAGE_ID."&find_searcher_id=$f_ID&find_searcher_id_exact_match=Y&set_filter=Y\">$f_TOTAL_HITS</a>";
		$row->AddViewField("TOTAL_HITS", $txt);
	}

	$arActions = Array();

	if (function_exists("ImageCreate"))
		$arActions[] = array("TITLE"=>GetMessage("STAT_GRAPH_ALT"), "ACTION"=>$lAdmin->ActionRedirect("searcher_graph_list.php?lang=".LANGUAGE_ID."&find_searchers[]=$f_ID&set_filter=Y"), "TEXT"=>GetMessage("STAT_GRAPH"), "DEFAULT"=>"Y");
	$arActions[] = array("TITLE"=>GetMessage("STAT_DYNAMICS_ALT"), "ACTION"=>$lAdmin->ActionRedirect("searcher_dynamic_list.php?lang=".LANGUAGE_ID."&find_searcher_id=$f_ID&find_searcher_id_exact_match=Y&set_filter=Y"), "TEXT"=>GetMessage("STAT_DYNAMICS"));
	$arActions[] = array("SEPARATOR"=>true);
	$arActions[] = array("ICON"=>"edit", "TITLE"=>GetMessage("STAT_CHANGE_SEARCHER"), "ACTION"=>$lAdmin->ActionRedirect("searcher_edit.php?lang=".LANGUAGE_ID."&ID=$f_ID"), "TEXT"=>GetMessage("STAT_CHANGE"));
	if ($STAT_RIGHT>="W")
		$arActions[] = array("ICON"=>"delete", "TITLE"=>GetMessage("STAT_DELETE_SEARCHER"), "ACTION"=>"javascript:if(confirm('".GetMessageJS("STAT_CONFIRM")."')) window.location='?lang=".LANGUAGE_ID."&action=delete&ID=$f_ID&".bitrix_sessid_get()."'", "TEXT"=>GetMessage("STAT_DELETE"));

	$row->AddActions($arActions);
}

$footer=array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		array("title"=>GetMessage("STAT_TOT_TD"), "value"=>$total_TODAY_COUNTER),
		array("title"=>GetMessage("STAT_TOT_YTD"), "value"=>$total_YESTERDAY_COUNTER),
		array("title"=>GetMessage("STAT_TOT_B_YTD"), "value"=>$total_B_YESTERDAY_COUNTER),
	);

if ($arFilter["DATE1_PERIOD"] <> '')
	$footer[]=array("title"=>GetMessage("STAT_TOT_PRD"), "value"=>$total_PERIOD_COUNTER);
$footer[]=array("title"=>GetMessage("STAT_TOTAL"), "value"=>$total_TOTAL_COUNTER);
$lAdmin->AddFooter($footer);

$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("STAT_DELETE_L"),
	)
);

$aMenu = array();
$aMenu[] = array(
	"TEXT"	=> GetMessage("STAT_ADD"),
	"LINK"=>"searcher_edit.php?lang=".LANG,
	"ICON" => "btn_new"
);
$aMenu[] = array("SEPARATOR"=>"Y");
$aMenu[] = array(
	"LINK"=>"searcher_diagram_list.php?lang=".LANGUAGE_ID."&set_default=Y",
	"TEXT"=>GetMessage("STAT_DIAGRAM_S"),
	"TITLE"=>GetMessage("STAT_DIAGRAM"),
);

$aMenu[] = array(
	"LINK"=>"searcher_graph_list.php?lang=".LANGUAGE_ID."&set_default=Y",
	"TEXT"=>GetMessage("STAT_GRAPH_FULL_S"),
	"TITLE"=>GetMessage("STAT_GRAPH_FULL"),
);

$lAdmin->AddAdminContextMenu($aMenu);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#"=>COption::GetOptionString("statistic","SEARCHER_DAYS"))));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/***************************************************************************
				HTML form
****************************************************************************/
?>
<a name="tb"></a>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("STAT_FL_ID"),
		GetMessage("STAT_FL_ACTIVE"),
		GetMessage("STAT_FL_HITS"),
		GetMessage("STAT_FL_DIAG"),
		GetMessage("STAT_FL_HITS_TOTAL"),
		GetMessage("STAT_FL_HITS_DATE"),
		GetMessage("STAT_FL_PERIOD"),
		"UserAgent",
	)
);

$oFilter->Begin();
?>
<tr>
	<td width="0%" nowrap><b><?echo GetMessage("STAT_F_NAME")?></b></td>
	<td width="0%" nowrap><input type="text" name="find_name" value="<?echo htmlspecialcharsbx($find_name)?>" size="47"><?=ShowExactMatchCheckbox("find_name")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("STAT_F_ID")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_ACTIVE")?></td>
	<td width="0%" nowrap><?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_STATICS")?></td>
	<td width="0%" nowrap><?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_save_statistic", $arr, htmlspecialcharsbx($find_save_statistic), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PIE_CHART")?></td>
	<td width="0%" nowrap><?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_diagram_default", $arr, htmlspecialcharsbx($find_diagram_default), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_HITS")?></td>
	<td width="0%" nowrap><input type="text" maxlength="10" name="find_hits1" value="<?echo htmlspecialcharsbx($find_hits1)?>" size="9"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" maxlength="10" name="find_hits2" value="<?echo htmlspecialcharsbx($find_hits2)?>" size="9"></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_LAST_HIT_DATE").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1_period", $find_date1_period, "find_date2_period", $find_date2_period, "form1","Y")?></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_USER_AGENT")?></td>
	<td width="0%" nowrap><input type="text" name="find_user_agent" value="<?echo htmlspecialcharsbx($find_user_agent)?>" size="47"><?=ShowExactMatchCheckbox("find_user_agent")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
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
