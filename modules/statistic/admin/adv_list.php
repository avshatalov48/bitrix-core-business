<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(isset($group_by))
{
	if($group_by!="referer1" && $group_by!="referer2")
		$group_by="";
}
else
	$group_by=false;//no setting (will be read later from session)

$base_currency = GetStatisticBaseCurrency();
if ($base_currency <> '')
{
	if (CModule::IncludeModule("currency"))
	{
		$currency_module = "Y";
		$base_currency = GetStatisticBaseCurrency();
		$view_currency = ($find_currency <> '' && $find_currency!="NOT_REF") ? $find_currency : $base_currency;
		$arrCurrency = array();
		$rsCur = CCurrency::GetList("sort", "asc");
		$arrRefID = array();
		$arrRef = array();
		while ($arCur = $rsCur->Fetch())
		{
				$arrRef[] = $arCur["CURRENCY"]." (".$arCur["FULL_NAME"].")";
				$arrRefID[] = $arCur["CURRENCY"];
		}
		$arrCurrency = array("REFERENCE" => $arrRef, "REFERENCE_ID" => $arrRefID);
	}
}

$sTableID = "tbl_adv_list";
$statDB = CDatabase::GetModuleConnection('statistic');
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arrExactMatch = array(
	"ID_EXACT_MATCH"	=> "find_id_exact_match",
	"REFERER1_EXACT_MATCH"	=> "find_referer12_exact_match",
	"REFERER2_EXACT_MATCH"	=> "find_referer12_exact_match"
	);
$FilterArr = Array(
	"find",
	"find_type",
	"find_id",
	"find_date1_period",
	"find_date2_period",
	"find_referer1",
	"find_referer2",
	"find_guests1",
	"find_guests2",
	"find_guests_back",
	"find_sessions1",
	"find_sessions2",
	"find_sessions_back",
	"find_cost1",
	"find_cost2",
	"find_revenue1",
	"find_revenue2",
	"find_benefit1",
	"find_benefit2",
	"find_roi1",
	"find_roi2",
	"find_currency",
	"find_attent1",
	"find_attent2",
	"find_activite1",
	"find_activite2",
	"find_visitors_per_day1",
	"find_visitors_per_day2",
	"find_duration1",
	"find_duration2",
);
$FilterArr = array_merge($FilterArr, array_values($arrExactMatch));

$lAdmin->InitFilter($FilterArr);

//Restore & Save settings (windows registry like)
$arSettings = array ("saved_group_by");
InitFilterEx($arSettings, $sTableID."_settings", "get");
if($group_by===false)//Restore saved setting
	$group_by=$saved_group_by;
elseif($saved_group_by!=$group_by)//Set if changed
	$saved_group_by=$group_by;
InitFilterEx($arSettings, $sTableID."_settings", "set");

AdminListCheckDate($lAdmin, array("find_date1_period"=>$find_date1_period, "find_date2_period"=>$find_date2_period));

$arFilter = Array(
	"ID"			=> $find!="" && $find_type=="id"?$find:$find_id,
	"DATE1_PERIOD"		=> $find_date1_period,
	"DATE2_PERIOD"		=> $find_date2_period,
	"REFERER1"		=> $find!="" && $find_type=="referer1"?$find:$find_referer1,
	"REFERER2"		=> $find!="" && $find_type=="referer2"?$find:$find_referer2,
	"GUESTS1"		=> $find_guests1,
	"GUESTS2"		=> $find_guests2,
	"GUESTS_BACK"		=> $find_guests_back,
	"SESSIONS1"		=> $find_sessions1,
	"SESSIONS2"		=> $find_sessions2,
	"SESSIONS_BACK"		=> $find_sessions_back,
	"COST1"			=> (($STAT_RIGHT>"M") ? $find_cost1 : ""),
	"COST2"			=> (($STAT_RIGHT>"M") ? $find_cost2 : ""),
	"REVENUE1"		=> (($STAT_RIGHT>"M") ? $find_revenue1 : ""),
	"REVENUE2"		=> (($STAT_RIGHT>"M") ? $find_revenue2 : ""),
	"BENEFIT1"		=> (($STAT_RIGHT>"M") ? $find_benefit1 : ""),
	"BENEFIT2"		=> (($STAT_RIGHT>"M") ? $find_benefit2 : ""),
	"ROI1"			=> (($STAT_RIGHT>"M") ? $find_roi1 : ""),
	"ROI2"			=> (($STAT_RIGHT>"M") ? $find_roi2 : ""),
	"CURRENCY"		=> $find_currency,
	"ATTENT1"		=> $find_attent1,
	"ATTENT2"		=> $find_attent2,
	"ATTENT_BACK"		=> $find_attent_back,
	"VISITORS_PER_DAY1"	=> $find_visitors_per_day1,
	"VISITORS_PER_DAY2"	=> $find_visitors_per_day2,
	"DURATION1"		=> $find_duration1,
	"DURATION2"		=> $find_duration2,
	"GROUP"			=> $group_by,
);
$arFilter = array_merge($arFilter, array_convert_name_2_value($arrExactMatch));

if(($arID = $lAdmin->GroupAction()) && $STAT_RIGHT>="W")
{
	if($_REQUEST['action_target'] == "selected")
	{
		$cData = new CAdv;
		$rsData = $cData->GetList('', '', $arFilter, $is_filtered2);
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
			if(!CAdv::Delete($ID))
			{
				$statDB->Rollback();
				$lAdmin->AddGroupError(GetMessage("STAT_DELETE_ERROR"), $ID);
			}
			else
			{
				$statDB->Commit();
			}
			break;
		case "clear":
			@set_time_limit(0);
			$statDB->StartTransaction();
			if(!CAdv::Reset($ID))
			{
				$statDB->Rollback();
				$lAdmin->AddGroupError(GetMessage("STAT_DELETE_ERROR"), $ID);
			}
			else
			{
				$statDB->Commit();
			}
			break;
		}
	}
}

global $by, $order;

$cData = new CAdv;
$rsData = $cData->GetList($by, $order, $arFilter, $is_filtered, "", $arrGROUP_DAYS);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_ADV_PAGES")));

$arHeaders = array();
if($group_by=="")
	$arHeaders[] =
		array(	"id"		=>"ID",
			"content"	=>"ID",
			"sort"		=>"ID",
			"align"		=>"right",
			"default"	=>true,
		);
if($group_by=="" || $group_by=="referer1")
	$arHeaders[]=
		array(	"id"		=>"REFERER1",
			"content"	=>"referer1",
			"sort"		=>"REFERER1",
			"default"	=>true,
		);
if($group_by=="" || $group_by=="referer2")
	$arHeaders[]=
		array(	"id"		=>"REFERER2",
			"content"	=>"referer2",
			"sort"		=>"REFERER2",
			"default"	=>true,
		);
$arHeaders[]=
	array(	"id"		=>"DATE_FIRST",
		"content"	=>GetMessage("STAT_BEGIN"),
		"sort"		=>"C_TIME_FIRST",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"DATE_LAST",
		"content"	=>GetMessage("STAT_END"),
		"sort"		=>"C_TIME_LAST",
		"default"	=>true,
	);
if($group_by=="")
	$arHeaders[]=
		array(	"id"		=>"PRIORITY",
			"content"	=>GetMessage("STAT_PRIORITY"),
			"sort"		=>"PRIORITY",
			"align"		=>"right",
			"default"	=>true,
		);
$arHeaders[]=
	array(	"id"		=>"SESSIONS_TODAY",
		"content"	=>GetMessage("STAT_SESSIONS_TODAY"),
		"sort"		=>$group_by==""?"SESSIONS_TODAY":false,
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"SESSIONS_BACK_TODAY",
		"content"	=>GetMessage("STAT_SESSIONS_BACK_TODAY"),
		"sort"		=>$group_by==""?"SESSIONS_BACK_TODAY":false,
		"align"		=>"right",
		"default"	=>false,
	);
$arHeaders[]=
	array(	"id"		=>"SESSIONS_YESTERDAY",
		"content"	=>GetMessage("STAT_SESSIONS_YESTERDAY"),
		"sort"		=>$group_by==""?"SESSIONS_YESTERDAY":false,
		"align"		=>"right",
		"default"	=>false,
	);
$arHeaders[]=
	array(	"id"		=>"SESSIONS_BACK_YESTERDAY",
		"content"	=>GetMessage("STAT_SESSIONS_BACK_YESTERDAY"),
		"sort"		=>$group_by==""?"SESSIONS_BACK_YESTERDAY":false,
		"align"		=>"right",
		"default"	=>false,
	);
if(($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):
	$arHeaders[]=
		array(	"id"		=>"SESSIONS_PERIOD",
			"content"	=>GetMessage("STAT_SESSIONS_PERIOD"),
			"sort"		=>$group_by==""?"SESSIONS_PERIOD":false,
			"align"		=>"right",
			"default"	=>true,
		);
	$arHeaders[]=
		array(	"id"		=>"SESSIONS_BACK_PERIOD",
			"content"	=>GetMessage("STAT_SESSIONS_BACK_PERIOD"),
			"sort"		=>$group_by==""?"SESSIONS_BACK_PERIOD":false,
			"align"		=>"right",
			"default"	=>true,
		);
endif;
$arHeaders[]=
	array(	"id"		=>"SESSIONS",
		"content"	=>GetMessage("STAT_SESSIONS_TOTAL"),
		"sort"		=>"SESSIONS",
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"SESSIONS_BACK",
		"content"	=>GetMessage("STAT_SESSIONS_BACK_TOTAL"),
		"sort"		=>"SESSIONS_BACK",
		"align"		=>"right",
		"default"	=>false,
	);
$arHeaders[]=
	array(	"id"		=>"EVENTS_TODAY",
		"content"	=>GetMessage("STAT_EVENTS_TOTAL_TODAY"),
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"EVENTS_TOTAL",
		"content"	=>GetMessage("STAT_EVENTS_TOTAL"),
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"VISITORS_PER_DAY",
		"content"	=>GetMessage("STAT_VISITORS_PER_DAY"),
		"sort"		=>"VISITORS_PER_DAY",
		"align"		=>"right",
		"default"	=>false,
	);
$arHeaders[]=
	array(	"id"		=>"ATTENT",
		"content"	=>GetMessage("STAT_ATTENTIVENESS"),
		"sort"		=>"ATTENT",
		"align"		=>"right",
		"default"	=>false,
	);
$arHeaders[]=
	array(	"id"		=>"ATTENT_BACK",
		"content"	=>GetMessage("STAT_ATTENTIVENESS_BACK"),
		"sort"		=>"ATTENT_BACK",
		"align"		=>"right",
		"default"	=>false,
	);
$arHeaders[]=
	array(	"id"		=>"ACTIVITY",
		"content"	=>GetMessage("STAT_ACTIVITY"),
		"align"		=>"right",
		"default"	=>false,
	);
$arHeaders[]=
	array(	"id"		=>"NEW_VISITORS",
		"content"	=>GetMessage("STAT_NEW_VISITORS"),
		"sort"		=>"NEW_VISITORS",
		"align"		=>"right",
		"default"	=>false,
	);
$arHeaders[]=
	array(	"id"		=>"RETURNED_VISITORS",
		"content"	=>GetMessage("STAT_RETURNED_VISITORS"),
		"sort"		=>"RETURNED_VISITORS",
		"align"		=>"right",
		"default"	=>false,
	);
if($STAT_RIGHT>"M"):
	$arHeaders[]=
		array(	"id"		=>"COST",
			"content"	=>GetMessage("STAT_INPUTS"),
			"sort"		=>"COST",
			"align"		=>"right",
			"default"	=>false,
		);
	$arHeaders[]=
		array(	"id"		=>"REVENUE",
			"content"	=>GetMessage("STAT_OUTPUTS"),
			"sort"		=>"REVENUE",
			"align"		=>"right",
			"default"	=>false,
		);
	$arHeaders[]=
		array(	"id"		=>"BENEFIT",
			"content"	=>GetMessage("STAT_BENEFIT"),
			"sort"		=>"BENEFIT",
			"align"		=>"right",
			"default"	=>false,
		);
	$arHeaders[]=
		array(	"id"		=>"ROI",
			"content"	=>GetMessage("STAT_ROI")." (%)",
			"sort"		=>"ROI",
			"align"		=>"right",
			"default"	=>false,
		);
	$arHeaders[]=
		array(	"id"		=>"SESSION_COST",
			"content"	=>GetMessage("STAT_SESSION_COST"),
			"sort"		=>"SESSION_COST",
			"align"		=>"right",
			"default"	=>false,
		);
	$arHeaders[]=
		array(	"id"		=>"VISITOR_COST",
			"content"	=>GetMessage("STAT_VISITOR_COST"),
			"sort"		=>"VISITOR_COST",
			"align"		=>"right",
			"default"	=>false,
		);
endif;

$lAdmin->AddHeaders($arHeaders);

//Helper function
function resolveValue($field_name)
{
	global $group_by,${"f_".$field_name},${"f_".mb_strtoupper($group_by)},$arrGROUP_DAYS;
	if($group_by=="")
		$value=intval(${"f_".$field_name});
	else
		$value=intval($arrGROUP_DAYS[${"f_".mb_strtoupper($group_by)}][$field_name]);
	return $value;
}

$now_date = GetTime(time());
$yesterday_date = GetTime(time()-86400);
$bef_yesterday_date = GetTime(time()-172800);
$thousand_sep = ($_REQUEST["mode"] == "excel")? "": "&nbsp;";

while($arRes = $rsData->NavNext(true, "f_")):

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$f_SESSIONS_TODAY=resolveValue("SESSIONS_TODAY");
	if(intval($f_SESSIONS_TODAY)>0):
		$strHTML = '<a title="'.GetMessage("STAT_SESSIONS_LIST").'" href="session_list.php?lang='.LANG.'&amp;find_date1='.urlencode($now_date).'&amp;find_date2='.urlencode($now_date).'&amp;';
		if ($group_by=="referer1") :
			$strHTML.='find_referer1='.urlencode("\"".$f_REFERER1."\"");
		elseif ($group_by=="referer2") :
			$strHTML.='find_referer2='.urlencode("\"".$f_REFERER2."\"");
		else :
			$strHTML.='find_adv_id='.$f_ID.'&amp;find_adv_id_exact_match=Y';
		endif;
		$strHTML.='&amp;find_adv_back=N&amp;set_filter=Y">'.intval($f_SESSIONS_TODAY).'</a>';
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("SESSIONS_TODAY", $strHTML);

	$f_SESSIONS_BACK_TODAY=resolveValue("SESSIONS_BACK_TODAY");
	if(intval($f_SESSIONS_BACK_TODAY)>0):
		$strHTML = '<a title="'.GetMessage("STAT_SESSIONS_LIST").'" href="session_list.php?lang='.LANG.'&amp;find_date1='.urlencode($now_date).'&amp;find_date2='.urlencode($now_date).'&amp;';
		if ($group_by=="referer1") :
			$strHTML.='find_referer1='.urlencode("\"".$f_REFERER1."\"");
		elseif ($group_by=="referer2") :
			$strHTML.='find_referer2='.urlencode("\"".$f_REFERER2."\"");
		else :
			$strHTML.='find_adv_id='.$f_ID.'&amp;find_adv_id_exact_match=Y';
		endif;
		$strHTML.='&amp;find_adv_back=Y&amp;set_filter=Y">'.intval($f_SESSIONS_BACK_TODAY).'</a>';
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("SESSIONS_BACK_TODAY", $strHTML);

	if(intval($f_SESSIONS)>0):
		$strHTML = '<a title="'.GetMessage("STAT_SESSIONS_LIST").'" href="session_list.php?lang='.LANG.'&amp;';
		if ($group_by=="referer1") :
			$strHTML.='find_referer1='.urlencode("\"".$f_REFERER1."\"");
		elseif ($group_by=="referer2") :
			$strHTML.='find_referer2='.urlencode("\"".$f_REFERER2."\"");
		else :
			$strHTML.='find_adv_id='.$f_ID.'&amp;find_adv_id_exact_match=Y';
		endif;
		$strHTML.='&amp;find_adv_back=N&amp;set_filter=Y">'.intval($f_SESSIONS).'</a>';
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("SESSIONS", $strHTML);

	if(intval($f_SESSIONS_BACK)>0):
		$strHTML = '<a title="'.GetMessage("STAT_SESSIONS_LIST").'" href="session_list.php?lang='.LANG.'&amp;';
		if ($group_by=="referer1") :
			$strHTML.='find_referer1='.urlencode("\"".$f_REFERER1."\"");
		elseif ($group_by=="referer2") :
			$strHTML.='find_referer2='.urlencode("\"".$f_REFERER2."\"");
		else :
			$strHTML.='find_adv_id='.$f_ID.'&amp;find_adv_id_exact_match=Y';
		endif;
		$strHTML.='&amp;find_adv_back=Y&amp;set_filter=Y">'.intval($f_SESSIONS_BACK).'</a>';
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("SESSIONS_BACK", $strHTML);

	if(($find_date1_period <> '' || $find_date2_period <> '') && $is_filtered):
		$f_SESSIONS_PERIOD=resolveValue("SESSIONS_PERIOD");
		if(intval($f_SESSIONS_PERIOD)>0):
			$strHTML = '<a title="'.GetMessage("STAT_SESSIONS_LIST").'" href="session_list.php?lang='.LANG.'&amp;find_date1='.urlencode($find_date1_period).'&amp;find_date2='.urlencode($find_date2_period).'&amp;';
			if ($group_by=="referer1") :
				$strHTML.='find_referer1='.urlencode("\"".$f_REFERER1."\"");
			elseif ($group_by=="referer2") :
				$strHTML.='find_referer2='.urlencode("\"".$f_REFERER2."\"");
			else :
				$strHTML.='find_adv_id='.$f_ID.'&amp;find_adv_id_exact_match=Y';
			endif;
			$strHTML.='&amp;find_adv_back=N&amp;set_filter=Y">'.intval($f_SESSIONS_PERIOD).'</a>';
		else:
			$strHTML='&nbsp;';
		endif;
		$row->AddViewField("SESSIONS_PERIOD", $strHTML);

		$f_SESSIONS_BACK_PERIOD=resolveValue("SESSIONS_BACK_PERIOD");
		if(intval($f_SESSIONS_BACK_PERIOD)>0):
			$strHTML = '<a title="'.GetMessage("STAT_SESSIONS_LIST").'" href="session_list.php?lang='.LANG.'&amp;find_date1='.urlencode($find_date1_period).'&amp;find_date2='.urlencode($find_date2_period).'&amp;';
			if ($group_by=="referer1") :
				$strHTML.='find_referer1='.urlencode("\"".$f_REFERER1."\"");
			elseif ($group_by=="referer2") :
				$strHTML.='find_referer2='.urlencode("\"".$f_REFERER2."\"");
			else :
				$strHTML.='find_adv_id='.$f_ID.'&amp;find_adv_id_exact_match=Y';
			endif;
			$strHTML.='&amp;find_adv_back=Y&amp;set_filter=Y">'.intval($f_SESSIONS_BACK_PERIOD).'</a>';
		else:
			$strHTML='&nbsp;';
		endif;
		$row->AddViewField("SESSIONS_BACK_PERIOD", $strHTML);
	endif;
	//$row->AddViewField("SESSIONS_PERIOD", getHTML("SESSIONS_PERIOD"));
	//$row->AddViewField("SESSIONS_BACK_PERIOD", getHTML("SESSIONS_BACK_PERIOD"));

	$show_events = ($f_EVENTS_VIEW == '') ? COption::GetOptionString("statistic", "ADV_EVENTS_DEFAULT") : $f_EVENTS_VIEW;
	$group_events = ($show_events=="event1" || $show_events=="event2") ? $show_events : "";
	$arF = array();
	$arF["DATE1_PERIOD"] = $arFilter["DATE1_PERIOD"];
	$arF["DATE2_PERIOD"] = $arFilter["DATE2_PERIOD"];
	if ($show_events=="event1") $arF["GROUP"] = "event1";
	elseif($show_events=="event2") $arF["GROUP"] = "event2";
	if ($group_by=="")
	{
		$events = CAdv::GetEventList($f_ID, "s_def", "desc", $arF);
	}
	else
	{
		$value = ($group_by=="referer1") ? $f_REFERER1 : $f_REFERER2;
		$events = CAdv::GetEventListByReferer($value, $arFilter);
	}
	$sum_today = 0;
	$sum_back_today = 0;
	$sum_total = 0;
	$sum_back_total = 0;
	while ($er = $events->Fetch())
	{
		$sum_today += intval($er["COUNTER_TODAY"]);
		$sum_back_today += intval($er["COUNTER_BACK_TODAY"]);
		$sum_total += intval($er["COUNTER"]);
		$sum_back_total += intval($er["COUNTER_BACK"]);
	}
	$strHTML=$sum_today>0||$sum_back_today>0?$sum_today+$sum_back_today:"&nbsp;";
	$row->AddViewField("EVENTS_TODAY", $strHTML);
	$strHTML=$sum_total>0||$sum_back_total>0?$sum_total+$sum_back_total:"&nbsp;";
	$row->AddViewField("EVENTS_TOTAL", $strHTML);

	$row->AddViewField("VISITORS_PER_DAY", $f_VISITORS_PER_DAY<0?"-":$f_VISITORS_PER_DAY);
	$row->AddViewField("ATTENT", $f_ATTENT<0?"-":$f_ATTENT);
	$row->AddViewField("ATTENT_BACK", $f_ATTENT_BACK<0?"-":$f_ATTENT_BACK);
	if (intval($f_GUESTS)<=0)
		$row->AddViewField("ACTIVITY", "-");
	else
	{
		$res = ($sum_total+$sum_back_total)/$f_GUESTS;
		$res_round = round($res,2);
		if ($res>0 && $res_round<=0)
			$row->AddViewField("ACTIVITY", "&#x2248;");
		else
			$row->AddViewField("ACTIVITY", $res_round);
	}
	$row->AddViewField("NEW_VISITORS", $f_NEW_VISITORS<0?"-":$f_NEW_VISITORS."%");
	$row->AddViewField("RETURNED_VISITORS", $f_RETURNED_VISITORS<0?"-":$f_RETURNED_VISITORS."%");

	if($STAT_RIGHT>"M"):
		$row->AddViewField("COST", str_replace(" ", $thousand_sep, number_format($f_COST, 2, ".", " ")));
		$row->AddViewField("REVENUE", str_replace(" ", $thousand_sep, number_format($f_REVENUE, 2, ".", " ")));
		$row->AddViewField("BENEFIT", str_replace(" ", $thousand_sep, number_format($f_BENEFIT, 2, ".", " ")));
		$row->AddViewField("ROI", $f_ROI<0?"-":str_replace(" ", $thousand_sep, number_format($f_ROI, 2, ".", " ")));
		$row->AddViewField("SESSION_COST", str_replace(" ", $thousand_sep, number_format($f_SESSION_COST, 2, ".", " ")));
		$row->AddViewField("VISITOR_COST", str_replace(" ", $thousand_sep, number_format($f_VISITOR_COST, 2, ".", " ")));
	endif;

	$arActions = Array();

//	$arActions[] = array(
//		"ICON"=>"view",
//		"DEFAULT"=>true,
//		"TEXT"=>GetMessage("STAT_DETAIL_VIEW"),
//		"ACTION"=>"jsUtils.OpenWindow('adv_list_popup.php?lang=".LANG."&ID=".$f_ID."&find_date1_period=".urlencode($find_date1_period)."&find_date2_period=".urlencode($find_date2_period)."&find_referer1=".urlencode($find_referer1)."&find_referer2=".urlencode($find_referer2)."&find_group=".urlencode($group_by)."&set_filter=Y', 600, 600);",
//	);
	$arActions[] = array(
		"ICON"=>"view",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("STAT_DETAIL_VIEW"),
		"ACTION"=>$lAdmin->ActionRedirect("adv_detail.php?lang=".LANG."&find_type=".urlencode($group_by)."&find=".($group_by==""?$f_ID:($group_by=="referer1"?$f_REFERER1:$f_REFERER2))."&find_date1_period=".urlencode($find_date1_period)."&find_date2_period=".urlencode($find_date2_period)."&set_filter=Y"),
	);

	if($group_by=="")
	{
		if($STAT_RIGHT>="W")
			$arActions[] = array(
				"ICON"=>"edit",
				"TEXT"=>GetMessage("STAT_EDIT"),
				"TITLE"=>GetMessage("STAT_EDIT_ADV"),
				"ACTION"=>$lAdmin->ActionRedirect("adv_edit.php?ID=".$f_ID)
			);
		if($STAT_RIGHT>="W")
			$arActions[] = array(
				"ICON"=>"clear",
				"TEXT"=>GetMessage("STAT_RESET"),
				"TITLE"=>GetMessage("STAT_RESET_ADV"),
				"ACTION"=>"if(confirm('".GetMessageJS('STAT_RESET_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "clear")
			);
		if($STAT_RIGHT>="W")
			$arActions[] = array(
				"ICON"=>"delete",
				"TEXT"=>GetMessage("STAT_DELETE"),
				"TITLE"=>GetMessage("STAT_DELETE_ADV"),
				"ACTION"=>"if(confirm('".GetMessageJS('STAT_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
			);

		$arActions[] = array("SEPARATOR"=>true);

		if(intval($f_HITS)>0)
		{
			$arActions[] = array(
				"ICON"=>"",
				"TEXT"=>GetMessage("STAT_SECTIONS"),
				"TITLE"=>GetMessage("STAT_SECTIONS_ALT"),
				"ACTION"=>$lAdmin->ActionRedirect("visit_section_list.php?find_adv[]=".$f_ID."&set_filter=Y")
			);
			$arActions[] = array(
				"ICON"=>"",
				"TEXT"=>GetMessage("STAT_SITE_PATH"),
				"TITLE"=>GetMessage("STAT_SITE_PATH_ALT"),
				"ACTION"=>$lAdmin->ActionRedirect("path_list.php?find_adv[]=".$f_ID."&set_filter=Y")
			);
			$dynamic_days = CAdv::DynamicDays($f_ID);
			if ($dynamic_days>=1)
				$arActions[] = array(
					"ICON"=>"",
					"TEXT"=>GetMessage("STAT_DYNAMICS"),
					"TITLE"=>GetMessage("STAT_DYNAMICS_ADV"),
					"ACTION"=>$lAdmin->ActionRedirect("adv_dynamic_list.php?find_adv_id=".$f_ID."&find_event_id_exact_match=Y&set_default	=Y")
				);
//			if ($dynamic_days>=2 && function_exists("ImageCreate"))
				$arActions[] = array(
					"ICON"=>"",
					"TEXT"=>GetMessage("STAT_GRAPH"),
					"TITLE"=>GetMessage("STAT_GRAPH_ADV"),
					"ACTION"=>$lAdmin->ActionRedirect("adv_graph_list.php?ADV_ID=".$f_ID."&set_default=Y")
				);
		}

	}

	if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
		unset($arActions[count($arActions)-1]);
	$row->AddActions($arActions);
endwhile;

$arFooter = array();
$arFooter[] = array(
	"title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"),
	"value"=>$rsData->SelectedRowsCount(),
	);
$arFooter[] = array(
	"counter"=>true,
	"title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"),
	"value"=>"0",
	);
$lAdmin->AddFooter($arFooter);

if($STAT_RIGHT>="W")
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("STAT_DELETE"),
		"clear"=>GetMessage("STAT_RESET"),
		array(
			"action" => "compareAdv()",
			"value" => "compare",
			"name" => GetMessage("STAT_COMPARE"),
		),
	), array("disable_action_target"=>true));

$aContext = array(
	array(
		"ICON"	=> "btn_new",
		"TEXT"	=> GetMessage("STAT_ADD"),
		"TITLE"	=> GetMessage("STAT_NEW_ADV"),
		"LINK"	=> "adv_edit.php?lang=".LANG
	),
	array(
		"SEPARATOR"=>"Y",
	),
	array(
		"TEXT"=>($group_by==""?GetMessage("STAT_GROUP"):GetMessage("STAT_GROUPED").$group_by),
		"MENU"=>array(
			array(
				"TEXT"=>GetMessage("STAT_NO_GROUP"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "group_by="),
				"ICON"=>($group_by==""?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_GROUP_BY_REFERER1"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "group_by=referer1"),
				"ICON"=>($group_by=="referer1"?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_GROUP_BY_REFERER2"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "group_by=referer2"),
				"ICON"=>($group_by=="referer2"?"checked":""),
			),
		),
	),
);
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#" => $STORED_DAYS)));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arFilterDropDown = array(
	GetMessage('STAT_F_ID'),
	GetMessage('STAT_F_REFERER_1_2'),
	GetMessage('STAT_F_DURATION'),
	GetMessage('STAT_F_SESSIONS'),
	GetMessage('STAT_F_GUESTS'),
	GetMessage('STAT_F_PERIOD'),
	GetMessage('STAT_F_ATTENTIVENESS'),
	GetMessage('STAT_F_VISITORS_PER_DAY'),
);
if($STAT_RIGHT>"M")
{
	$arFilterDropDown[]=GetMessage("STAT_F_COST");
	$arFilterDropDown[]=GetMessage("STAT_F_REVENUE");
	$arFilterDropDown[]=GetMessage("STAT_F_BENEFIT");
	$arFilterDropDown[]=GetMessage("STAT_F_ROI");
	if($currency_module=="Y")
		$arFilterDropDown[]=GetMessage("STAT_F_CURRENCY");
}

$oFilter = new CAdminFilter($sTableID."_filter",$arFilterDropDown);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage("STAT_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("STAT_FIND_TITLE")?>">
		<?
		$arr = array(
			"reference" => array(
				"referer1",
				"referer2",
				GetMessage('STAT_F_ID'),
			),
			"reference_id" => array(
				"referer1",
				"referer2",
				"id",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ID")?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_REFERER_1_2")?>:</td>
	<td><input type="text" name="find_referer1" size="9" value="<?echo htmlspecialcharsbx($find_referer1)?>">&nbsp;&nbsp;/&nbsp;&nbsp;<input class="typeinput" type="text" name="find_referer2" size="9" value="<?echo htmlspecialcharsbx($find_referer2)?>"><?=ShowExactMatchCheckbox("find_referer12")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_DURATION")?>:</td>
	<td><input type="text" maxlength="10" name="find_duration1" value="<?echo htmlspecialcharsbx($find_duration1)?>" size="9">&nbsp;<?echo GetMessage("STAT_TILL")?>&nbsp;<input type="text" maxlength="10" name="find_duration2" value="<?echo htmlspecialcharsbx($find_duration2)?>" size="9"></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_SESSIONS")?>:</td>
	<td><input type="text" maxlength="10" name="find_sessions1" value="<?echo htmlspecialcharsbx($find_sessions1)?>" size="9">&nbsp;<?echo GetMessage("STAT_TILL")?>&nbsp;<input type="text" maxlength="10" name="find_sessions2" value="<?echo htmlspecialcharsbx($find_sessions2)?>" size="9">&nbsp;<?=GetMessage("STAT_F_BACK")?>&nbsp;<?echo InputType("checkbox","find_sessions_back","Y",$find_sessions_back,false)?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_GUESTS")?>:</td>
	<td><input type="text" maxlength="10" name="find_guests1" value="<?echo htmlspecialcharsbx($find_guests1)?>" size="9">&nbsp;<?echo GetMessage("STAT_TILL")?>&nbsp;<input type="text" maxlength="10" name="find_guests2" value="<?echo htmlspecialcharsbx($find_guests2)?>" size="9">&nbsp;<?=GetMessage("STAT_F_BACK")?>&nbsp;<?echo InputType("checkbox","find_guests_back","Y",$find_guests_back,false) ?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_PERIOD")." (".FORMAT_DATE."):"?></td>
	<td><?echo CalendarPeriod("find_date1_period", $find_date1_period, "find_date2_period", $find_date2_period, "find_form","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ATTENTIVENESS")?>:</td>
	<td><input type="text" maxlength="10" name="find_attent1" value="<?echo htmlspecialcharsbx($find_attent1)?>" size="9">&nbsp;<?echo GetMessage("STAT_TILL")?>&nbsp;<input type="text" maxlength="10" name="find_attent2" value="<?echo htmlspecialcharsbx($find_attent2)?>" size="9">&nbsp;<?=GetMessage("STAT_F_BACK")?>&nbsp;<?echo InputType("checkbox","find_attent_back","Y",$find_attent_back,false) ?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_VISITORS_PER_DAY")?>:</td>
	<td><input type="text" maxlength="10" name="find_visitors_per_day1" value="<?echo htmlspecialcharsbx($find_visitors_per_day1)?>" size="9">&nbsp;<?echo GetMessage("STAT_TILL")?>&nbsp;<input type="text" maxlength="10" name="find_visitors_per_day2" value="<?echo htmlspecialcharsbx($find_visitors_per_day2)?>" size="9"></td>
</tr>
<?if ($STAT_RIGHT>"M"):?>
<tr>
	<td><?echo GetMessage("STAT_F_COST")?>:</td>
	<td><input type="text" maxlength="10" name="find_cost1" value="<?echo htmlspecialcharsbx($find_cost1)?>" size="9">&nbsp;<?echo GetMessage("STAT_TILL")?>&nbsp;<input type="text" maxlength="10" name="find_cost2" value="<?echo htmlspecialcharsbx($find_cost2)?>" size="9"></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_REVENUE")?>:</td>
	<td><input type="text" maxlength="10" name="find_revenue1" value="<?echo htmlspecialcharsbx($find_revenue1)?>" size="9">&nbsp;<?echo GetMessage("STAT_TILL")?>&nbsp;<input type="text" maxlength="10" name="find_revenue2" value="<?echo htmlspecialcharsbx($find_revenue2)?>" size="9"></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_BENEFIT")?>:</td>
	<td><input type="text" maxlength="10" name="find_benefit1" value="<?echo htmlspecialcharsbx($find_benefit1)?>" size="9">&nbsp;<?echo GetMessage("STAT_TILL")?><input type="text" maxlength="10" name="find_benefit2" value="<?echo htmlspecialcharsbx($find_benefit2)?>" size="9"></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ROI")?>:</td>
	<td><input type="text" maxlength="10" name="find_roi1" value="<?echo htmlspecialcharsbx($find_roi1)?>" size="9">&nbsp;<?echo GetMessage("STAT_TILL")?><input type="text" maxlength="10" name="find_roi2" value="<?echo htmlspecialcharsbx($find_roi2)?>" size="9"></td>
</tr>
<?if ($currency_module=="Y") : ?>
<tr>
	<td><?echo GetMessage("STAT_F_CURRENCY")?>:</td>
	<td><?
	echo SelectBoxFromArray("find_currency", $arrCurrency, htmlspecialcharsbx($find_currency), GetMessage("STAT_F_BASE_CURRENCY"));?></td>
</tr>
<?endif;?>
<?endif;?>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(), "form" => "find_form"));
$oFilter->End();
?>
</form>

<script type="text/javascript">
<!--
function compareAdv()
{
	var oForm = document.form_<?=$sTableID?>;
	var url = '';

	for (var i = 0; i < oForm.elements.length; i++)
	{
		if (oForm.elements[i].tagName.toUpperCase() == "INPUT"
			&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
			&& oForm.elements[i].name.toUpperCase() == "ID[]"
			&& oForm.elements[i].checked == true)
		{
			url+='&find_adv[]='+oForm.elements[i].value;
		}
	}
	if(url.length>0)
		window.location='adv_analysis.php?lang=<?=LANG?>&set_filter=Y'+url;
}
//-->
</script>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
