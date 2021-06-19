<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("STAT_CAMPAIGN"), "ICON"=>"main_user_edit", "TITLE"=>""),
	array("DIV" => "edit2", "TAB" => GetMessage("STAT_AUDIENCE"), "ICON"=>"main_user_edit", "TITLE"=>""),
);
if($STAT_RIGHT>="M")
$aTabs[]=array("DIV" => "edit3", "TAB" => GetMessage("STAT_FINANCES"), "ICON"=>"main_user_edit", "TITLE"=>"");

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$strError="";

$FilterArr= array(
	"find_date1_period",
	"find_date2_period",
);

$arFilter=array();
AdminListCheckDate($strError, array("find_date1_period"=>$find_date1_period, "find_date2_period"=>$find_date2_period));

if($find_group=="referer1")
{
	$GROUP = "Y";
	$arFilter["REFERER1"]=$referer1;
	$arFilter["REFERER1_EXACT_MATCH"]="Y";
	$arFilter["GROUP"]=$find_group;
}
elseif($find_group=="referer2")
{
	$GROUP = "Y";
	$arFilter["REFERER2"]=$referer2;
	$arFilter["REFERER2_EXACT_MATCH"]="Y";
	$arFilter["GROUP"]=$find_group;
}
else
{
	$GROUP = "N";
	$arFilter["ID"]=$ID;
	$arFilter["ID_EXACT_MATCH"]="Y";
}
$arFilter["DATE1_PERIOD"]=$find_date1_period;
$arFilter["DATE2_PERIOD"]=$find_date2_period;

$adv = CAdv::GetList('', '', $arFilter, $is_filtered, "", $arrGROUP_DAYS);

$now_date = GetTime(time());
$yesterday_date = GetTime(time()-86400);
$bef_yesterday_date = GetTime(time()-172800);

$arrREF_ID_2 = array(
	"GUESTS_TODAY",
	"GUESTS_BACK_TODAY",
	"NEW_GUESTS_TODAY",
	"C_HOSTS_TODAY",
	"HOSTS_BACK_TODAY",
	"SESSIONS_TODAY",
	"SESSIONS_BACK_TODAY",
	"HITS_TODAY",
	"HITS_BACK_TODAY",
	"GUESTS_YESTERDAY",
	"GUESTS_BACK_YESTERDAY",
	"NEW_GUESTS_YESTERDAY",
	"C_HOSTS_YESTERDAY",
	"HOSTS_BACK_YESTERDAY",
	"SESSIONS_YESTERDAY",
	"SESSIONS_BACK_YESTERDAY",
	"HITS_YESTERDAY",
	"HITS_BACK_YESTERDAY",
	"GUESTS_BEF_YESTERDAY",
	"GUESTS_BACK_BEF_YESTERDAY",
	"NEW_GUESTS_BEF_YESTERDAY",
	"C_HOSTS_BEF_YESTERDAY",
	"HOSTS_BACK_BEF_YESTERDAY",
	"SESSIONS_BEF_YESTERDAY",
	"SESSIONS_BACK_BEF_YESTERDAY",
	"HITS_BEF_YESTERDAY",
	"HITS_BACK_BEF_YESTERDAY",
	"GUESTS_PERIOD",
	"GUESTS_BACK_PERIOD",
	"NEW_GUESTS_PERIOD",
	"C_HOSTS_PERIOD",
	"HOSTS_BACK_PERIOD",
	"SESSIONS_PERIOD",
	"SESSIONS_BACK_PERIOD",
	"HITS_PERIOD",
	"HITS_BACK_PERIOD",
);

ClearVars("f_");
$adv->NavStart(1);
$ar = $adv->NavNext(true, "f_");


if ($GROUP=="Y")
{
	// init period data
	reset($arrREF_ID_2);
	foreach($arrREF_ID_2 as $key)
		${"f_".$key} = $arrGROUP_DAYS[${"f_".mb_strtoupper($find_group)}][$key];
}

$show_events = "";

function advlist_format_alt($value, $total, $title)
{
	if ($value>0 && $total>0)
		return (round($value/intval($total),4)*100)."% ".$title;
	else
		return "";
}
function advlist_format_link($value, $is_back, $group, $alt, $url="")
{
	if($value>0)
	{
		if($group=="Y")
			return	'<span title="'.htmlspecialcharsbx($alt).'">'.
				$value.($is_back?'*':'').
				'</span>';
		else
			return	'<a target="_blank" title="'.htmlspecialcharsbx($alt).'" '.
				'href="'.htmlspecialcharsbx($url).'">'.
				$value.'</a>'.($is_back?'*':'');
	}
	else
		return '&nbsp;';
}
function event_format_link($value, $total, $is_back, $group, $url)
{
	$sum_alt=advlist_format_alt($value, $total, GetMessage("STAT_PER_VISITORS"));
	if($group!=="Y")
		$sum_alt.="\n".GetMessage("STAT_VIEW_EVENT_LIST");
	return advlist_format_link($value, $is_back, $group, $sum_alt, $url);
}

// gather events data

$sTableID = "tbl_event_list_popup";
$oSort = new CAdminSorting($sTableID, "s_def", "desc");

$show_events = ($f_EVENTS_VIEW == '') ? COption::GetOptionString("statistic", "ADV_EVENTS_DEFAULT") : $f_EVENTS_VIEW;
$group_events = ($show_events=="event1" || $show_events=="event2") ? $show_events : "";
$arF = array();
$arF["DATE1_PERIOD"] = $arFilter["DATE1_PERIOD"];
$arF["DATE2_PERIOD"] = $arFilter["DATE2_PERIOD"];
if ($show_events=="event1") $arF["GROUP"] = "event1";
elseif($show_events=="event2") $arF["GROUP"] = "event2";
if($find!="" && $find_type=="event1") $arF["EVENT1"] = $find;
if($find!="" && $find_type=="event2") $arF["EVENT2"] = $find;

if ($GROUP=="N")
{
	$events = CAdv::GetEventList($f_ID, '', '', $arF);
}
elseif ($GROUP=="Y")
{
	$value = ($find_group=="referer1") ? $f_REFERER1 : $f_REFERER2;
	$events = CAdv::GetEventListByReferer($value, $arFilter);
}
$sum_today = 0;
$sum_back_today = 0;
$sum_yesterday = 0;
$sum_back_yesterday = 0;
$sum_bef_yesterday = 0;
$sum_back_bef_yesterday = 0;
$sum_period = 0;
$sum_back_period = 0;
$sum_total = 0;
$sum_back_total = 0;
$arEvents = array();
while ($er = $events->Fetch())
{
	$arEvents[] = $er;
	$sum_today += intval($er["COUNTER_TODAY"]);
	$sum_back_today += intval($er["COUNTER_BACK_TODAY"]);
	$sum_yesterday += intval($er["COUNTER_YESTERDAY"]);
	$sum_back_yesterday += intval($er["COUNTER_BACK_YESTERDAY"]);
	$sum_bef_yesterday += intval($er["COUNTER_BEF_YESTERDAY"]);
	$sum_back_bef_yesterday += intval($er["COUNTER_BACK_BEF_YESTERDAY"]);
	$sum_period += intval($er["COUNTER_PERIOD"]);
	$sum_back_period += intval($er["COUNTER_BACK_PERIOD"]);
	$sum_total += intval($er["COUNTER"]);
	$sum_back_total += intval($er["COUNTER_BACK"]);
}
$total_events_sum = $sum_total + $sum_back_total;

$arSum = array(
	"TODAY"=>event_format_link(
		$sum_today,
		$f_GUESTS_TODAY,
		false,
		$GROUP,
		"event_list.php?lang=".LANG."&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($now_date)."&find_date2=". urlencode($now_date)."&set_filter=Y"
	),
	"TODAY_BACK"=>event_format_link(
		$sum_back_today,
		$f_GUESTS_BACK_TODAY,
		true,
		$GROUP,
		"event_list.php?lang=".LANG."&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($now_date)."&find_date2=". urlencode($now_date)."&set_filter=Y"
	),
	"YESTERDAY"=>event_format_link(
		$sum_yesterday,
		$f_GUESTS_YESTERDAY,
		false,
		$GROUP,
		"event_list.php?lang=".LANG."&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($yesterday_date)."&find_date2=". urlencode($yesterday_date)."&set_filter=Y"
	),
	"YESTERDAY_BACK"=>event_format_link(
		$sum_back_yesterday,
		$f_GUESTS_BACK_YESTERDAY,
		true,
		$GROUP,
		"event_list.php?lang=".LANG."&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($yesterday_date)."&find_date2=". urlencode($yesterday_date)."&set_filter=Y"
	),
	"BEF_YESTERDAY"=>event_format_link(
		$sum_bef_yesterday,
		$f_GUESTS_BEF_YESTERDAY,
		false,
		$GROUP,
		"event_list.php?lang=".LANG."&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($bef_yesterday_date)."&find_date2=". urlencode($bef_yesterday_date)."&set_filter=Y"
	),
	"BEF_YESTERDAY_BACK"=>event_format_link(
		$sum_back_bef_yesterday,
		$f_GUESTS_BACK_BEF_YESTERDAY,
		true,
		$GROUP,
		"event_list.php?lang=".LANG."&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($bef_yesterday_date)."&find_date2=". urlencode($bef_yesterday_date)."&set_filter=Y"
	),
	"PERIOD"=>event_format_link(
		$sum_period,
		$f_GUESTS_PERIOD,
		false,
		$GROUP,
		"event_list.php?lang=".LANG."&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($find_date1_period)."&find_date2=". urlencode($find_date2_period)."&set_filter=Y"
	),
	"PERIOD_BACK"=>event_format_link(
		$sum_back_period,
		$f_GUESTS_BACK_PERIOD,
		true,
		$GROUP,
		"event_list.php?lang=".LANG."&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($find_date1_period)."&find_date2=". urlencode($find_date2_period)."&set_filter=Y"
	),
	"TOTAL"=>event_format_link(
		$sum_total,
		$f_GUESTS,
		false,
		$GROUP,
		"event_list.php?lang=".LANG."&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=N&&set_filter=Y"
	),
	"TOTAL_BACK"=>event_format_link(
		$sum_back_total,
		$f_GUESTS_BACK,
		true,
		$GROUP,
		"event_list.php?lang=".LANG."&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=Y&&set_filter=Y"
	),
);


$lAdmin = new CAdminList($sTableID, $oSort);
$thousand_sep = "&nbsp;";

$full_list = $show_events=="list" || $show_events=="event1" || $show_events=="event2";

$arHeaders = array();
if($show_events=="list" || $show_events=="event1")
	$arHeaders[]=
		array(	"id"		=>"EVENT1",
			"content"	=>"event1",
			"sort"		=>$full_list?"s_event1":false,
			"default"	=>true,
		);
if($show_events=="list" || $show_events=="event2")
	$arHeaders[]=
		array(	"id"		=>"EVENT2",
			"content"	=>"event2",
			"sort"		=>$full_list?"s_event2":false,
			"default"	=>true,
		);
if($list_mode!="period"):
	$arHeaders[]=
		array(	"id"		=>"today",
			"content"	=>GetMessage("STAT_TODAY")."<br>".GetMessage("STAT_STRAIGHT"),
			"sort"		=>$full_list?"s_counter_today":false,
			"align"		=>"right",
			"default"	=>true,
		);
	$arHeaders[]=
		array(	"id"		=>"today_back",
			"content"	=>GetMessage("STAT_TODAY")."<br>".GetMessage("STAT_BACK"),
			"sort"		=>$full_list?"s_counter_back_today":false,
			"align"		=>"right",
			"default"	=>true,
		);
	$arHeaders[]=
		array(	"id"		=>"yesterday",
			"content"	=>GetMessage("STAT_YESTERDAY")."<br>".GetMessage("STAT_STRAIGHT"),
			"sort"		=>$full_list?"s_counter_yestoday":false,
			"align"		=>"right",
			"default"	=>true,
		);
	$arHeaders[]=
		array(	"id"		=>"yesterday_back",
			"content"	=>GetMessage("STAT_YESTERDAY")."<br>".GetMessage("STAT_BACK"),
			"sort"		=>$full_list?"s_counter_back_yestoday":false,
			"align"		=>"right",
			"default"	=>true,
		);
	$arHeaders[]=
		array(	"id"		=>"bef_yesterday",
			"content"	=>GetMessage("STAT_BEFYESTERDAY")."<br>".GetMessage("STAT_STRAIGHT"),
			"sort"		=>$full_list?"s_counter_bef_yestoday":false,
			"align"		=>"right",
			"default"	=>true,
		);
	$arHeaders[]=
		array(	"id"		=>"bef_yesterday_back",
			"content"	=>GetMessage("STAT_BEFYESTERDAY")."<br>".GetMessage("STAT_BACK"),
			"sort"		=>$full_list?"s_counter_back_bef_yestoday":false,
			"align"		=>"right",
			"default"	=>true,
		);
endif;
if (($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):
	$arHeaders[]=
		array(	"id"		=>"period",
			"content"	=>GetMessage("STAT_PERIOD")."<br>".GetMessage("STAT_STRAIGHT"),
			"sort"		=>$full_list?"s_counter_period":false,
			"align"		=>"right",
			"default"	=>true,
		);
	$arHeaders[]=
		array(	"id"		=>"period_back",
			"content"	=>GetMessage("STAT_PERIOD")."<br>".GetMessage("STAT_BACK"),
			"sort"		=>$full_list?"s_counter_back_period":false,
			"align"		=>"right",
			"default"	=>true,
		);
endif;
$arHeaders[]=
	array(	"id"		=>"total",
		"content"	=>GetMessage("STAT_TOTAL")."<br>".GetMessage("STAT_STRAIGHT"),
		"sort"		=>$full_list?"s_counter":false,
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"total_back",
		"content"	=>GetMessage("STAT_TOTAL")."<br>".GetMessage("STAT_BACK"),
		"sort"		=>$full_list?"s_counter_back":false,
		"align"		=>"right",
		"default"	=>true,
	);

$lAdmin->AddHeaders($arHeaders);

if($full_list)
{
	$events = new CDBResult;
	$events->InitFromArray($arEvents);

	$rsData = new CAdminResult($events, $sTableID);

	$rsData->NavStart(10);
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_EVENTS")));
	while ($arRes = $rsData->NavNext(true, "e_"))
	{
		$row =& $lAdmin->AddRow($e_ID, $arRes);

		if ($show_events=="list")
		{
			$title = "ID = ".$e_ID;
			if ($e_EVENT1 <> '') $title .= "\nevent1 = ".$e_EVENT1;
			if ($e_EVENT2 <> '') $title .= "\nevent2 = ".$e_EVENT2;
			if ($e_NAME <> '') $title .= "\n".GetMessage("STAT_NAME")." ".$e_NAME;
			if ($e_DESCRIPTION <> '') $title .= "\n".GetMessage("STAT_DESCRIPTION")." ".$e_DESCRIPTION;
			$name = "<a target=\"_blank\" href=\"event_type_list.php?lang=".LANG."&find_id=".$e_ID."&find_id_exact_match=Y&set_filter=Y\" class=\"tablebodylink\" title=\"".$title."\">".$e_EVENT."</a>";
		}
		elseif ($show_events=="event1")
		{
			$name = "<a target=\"_blank\" href=\"event_type_list.php?lang=".LANG."&find_event1=".urlencode("\"".$e_EVENT1."\"")."&set_filter=Y\" class=\"tablebodylink\">".$e_EVENT1."</a>";
		}
		elseif ($show_events=="event2")
		{
			$name = "<a target=\"_blank\" href=\"event_type_list.php?lang=".LANG."&find_event2=".urlencode("\"".$e_EVENT2."\"")."&set_filter=Y\" class=\"tablebodylink\">".$e_EVENT2."</a>";
		}

		$strHTML=event_format_link(
			$e_COUNTER_TODAY,
			$f_GUESTS_TODAY,
			false,
			$GROUP,
			"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_event_id_exact_match=Y&find_adv_id=".$f_ID."&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($now_date)."&find_date2=". urlencode($now_date)."&set_filter=Y"
		);
		$row->AddViewField("today", $strHTML);
		$strHTML=event_format_link(
			$e_COUNTER_BACK_TODAY,
			$f_GUESTS_BACK_TODAY,
			true,
			$GROUP,
			"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_id=".$f_ID."&find_adv_back=Y&find_date1=".urlencode($now_date)."&find_date2=". urlencode($now_date)."&set_filter=Y"
		);
		$row->AddViewField("today_back", $strHTML);
		$strHTML=event_format_link(
			$e_COUNTER_YESTERDAY,
			$f_GUESTS_YESTERDAY,
			false,
			$GROUP,
			"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$f_ID."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($yesterday_date)."&find_date2=". urlencode($yesterday_date)."&set_filter=Y"
		);
		$row->AddViewField("yesterday", $strHTML);
		$strHTML=event_format_link(
			$e_COUNTER_BACK_YESTERDAY,
			$f_GUESTS_BACK_YESTERDAY,
			true,
			$GROUP,
			"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$f_ID."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($yesterday_date)."&find_date2=". urlencode($yesterday_date)."&set_filter=Y"
		);
		$row->AddViewField("yesterday_back", $strHTML);
		$strHTML=event_format_link(
			$e_COUNTER_BEF_YESTERDAY,
			$f_GUESTS_BEF_YESTERDAY,
			false,
			$GROUP,
			"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$f_ID."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($bef_yesterday_date)."&find_date2=". urlencode($bef_yesterday_date)."&set_filter=Y"
		);
		$row->AddViewField("bef_yesterday", $strHTML);
		$strHTML=event_format_link(
			$e_COUNTER_BACK_BEF_YESTERDAY,
			$f_GUESTS_BACK_BEF_YESTERDAY,
			true,
			$GROUP,
			"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$f_ID."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($bef_yesterday_date)."&find_date2=". urlencode($bef_yesterday_date)."&set_filter=Y"
		);
		$row->AddViewField("bef_yesterday_back", $strHTML);
		if (($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):
			$strHTML=event_format_link(
				$e_COUNTER_PERIOD,
				$f_GUESTS_PERIOD,
				false,
				$GROUP,
				"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$f_ID."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($find_date1_period)."&find_date2=". urlencode($find_date2_period)."&set_filter=Y"
				);
			$row->AddViewField("period", $strHTML);
			$strHTML=event_format_link(
				$e_COUNTER_BACK_PERIOD,
				$f_GUESTS_BACK_PERIOD,
				true,
				$GROUP,
				"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$f_ID."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($find_date1_period)."&find_date2=". urlencode($find_date2_period)."&set_filter=Y"
			);
			$row->AddViewField("period_back", $strHTML);
		endif;
		$strHTML=event_format_link(
			$e_COUNTER,
			$f_GUESTS,
			false,
			$GROUP,
			"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$f_ID."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=N&set_filter=Y"
		);
		$row->AddViewField("total", $strHTML);
		$strHTML=event_format_link(
			$e_COUNTER_BACK,
			$f_GUESTS_BACK,
			true,
			$GROUP,
			"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$f_ID."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=Y&set_filter=Y"
		);
		$row->AddViewField("total_back", $strHTML);
	}
}

$row =& $lAdmin->AddRow(0, array());
$row->SetFeatures(array("footer"=>$full_list));
$row->AddViewField("EVENT1", GetMessage("STAT_FOOTER"));
$row->AddViewField("today", $arSum["TODAY"]);
$row->AddViewField("today_back", $arSum["TODAY_BACK"]);
$row->AddViewField("yesterday", $arSum["YESTERDAY"]);
$row->AddViewField("yesterday_back", $arSum["YESTERDAY_BACK"]);
$row->AddViewField("bef_yesterday", $arSum["BEF_YESTERDAY"]);
$row->AddViewField("bef_yesterday_back", $arSum["BEF_YESTERDAY_BACK"]);
if (($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):
	$row->AddViewField("period", $arSum["PERIOD"]);
	$row->AddViewField("period_back", $arSum["PERIOD_BACK"]);
endif;
$row->AddViewField("total", $arSum["TOTAL"]);
$row->AddViewField("total_back", $arSum["TOTAL_BACK"]);

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

ShowError($strError);
?>
<?if($list_mode!="period"):?>
<table cellspacing="0" cellpadding="0" width="100%" border="0" class="edit-table"><tr><td>
<table border="0" width="100%" cellspacing="1" cellpadding="3" class="internal">
	<tr class="heading">
		<td width="50%" nowrap>
			<?if ($GROUP!="Y") :
				echo "[".$f_ID."]";
			else:
				echo "&nbsp;";
			endif;
			?><b><?echo $f_REFERER1?><?if ($f_REFERER2 <> '') echo "&nbsp;/&nbsp;";?><?echo $f_REFERER2?></b><?if ($GROUP=="Y") echo "&nbsp;(".GetMessage("STAT_EVENTS_GROUP_BY")."&nbsp;\"".$find_group."\")"?>
		</td>
		<td width="50%" nowrap><?echo $f_DATE_FIRST?><?echo $f_DATE_LAST <> '' ? "&nbsp;-&nbsp;".$f_DATE_LAST : "&nbsp;"?>
		</td>
	</tr>
</table>
</td></tr></table>
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<tr><td>
<table border="0" width="100%" cellspacing="1" cellpadding="3" class="internal">
	<tr class="heading">
		<td width="30%" nowrap>&nbsp;</td>
		<td width="15%" colspan="2" nowrap><?echo GetMessage("STAT_TODAY")?><br><?=$now_date?></td>
		<td width="15%" colspan="2" nowrap><?echo GetMessage("STAT_YESTERDAY")?><br><?=$yesterday_date?></td>
		<td width="15%" colspan="2" nowrap><?echo GetMessage("STAT_BEFYESTERDAY")?><br><?=$bef_yesterday_date?></td>
		<?if (($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):?>
		<td width="15%" colspan="2"><?echo GetMessage("STAT_PERIOD")?><br><?=htmlspecialcharsEx($arFilter["DATE1_PERIOD"])?>&nbsp;- <?=htmlspecialcharsEx($arFilter["DATE2_PERIOD"])?></td>
		<?endif;?>
		<td width="25%" colspan="2" nowrap><?echo GetMessage("STAT_TOTAL")?><br><?
			$days = intval($f_ADV_TIME/86400);
			echo $days."&nbsp;".GetMessage("STAT_DAYS")."&nbsp;";
			$f_ADV_TIME = $f_ADV_TIME - $days*86400;
			$hours = intval($f_ADV_TIME/3600);
			echo $hours."&nbsp;".GetMessage("STAT_HOURS");
			?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_SESSIONS")?>:</td>
		<td align="right"><?
			if (intval($f_SESSIONS_TODAY)>0) :
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;find_date1=<?echo urlencode($now_date)?>&amp;find_date2=<?echo urlencode($now_date)?>&amp;<?
				if ($find_group=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_group=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=N&amp;set_filter=Y"><?echo intval($f_SESSIONS_TODAY)?></a><?
			else:
				?>&nbsp;<?
			endif;
		?></td>
		<td align="right"><?
			if (intval($f_SESSIONS_BACK_TODAY)>0):
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;find_date1=<?echo urlencode($now_date)?>&amp;find_date2=<?echo urlencode($now_date)?>&amp;<?
				if ($find_group=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_group=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=Y&amp;set_filter=Y"><?echo intval($f_SESSIONS_BACK_TODAY)?></a>*<?
			else:
				?>&nbsp;<?
			endif;
		?></td>
		<td align="right"><?
			if (intval($f_SESSIONS_YESTERDAY)>0):
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;find_date1=<?echo urlencode($yesterday_date)?>&amp;find_date2=<?echo urlencode($yesterday_date)?>&amp;<?
				if ($find_group=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_group=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=N&amp;set_filter=Y"><?=intval($f_SESSIONS_YESTERDAY)?></a><?
			else :
				?>&nbsp;<?
			endif;
		?></td>
		<td align="right"><?
			if (intval($f_SESSIONS_BACK_YESTERDAY)>0) :
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;find_date1=<?echo urlencode($yesterday_date)?>&amp;find_date2=<?echo urlencode($yesterday_date)?>&amp;<?
				if ($find_group=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_group=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=Y&amp;set_filter=Y"><?echo intval($f_SESSIONS_BACK_YESTERDAY)?></a>*<?
			else :
				?>&nbsp;<?
			endif;
		?></td>
		<td align="right"><?
			if (intval($f_SESSIONS_BEF_YESTERDAY)>0) :
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;find_date1=<?echo urlencode($bef_yesterday_date)?>&amp;find_date2=<?echo urlencode($bef_yesterday_date)?>&amp;<?
				if ($find_group=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_group=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=N&amp;set_filter=Y"><?=intval($f_SESSIONS_BEF_YESTERDAY)?></a><?
			else :
				?>&nbsp;<?
			endif;
		?></td>
		<td align="right"><?
			if (intval($f_SESSIONS_BACK_BEF_YESTERDAY)>0) :
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;find_date1=<?echo urlencode($bef_yesterday_date)?>&amp;find_date2=<?echo urlencode($bef_yesterday_date)?>&amp;<?
				if ($find_group=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_group=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=Y&samp;et_filter=Y"><?echo intval($f_SESSIONS_BACK_BEF_YESTERDAY)?></a>*<?
			else :
				?>&nbsp;<?
			endif;
		?></td>
		<?if (($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):?>
		<td align="right"><?
			if (intval($f_SESSIONS_PERIOD)>0):
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_date1=<?=urlencode($find_date1_period); ?>&amp;find_date2=<?=urlencode($find_date2_period)?>&amp;<?
				if ($find_group=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_group=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=N&amp;set_filter=Y"><?=intval($f_SESSIONS_PERIOD)?></a><?
			else :
				?>&nbsp;<?
			endif;
		?></td>
		<td align="right"><?
			if (intval($f_SESSIONS_BACK_PERIOD)>0) :
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;find_date1=<?=urlencode($find_date1_period); ?>&amp;find_date2=<?=urlencode($find_date2_period)?>&amp;<?
				if ($find_group=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_group=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=Y&amp;set_filter=Y"><?echo intval($f_SESSIONS_BACK_PERIOD)?></a>*<?
			else :
				?>&nbsp;<?
			endif;
		?></td>
		<?endif;?>
		<td align="right">
			<a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;<?
				if ($find_group=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_group=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=N&amp;set_filter=Y"><b><?=intval($f_SESSIONS)?></b></a>
		</td>
		<td align="right">
			&nbsp;<a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;<?
					if ($find_group=="referer1") :
						echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
					elseif ($find_group=="referer2") :
						echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
					else :
						echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
					endif;
					?>&amp;find_adv_back=Y&amp;set_filter=Y"><b><?echo intval($f_SESSIONS_BACK)?></b></a>*
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_GUESTS")?>:</td>
		<td align="right"><?echo (intval($f_GUESTS_TODAY)>0 ? intval($f_GUESTS_TODAY) : "&nbsp;")?></td>
		<td align="right"><?echo (intval($f_GUESTS_BACK_TODAY)>0 ? intval($f_GUESTS_BACK_TODAY)."*" : "&nbsp;")?></td>
		<td align="right"><?echo intval($f_GUESTS_YESTERDAY)>0 ? intval($f_GUESTS_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_GUESTS_BACK_YESTERDAY)>0 ? intval($f_GUESTS_BACK_YESTERDAY)."*" : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_GUESTS_BEF_YESTERDAY)>0 ? intval($f_GUESTS_BEF_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_GUESTS_BACK_BEF_YESTERDAY)>0 ? intval($f_GUESTS_BACK_BEF_YESTERDAY)."*" : "&nbsp;"?></td>
		<?if (($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):?>
		<td align="right"><?echo intval($f_GUESTS_PERIOD)>0 ? intval($f_GUESTS_PERIOD) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_GUESTS_BACK_PERIOD)>0 ? intval($f_GUESTS_BACK_PERIOD)."*" : "&nbsp;"?></td>
		<?endif;?>
		<td align="right"><b><?echo intval($f_GUESTS)?></b></td>
		<td align="right"><b><?echo intval($f_GUESTS_BACK)?></b>*</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_NEW_GUESTS")?>:</td>
		<td align="right"><?echo (intval($f_NEW_GUESTS_TODAY)>0 ? intval($f_NEW_GUESTS_TODAY) : "&nbsp;")?></td>
		<td align="right">&nbsp;</td>
		<td align="right"><?echo intval($f_NEW_GUESTS_YESTERDAY)>0 ? intval($f_NEW_GUESTS_YESTERDAY) : "&nbsp;"?></td>
		<td align="right">&nbsp;</td>
		<td align="right"><?echo intval($f_NEW_GUESTS_BEF_YESTERDAY)>0 ? intval($f_NEW_GUESTS_BEF_YESTERDAY) : "&nbsp;"?></td>
		<td align="right">&nbsp;</td>
		<?if (($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):?>
		<td align="right"><?echo (intval($f_NEW_GUESTS_PERIOD)>0 ? intval($f_NEW_GUESTS_PERIOD) : "&nbsp;")?></td>
		<td align="right">&nbsp;</td>
		<?endif;?>
		<td align="right"><b><?=intval($f_NEW_GUESTS)?></b></td>
		<td align="right">&nbsp;</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_HOSTS")?>:</td>
		<td align="right"><?echo (intval($f_C_HOSTS_TODAY)>0 ? intval($f_C_HOSTS_TODAY) : "&nbsp;")?></td>
		<td align="right"><?echo (intval($f_HOSTS_BACK_TODAY)>0 ? intval($f_HOSTS_BACK_TODAY)."*" : "&nbsp;")?></td>
		<td align="right"><?echo intval($f_C_HOSTS_YESTERDAY)>0 ? intval($f_C_HOSTS_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HOSTS_BACK_YESTERDAY)>0 ? intval($f_HOSTS_BACK_YESTERDAY)."*" : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_C_HOSTS_BEF_YESTERDAY)>0 ? intval($f_C_HOSTS_BEF_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HOSTS_BACK_BEF_YESTERDAY)>0 ? intval($f_HOSTS_BACK_BEF_YESTERDAY)."*" : "&nbsp;"?></td>
		<?if (($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):?>
		<td align="right"><?echo intval($f_C_HOSTS_PERIOD) ? intval($f_C_HOSTS_PERIOD) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HOSTS_BACK_PERIOD) ? intval($f_HOSTS_BACK_PERIOD)."*" : "&nbsp;"?></td>
		<?endif;?>
		<td align="right"><b><?echo intval($f_C_HOSTS)?></b></td>
		<td align="right"><b><?echo intval($f_HOSTS_BACK)?></b>*</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_HITS")?>:</td>
		<td align="right"><?echo (intval($f_HITS_TODAY)>0 ? intval($f_HITS_TODAY) : "&nbsp;")?></td>
		<td align="right"><?echo (intval($f_HITS_BACK_TODAY)>0 ? intval($f_HITS_BACK_TODAY)."*" : "&nbsp;")?></td>
		<td align="right"><?echo intval($f_HITS_YESTERDAY)>0 ? intval($f_HITS_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HITS_BACK_YESTERDAY)>0 ? intval($f_HITS_BACK_YESTERDAY)."*" : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HITS_BEF_YESTERDAY)>0 ? intval($f_HITS_BEF_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HITS_BACK_BEF_YESTERDAY)>0 ? intval($f_HITS_BACK_BEF_YESTERDAY)."*" : "&nbsp;"?></td>
		<?if (($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):?>
		<td align="right"><?echo intval($f_HITS_PERIOD)>0 ? intval($f_HITS_PERIOD) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HITS_BACK_PERIOD)>0 ? intval($f_HITS_BACK_PERIOD)."*" : "&nbsp;"?></td>
		<?endif;?>
		<td align="right"><b><?echo intval($f_HITS)?></b></td>
		<td align="right"><b><?echo intval($f_HITS_BACK)?></b>*</td>
	</tr>
</table>
</td></tr>
<?$tabControl->BeginNextTab();?>
<tr>
	<td width="50%"><span title="<?=GetMessage("STAT_VISITORS_PER_DAY_ALT")?>"><?echo GetMessage("STAT_VISITORS_PER_DAY")?>:</span></td>
	<td width="50%"><?echo $f_VISITORS_PER_DAY<0 ? "-" : $f_VISITORS_PER_DAY?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_ATTENTIVENESS_ALT")?>"><?echo GetMessage("STAT_ATTENTIVENESS")?>:</span></td>
	<td>&nbsp;<?echo $f_ATTENT<0 ? "-" : $f_ATTENT?>(<?echo $f_ATTENT_BACK<0 ? "-" : $f_ATTENT_BACK?>*)</td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_ACTIVITY_ALT")?>"><?echo GetMessage("STAT_ACTIVITY")?>:</span></td>
	<td><?
		if (intval($f_GUESTS)<=0) echo "-";
		else
		{
			$res = $total_events_sum/$f_GUESTS;
			$res_round = round($res,2);
			if ($res>0 && $res_round<=0)
				echo "&#x2248;0";
			else
				echo $res_round;
		}
	?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_NEW_VISITORS_ALT")?>"><?echo GetMessage("STAT_NEW_VISITORS")?>:</span></td>
	<td><?echo $f_NEW_VISITORS<0 ? "-" : $f_NEW_VISITORS."%"?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_RETURNED_VISITORS_ALT")?>"><?echo GetMessage("STAT_RETURNED_VISITORS")?>:</span></td>
	<td><?echo $f_RETURNED_VISITORS<0 ? "-" : $f_RETURNED_VISITORS."%"?></td>
</tr>
<?
if($STAT_RIGHT>="M"):
$tabControl->BeginNextTab();
?>
<tr>
	<td width="50%"><span title="<?=GetMessage("STAT_INPUTS_ALT")?>"><?echo GetMessage("STAT_INPUTS")?>:</span></td>
	<td width="50%"><?echo str_replace(" ", $thousand_sep, number_format($f_COST, 2, ".", " "));?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_OUTPUTS_ALT")?>"><?echo GetMessage("STAT_OUTPUTS")?>:</span></td>
	<td><?echo str_replace(" ", $thousand_sep, number_format($f_REVENUE, 2, ".", " "));?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_BENEFIT_ALT")?>"><?echo GetMessage("STAT_BENEFIT")?>:</span></td>
	<td><?
	if ($f_BENEFIT<0) :
		?><span class="required"><?
		echo str_replace(" ", $thousand_sep, number_format($f_BENEFIT, 2, ".", " "));
		?></span><?
	else :
		?><span class="stat_pointed"><?
		echo str_replace(" ", $thousand_sep, number_format($f_BENEFIT, 2, ".", " "));
		?></span><?
	endif;
	?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_ROI_ALT")?>"><?echo GetMessage("STAT_ROI")?> (%):</span></td>
	<td><?
	if ($f_ROI<0) :
		echo "-";
	else :
		?><span class="stat_pointed"><?
	echo str_replace(" ", $thousand_sep, number_format($f_ROI, 2, ".", " "));
		?></span><?
	endif;
	?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_SESSION_COST_ALT")?>"><?echo GetMessage("STAT_SESSION_COST")?>:</span></td>
	<td><?echo str_replace(" ", $thousand_sep, number_format($f_SESSION_COST, 2, ".", " "));?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_VISITOR_COST_ALT")?>"><?echo GetMessage("STAT_VISITOR_COST")?>:</span></td>
	<td><?echo str_replace(" ", $thousand_sep, number_format($f_VISITOR_COST, 2, ".", " "));?></td>
</tr>
<?
endif;

$tabControl->End();

endif;//if($list_mode!="period"):

if($full_list):

$oFilter = new CAdminFilter($sTableID."_filter",false);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage("STAT_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("STAT_FIND_TITLE")?>">
		<?
		if($show_events=="event1")
			$arr = array(
				"reference" => array("event1"),
				"reference_id" => array("event1")
			);
		elseif($show_events=="event2")
			$arr = array(
				"reference" => array("event2"),
				"reference_id" => array("event2")
			);
		else
			$arr = array(
				"reference" => array("event1","event2"),
				"reference_id" => array("event1","event2")
			);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(), "form" => "find_form"));
$oFilter->End();
?>
</form>
<?
endif;//if($full_list)
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
