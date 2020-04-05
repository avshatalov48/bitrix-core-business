<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);


define("HELP_FILE","adv_list.php");
$err_mess = "File: ".__FILE__."<br>Line: ";

$sTableID = "t_adv_dynamic_list";
$oSort = new CAdminSorting($sTableID,"s_date", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	Array(
	)
);

if($lAdmin->IsDefaultFilter())
{
	$find_date1_DAYS_TO_BACK=90;
	$set_filter = "Y";
}

$FilterArr1 = array(
	"find_adv_id"
	);

$FilterArr = array(
	"find_date1",
	"find_date2"
	);

$lAdmin->InitFilter($FilterArr);

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2,
);

$find_adv_id = intval($find_adv_id);
$statDB = CDatabase::GetModuleConnection('statistic');
$strSql = "SELECT EVENTS_VIEW FROM b_stat_adv WHERE ID = ".$find_adv_id;
$a = $statDB->Query($strSql,false,$err_mess.__LINE__);
if (!$ar = $a->Fetch())
{
	$lAdmin->BeginCustomContent();
	CAdminMessage::ShowMessage(GetMessage("STAT_INCORRECT_ADV_ID"));
	$lAdmin->EndCustomContent();
}
else
{
	$EVENTS_VIEW = $ar["EVENTS_VIEW"];

	$rsData = CAdv::GetDynamicList($find_adv_id, $by, $order, $arMaxMin, $arFilter);
	$rsData = new CAdminResult($rsData, $sTableID);
	$rsData->NavStart();

	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_ADV_DYN_PAGES")));

	$arHeaders = Array(
		array("id"=>"DATE_STAT", "content"=>GetMessage("STAT_DATE"), "sort"=>"s_date", "default"=>true, "align" => "right"),
		array("id"=>"SESSIONS", "content"=>GetMessage("STAT_SESSIONS")." ".GetMessage("STAT_STRAIGHT"), "default"=>true,"align" => "right"),
		array("id"=>"SESSIONS_BACK", "content"=>GetMessage("STAT_SESSIONS")." ".GetMessage("STAT_BACK")."*", "default"=>true,"align" => "right"),
		array("id"=>"GUESTS", "content"=>GetMessage("STAT_GUESTS")." ".GetMessage("STAT_STRAIGHT"), "default"=>true,"align" => "right"),
		array("id"=>"GUESTS_BACK", "content"=>GetMessage("STAT_GUESTS")." ".GetMessage("STAT_BACK")."*", "default"=>true,"align" => "right"),
		array("id"=>"NEW_GUESTS", "content"=>GetMessage("STAT_GUESTS")." ".GetMessage("STAT_NEW"), "default"=>true,"align" => "right"),
		array("id"=>"C_HOSTS", "content"=>GetMessage("STAT_HOSTS")." ".GetMessage("STAT_STRAIGHT"), "default"=>true,"align" => "right"),
		array("id"=>"HOSTS_BACK", "content"=>GetMessage("STAT_HOSTS")." ".GetMessage("STAT_BACK")."*", "default"=>true,"align" => "right"),
		array("id"=>"HITS", "content"=>GetMessage("STAT_HITS")." ".GetMessage("STAT_STRAIGHT"), "default"=>true,"align" => "right"),
		array("id"=>"HITS_BACK", "content"=>GetMessage("STAT_HITS")." ".GetMessage("STAT_BACK")."*", "default"=>true,"align" => "right"),
		array("id"=>"EVENTS", "content"=>GetMessage("STAT_EVENTS"), "default"=>true),
	);

	$lAdmin->AddHeaders($arHeaders);

	while($arRes = $rsData->NavNext(true, "f_"))
	{
		$row =& $lAdmin->AddRow($f_DATE_STAT, $arRes);


		if (intval($f_SESSIONS)>0)
		{
			$str = "<a href=\"session_list.php?lang=".LANG."&amp;find_adv_id=".urlencode($find_adv_id)."&amp;find_adv_id_exact_match=Y&amp;find_date1=".$f_DATE_STAT."&amp;find_date2=".$f_DATE_STAT."&amp;set_filter=Y\">".intval($f_SESSIONS)."</a>";

			$row->AddViewField("SESSIONS", $str);
		}

		$arF["DATE1_PERIOD"] = $f_DATE_STAT;
		$arF["DATE2_PERIOD"] = $f_DATE_STAT;
		$arF["COUNTER_ADV_DYNAMIC_LIST"] = "1";
		$events = CAdv::GetEventList($find_adv_id,($by2="s_def"),($order2="desc"), $arF, $is_filtered);

		$sum = 0;
		$sum_back = 0;
		while($ar = $events->Fetch("e_"))
		{
			$sum += intval($ar["COUNTER"]);
			$sum_back += intval($ar["COUNTER_BACK"]);
		}

		$str = '<a title="'.GetMessage("STAT_VIEW_EVENT_LIST").'" href="event_list.php?lang='.LANG.'&find_adv_id='.urlencode($find_adv_id).'&find_adv_id_exact_match=Y&find_adv_back=N&find_date1='.$f_DATE_STAT.'&find_date2='.$f_DATE_STAT.'&set_filter=Y">'.$sum.'</a>&nbsp;(<a title="'.GetMessage("STAT_VIEW_EVENT_LIST_BACK").'" href="event_list.php?lang='.LANG.'&find_adv_id='.urlencode($find_adv_id).'&find_adv_back=Y&find_date1='.$f_DATE_STAT.'&find_date2='.$f_DATE_STAT.'&set_filter=Y">'.$sum_back.'</a>*)';
		$row->AddViewField("EVENTS", $str);


		$arActions = Array();

		$arActions[] = array(
			"ICON"=>"list",
			"TEXT"=>GetMessage("STAT_EVENTS_LIST"),
			"ACTION"=>"javascript:CloseWaitWindow(); jsUtils.OpenWindow('adv_list_popup.php?list_mode=period&lang=".LANG."&ID=".$find_adv_id."&find_date1_period=".$f_DATE_STAT."&find_date2_period=".$f_DATE_STAT."&set_filter=Y', '700', '550');",
			"DEFAULT" => "Y",
		);

		$row->AddActions($arActions);

	}


	$max_date = mktime(24,59,59,$arMaxMin["MAX_MONTH"], $arMaxMin["MAX_DAY"], $arMaxMin["MAX_YEAR"]);
	$min_date = mktime(0,0,0,$arMaxMin["MIN_MONTH"], $arMaxMin["MIN_DAY"], $arMaxMin["MIN_YEAR"]);
	$arF = Array(
		"ID"			=> $find_adv_id,
		"DATE1_PERIOD"	=> $arFilter["DATE1"],
		"DATE2_PERIOD"	=> $arFilter["DATE2"]
		);
	$a = CAdv::GetList($by3, $order3, $arF, $is_filtered, "", $arrGROUP_DAYS, $v);
	$ar = $a->GetNext();


	$row =& $lAdmin->AddRow(0, array());
	$row->SetFeatures(array("footer"=>true));


	$row->AddViewField("DATE_STAT", GetMessage("STAT_TOTAL"));
	$row->AddViewField("SESSIONS", $ar["SESSIONS_PERIOD"]);
	$row->AddViewField("SESSIONS_BACK", $ar["SESSIONS_BACK_PERIOD"]);
	$row->AddViewField("GUESTS", $ar["GUESTS_PERIOD"]);
	$row->AddViewField("GUESTS_BACK", $ar["GUESTS_BACK_PERIOD"]);
	$row->AddViewField("NEW_GUESTS", $ar["NEW_GUESTS_PERIOD"]);
	$row->AddViewField("C_HOSTS", $ar["C_HOSTS_PERIOD"]);
	$row->AddViewField("HOSTS_BACK", $ar["HOSTS_BACK_PERIOD"]);
	$row->AddViewField("HITS", $ar["HITS_PERIOD"]);
	$row->AddViewField("HITS_BACK", $ar["HITS_BACK_PERIOD"]);
	$row->AddViewField("EVENTS", "&nbsp;");
}


$aContext = array(
	array(
		"TEXT" => GetMessage("STAT_ADV_LIST"),
		"ICON" => "btn_list",
		"LINK" =>"/bitrix/admin/adv_list.php?lang=".LANG,
	),
);

$dynamic_days = CAdv::DynamicDays($find_adv_id, $arFilter["DATE1"], $arFilter["DATE2"]);
if ($dynamic_days>=2 && function_exists("ImageCreate"))
{
	$aContext[] = array(
		"TEXT" => GetMessage("STAT_GRAPH"),
		"LINK" =>"/bitrix/admin/adv_graph_list.php?lang=".LANG."&ADV_ID=".$find_adv_id."&find_date1=".$arFilter["DATE1"]."&find_date2=".$arFilter["DATE2"]."&set_filter=Y",
	);
}

if($context!="tab")
	$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>

<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?>
	</td>
</tr>
</form>
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()."?find_adv_id=".intval($find_adv_id), "form"=>"form1"));$filter->End();?>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?echo BeginNote();?>
<table border="0" width="100%" cellspacing="1" cellpadding="3">
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_STRAIGHT")?></td>
		<td valign="top" nowrap> - <?echo GetMessage("STAT_STRAIGHT_ALT")?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_BACK")?>*</td>
		<td valign="top" nowrap> - <?echo GetMessage("STAT_BACK_ALT")?></td>
	</tr>
	<tr>
		<td valign="top" nowrap><?echo GetMessage("STAT_NEW")?></td>
		<td valign="top" nowrap> - <?echo GetMessage("STAT_NEW_ALT")?></td>
	</tr>
</table>
<?echo EndNote();?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
