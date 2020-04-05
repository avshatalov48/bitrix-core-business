<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

/***************************************************************************
				Functions
***************************************************************************/

$sTableID = "t_adv_detail";
$sFilterID = $sTableID."_filter_id";
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);
ClearVars("f_");

$FilterArr = Array(
	"find",
	"find_type",
	"find_date1_period",
	"find_date2_period"
);

$lAdmin->InitFilter($FilterArr);

$strError="";
AdminListCheckDate($strError, array("find_date1_period"=>$find_date1_period, "find_date2_period"=>$find_date2_period));

if($find_type=="referer1")
{
	$GROUP = "Y";
	$arFilter["REFERER1"]=$find;
	$arFilter["REFERER1_EXACT_MATCH"]="Y";
	$arFilter["GROUP"]=$find_type;
}
elseif($find_type=="referer2")
{
	$GROUP = "Y";
	$arFilter["REFERER2"]=$find;
	$arFilter["REFERER2_EXACT_MATCH"]="Y";
	$arFilter["GROUP"]=$find_type;
}
else
{
	$GROUP = "N";
	$arFilter["ID"]=$find;
	$arFilter["ID_EXACT_MATCH"]="Y";
	$find_type = "id";
}
$arFilter["DATE1_PERIOD"]=$find_date1_period;
$arFilter["DATE2_PERIOD"]=$find_date2_period;

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

$sTableID_tab1 = "t_adv_detail_tab1";
$sTableID_tab2 = "t_adv_detail_tab2";
$sTableID_tab3 = "t_adv_detail_tab3";

if(strlen($strError)<=0 && (
	$_REQUEST["table_id"]=="" ||
	$_REQUEST["table_id"]==$sTableID_tab1 ||
	$_REQUEST["table_id"]==$sTableID_tab2 ||
	$_REQUEST["table_id"]==$sTableID_tab3
	))
{
	$adv = CAdv::GetList($by2, $order2, $arFilter, $is_filtered, "", $arrGROUP_DAYS, $v);
	$adv->NavStart(1);
	$ar = $adv->NavNext(true, "f_");
	if($ar && $GROUP=="Y")
	{
		// init period data
		reset($arrREF_ID_2);
		foreach($arrREF_ID_2 as $key)
			${"f_".$key} = $arrGROUP_DAYS[${"f_".strtoupper($find_type)}][$key];
	}
}

function advlist_format_alt($value, $total, $title)
{
	if ($value>0 && $total>0)
		return (round($value/intval($total),4)*100)."% ".$title;
	else
		return "";
}
function advlist_format_link($value, $is_back, $group, $alt, $url="", $show_money)
{
	if($value["C"]>0)
	{
		if($group=="Y")
			return	'<span title="'.htmlspecialcharsbx($alt).'">'.
				$value["C"].($show_money=="Y"?'('.str_replace(" ", "&nbsp;", number_format($value["M"], 2, ".", " ")).')':'').($is_back?'<span class="required">*</span>':'').
				'</span>';
		else
			return	'<a target="_blank" title="'.htmlspecialcharsbx($alt).'" '.
				'href="'.htmlspecialcharsbx($url).'">'.
				$value["C"].($show_money=="Y"?'('.str_replace(" ", "&nbsp;", number_format($value["M"], 2, ".", " ")).')':'').'</a>'.($is_back?'<span class="required">*</span>':'');
	}
	else
		return '&nbsp;';
}
function event_format_link($value, $total, $is_back, $group, $url, $show_money)
{
	$sum_alt=advlist_format_alt($value["C"], $total, GetMessage("STAT_PER_VISITORS"));
	if($group!=="Y")
		$sum_alt.="\n".GetMessage("STAT_VIEW_EVENT_LIST");
	return advlist_format_link($value, $is_back, $group, $sum_alt, $url, $show_money);
}

/**
 * @param CAdminList $lAdmin
 * @param boolean $show_money
 * @param boolean $get_total_events
 *
 * @return integer
 */
function create_event_list(&$lAdmin, $show_money=false, $get_total_events=false)
{
	// gather events data
	global $f_EVENTS_VIEW;
	global $arFilter;

	$show_events = (strlen($f_EVENTS_VIEW)<=0) ? COption::GetOptionString("statistic", "ADV_EVENTS_DEFAULT") : $f_EVENTS_VIEW;
	$arF = array();
	$arF["DATE1_PERIOD"] = $arFilter["DATE1_PERIOD"];
	$arF["DATE2_PERIOD"] = $arFilter["DATE2_PERIOD"];
	if($show_money)
		$arF["MONEY1"] = 0.0001;
	if ($show_events=="event1") $arF["GROUP"] = "event1";
	elseif($show_events=="event2") $arF["GROUP"] = "event2";
	global $GROUP,$find_type,$find,$find_id,$f_REFERER1,$f_REFERER2;

	$adv_id = intval($find_type=="id" && $find<>""?$find:$find_id);

	if ($GROUP=="N")
	{
		$events = CAdv::GetEventList($adv_id,$by,$order, $arF, $v1);
	}
	else
	{
		$value = ($find_type=="referer1") ? $f_REFERER1 : $f_REFERER2;
		$events = CAdv::GetEventListByReferer($value, $arFilter);
	}
	$sum_today = array("C"=>0,"M"=>0.0);
	$sum_back_today = array("C"=>0,"M"=>0.0);
	$sum_yesterday = array("C"=>0,"M"=>0.0);
	$sum_back_yesterday = array("C"=>0,"M"=>0.0);
	$sum_bef_yesterday = array("C"=>0,"M"=>0.0);
	$sum_back_bef_yesterday = array("C"=>0,"M"=>0.0);
	$sum_period = array("C"=>0,"M"=>0.0);
	$sum_back_period = array("C"=>0,"M"=>0.0);
	$sum_total = array("C"=>0,"M"=>0.0);
	$sum_back_total = array("C"=>0,"M"=>0.0);
	$arEvents = array();
	while ($er = $events->Fetch())
	{
		$arEvents[] = $er;
		$sum_today["C"] += intval($er["COUNTER_TODAY"]);
		$sum_back_today["C"] += intval($er["COUNTER_BACK_TODAY"]);
		$sum_yesterday["C"] += intval($er["COUNTER_YESTERDAY"]);
		$sum_back_yesterday["C"] += intval($er["COUNTER_BACK_YESTERDAY"]);
		$sum_bef_yesterday["C"] += intval($er["COUNTER_BEF_YESTERDAY"]);
		$sum_back_bef_yesterday["C"] += intval($er["COUNTER_BACK_BEF_YESTERDAY"]);
		$sum_period["C"] += intval($er["COUNTER_PERIOD"]);
		$sum_back_period["C"] += intval($er["COUNTER_BACK_PERIOD"]);
		$sum_total["C"] += intval($er["COUNTER"]);
		$sum_back_total["C"] += intval($er["COUNTER_BACK"]);
		if($show_money=="Y")
		{
			$sum_today["M"] += doubleval($er["MONEY_TODAY"]);
			$sum_back_today["M"] += doubleval($er["MONEY_BACK_TODAY"]);
			$sum_yesterday["M"] += doubleval($er["MONEY_YESTERDAY"]);
			$sum_back_yesterday["M"] += doubleval($er["MONEY_BACK_YESTERDAY"]);
			$sum_bef_yesterday["M"] += doubleval($er["MONEY_BEF_YESTERDAY"]);
			$sum_back_bef_yesterday["M"] += doubleval($er["MONEY_BACK_BEF_YESTERDAY"]);
			$sum_period["M"] += doubleval($er["MONEY_PERIOD"]);
			$sum_back_period["M"] += doubleval($er["MONEY_BACK_PERIOD"]);
			$sum_total["M"] += doubleval($er["MONEY"]);
			$sum_back_total["M"] += doubleval($er["MONEY_BACK"]);
		}
	}
	$total_events_sum=array("C"=>0,"M"=>0.0);
	$total_events_sum["C"] = $sum_total["C"] + $sum_back_total["C"];
	$total_events_sum["M"] = $sum_total["M"] + $sum_back_total["M"];
	if($get_total_events)
		return $total_events_sum["C"];

	global $f_GUESTS_TODAY,$f_GUESTS_BACK_TODAY,$f_GUESTS_YESTERDAY,$f_GUESTS_BACK_YESTERDAY;
	global $f_GUESTS_BEF_YESTERDAY,$f_GUESTS_BACK_BEF_YESTERDAY,$f_GUESTS_PERIOD,$f_GUESTS_BACK_PERIOD;
	global $f_GUESTS,$f_GUESTS_BACK;

	$arSum = array(
		"TODAY"=>event_format_link(
			$sum_today,
			$f_GUESTS_TODAY,
			false,
			$GROUP,
			"event_list.php?lang=".LANG."&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($now_date)."&find_date2=". urlencode($now_date)."&set_filter=Y",
			$show_money
		),
		"TODAY_BACK"=>event_format_link(
			$sum_back_today,
			$f_GUESTS_BACK_TODAY,
			true,
			$GROUP,
			"event_list.php?lang=".LANG."&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($now_date)."&find_date2=". urlencode($now_date)."&set_filter=Y",
			$show_money
		),
		"YESTERDAY"=>event_format_link(
			$sum_yesterday,
			$f_GUESTS_YESTERDAY,
			false,
			$GROUP,
			"event_list.php?lang=".LANG."&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($yesterday_date)."&find_date2=". urlencode($yesterday_date)."&set_filter=Y",
			$show_money
		),
		"YESTERDAY_BACK"=>event_format_link(
			$sum_back_yesterday,
			$f_GUESTS_BACK_YESTERDAY,
			true,
			$GROUP,
			"event_list.php?lang=".LANG."&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($yesterday_date)."&find_date2=". urlencode($yesterday_date)."&set_filter=Y",
			$show_money
		),
		"BEF_YESTERDAY"=>event_format_link(
			$sum_bef_yesterday,
			$f_GUESTS_BEF_YESTERDAY,
			false,
			$GROUP,
			"event_list.php?lang=".LANG."&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($bef_yesterday_date)."&find_date2=". urlencode($bef_yesterday_date)."&set_filter=Y",
			$show_money
		),
		"BEF_YESTERDAY_BACK"=>event_format_link(
			$sum_back_bef_yesterday,
			$f_GUESTS_BACK_BEF_YESTERDAY,
			true,
			$GROUP,
			"event_list.php?lang=".LANG."&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($bef_yesterday_date)."&find_date2=". urlencode($bef_yesterday_date)."&set_filter=Y",
			$show_money
		),
		"PERIOD"=>event_format_link(
			$sum_period,
			$f_GUESTS_PERIOD,
			false,
			$GROUP,
			"event_list.php?lang=".LANG."&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($find_date1_period)."&find_date2=". urlencode($find_date2_period)."&set_filter=Y",
			$show_money
		),
		"PERIOD_BACK"=>event_format_link(
			$sum_back_period,
			$f_GUESTS_BACK_PERIOD,
			true,
			$GROUP,
			"event_list.php?lang=".LANG."&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($find_date1_period)."&find_date2=". urlencode($find_date2_period)."&set_filter=Y",
			$show_money
		),
		"TOTAL"=>event_format_link(
			$sum_total,
			$f_GUESTS,
			false,
			$GROUP,
			"event_list.php?lang=".LANG."&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=N&&set_filter=Y",
			$show_money
		),
		"TOTAL_BACK"=>event_format_link(
			$sum_back_total,
			$f_GUESTS_BACK,
			true,
			$GROUP,
			"event_list.php?lang=".LANG."&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=Y&&set_filter=Y",
			$show_money
		),
	);
	$full_list = $show_events=="list" || $show_events=="event1" || $show_events=="event2";

	$arHeaders = array();
	if($show_events=="list" || $show_events=="event1")
		$arHeaders[]=
			array(	"id"		=>"EVENT1",
				"content"	=>"event1",
				"default"	=>true,
			);
	if($show_events=="list" || $show_events=="event2")
		$arHeaders[]=
			array(	"id"		=>"EVENT2",
				"content"	=>"event2",
				"default"	=>true,
			);
	if($list_mode!="period"):
		$arHeaders[]=
			array(	"id"		=>"today",
				"content"	=>GetMessage("STAT_TODAY")."<br>".GetMessage("STAT_STRAIGHT"),
				"align"		=>"right",
				"default"	=>true,
			);
		$arHeaders[]=
			array(	"id"		=>"today_back",
				"content"	=>GetMessage("STAT_TODAY")."<br>".GetMessage("STAT_BACK"),
				"align"		=>"right",
				"default"	=>true,
			);
		$arHeaders[]=
			array(	"id"		=>"yesterday",
				"content"	=>GetMessage("STAT_YESTERDAY")."<br>".GetMessage("STAT_STRAIGHT"),
				"align"		=>"right",
				"default"	=>true,
			);
		$arHeaders[]=
			array(	"id"		=>"yesterday_back",
				"content"	=>GetMessage("STAT_YESTERDAY")."<br>".GetMessage("STAT_BACK"),
				"align"		=>"right",
				"default"	=>true,
			);
		$arHeaders[]=
			array(	"id"		=>"bef_yesterday",
				"content"	=>GetMessage("STAT_BEFYESTERDAY")."<br>".GetMessage("STAT_STRAIGHT"),
				"align"		=>"right",
				"default"	=>true,
			);
		$arHeaders[]=
			array(	"id"		=>"bef_yesterday_back",
				"content"	=>GetMessage("STAT_BEFYESTERDAY")."<br>".GetMessage("STAT_BACK"),
				"align"		=>"right",
				"default"	=>true,
			);
	endif;
	global $find_date1_period,$find_date2_period,$is_filtered;
	if ((strlen($find_date1_period)>0 || strlen($find_date2_period)>0) && $is_filtered):
		$arHeaders[]=
			array(	"id"		=>"period",
				"content"	=>GetMessage("STAT_PERIOD")."<br>".GetMessage("STAT_STRAIGHT"),
				"align"		=>"right",
				"default"	=>true,
			);
		$arHeaders[]=
			array(	"id"		=>"period_back",
				"content"	=>GetMessage("STAT_PERIOD")."<br>".GetMessage("STAT_BACK"),
				"align"		=>"right",
				"default"	=>true,
			);
	endif;
	$arHeaders[]=
		array(	"id"		=>"total",
			"content"	=>GetMessage("STAT_TOTAL")."<br>".GetMessage("STAT_STRAIGHT"),
			"align"		=>"right",
			"default"	=>true,
		);
	$arHeaders[]=
		array(	"id"		=>"total_back",
			"content"	=>GetMessage("STAT_TOTAL")."<br>".GetMessage("STAT_BACK"),
			"align"		=>"right",
			"default"	=>true,
		);

	$lAdmin->AddHeaders($arHeaders);

	if($full_list)
	{
		$events = new CDBResult;
		$events->InitFromArray($arEvents);

		$rsData = new CAdminResult($events, $lAdmin->table_id);

		$first=true;
		$i=COption::GetOptionInt("statistic","ADV_DETAIL_TOP_SIZE");
		while ($i>0 && $arRes = $rsData->NavNext(true, "e_"))
		{
			if($first)
			{
				foreach($arRes as $key=>$value)
					global ${"e_".$key};
				$first=false;
			}
			$row =& $lAdmin->AddRow($e_ID, $arRes);

			$strHTML=event_format_link(
				array("C"=>$e_COUNTER_TODAY,"M"=>$e_MONEY_TODAY),
				$f_GUESTS_TODAY,
				false,
				$GROUP,
				"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_event_id_exact_match=Y&find_adv_id=".$adv_id."&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($now_date)."&find_date2=". urlencode($now_date)."&set_filter=Y",
				$show_money
			);
			$row->AddViewField("today", $strHTML);
			$strHTML=event_format_link(
				array("C"=>$e_COUNTER_BACK_TODAY,"M"=>$e_MONEY_BACK_TODAY),
				$f_GUESTS_BACK_TODAY,
				true,
				$GROUP,
				"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_id=".$adv_id."&find_adv_back=Y&find_date1=".urlencode($now_date)."&find_date2=". urlencode($now_date)."&set_filter=Y",
				$show_money
			);
			$row->AddViewField("today_back", $strHTML);
			$strHTML=event_format_link(
				array("C"=>$e_COUNTER_YESTERDAY,"M"=>$e_MONEY_YESTERDAY),
				$f_GUESTS_YESTERDAY,
				false,
				$GROUP,
				"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$adv_id."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($yesterday_date)."&find_date2=". urlencode($yesterday_date)."&set_filter=Y",
				$show_money
			);
			$row->AddViewField("yesterday", $strHTML);
			$strHTML=event_format_link(
				array("C"=>$e_COUNTER_BACK_YESTERDAY,"M"=>$e_MONEY_BACK_YESTERDAY),
				$f_GUESTS_BACK_YESTERDAY,
				true,
				$GROUP,
				"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$adv_id."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($yesterday_date)."&find_date2=". urlencode($yesterday_date)."&set_filter=Y",
				$show_money
			);
			$row->AddViewField("yesterday_back", $strHTML);
			$strHTML=event_format_link(
				array("C"=>$e_COUNTER_BEF_YESTERDAY,"M"=>$e_MONEY_BEF_YESTERDAY),
				$f_GUESTS_BEF_YESTERDAY,
				false,
				$GROUP,
				"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$adv_id."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($bef_yesterday_date)."&find_date2=". urlencode($bef_yesterday_date)."&set_filter=Y",
				$show_money
			);
			$row->AddViewField("bef_yesterday", $strHTML);
			$strHTML=event_format_link(
				array("C"=>$e_COUNTER_BACK_BEF_YESTERDAY,"M"=>$e_MONEY_BACK_BEF_YESTERDAY),
				$f_GUESTS_BACK_BEF_YESTERDAY,
				true,
				$GROUP,
				"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$adv_id."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($bef_yesterday_date)."&find_date2=". urlencode($bef_yesterday_date)."&set_filter=Y",
				$show_money
			);
			$row->AddViewField("bef_yesterday_back", $strHTML);
			if ((strlen($find_date1_period)>0 || strlen($find_date2_period)>0) && $is_filtered):
				$strHTML=event_format_link(
					array("C"=>$e_COUNTER_PERIOD,"M"=>$e_MONEY_PERIOD),
					$f_GUESTS_PERIOD,
					false,
					$GROUP,
					"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$adv_id."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=N&find_date1=".urlencode($find_date1_period)."&find_date2=". urlencode($find_date2_period)."&set_filter=Y",
					$show_money
					);
				$row->AddViewField("period", $strHTML);
				$strHTML=event_format_link(
					array("C"=>$e_COUNTER_BACK_PERIOD,"M"=>$e_MONEY_BACK_PERIOD),
					$f_GUESTS_BACK_PERIOD,
					true,
					$GROUP,
					"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$adv_id."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=Y&find_date1=".urlencode($find_date1_period)."&find_date2=". urlencode($find_date2_period)."&set_filter=Y",
					$show_money
				);
				$row->AddViewField("period_back", $strHTML);
			endif;
			$strHTML=event_format_link(
				array("C"=>$e_COUNTER,"M"=>$e_MONEY),
				$f_GUESTS,
				false,
				$GROUP,
				"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$adv_id."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=N&set_filter=Y",
				$show_money
			);
			$row->AddViewField("total", $strHTML);
			$strHTML=event_format_link(
				array("C"=>$e_COUNTER_BACK,"M"=>$e_MONEY_BACK),
				$f_GUESTS_BACK,
				true,
				$GROUP,
				"event_list.php?lang=".LANG."&find_event_id=".$e_ID."&find_adv_id=".$adv_id."&find_event_id_exact_match=Y&find_adv_id_exact_match=Y&find_adv_back=Y&set_filter=Y",
				$show_money
			);
			$row->AddViewField("total_back", $strHTML);
			--$i;
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
	if ((strlen($find_date1_period)>0 || strlen($find_date2_period)>0) && $is_filtered):
		$row->AddViewField("period", $arSum["PERIOD"]);
		$row->AddViewField("period_back", $arSum["PERIOD_BACK"]);
	endif;
	$row->AddViewField("total", $arSum["TOTAL"]);
	$row->AddViewField("total_back", $arSum["TOTAL_BACK"]);

	return 0;
}

$oSort_tab1 = new CAdminSorting($sTableID_tab1);
$lAdmin_tab1 = new CAdminList($sTableID_tab1, $oSort_tab1);
$lAdmin_tab1->InitFilter(array());

//Setup title
if($find_type=="referer1")
	$title = $find." / ";
elseif($find_type=="referer2")
	$title = " / ".$find;
elseif(is_array($ar))
	$title = $ar["REFERER1"]." / ".$ar["REFERER2"]." [".$ar["ID"]."]";
else
	$title = "[".$find."]";
$lAdmin_tab1->onLoadScript = "BX.adminPanel.setTitle('".CUtil::JSEscape(GetMessage("STAT_ADV_CAMPAIGN_TITLE")." ".$title)."');";

$lAdmin_tab1->BeginCustomContent();
if(strlen($strError)>0):
	$m = new CAdminMessage($strError);
	echo $m->Show();
elseif($ar==false):
	$m = new CAdminMessage(GetMessage("STAT_NO_DATA_FOR_FILTER"));
	echo $m->Show();
elseif($_REQUEST["table_id"]=="" || $_REQUEST["table_id"]==$sTableID_tab1):
?>
<table border="0" cellspacing="1" cellpadding="3" class="list-table">
	<tr class="heading">
		<td nowrap>&nbsp;</td>
		<td colspan="2" nowrap><?echo GetMessage("STAT_TODAY")?><br><?=$now_date?></td>
		<td colspan="2" nowrap><?echo GetMessage("STAT_YESTERDAY")?><br><?=$yesterday_date?></td>
		<td colspan="2" nowrap><?echo GetMessage("STAT_BEFYESTERDAY")?><br><?=$bef_yesterday_date?></td>
		<?if ((strlen($find_date1_period)>0 || strlen($find_date2_period)>0) && $is_filtered):?>
		<td colspan="2"><?echo GetMessage("STAT_PERIOD")?><br><?=$arFilter["DATE1_PERIOD"]?>&nbsp;- <?=$arFilter["DATE2_PERIOD"]?></td>
		<?endif;?>
		<td colspan="2" nowrap><?echo GetMessage("STAT_TOTAL")?><br><?
			$days = intval(intval($f_ADV_TIME)/86400);
			echo $days."&nbsp;".GetMessage("STAT_DAYS")."&nbsp;";
			$f_ADV_TIME = $f_ADV_TIME - $days*86400;
			$hours = intval(intval($f_ADV_TIME)/3600);
			echo $hours."&nbsp;".GetMessage("STAT_HOURS");
			?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_SESSIONS")?>:</td>
		<td align="right"><?
			if (intval($f_SESSIONS_TODAY)>0) :
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;find_date1=<?echo urlencode($now_date)?>&amp;find_date2=<?echo urlencode($now_date)?>&amp;<?
				if ($find_type=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_type=="referer2") :
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
				if ($find_type=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_type=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=Y&amp;set_filter=Y"><?echo intval($f_SESSIONS_BACK_TODAY)?></a><span class="required">*</span><?
			else:
				?>&nbsp;<?
			endif;
		?></td>
		<td align="right"><?
			if (intval($f_SESSIONS_YESTERDAY)>0):
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;find_date1=<?echo urlencode($yesterday_date)?>&amp;find_date2=<?echo urlencode($yesterday_date)?>&amp;<?
				if ($find_type=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_type=="referer2") :
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
				if ($find_type=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_type=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=Y&amp;set_filter=Y"><?echo intval($f_SESSIONS_BACK_YESTERDAY)?></a><span class="required">*</span><?
			else :
				?>&nbsp;<?
			endif;
		?></td>
		<td align="right"><?
			if (intval($f_SESSIONS_BEF_YESTERDAY)>0) :
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;find_date1=<?echo urlencode($bef_yesterday_date)?>&amp;find_date2=<?echo urlencode($bef_yesterday_date)?>&amp;<?
				if ($find_type=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_type=="referer2") :
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
				if ($find_type=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_type=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=Y&samp;et_filter=Y"><?echo intval($f_SESSIONS_BACK_BEF_YESTERDAY)?></a><span class="required">*</span><?
			else :
				?>&nbsp;<?
			endif;
		?></td>
		<?if ((strlen($find_date1_period)>0 || strlen($find_date2_period)>0) && $is_filtered):?>
		<td align="right"><?
			if (intval($f_SESSIONS_PERIOD)>0):
				?><a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_date1=<?=urlencode($find_date1_period); ?>&amp;find_date2=<?=urlencode($find_date2_period)?>&amp;<?
				if ($find_type=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_type=="referer2") :
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
				if ($find_type=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_type=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=Y&amp;set_filter=Y"><?echo intval($f_SESSIONS_BACK_PERIOD)?></a><span class="required">*</span><?
			else :
				?>&nbsp;<?
			endif;
		?></td>
		<?endif;?>
		<td align="right">
			<a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;<?
				if ($find_type=="referer1") :
					echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
				elseif ($find_type=="referer2") :
					echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
				else :
					echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
				endif;
				?>&amp;find_adv_back=N&amp;set_filter=Y"><b><?=intval($f_SESSIONS)?></b></a>
		</td>
		<td align="right">
			&nbsp;<a target="_blank" title="<?echo GetMessage("STAT_SESSIONS_LIST")?>" href="session_list.php?lang=<?=LANG?>&amp;<?
					if ($find_type=="referer1") :
						echo "find_referer1=".urlencode("\"".$f_REFERER1."\"");
					elseif ($find_type=="referer2") :
						echo "find_referer2=".urlencode("\"".$f_REFERER2."\"");
					else :
						echo "find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y";
					endif;
					?>&amp;find_adv_back=Y&amp;set_filter=Y"><b><?echo intval($f_SESSIONS_BACK)?></b></a><span class="required">*</span>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_GUESTS")?>:</td>
		<td align="right"><?echo (intval($f_GUESTS_TODAY)>0 ? intval($f_GUESTS_TODAY) : "&nbsp;")?></td>
		<td align="right"><?echo (intval($f_GUESTS_BACK_TODAY)>0 ? intval($f_GUESTS_BACK_TODAY).'<span class="required">*</span>' : "&nbsp;")?></td>
		<td align="right"><?echo intval($f_GUESTS_YESTERDAY)>0 ? intval($f_GUESTS_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_GUESTS_BACK_YESTERDAY)>0 ? intval($f_GUESTS_BACK_YESTERDAY).'<span class="required">*</span>' : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_GUESTS_BEF_YESTERDAY)>0 ? intval($f_GUESTS_BEF_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_GUESTS_BACK_BEF_YESTERDAY)>0 ? intval($f_GUESTS_BACK_BEF_YESTERDAY).'<span class="required">*</span>' : "&nbsp;"?></td>
		<?if ((strlen($find_date1_period)>0 || strlen($find_date2_period)>0) && $is_filtered):?>
		<td align="right"><?echo intval($f_GUESTS_PERIOD)>0 ? intval($f_GUESTS_PERIOD) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_GUESTS_BACK_PERIOD)>0 ? intval($f_GUESTS_BACK_PERIOD).'<span class="required">*</span>' : "&nbsp;"?></td>
		<?endif;?>
		<td align="right"><b><?echo intval($f_GUESTS)?></b></td>
		<td align="right"><b><?echo intval($f_GUESTS_BACK)?></b><span class="required">*</span></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_NEW_GUESTS")?>:</td>
		<td align="right"><?echo (intval($f_NEW_GUESTS_TODAY)>0 ? intval($f_NEW_GUESTS_TODAY) : "&nbsp;")?></td>
		<td align="right">&nbsp;</td>
		<td align="right"><?echo intval($f_NEW_GUESTS_YESTERDAY)>0 ? intval($f_NEW_GUESTS_YESTERDAY) : "&nbsp;"?></td>
		<td align="right">&nbsp;</td>
		<td align="right"><?echo intval($f_NEW_GUESTS_BEF_YESTERDAY)>0 ? intval($f_NEW_GUESTS_BEF_YESTERDAY) : "&nbsp;"?></td>
		<td align="right">&nbsp;</td>
		<?if ((strlen($find_date1_period)>0 || strlen($find_date2_period)>0) && $is_filtered):?>
		<td align="right"><?echo (intval($f_NEW_GUESTS_PERIOD)>0 ? intval($f_NEW_GUESTS_PERIOD) : "&nbsp;")?></td>
		<td align="right">&nbsp;</td>
		<?endif;?>
		<td align="right"><b><?=intval($f_NEW_GUESTS)?></b></td>
		<td align="right">&nbsp;</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_HOSTS")?>:</td>
		<td align="right"><?echo (intval($f_C_HOSTS_TODAY)>0 ? intval($f_C_HOSTS_TODAY) : "&nbsp;")?></td>
		<td align="right"><?echo (intval($f_HOSTS_BACK_TODAY)>0 ? intval($f_HOSTS_BACK_TODAY).'<span class="required">*</span>' : "&nbsp;")?></td>
		<td align="right"><?echo intval($f_C_HOSTS_YESTERDAY)>0 ? intval($f_C_HOSTS_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HOSTS_BACK_YESTERDAY)>0 ? intval($f_HOSTS_BACK_YESTERDAY).'<span class="required">*</span>' : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_C_HOSTS_BEF_YESTERDAY)>0 ? intval($f_C_HOSTS_BEF_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HOSTS_BACK_BEF_YESTERDAY)>0 ? intval($f_HOSTS_BACK_BEF_YESTERDAY).'<span class="required">*</span>' : "&nbsp;"?></td>
		<?if ((strlen($find_date1_period)>0 || strlen($find_date2_period)>0) && $is_filtered):?>
		<td align="right"><?echo intval($f_C_HOSTS_PERIOD) ? intval($f_C_HOSTS_PERIOD) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HOSTS_BACK_PERIOD) ? intval($f_HOSTS_BACK_PERIOD).'<span class="required">*</span>' : "&nbsp;"?></td>
		<?endif;?>
		<td align="right"><b><?echo intval($f_C_HOSTS)?></b></td>
		<td align="right"><b><?echo intval($f_HOSTS_BACK)?></b><span class="required">*</span></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_HITS")?>:</td>
		<td align="right"><?echo (intval($f_HITS_TODAY)>0 ? intval($f_HITS_TODAY) : "&nbsp;")?></td>
		<td align="right"><?echo (intval($f_HITS_BACK_TODAY)>0 ? intval($f_HITS_BACK_TODAY).'<span class="required">*</span>' : "&nbsp;")?></td>
		<td align="right"><?echo intval($f_HITS_YESTERDAY)>0 ? intval($f_HITS_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HITS_BACK_YESTERDAY)>0 ? intval($f_HITS_BACK_YESTERDAY).'<span class="required">*</span>' : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HITS_BEF_YESTERDAY)>0 ? intval($f_HITS_BEF_YESTERDAY) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HITS_BACK_BEF_YESTERDAY)>0 ? intval($f_HITS_BACK_BEF_YESTERDAY).'<span class="required">*</span>' : "&nbsp;"?></td>
		<?if ((strlen($find_date1_period)>0 || strlen($find_date2_period)>0) && $is_filtered):?>
		<td align="right"><?echo intval($f_HITS_PERIOD)>0 ? intval($f_HITS_PERIOD) : "&nbsp;"?></td>
		<td align="right"><?echo intval($f_HITS_BACK_PERIOD)>0 ? intval($f_HITS_BACK_PERIOD).'<span class="required">*</span>' : "&nbsp;"?></td>
		<?endif;?>
		<td align="right"><b><?echo intval($f_HITS)?></b></td>
		<td align="right"><b><?echo intval($f_HITS_BACK)?></b><span class="required">*</span></td>
	</tr>
</table>
<br>
<table border="0" cellspacing="1" cellpadding="3" class="list-table">
<tr class="heading">
	<td colspan="2"><?=GetMessage("STAT_ANALYTIC_PARAMS")?></td>
</tr>
<tr>
	<td width="50%"><span title="<?=GetMessage("STAT_VISITORS_PER_DAY_ALT")?>"><?echo GetMessage("STAT_VISITORS_PER_DAY")?>:</span></td>
	<td width="50%"><?echo $f_VISITORS_PER_DAY<0 ? "-" : $f_VISITORS_PER_DAY?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_ATTENTIVENESS_ALT")?>"><?echo GetMessage("STAT_ATTENTIVENESS")?>:</span></td>
	<td>&nbsp;<?echo $f_ATTENT<0 ? "-" : $f_ATTENT?>(<?echo $f_ATTENT_BACK<0 ? "-" : $f_ATTENT_BACK?><span class="required">*</span>)</td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_ACTIVITY_ALT")?>"><?echo GetMessage("STAT_ACTIVITY")?>:</span></td>
	<td><?
		if (intval($f_GUESTS)<=0) echo "-";
		else
		{
			$res = create_event_list($lAdmin_tab1,false,true)/$f_GUESTS;
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
</table>

<?endif;
$lAdmin_tab1->EndCustomContent();
if($_REQUEST["table_id"]=="" || $_REQUEST["table_id"]==$sTableID_tab1)
	$lAdmin_tab1->CheckListMode();

if($STAT_RIGHT > "M"):

$oSort_tab2 = new CAdminSorting($sTableID_tab2);
$lAdmin_tab2 = new CAdminList($sTableID_tab2, $oSort_tab2);
$lAdmin_tab2->InitFilter(array());
$lAdmin_tab2->BeginPrologContent();
if(strlen($strError)>0):
	CAdminMessage::ShowMessage($strError);
elseif($ar==false):
	CAdminMessage::ShowMessage(GetMessage("STAT_NO_DATA_FOR_FILTER"));
elseif($site_filter=="Y" && $_REQUEST["table_id"]==$sTableID_tab2):
	CAdminMessage::ShowMessage(GetMessage("STAT_NO_DATA"));
elseif($_REQUEST["table_id"]==$sTableID_tab2):
?>
<table border="0" cellspacing="1" cellpadding="3" class="list-table">
<tr>
	<td width="50%">
	<?if($GROUP=="N"):?>
		<a href="adv_edit.php?lang=<?=LANG?>&amp;ID=<?=$f_ID?>" title="<?=GetMessage("STAT_INPUTS_ALT")?>"><?echo GetMessage("STAT_INPUTS")?>:</a>
	<?else:?>
		<span title="<?=GetMessage("STAT_INPUTS_ALT")?>"><?echo GetMessage("STAT_INPUTS")?>:</span>
	<?endif;?>
	</td>
	<td width="50%"><?echo str_replace(" ", "&nbsp;", number_format($f_COST, 2, ".", " "));?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_OUTPUTS_ALT")?>"><?echo GetMessage("STAT_OUTPUTS")?>:</span></td>
	<td><?echo str_replace(" ", "&nbsp;", number_format($f_REVENUE, 2, ".", " "));?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_BENEFIT_ALT")?>"><?echo GetMessage("STAT_BENEFIT")?>:</span></td>
	<td><?
	if ($f_BENEFIT<0) :
		?><span class="required"><?
		echo str_replace(" ", "&nbsp;", number_format($f_BENEFIT, 2, ".", " "));
		?></span><?
	else :
		?><span class="stat_pointed"><?
		echo str_replace(" ", "&nbsp;", number_format($f_BENEFIT, 2, ".", " "));
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
	echo str_replace(" ", "&nbsp;", number_format($f_ROI, 2, ".", " "));
		?></span><?
	endif;
	?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_SESSION_COST_ALT")?>"><?echo GetMessage("STAT_SESSION_COST")?>:</span></td>
	<td><?echo str_replace(" ", "&nbsp;", number_format($f_SESSION_COST, 2, ".", " "));?></td>
</tr>
<tr>
	<td><span title="<?=GetMessage("STAT_VISITOR_COST_ALT")?>"><?echo GetMessage("STAT_VISITOR_COST")?>:</span></td>
	<td><?echo str_replace(" ", "&nbsp;", number_format($f_VISITOR_COST, 2, ".", " "));?></td>
</tr>
</table>
<h2><?=GetMessage("STAT_FINANCE_EVENTS")?></h2>
<a href="event_list.php?lang=<?=LANG?>&amp;find_adv_id=<?=$f_ID?>&amp;find_adv_id_exact_match=Y&amp;find_money1=0.0001&amp;set_filter=Y"><?=GetMessage("STAT_ALL_FINANCE_EVENTS")?></a>
<br><br>
<?else:
	CAdminMessage::ShowMessage(GetMessage("STAT_NO_PERMISSIONS"));
endif;?>
<?
	$lAdmin_tab2->EndPrologContent();

if($_REQUEST["table_id"]==$sTableID_tab2)
	create_event_list($lAdmin_tab2, true);
if($_REQUEST["table_id"]==$sTableID_tab2)
	$lAdmin_tab2->CheckListMode();

endif; //$STAT_RIGHT > "M"

$oSort_tab3 = new CAdminSorting($sTableID_tab3, "s_def", "desc");
$lAdmin_tab3 = new CAdminList($sTableID_tab3, $oSort_tab3);
$lAdmin_tab3->InitFilter(array());
if(strlen($strError)>0):
	CAdminMessage::ShowMessage($strError);
elseif($site_filter=="Y" && $_REQUEST["table_id"]==$sTableID_tab3):
	CAdminMessage::ShowMessage(GetMessage("STAT_NO_DATA"));
elseif($_REQUEST["table_id"]==$sTableID_tab3):
	create_event_list($lAdmin_tab3);
endif;

if($_REQUEST["table_id"]==$sTableID_tab3)
	$lAdmin_tab3->CheckListMode();

$sTableID_tab4 = "t_visit_section_list_ENTER_COUNTER";
$lAdmin_tab4 = new CAdminList($sTableID_tab4);
$lAdmin_tab4->BeginCustomContent();
if(strlen($strError)>0):
	CAdminMessage::ShowMessage($strError);
elseif($_REQUEST["table_id"]==$sTableID_tab4):
?>
Hello
<?
endif;
$lAdmin_tab4->EndCustomContent();
if($_REQUEST["table_id"]==$sTableID_tab4)
	$lAdmin_tab4->CheckListMode();

$sTableID_tab5 = "t_visit_section_list_EXIT_COUNTER";
$lAdmin_tab5 = new CAdminList($sTableID_tab5);
$lAdmin_tab5->BeginCustomContent();
if(strlen($strError)>0):
	CAdminMessage::ShowMessage($strError);
elseif($_REQUEST["table_id"]==$sTableID_tab5):
?>
Hello
<?
endif;
$lAdmin_tab5->EndCustomContent();
if($_REQUEST["table_id"]==$sTableID_tab5)
	$lAdmin_tab5->CheckListMode();

$sTableID_tab6 = "t_visit_section_list_COUNTER";
$lAdmin_tab6 = new CAdminList($sTableID_tab6);
$lAdmin_tab6->BeginCustomContent();
if(strlen($strError)>0):
	CAdminMessage::ShowMessage($strError);
elseif($_REQUEST["table_id"]==$sTableID_tab6):
?>
Hello
<?
endif;
$lAdmin_tab6->EndCustomContent();
if($_REQUEST["table_id"]==$sTableID_tab6)
	$lAdmin_tab->CheckListMode();

$sTableID_tab7 = "t_path_list_COUNTER";
$lAdmin_tab7 = new CAdminList($sTableID_tab7);
$lAdmin_tab7->BeginCustomContent();
if(strlen($strError)>0):
	CAdminMessage::ShowMessage($strError);
elseif($_REQUEST["table_id"]==$sTableID_tab7):
?>
Hello
<?
endif;
$lAdmin_tab7->EndCustomContent();
if($_REQUEST["table_id"]==$sTableID_tab7)
	$lAdmin_tab->CheckListMode();

$sTableID_tab8 = "t_path_list_COUNTER_FULL_PATH";
$lAdmin_tab8 = new CAdminList($sTableID_tab8);
$lAdmin_tab8->BeginCustomContent();
if(strlen($strError)>0):
	CAdminMessage::ShowMessage($strError);
elseif($_REQUEST["table_id"]==$sTableID_tab8):
?>
Hello
<?
endif;
$lAdmin_tab8->EndCustomContent();
if($_REQUEST["table_id"]==$sTableID_tab8)
	$lAdmin_tab->CheckListMode();

$sTableID_tab9 = "t_adv_graph_list";
$lAdmin_tab9 = new CAdminList($sTableID_tab9);
$lAdmin_tab9->BeginCustomContent();
if(strlen($strError)>0):
	CAdminMessage::ShowMessage($strError);
elseif($_REQUEST["table_id"]==$sTableID_tab9):
?>
Hello
<?
endif;
$lAdmin_tab9->EndCustomContent();
if($_REQUEST["table_id"]==$sTableID_tab9)
	$lAdmin_tab->CheckListMode();

$aTabs = array(
	array(
		"DIV" => "tab1",
		"TAB" => GetMessage("STAT_STATISTICS"),
		"ICON"=>"",
		"TITLE"=> GetMessage("STAT_CAMPAIGN_STATISTICS"),
		"ONSELECT"=>"selectTabWithFilter(".$sFilterID.", ".$sTableID_tab1.");"
	),
	array(
		"DIV" => "tab9",
		"TAB" => GetMessage("STAT_GRAPHICS"),
		"ICON"=>"",
		"TITLE"=> GetMessage("STAT_DYNAMICS_LIST"),
		"ONSELECT"=>"selectTabWithFilter(".$sFilterID.", ".$sTableID_tab9.");"
	),
);
if($STAT_RIGHT > "M")
{
	$aTabs[] = array(
		"DIV" => "tab2",
		"TAB" => GetMessage("STAT_FINANCES")." (ROI)",
		"ICON"=>"",
		"TITLE"=>GetMessage("STAT_FINANCES")." (ROI)",
		"ONSELECT"=>"selectTabWithFilter(".$sFilterID.", ".$sTableID_tab2.");"
	);
}
$aTabs[] = array(
	"DIV" => "tab3",
	"TAB" => GetMessage("STAT_EVENTS"),
	"ICON"=>"",
	"TITLE"=> GetMessage("STAT_EVENTS").' (Top '.COption::GetOptionInt("statistic","ADV_DETAIL_TOP_SIZE").')',
	"ONSELECT"=>"selectTabWithFilter(".$sFilterID.", ".$sTableID_tab3.");"
);
$aTabs[] = array(
	"DIV" => "tab4",
	"TAB" => GetMessage("STAT_ENTERS"),
	"ICON"=>"",
	"TITLE"=> GetMessage("STAT_ENTERS").' (Top '.COption::GetOptionInt("statistic","ADV_DETAIL_TOP_SIZE").')',
	"ONSELECT"=>"selectTabWithFilter(".$sFilterID.", ".$sTableID_tab4.");"
);
$aTabs[] = array(
	"DIV" => "tab5",
	"TAB" => GetMessage("STAT_EXITS"),
	"ICON"=>"",
	"TITLE"=> GetMessage("STAT_EXITS").' (Top '.COption::GetOptionInt("statistic","ADV_DETAIL_TOP_SIZE").')',
	"ONSELECT"=>"selectTabWithFilter(".$sFilterID.", ".$sTableID_tab5.");"
);
$aTabs[] = array(
	"DIV" => "tab6",
	"TAB" => GetMessage("STAT_VISITS"),
	"ICON"=>"",
	"TITLE"=> GetMessage("STAT_RECORDS_LIST").' (Top '.COption::GetOptionInt("statistic","ADV_DETAIL_TOP_SIZE").')',
	"ONSELECT"=>"selectTabWithFilter(".$sFilterID.", ".$sTableID_tab6.");"
);
$aTabs[] = array(
	"DIV" => "tab7",
	"TAB" => GetMessage("STAT_SEGMENT_PATH"),
	"ICON"=>"",
	"TITLE"=> GetMessage("STAT_SEGMENT_PATH_LIST").' (Top '.COption::GetOptionInt("statistic","ADV_DETAIL_TOP_SIZE").')',
	"ONSELECT"=>"selectTabWithFilter(".$sFilterID.", ".$sTableID_tab7.");"
);
$aTabs[] = array(
	"DIV" => "tab8",
	"TAB" => GetMessage("STAT_PATH_LIST"),
	"ICON"=>"",
	"TITLE"=> GetMessage("STAT_FULL_PATH_LIST").' (Top '.COption::GetOptionInt("statistic","ADV_DETAIL_TOP_SIZE").')',
	"ONSELECT"=>"selectTabWithFilter(".$sFilterID.", ".$sTableID_tab8.");"
);

$tabControl = new CAdminViewTabControl("tabControl", $aTabs);

$lAdmin->BeginCustomContent();
?>
<p><?($find_type=="id"?GetMessage("STAT_GROUP"):GetMessage("STAT_GROUPED").$find_type)?></p>
<?
$lAdmin->EndCustomContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_ADV_CAMPAIGN_TITLE")." ".$title);
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter($sFilterID, array(
	GetMessage("STAT_F_PERIOD"),
	));
?>

<script type="text/javascript">
var currentTable = null;
var cached = new Array('<?=$sTableID_tab1?>');
var urls = [];
urls['<?=$sTableID_tab1?>']='adv_detail.php?lang=<?echo LANGUAGE_ID?>';
urls['<?=$sTableID_tab2?>']='adv_detail.php?lang=<?echo LANGUAGE_ID?>';
urls['<?=$sTableID_tab3?>']='adv_detail.php?lang=<?echo LANGUAGE_ID?>';
urls['<?=$sTableID_tab4?>']='visit_section_list.php?lang=<?echo LANGUAGE_ID?>';
urls['<?=$sTableID_tab5?>']='visit_section_list.php?lang=<?echo LANGUAGE_ID?>';
urls['<?=$sTableID_tab6?>']='visit_section_list.php?lang=<?echo LANGUAGE_ID?>';
urls['<?=$sTableID_tab7?>']='path_list.php?lang=<?echo LANGUAGE_ID?>';
urls['<?=$sTableID_tab8?>']='path_list.php?lang=<?echo LANGUAGE_ID?>';
urls['<?=$sTableID_tab9?>']='adv_graph_list.php?lang=<?echo LANGUAGE_ID?>';

function selectTabWithFilter(filter, table, force)
{
	var resultDiv = document.getElementById(table.table_id+"_result_div");
	var url = urls[table.table_id];
	if(resultDiv)
	{
		if(force || !cached[table.table_id])
		{
			var params = filter.GetParameters();
			if(
				table.table_id.indexOf('t_visit_section_list_')==0
				|| table.table_id.indexOf('t_path_list_')==0
			)
			{
				if(params.indexOf('&find_type=id')>=0)
				{
					params = params.replace(/&find_type=id/,'');
					params = params.replace(/&find=/,'&find_id=');
				}
				if(params.indexOf('&find_type=referer1')>=0)
				{
					params = params.replace(/&find_type=referer1/,'');
					params = params.replace(/&find=/,'&find_referer1=');
				}
				if(params.indexOf('&find_type=referer2')>=0)
				{
					params = params.replace(/&find_type=referer2/,'');
					params = params.replace(/&find=/,'&find_referer2=');
				}
				params = params.replace(/&find_id=/,'&find_adv[]=');
				params = params.replace(/&find_date1_period=/,'&find_date1=');
				params = params.replace(/&find_date2_period=/,'&find_date2=');
				params+= '&find_adv_data_type=S';
				if(table.table_id=='t_visit_section_list_ENTER_COUNTER')
					params+= '&find_diagram_type=ENTER_COUNTER';
				if(table.table_id=='t_visit_section_list_EXIT_COUNTER')
					params+= '&find_diagram_type=EXIT_COUNTER';
				if(table.table_id=='t_visit_section_list_COUNTER')
					params+= '&find_diagram_type=COUNTER';
				if(table.table_id=='t_path_list_COUNTER_FULL_PATH')
					params+= '&find_diagram_type=COUNTER_FULL_PATH';
				if(table.table_id=='t_path_list_COUNTER')
					params+= '&find_diagram_type=COUNTER';
				params+= '&by=s_counter';
				params+= '&order=desc';
				//PAGING
				params+= '&PAGEN_1=1';
				params+= '&SIZEN_1=<?=COption::GetOptionInt('statistic','ADV_DETAIL_TOP_SIZE')?>';
				params+= '&SHOWALL_1=0';
				params+= '&context=tab';

			}
			if(table.table_id.indexOf('t_adv_graph_list')==0)
			{
				if(params.indexOf('&find_type=id')>=0)
				{
					params = params.replace(/&find_type=id/,'');
					params = params.replace(/&find=/,'&find_id=');
				}
				params = params.replace(/&find_id=/,'&ADV_ID=');
				params = params.replace(/&find_date1_period=/,'&find_date1=');
				params = params.replace(/&find_date2_period=/,'&find_date2=');
				params+= '&by=s_date';
				params+= '&order=desc';
				params+= '&context=tab';

			}
			if(table.table_id.indexOf('t_visit_section_list')==0)
			{
				params = params.replace(/&find_date1_period_/,'&find_date1_');
				params = params.replace(/&find_date2_period_/,'&find_date2_');
			}
			if(url.indexOf('?')>=0)
				url += '&set_filter=Y'+params;
			else
				url += '?set_filter=Y'+params;
			//alert(url);
			resultDiv.innerHTML='<br><?=addslashes(GetMessage("STAT_WAIT_DATA_LOADING"))?><br>';
			table.GetAdminList(url);
			cached[table.table_id]=true;
		}
		currentTable = table;
	}
}
function applyFilter(filter)
{
	cached=[];
	tabControl.SelectTab('tab1');
	selectTabWithFilter(filter, t_adv_detail_tab1);
}
function clearFilter(filter)
{
	filter.ClearParameters();
	filter.SetActive(false);
	applyFilter(filter);
}
</script>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage("STAT_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("STAT_FIND_TITLE")?>">
		<?
		$arr = array(
			"reference" => array("ID","referer1","referer2"),
			"reference_id" => array("id","referer1","referer2"),
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr valign="center">
	<td align="right" width="0%" nowrap><?echo GetMessage("STAT_F_DATE")." (".FORMAT_DATE."):"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1_period", $find_date1_period, "find_date2_period", $find_date2_period, "form1","Y")?></td>
</tr>
<?$oFilter->Buttons()?>
<input type="submit" name="set_filter" value="<?=GetMessage("STAT_F_FIND")?>" title="<?=GetMessage("STAT_F_FIND_TITLE")?>" onClick="applyFilter(<?=$sFilterID?>); return false;">
<input type="submit" name="del_filter" value="<?=GetMessage("STAT_F_CLEAR")?>" title="<?=GetMessage("STAT_F_CLEAR_TITLE")?>" onClick="clearFilter(<?=$sFilterID?>); return false;">
<?
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>
<div class="adm-detail-content-wrap">
	<div class="adm-detail-content">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
$lAdmin_tab1->DisplayList();
?>
<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("STAT_ADV_BACK_ALT")?>
<?echo EndNote();?>

<?$tabControl->BeginNextTab();
$lAdmin_tab9->DisplayList();?>

<?if($STAT_RIGHT > "M"):?>
<?$tabControl->BeginNextTab();
$lAdmin_tab2->DisplayList();
?>
<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("STAT_ADV_BACK_ALT")?>
<?echo EndNote();?>
<?endif;?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/event_type_list.php?lang=<?=LANG?>"><?=GetMessage("STAT_ALL_EVENTS")?></a><br><br>
<?$lAdmin_tab3->DisplayList();?>
<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("STAT_ADV_BACK_ALT")?>
<?echo EndNote();?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/visit_section_list.php?lang=<?=LANG?>&amp;set_default=Y&amp;find_diagram_type=ENTER_COUNTER&amp;SIZEN_1=20"><?=GetMessage("STAT_ALL_ENTERS")?></a><br>
<?$lAdmin_tab4->DisplayList();?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/visit_section_list.php?lang=<?=LANG?>&amp;set_default=Y&amp;find_diagram_type=EXIT_COUNTER&amp;SIZEN_1=20"><?=GetMessage("STAT_ALL_EXITS")?></a><br>
<?$lAdmin_tab5->DisplayList();?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/visit_section_list.php?lang=<?=LANG?>&amp;set_default=Y&amp;find_diagram_type=COUNTER&amp;SIZEN_1=20"><?=GetMessage("STAT_ALL_RECORDS_LIST")?></a><br>
<?$lAdmin_tab6->DisplayList();?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/path_list.php?lang=<?=LANG?>&amp;set_default=Y&amp;find_diagram_type=COUNTER&amp;SIZEN_1=20"><?=GetMessage("STAT_ALL_SEGMENT_PATH")?></a><br>
<?$lAdmin_tab7->DisplayList();?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/path_list.php?lang=<?=LANG?>&amp;set_default=Y&amp;find_diagram_type=COUNTER_FULL_PATH&amp;SIZEN_1=20"><?=GetMessage("STAT_ALL_FULL_PATH")?></a><br>
<?$lAdmin_tab8->DisplayList();?>

<?$tabControl->End();?>
	</div>
	<br />
</div>
<?
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
