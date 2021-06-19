<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
$statDB = CDatabase::GetModuleConnection('statistic');
if(isset($group_by))
{
	if($group_by!="event1" && $group_by!="event2")
		$group_by="";
}
else
	$group_by=false;//no setting (will be read later from session)


$today = GetTime(time());
$yesterday = GetTime(time()-86400);
$b_yesterday = GetTime(time()-172800);

$base_currency = GetStatisticBaseCurrency();
if ($base_currency <> '' && CModule::IncludeModule("currency"))
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

$sTableID = "tbl_event_type_stat";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

function CheckFilter()
{
	global $FilterArr, $lAdmin, $statDB;

	foreach ($FilterArr as $f) global $$f;
	$arr = array();

	$arr[] = array(
		"date1" => $find_date_enter_1,
		"date2" => $find_date_enter_2,
		"mess1" => GetMessage("STAT_WRONG_FIRST_DATE_FROM"),
		"mess2" => GetMessage("STAT_WRONG_FIRST_DATE_TILL"),
		"mess3" => GetMessage("STAT_FROM_TILL_FIRST_DATE")
		);

	$arr[] = array(
		"date1" => $find_date_last_1,
		"date2" => $find_date_last_2,
		"mess1" => GetMessage("STAT_WRONG_LAST_DATE_FROM"),
		"mess2" => GetMessage("STAT_WRONG_LAST_DATE_TILL"),
		"mess3" => GetMessage("STAT_FROM_TILL_LAST_DATE")
		);

	$arr[] = array(
		"date1" => $find_date1_period,
		"date2" => $find_date2_period,
		"mess1" => GetMessage("STAT_WRONG_PERIOD_DATE_FROM"),
		"mess2" => GetMessage("STAT_WRONG_PERIOD_DATE_TILL"),
		"mess3" => GetMessage("STAT_FROM_TILL_PERIOD_DATE")
		);

	foreach($arr as $ar)
	{
		if ($ar["date1"] <> '' && !CheckDateTime($ar["date1"]))
			$lAdmin->AddFilterError($ar["mess1"]);
		if ($ar["date2"] <> '' && !CheckDateTime($ar["date2"]))
			$lAdmin->AddFilterError($ar["mess2"]);
		if ($ar["date1"] <> '' && $ar["date2"] <> '' &&
		$statDB->CompareDates($ar["date1"], $ar["date2"])==1)
			$lAdmin->AddFilterError($ar["mess3"]);
	}

	// sessions
	if(intval($find_counter1) > intval($find_counter2))
		$lAdmin->AddFilterError(GetMessage("STAT_COUNTER1_COUNTER2"));

	// statistics keep days
	if(intval($find_keep_days1) > intval($find_keep_days2))
		$lAdmin->AddFilterError(GetMessage("STAT_DAYS1_DAYS2"));

	// dynamics keep days
	if(intval($find_dynamic_keep_days1) > intval($find_dynamic_keep_days2))
		$lAdmin->AddFilterError(GetMessage("STAT_DYNAMIC_DAYS1_DAYS2"));

	return count($lAdmin->arFilterErrors)==0;
}

$arrExactMatch = array(
	"ID_EXACT_MATCH"		=> "find_id_exact_match",
	"DESCRIPTION_EXACT_MATCH"	=> "find_description_exact_match",
	"NAME_EXACT_MATCH"		=> "find_name_exact_match",
	"EVENT1_EXACT_MATCH"		=> "find_event1_exact_match",
	"EVENT2_EXACT_MATCH"		=> "find_event2_exact_match",
	);
$FilterArr = Array(
	"find",
	"find_type",
	"find_id",
	"find_date_enter_1",
	"find_date_enter_2",
	"find_date_last_1",
	"find_date_last_2",
	"find_date1_period",
	"find_date2_period",
	"find_event1",
	"find_event2",
	"find_counter1",
	"find_counter2",
	"find_money1",
	"find_money2",
	"find_currency",
	"find_adv_visible",
	"find_diagram_default",
	"find_name",
	"find_description",
	"find_keep_days1",
	"find_keep_days2",
	"find_dynamic_keep_days1",
	"find_dynamic_keep_days2",
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

if (CheckFilter())
{
	$arFilter = Array(
		"ID"			=> ($find!="" && $find_type == "id"? $find:$find_id),
		"DATE_ENTER_1"		=> $find_date_enter_1,
		"DATE_ENTER_2"		=> $find_date_enter_2,
		"DATE_LAST_1"		=> $find_date_last_1,
		"DATE_LAST_2"		=> $find_date_last_2,
		"DATE1_PERIOD"		=> $find_date1_period,
		"DATE2_PERIOD"		=> $find_date2_period,
		"EVENT1"		=> ($find!="" && $find_type == "event1"? $find:$find_event1),
		"EVENT2"		=> ($find!="" && $find_type == "event2"? $find:$find_event2),
		"COUNTER1"		=> $find_counter1,
		"COUNTER2"		=> $find_counter2,
		"MONEY1"		=> (($STAT_RIGHT>"M") ? $find_money1 : ""),
		"MONEY2"		=> (($STAT_RIGHT>"M") ? $find_money2 : ""),
		"CURRENCY"		=> $find_currency,
		"ADV_VISIBLE"		=> $find_adv_visible,
		"DIAGRAM_DEFAULT"	=> $find_diagram_default,
		"NAME"			=> $find_name,
		"DESCRIPTION"		=> $find_description,
		"KEEP_DAYS1"		=> $find_keep_days1,
		"KEEP_DAYS2"		=> $find_keep_days2,
		"DYNAMIC_KEEP_DAYS1"	=> $find_dynamic_keep_days1,
		"DYNAMIC_KEEP_DAYS2"	=> $find_dynamic_keep_days2,
		"GROUP"			=> $group_by
		);
	$arFilter = array_merge($arFilter, array_convert_name_2_value($arrExactMatch));
}

if(($arID = $lAdmin->GroupAction()) && $STAT_RIGHT>="W")
{
	if($_REQUEST['action_target'] == "selected")
	{
		$cData = new CStatEventType;
		$rsData = $cData->GetList('', '', $arFilter);
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
			if(!CStatEventType::Delete($ID))
			{
				$statDB->Rollback();
				$lAdmin->AddGroupError(GetMessage("STAT_DELETE_ERROR"), $ID);
			}
			$statDB->Commit();
			break;
		case "clear":
			@set_time_limit(0);
			$statDB->StartTransaction();
			if(!CStatEventType::Delete($ID, "N"))
			{
				$statDB->Rollback();
				$lAdmin->AddGroupError(GetMessage("STAT_DELETE_ERROR"), $ID);
			}
			$statDB->Commit();
			break;
		}
	}
}

global $by, $order;

$cData = new CStatEventType;
$rsData = $cData->GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_EVENT_TYPE_PAGES")));

$arHeaders = array();

if($group_by=="")
{
	$arHeaders[]=
		array(	"id"		=>"ID",
			"content"	=>"ID",
			"sort"		=>"s_id",
			"align"		=>"right",
			"default"	=>true,
		);
	$arHeaders[]=
		array(	"id"		=>"NAME",
			"content"	=>GetMessage("STAT_NAME").$group_by,
			"sort"		=>"s_name",
			"default"	=>true,
		);
}
if($group_by=="" || $group_by=="event1")
	$arHeaders[]=
		array(	"id"		=>"EVENT1",
			"content"	=>"event1",
			"sort"		=>"s_event1",
			"default"	=>true,
		);
if($group_by=="" || $group_by=="event2")
	$arHeaders[]=
		array(	"id"		=>"EVENT2",
			"content"	=>"event2",
			"sort"		=>"s_event2",
			"default"	=>true,
		);
$arHeaders[]=
	array(	"id"		=>"TODAY_COUNTER",
		"content"	=>GetMessage("STAT_TODAY_COUNTER"),
		"sort"		=>"s_today_counter",
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"YESTERDAY_COUNTER",
		"content"	=>GetMessage("STAT_YESTERDAY_COUNTER"),
		"sort"		=>"s_yesterday_counter",
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"B_YESTERDAY_COUNTER",
		"content"	=>GetMessage("STAT_B_YESTERDAY_COUNTER"),
		"sort"		=>"s_b_yesterday_counter",
		"align"		=>"right",
		"default"	=>true,
	);
$bIsPeriod=($arFilter["DATE1_PERIOD"] <> '' || $arFilter["DATE1_PERIOD"] <> '');
if($bIsPeriod)

$arHeaders[]=
	array(	"id"		=>"PERIOD_COUNTER",
		"content"	=>GetMessage("STAT_PERIOD_COUNTER"),
		"sort"		=>"s_period_counter",
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"TOTAL_COUNTER",
		"content"	=>GetMessage("STAT_TOTAL_COUNTER"),
		"sort"		=>"s_total_counter",
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"DATE_ENTER",
		"content"	=>GetMessage("STAT_DATE_ENTER"),
		"sort"		=>"s_date_enter",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"DATE_LAST",
		"content"	=>GetMessage("STAT_DATE_LAST"),
		"sort"		=>"s_date_last",
		"default"	=>true,
	);
$lAdmin->AddHeaders($arHeaders);
$thousand_sep = ($_REQUEST["mode"] == "excel")? "": "&nbsp;";

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if($f_TODAY_COUNTER>0):
		if ($group_by==""):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event_id='.$f_ID.'&amp;find_event_id_exact_match=Y&amp;find_date1='.$today.'&amp;set_filter=Y">'.$f_TODAY_COUNTER.'</a>';
		elseif ($group_by=="event1"):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event1='.urlencode("\"".$f_EVENT1."\"").'&amp;find_event12_exact_match=Y&amp;find_date1='.$today.'&amp;set_filter=Y">'.$f_TODAY_COUNTER.'</a>';
		elseif ($group_by=="event2"):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event2='.urlencode("\"".$f_EVENT2."\"").'&amp;find_event12_exact_match=Y&amp;find_date1='.$today.'&amp;set_filter=Y">'.$f_TODAY_COUNTER.'</a>';
		endif;
		if($f_TODAY_MONEY>0 && $STAT_RIGHT>"M"):
			$strHTML.=" (".str_replace(" ", $thousand_sep, number_format($f_TODAY_MONEY, 2, ".", " ")).")";
		endif;
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("TODAY_COUNTER", $strHTML);

	if($f_YESTERDAY_COUNTER>0):
		if ($group_by==""):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event_id='.$f_ID.'&amp;find_event_id_exact_match=Y&amp;find_date1='.$yesterday.'&amp;find_date2='.$yesterday.'&amp;set_filter=Y">'.$f_YESTERDAY_COUNTER.'</a>';
		elseif ($group_by=="event1"):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event1='.urlencode("\"".$f_EVENT1."\"").'&amp;find_event12_exact_match=Y&amp;find_date1='.$yesterday.'&amp;find_date2='.$yesterday.'&amp;set_filter=Y">'.$f_YESTERDAY_COUNTER.'</a>';
		elseif ($group_by=="event2"):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event2='.urlencode("\"".$f_EVENT2."\"").'&amp;find_event12_exact_match=Y&amp;find_date1='.$yesterday.'&amp;find_date2='.$yesterday.'&amp;set_filter=Y">'.$f_YESTERDAY_COUNTER.'</a>';
		endif;
		if($f_YESTERDAY_MONEY>0 && $STAT_RIGHT>"M"):
			$strHTML.=" (".str_replace(" ", $thousand_sep, number_format($f_YESTERDAY_MONEY, 2, ".", " ")).")";
		endif;
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("YESTERDAY_COUNTER", $strHTML);

	if($f_B_YESTERDAY_COUNTER>0):
		if ($group_by==""):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event_id='.$f_ID.'&amp;find_event_id_exact_match=Y&amp;find_date1='.$b_yesterday.'&amp;find_date2='.$b_yesterday.'&amp;set_filter=Y">'.$f_B_YESTERDAY_COUNTER.'</a>';
		elseif ($group_by=="event1"):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event1='.urlencode("\"".$f_EVENT1."\"").'&amp;find_event12_exact_match=Y&amp;find_date1='.$b_yesterday.'&amp;find_date2='.$b_yesterday.'&amp;set_filter=Y">'.$f_B_YESTERDAY_COUNTER.'</a>';
		elseif ($group_by=="event2"):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event2='.urlencode("\"".$f_EVENT2."\"").'&amp;find_event12_exact_match=Y&amp;find_date1='.$b_yesterday.'&amp;find_date2='.$b_yesterday.'&amp;set_filter=Y">'.$f_B_YESTERDAY_COUNTER.'</a>';
		endif;
		if($f_B_YESTERDAY_MONEY>0 && $STAT_RIGHT>"M"):
			$strHTML.=" (".str_replace(" ", $thousand_sep, number_format($f_B_YESTERDAY_MONEY, 2, ".", " ")).")";
		endif;
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("B_YESTERDAY_COUNTER", $strHTML);

	if($bIsPeriod):
		if($f_PERIOD_COUNTER>0):
			if ($group_by==""):
				$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event_id='.$f_ID.'&amp;find_event_id_exact_match=Y&amp;find_date1='.$arFilter["DATE1_PERIOD"].'&amp;find_date2='.$arFilter["DATE2_PERIOD"].'&amp;set_filter=Y">'.$f_PERIOD_COUNTER.'</a>';
			elseif ($group_by=="event1"):
				$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event1='.urlencode("\"".$f_EVENT1."\"").'&amp;find_date1='.$arFilter["DATE1_PERIOD"].'&amp;find_event12_exact_match=Y&amp;find_date2='.$arFilter["DATE2_PERIOD"].'&amp;set_filter=Y">'.$f_PERIOD_COUNTER.'</a>';
			elseif ($group_by=="event2"):
				$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event2='.urlencode("\"".$f_EVENT2."\"").'&amp;find_date1='.$arFilter["DATE1_PERIOD"].'&amp;find_event12_exact_match=Y&amp;find_date2='.$arFilter["DATE2_PERIOD"].'&amp;set_filter=Y">'.$f_PERIOD_COUNTER.'</a>';
			endif;
			if($f_PERIOD_MONEY>0 && $STAT_RIGHT>"M"):
				$strHTML.=" (".str_replace(" ", $thousand_sep, number_format($f_PERIOD_MONEY, 2, ".", " ")).")";
			endif;
		else:
			$strHTML='&nbsp;';
		endif;
		$row->AddViewField("PERIOD_COUNTER", $strHTML);
	endif;
	if($f_TOTAL_COUNTER>0):
		if ($group_by==""):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event_id='.$f_ID.'&amp;find_event_id_exact_match=Y&amp;set_filter=Y">'.$f_TOTAL_COUNTER.'</a>';
		elseif ($group_by=="event1"):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event1='.urlencode("\"".$f_EVENT1."\"").'&amp;find_event12_exact_match=Y&amp;set_filter=Y">'.$f_TOTAL_COUNTER.'</a>';
		elseif ($group_by=="event2"):
			$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_event2='.urlencode("\"".$f_EVENT2."\"").'&amp;find_event12_exact_match=Y&amp;set_filter=Y">'.$f_TOTAL_COUNTER.'</a>';
		endif;
		if($f_TOTAL_MONEY>0 && $STAT_RIGHT>"M"):
			$strHTML.=" (".str_replace(" ", $thousand_sep, number_format($f_TOTAL_MONEY, 2, ".", " ")).")";
		endif;
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("TOTAL_COUNTER", $strHTML);

	$arActions = Array();

	if($STAT_RIGHT=="W")
		$arActions[] = array(
			"ICON"=>"edit",
			"TEXT"=>GetMessage("STAT_CHANGE"),
			"ACTION"=>$lAdmin->ActionRedirect("event_type_edit.php?ID=".$f_ID)
		);
	if($STAT_RIGHT=="W")
		$arActions[] = array(
			"ICON"=>"clear",
			"TEXT"=>GetMessage("STAT_CLEAR"),
			"ACTION"=>"if(confirm('".GetMessageJS('STAT_CONFIRM_CLEAR')."')) ".$lAdmin->ActionDoGroup($f_ID, "clear")
		);
	if($STAT_RIGHT=="W")
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("STAT_DELETE"),
			"ACTION"=>"if(confirm('".GetMessageJS('STAT_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);

	$arActions[] = array("SEPARATOR"=>true);

	$dynamic_days = CStatEventType::DynamicDays($f_ID);
	if ($dynamic_days>=2 && function_exists("ImageCreate"))
		$arActions[] = array(
			"DEFAULT"=>true,
			"TEXT"=>GetMessage("STAT_GRAPH"),
			"ACTION"=>$lAdmin->ActionRedirect("event_graph_list.php?find_events[]=".$f_ID."&set_filter=Y")
		);
	if ($dynamic_days>=1)
		$arActions[] = array(
			"ICON"=>"",
			"TEXT"=>GetMessage("STAT_DYNAMICS"),
			"ACTION"=>$lAdmin->ActionRedirect("event_dynamic_list.php?find_event_id=".$f_ID."&find_event_id_exact_match=Y&set_filter=Y")
		);
	$arActions[] = array(
		"ICON"=>"",
		"TEXT"=>GetMessage("STAT_ANALYSIS"),
		"ACTION"=>$lAdmin->ActionRedirect("/bitrix/admin/adv_analysis.php?find_data_type=EVENT_SUMMA&find_events[]=".$f_ID."&set_filter=Y")
	);

	if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
		unset($arActions[count($arActions)-1]);
	if($group_by=="")
		$row->AddActions($arActions);

endwhile;

//Totals
$arTotalFilter = $arFilter;
$arTotalFilter["GROUP"]="total";
$rsTotalData = $cData->GetList('', '', $arTotalFilter);
$arTotal = $rsTotalData->Fetch();

$arTotal["TODAY_COUNTER"] = intval($arTotal["TODAY_COUNTER"]);
$arTotal["YESTERDAY_COUNTER"] = intval($arTotal["YESTERDAY_COUNTER"]);
$arTotal["B_YESTERDAY_COUNTER"] = intval($arTotal["B_YESTERDAY_COUNTER"]);
$arTotal["PERIOD_COUNTER"] = intval($arTotal["PERIOD_COUNTER"]);
$arTotal["TOTAL_COUNTER"] = intval($arTotal["TOTAL_COUNTER"]);

$arTotal["TODAY_MONEY"] = round(doubleval($arTotal["TODAY_MONEY"]),2);
$arTotal["YESTERDAY_MONEY"] = round(doubleval($arTotal["YESTERDAY_MONEY"]),2);
$arTotal["B_YESTERDAY_MONEY"] = round(doubleval($arTotal["B_YESTERDAY_MONEY"]),2);
$arTotal["PERIOD_MONEY"] = round(doubleval($arTotal["PERIOD_MONEY"]),2);
$arTotal["TOTAL_MONEY"] = round(doubleval($arTotal["TOTAL_MONEY"]),2);

$arFooter = array();
$arFooter[] = array(
	"title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"),
	"value"=>$rsData->SelectedRowsCount(),
	);
if($group_by=="")
	$arFooter[] = array(
		"counter"=>true,
		"title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"),
		"value"=>"0",
		);
$arFooter[] = array(
	"title"=>GetMessage("STAT_TODAY_EVENTS"),
	"value"=>$arTotal["TODAY_COUNTER"].($STAT_RIGHT>"M" && $arTotal["TODAY_MONEY"]>0?"(".str_replace(" ", $thousand_sep, number_format($arTotal["TODAY_MONEY"], 2, ".", " ")).")":""),
	);
$arFooter[] = array(
	"title"=>GetMessage("STAT_YESTERDAY_EVENTS"),
	"value"=>$arTotal["YESTERDAY_COUNTER"].($STAT_RIGHT>"M" && $arTotal["YESTERDAY_MONEY"]>0?"(".str_replace(" ", $thousand_sep, number_format($arTotal["YESTERDAY_MONEY"], 2, ".", " ")).")":""),
	);
$arFooter[] = array(
	"title"=>GetMessage("STAT_B_YESTERDAY_EVENTS"),
	"value"=>$arTotal["B_YESTERDAY_COUNTER"].($STAT_RIGHT>"M" && $arTotal["B_YESTERDAY_MONEY"]>0?"(".str_replace(" ", $thousand_sep, number_format($arTotal["B_YESTERDAY_MONEY"], 2, ".", " ")).")":""),
	);
if($bIsPeriod)
	$arFooter[] = array(
		"title"=>GetMessage("STAT_PERIOD_EVENTS"),
		"value"=>$arTotal["PERIOD_COUNTER"].($STAT_RIGHT>"M" && $arTotal["PERIOD_MONEY"]>0?"(".str_replace(" ", $thousand_sep, number_format($arTotal["PERIOD_MONEY"], 2, ".", " ")).")":""),
		);
$arFooter[] = array(
	"title"=>GetMessage("STAT_TOTAL_EVENTS"),
	"value"=>$arTotal["TOTAL_COUNTER"].($STAT_RIGHT>"M" && $arTotal["TOTAL_MONEY"]>0?"(".str_replace(" ", $thousand_sep, number_format($arTotal["TOTAL_MONEY"], 2, ".", " ")).")":""),
	);
$lAdmin->AddFooter($arFooter);

if($group_by=="")
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("STAT_DELETE"),
		"clear"=>GetMessage("STAT_CLEAR"),
		));

$aContext = array(
	array(
		"TEXT"=>GetMessage("STAT_ADD"),
		"LINK"=>"event_type_edit.php?lang=".LANG,
		"TITLE"=>GetMessage("STAT_ADD_TITLE"),
		"ICON"=>"btn_new",
	),
);
$aContext[] =
	array(
		"TEXT"=>($group_by==""?GetMessage("STAT_GROUP"):GetMessage("STAT_GROUPED").$group_by),
		"MENU"=>array(
			array(
				"TEXT"=>GetMessage("STAT_WO_GROUP"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "group_by="),
				"ICON"=>($group_by==""?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_EVENT1_GROUP"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "group_by=event1"),
				"ICON"=>($group_by=="event1"?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_EVENT2_GROUP"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "group_by=event2"),
				"ICON"=>($group_by=="event2"?"checked":""),
			),
		),
	);
$aContext[] =
	array(
		"SEPARATOR"=>"Y",
	);
$aContext[] =
	array(
		"TEXT"=>GetMessage("STAT_DIAGRAM_S"),
		"LINK"=>"/bitrix/admin/event_diagram_list.php?lang=".LANGUAGE_ID."&set_default=Y",
		"TITLE"=>GetMessage("STAT_DIAGRAM"),
	);
$aContext[] =
	array(
		"TEXT"=>GetMessage("STAT_GRAPH_FULL_S"),
		"LINK"=>"/bitrix/admin/event_graph_list.php?lang=".LANGUAGE_ID."&set_default=Y",
		"TITLE"=>GetMessage("STAT_GRAPH_FULL"),
	);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#" => $STORED_DAYS)));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arFilterDropDown = array(
	GetMessage('STAT_F_ID'),
	"event1",
	"event2",
	GetMessage("STAT_F_NAME"),
	GetMessage("STAT_F_DESCRIPTION"),
	GetMessage("STAT_F_DATE_ENTER"),
	GetMessage("STAT_F_DATE_LAST"),
	GetMessage("STAT_F_PERIOD"),
	GetMessage("STAT_F_COUNTER"),
);
if($STAT_RIGHT>"M")
{
	$arFilterDropDown[]=GetMessage("STAT_F_MONEY");
	if($currency_module=="Y")
		$arFilterDropDown[]=GetMessage("STAT_F_CURRENCY");
}
$arFilterDropDown[]=GetMessage("STAT_F_KEEP_DAYS");
$arFilterDropDown[]=GetMessage("STAT_F_DYNAMIC_KEEP_DAYS");
$arFilterDropDown[]=GetMessage("STAT_F_ADV_VISIBLE");
$arFilterDropDown[]=GetMessage("STAT_F_DIAGRAM_DEFAULT");

$oFilter = new CAdminFilter($sTableID."_filter",$arFilterDropDown);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage("STAT_F_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("STAT_F_FIND_ENTER")?>">
		<?
		$arr = array(
			"reference" => array(
				"event1",
				"event2",
				GetMessage('STAT_F_ID'),
			),
			"reference_id" => array(
				"event1",
				"event2",
				"id",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ID")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>event1:</td>
	<td><input type="text" name="find_event1" size="47" value="<?echo htmlspecialcharsbx($find_event1)?>"><?=ShowExactMatchCheckbox("find_event1")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>event2:</td>
	<td><input type="text" name="find_event2" size="47" value="<?echo htmlspecialcharsbx($find_event2)?>"><?=ShowExactMatchCheckbox("find_event2")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_NAME")?></td>
	<td><input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>"><?=ShowExactMatchCheckbox("find_name")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_DESCRIPTION")?></td>
	<td><input type="text" name="find_description" size="47" value="<?echo htmlspecialcharsbx($find_description)?>"><?=ShowExactMatchCheckbox("find_description")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_DATE_ENTER")." (".FORMAT_DATE."):"?></td>
	<td><?echo CalendarPeriod("find_date_enter_1", $find_date_enter_1, "find_date_enter_2", $find_date_enter_2, "find_form", "Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_DATE_LAST")." (".FORMAT_DATE."):"?></td>
	<td><?echo CalendarPeriod("find_date_last_1", $find_date_last_1, "find_date_last_2", $find_date_last_2, "find_form", "Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_PERIOD")." (".FORMAT_DATE."):"?></td>
	<td><?echo CalendarPeriod("find_date1_period", $find_date1_period, "find_date2_period", $find_date2_period, "find_form","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_COUNTER")?></td>
	<td><input type="text" name="find_counter1" size="9" value="<?echo htmlspecialcharsbx($find_counter1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_counter2" size="9" value="<?echo htmlspecialcharsbx($find_counter2)?>"></td>
</tr>
<?if($STAT_RIGHT>"M"):?>
<tr>
	<td><?echo GetMessage("STAT_F_MONEY")?></td>
	<td><input type="text" name="find_money1" size="9" value="<?echo htmlspecialcharsbx($find_money1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_money2" size="9" value="<?echo htmlspecialcharsbx($find_money2)?>"></td>
</tr>
<?if($currency_module=="Y"):?>
<tr valign="center">
	<td><?echo GetMessage("STAT_F_CURRENCY")?></td>
	<td><?
	echo SelectBoxFromArray("find_currency", $arrCurrency, htmlspecialcharsbx($find_currency), GetMessage("STAT_F_BASE_CURRENCY"));?></td>
</tr>
<?endif;?>
<?endif;?>
<tr>
	<td><?echo GetMessage("STAT_F_KEEP_DAYS")?></td>
	<td><input type="text" name="find_keep_days1" size="9" value="<?echo htmlspecialcharsbx($find_keep_days1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_keep_days2" size="9" value="<?echo htmlspecialcharsbx($find_keep_days2)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_DYNAMIC_KEEP_DAYS")?></td>
	<td><input type="text" name="find_dynamic_keep_days1" size="9" value="<?echo htmlspecialcharsbx($find_dynamic_keep_days1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_dynamic_keep_days2" size="9" value="<?echo htmlspecialcharsbx($find_dynamic_keep_days2)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ADV_VISIBLE")?></td>
	<td><?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_adv_visible", $arr, htmlspecialcharsbx($find_adv_visible), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_DIAGRAM_DEFAULT")?></td>
	<td class="tablebody" width="0%" nowrap><?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_diagram_default", $arr, htmlspecialcharsbx($find_diagram_default), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(), "form" => "find_form"));
$oFilter->End();
?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
