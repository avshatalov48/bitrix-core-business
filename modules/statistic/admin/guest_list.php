<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
$statDB = CDatabase::GetModuleConnection('statistic');
IncludeModuleLangFile(__FILE__);

/***************************************************************************
				functions
***************************************************************************/

function CheckFilter()
{
	global $strError, $arFilterFields, $statDB;
	foreach ($arFilterFields as $f) global $$f;
	$str = "";
	$arMsg = Array();

	$arr[] = array(
		"date1" => $find_first_date1,
		"date2" => $find_first_date2,
		"mess1" => GetMessage("STAT_WRONG_FIRST_DATE_FROM"),
		"mess2" => GetMessage("STAT_WRONG_FIRST_DATE_TILL"),
		"mess3" => GetMessage("STAT_FROM_TILL_FIRST_DATE")
		);

	$arr[] = array(
		"date1" => $find_period_date1,
		"date2" => $find_period_date2,
		"mess1" => GetMessage("STAT_WRONG_PERIOD_DATE_FROM"),
		"mess2" => GetMessage("STAT_WRONG_PERIOD_DATE_TILL"),
		"mess3" => GetMessage("STAT_FROM_TILL_PERIOD_DATE")
		);

	$arr[] = array(
		"date1" => $find_last_date1,
		"date2" => $find_last_date2,
		"mess1" => GetMessage("STAT_WRONG_LAST_DATE_FROM"),
		"mess2" => GetMessage("STAT_WRONG_LAST_DATE_TILL"),
		"mess3" => GetMessage("STAT_FROM_TILL_LAST_DATE")
		);


	foreach($arr as $ar)
	{
		if ($ar["date1"] <> '' && !CheckDateTime($ar["date1"]))
			//$str.= $ar["mess1"]."<br>";
			$arMsg[] = array("id"=>"find_first_date1", "text"=> $ar["mess1"]);
		elseif ($ar["date2"] <> '' && !CheckDateTime($ar["date2"]))
			//$str.= $ar["mess2"]."<br>";
			$arMsg[] = array("id"=>"find_first_date2", "text"=> $ar["mess2"]);
		elseif ($ar["date1"] <> '' && $ar["date2"] <> '' && $statDB->CompareDates($ar["date1"], $ar["date2"])==1)
			//$str.= $ar["mess3"]."<br>";
			$arMsg[] = array("id"=>"find_first_date2", "text"=> $ar["mess2"]);
	}

	// sessions
	if (intval($find_sess1)>0 and intval($find_sess2)>0 and $find_sess1>$find_sess2)
		//$str .= GetMessage("STAT_SESS1_SESS2")."<br>";
		$arMsg[] = array("id"=>"find_sess2", "text"=> GetMessage("STAT_SESS1_SESS2"));

	// hits
	if (intval($find_hits1)>0 and intval($find_hits2)>0 and $find_hits1>$find_hits2)
		//$str .= GetMessage("STAT_HITS1_HITS2")."<br>";
		$arMsg[] = array("id"=>"find_hits2", "text"=> GetMessage("STAT_HITS1_HITS2"));

	// events
	if (intval($find_events1)>0 and intval($find_events2)>0 and $find_events1>$find_events2)
		//$str .= GetMessage("STAT_EVENTS1_EVENTS2")."<br>";
		$arMsg[] = array("id"=>"find_events2", "text"=> GetMessage("STAT_EVENTS1_EVENTS2"));

	if(!empty($arMsg))
	{
		$e = new CAdminException($arMsg);
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	}

	return true;
}

$arSites = array();
$ref = $ref_id = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
{
	$ref[] = $ar["ID"];
	$ref_id[] = $ar["ID"];
	$arSites[$ar["ID"]] = "[<a href=\"/bitrix/admin/site_edit.php?LID=".$ar["ID"]."&lang=".LANGUAGE_ID."\">".$ar["ID"]."</a>]&nbsp;";
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

$sTableID = "t_guest_list";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		GetMessage("STAT_F_GUEST_ID"),
		GetMessage("STAT_F_REGISTERED"),
		GetMessage("STAT_F_USER_AGENT"),
		GetMessage("STAT_F_IP"),
		GetMessage("STAT_F_LANG"),
		GetMessage("STAT_F_COUNTRY"),
		GetMessage("STAT_F_REGION"),
		GetMessage("STAT_F_CITY"),
		GetMessage("STAT_F_FIRST_DATE"),
		GetMessage("STAT_F_PERIOD_DATE"),
		GetMessage("STAT_F_LAST_DATE"),
		GetMessage("STAT_F_PAGE"),
		GetMessage("STAT_F_FIRST_ADV"),
		GetMessage("STAT_F_ADV"),
		GetMessage("STAT_F_REFERER12"),
		GetMessage("STAT_F_REFERER3"),
		GetMessage("STAT_F_EVENTS_1_2"),
		GetMessage("STAT_F_SESS_1_2"),
		GetMessage("STAT_F_HITS_1_2"),
		GetMessage("STAT_F_LOGIC"),
	)
);


$arFilterFields = Array(
	"find_user", "find_user_exact_match",
	"find_id", "find_id_exact_match",
	"find_registered",

	"find_user_agent", "find_user_agent_exact_match",

	"find_ip","find_ip_exact_match",
	"find_lang","find_lang_exact_match",

	"find_country_id","find_country","find_country_exact_match",
	"find_region","find_region_exact_match",
	"find_city_id","find_city","find_city_exact_match",

	"find_first_date1", "find_first_date2",
	"find_period_date1", "find_period_date2",
	"find_last_date1", "find_last_date2",

	"find_site_id", "find_url_404", "find_url", "find_url_exact_match",
	"find_adv",
	"find_adv_id","find_adv_id_exact_match",

	"find_referer1", "find_referer2", "find_referer12_exact_match",
	"find_referer3", "find_referer3_exact_match",
	"find_events1", "find_events2",
	"find_sess1", "find_sess2",
	"find_hits1","find_hits2",

	"FILTER_logic",
);


$lAdmin->InitFilter($arFilterFields);

InitBVar($find_id_exact_match);
InitBVar($find_url_exact_match);
InitBVar($find_user_agent_exact_match);
InitBVar($find_adv_id_exact_match);
InitBVar($find_referer12_exact_match);
InitBVar($find_referer12_exact_match);
InitBVar($find_referer3_exact_match);
InitBVar($find_ip_exact_match);
InitBVar($find_lang_exact_match);
InitBVar($find_country_exact_match);
InitBVar($find_region_exact_match);
InitBVar($find_city_exact_match);
InitBVar($find_user_exact_match);

if (CheckFilter())
{
	$arFilter = Array(
		"ID"		=> $find_id,
		"REGISTERED"	=> $find_registered,
		"USER"		=> $find_user,
		"FIRST_DATE1"	=> $find_first_date1,
		"FIRST_DATE2"	=> $find_first_date2,
		"LAST_DATE1"	=> $find_last_date1,
		"LAST_DATE2"	=> $find_last_date2,
		"PERIOD_DATE1"	=> $find_period_date1,
		"PERIOD_DATE2"	=> $find_period_date2,
		"SITE_ID"	=> $find_site_id,
		"URL"		=> $find_url,
		"URL_404"	=> $find_url_404,
		"USER_AGENT"	=> $find_user_agent,
		"ADV"		=> $find_adv,
		"ADV_ID"	=> $find_adv_id,
		"REFERER1"	=> $find_referer1,
		"REFERER2"	=> $find_referer2,
		"REFERER3"	=> $find_referer3,
		"EVENTS1"	=> $find_events1,
		"EVENTS2"	=> $find_events2,
		"SESS1"		=> $find_sess1,
		"SESS2"		=> $find_sess2,
		"HITS1"		=> $find_hits1,
		"HITS2"		=> $find_hits2,
		"IP"		=> $find_ip,
		"COUNTRY"	=> $find_country,
		"COUNTRY_ID"	=> $find_country_id,
		"REGION"	=> $find_region,
		"CITY"		=> $find_city,
		"CITY_ID"	=> $find_city_id,
		"LANG"		=> $find_lang,

		"ID_EXACT_MATCH"	=> $find_id_exact_match,
		"URL_EXACT_MATCH"	=> $find_url_exact_match,
		"USER_AGENT_EXACT_MATCH"=> $find_user_agent_exact_match,
		"ADV_ID_EXACT_MATCH"	=> $find_adv_id_exact_match,
		"REFERER1_EXACT_MATCH"	=> $find_referer12_exact_match,
		"REFERER2_EXACT_MATCH"	=> $find_referer12_exact_match,
		"REFERER3_EXACT_MATCH"	=> $find_referer3_exact_match,
		"IP_EXACT_MATCH"	=> $find_ip_exact_match,
		"LANG_EXACT_MATCH"	=> $find_lang_exact_match,
		"COUNTRY_EXACT_MATCH"	=> $find_country_exact_match,
		"COUNTRY_ID_EXACT_MATCH"=> $find_country_exact_match,
		"REGION_EXACT_MATCH"	=> $find_region_exact_match,
		"CITY_EXACT_MATCH"	=> $find_city_exact_match,
		"CITY_ID_EXACT_MATCH"	=> $find_city_exact_match,
		"USER_EXACT_MATCH"	=> $find_user_exact_match,
		);
}
else
{
	if($e = $APPLICATION->GetException())
		$GLOBALS["lAdmin"]->AddFilterError(GetMessage("STAT_FILTER_ERROR").": ".$e->GetString());
}

global $by, $order;

$rsData = CGuest::GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_GUEST_PAGES")));

$arHeaders = Array();

$arHeaders[] = array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true,);
$arHeaders[] = array("id"=>"LAST_USER_ID", "content"=>GetMessage("STAT_USER_S"), "sort"=>"s_last_user_id", "default"=>true,);

$arHeaders[] = array("id"=>"SESSIONS", "content"=>GetMessage("STAT_SESSIONS_S"), "sort"=>"s_sessions", "default"=>true, "align" => "right");
$arHeaders[] = array("id"=>"C_EVENTS", "content"=>GetMessage("STAT_EVENTS_S"), "sort"=>"s_events", "default"=>true,"align" => "right");
$arHeaders[] = array("id"=>"HITS", "content"=>GetMessage("STAT_HITS_S"), "sort"=>"s_hits", "default"=>true,"align" => "right");

$arHeaders[] = array("id"=>"FIRST_DATE", "content"=>GetMessage("STAT_FIRST_ENTER"), "sort"=>"s_first_date", "default"=>true,);
$arHeaders[] = array("id"=>"LAST_DATE", "content"=>GetMessage("STAT_LAST_ENTER"), "sort"=>"s_last_date", "default"=>true,);



$arHeaders[] = array("id"=>"FIRST_URL_FROM", "content"=>GetMessage("STAT_URL_FROM"), "sort"=>"s_first_url_from", "default"=>false,);
$arHeaders[] = array("id"=>"FIRST_URL_TO", "content"=>GetMessage("STAT_URL_TO"), "sort"=>"", "default"=>false,);
$arHeaders[] = array("id"=>"LAST_URL_LAST", "content"=>GetMessage("STAT_LASTPAGE_XLS"), "sort"=>"s_last_url_last", "default"=>false,);


$arHeaders[] = array("id"=>"FIRST_ADV_ID", "content"=>GetMessage("STAT_FIRST_ADV"), "sort"=>"s_first_adv_id", "default"=>false,);
$arHeaders[] = array("id"=>"LAST_ADV_ID", "content"=>GetMessage("STAT_LAST_ADV"), "sort"=>"s_last_adv_id", "default"=>false,);

$arHeaders[] = array("id"=>"LAST_USER_AGENT", "content"=>GetMessage("STAT_USER_AGENT"), "sort"=>"s_last_user_agent", "default"=>false,);

$arHeaders[] = array("id"=>"LAST_IP", "content"=>GetMessage("STAT_IP_HOST"), "sort"=>"s_last_ip", "default"=>true,);
$arHeaders[] = array("id"=>"LAST_COUNTRY_ID", "content"=>GetMessage("STAT_COUNTRY"), "sort"=>"s_last_country_id", "default"=>true,);
$arHeaders[] = array("id"=>"LAST_REGION_NAME", "content"=>GetMessage("STAT_REGION"), "sort"=>"s_last_region_name", "default"=>false,);
$arHeaders[] = array("id"=>"LAST_CITY_ID", "content"=>GetMessage("STAT_CITY"), "sort"=>"s_last_city_id", "default"=>true,);

$lAdmin->AddHeaders($arHeaders);

$arrUsers = array();

while($arRes = $rsData->NavNext(true, "f_"))
{

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if ($f_ID==$_SESSION["SESS_GUEST_ID"])
		$row->AddViewField("ID",'<span class="stat_attention">'.$f_ID.'</span>');


	$str = "";
	if (intval($f_LAST_USER_ID)>0)
	{
		$str .= "[<a title=\"".GetMessage("STAT_EDIT_USER")."\" href=\"user_edit.php?lang=".LANGUAGE_ID."&amp;ID=".$f_LAST_USER_ID."\">".$f_LAST_USER_ID."</a>]";

		if ($f_LOGIN <> '') :
			$str .= " (".$f_LOGIN.") ".$f_USER_NAME;
		else:
			if (!in_array($f_LAST_USER_ID, array_keys($arrUsers)))
			{
				$rsUser = CUser::GetByID($f_LAST_USER_ID);
				$arUser = $rsUser->GetNext();
				$LOGIN = $arUser["LOGIN"];
				$USER_NAME = $arUser["NAME"]." ".$arUser["LAST_NAME"];
				$arrUsers[$f_LAST_USER_ID]["USER_NAME"] = $USER_NAME;
				$arrUsers[$f_LAST_USER_ID]["LOGIN"] = $LOGIN;
			}
			else
			{
				$USER_NAME = $arrUsers[$f_LAST_USER_ID]["USER_NAME"];
				$LOGIN = $arrUsers[$f_LAST_USER_ID]["LOGIN"];
			}

			if ($LOGIN <> '') :
				$str .= " (".$LOGIN.") ".$USER_NAME;
			endif;
		endif;

		if ($f_LAST_USER_AUTH!="Y")
			$str .= "<br><nobr><span class=\"stat_notauth\">".GetMessage("STAT_NOT_AUTH")."</span></nobr>";
	}
	else
	{
		$str .= GetMessage("STAT_NOT_REGISTERED");
	}

	$row->AddViewField("LAST_USER_ID", $str);



	$str = '<a title="'.GetMessage("STAT_VIEW_SESSIONS_LIST").'" href="session_list.php?lang='.LANGUAGE_ID.'&find_guest_id='.$f_ID.'&find_guest_id_exact_match=Y&set_filter=Y">'.$f_SESSIONS.'</a>';
	$row->AddViewField("SESSIONS", $str);

	$str = '<a title="'.GetMessage("STAT_VIEW_EVENTS_LIST").'" href="event_list.php?lang='.LANGUAGE_ID.'&find_guest_id='.$f_ID.'&find_guest_id_exact_match=Y&set_filter=Y">'.$f_C_EVENTS.'</a>';
	$row->AddViewField("C_EVENTS", $str);

	$str = '<a title="'.GetMessage("STAT_HITS_LIST").'" href="hit_list.php?lang='.LANGUAGE_ID.'&find_guest_id='.$f_ID.'&find_guest_id_exact_match=Y&set_filter=Y">'.$f_HITS.'</a>';
	$row->AddViewField("HITS", $str);

	if ($f_FIRST_URL_FROM <> '')
	{
		$row->AddViewField("FIRST_URL_FROM", StatAdminListFormatURL($arRes["FIRST_URL_FROM"], array(
			"new_window" => false,
			"max_display_chars" => "default",
			"chars_per_line" => "default",
			"kill_sessid" => $STAT_RIGHT < "W",
		)));
	}

	$str = "";
	if ($f_FIRST_SITE_ID <> '')
	{
		$str .= '[<a title="'.GetMessage("STAT_SITE").'" href="/bitrix/admin/site_edit.php?LID='.$f_FIRST_SITE_ID.'&lang='.LANGUAGE_ID.'">'.$f_FIRST_SITE_ID.'</a>]&nbsp;';
	}

	$row->AddViewField("FIRST_URL_TO", $str.StatAdminListFormatURL($arRes["FIRST_URL_TO"], array(
		"new_window" => false,
		"attention" => $f_FIRST_URL_TO_404 == "Y",
		"max_display_chars" => "default",
		"chars_per_line" => "default",
		"kill_sessid" => $STAT_RIGHT < "W",
	)));

	$str = "";
	if ($f_LAST_SITE_ID <> '')
	{
		$str .= '[<a title="'.GetMessage("STAT_SITE").'" href="/bitrix/admin/site_edit.php?LID='.$f_LAST_SITE_ID.'&lang='.LANGUAGE_ID.'">'.$f_LAST_SITE_ID.'</a>]&nbsp;';
	}

	$row->AddViewField("LAST_URL_LAST", $str.StatAdminListFormatURL($arRes["FIRST_URL_TO"], array(
		"new_window" => false,
		"attention" => $f_LAST_URL_LAST_404 == "Y",
		"max_display_chars" => "default",
		"chars_per_line" => "default",
		"kill_sessid" => $STAT_RIGHT < "W",
	)));

	$str = "";
	if (intval($f_FIRST_ADV_ID)>0)
	{
		$str = '<a href="adv_list.php?lang='.LANGUAGE_ID.'&find_id='.$f_FIRST_ADV_ID.'&find_id_exact_match=Y&set_filter=Y">'.$f_FIRST_ADV_ID.'</a> (<a title="'.GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_1").'" href="session_list.php?lang='.LANGUAGE_ID.'&find_referer1='.urlencode("\"".$f_FIRST_REFERER1."\"").'&find_referer12_exact_match=Y&set_filter=Y">'.$f_FIRST_REFERER1.'</a> / <a title="'.GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_2").'" href="session_list.php?lang='.LANGUAGE_ID.'&find_referer2='.urlencode("\"".$f_FIRST_REFERER2."\"").'&find_referer12_exact_match=Y&set_filter=Y">'.$f_FIRST_REFERER2.'</a> / <a title="'.GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_3").'" href="session_list.php?lang='.LANGUAGE_ID.'&find_referer3='.urlencode("\"".$f_FIRST_REFERER3."\"").'&find_referer3_exact_match=Y&set_filter=Y">'.$f_FIRST_REFERER3.'</a> )';

	}
	$row->AddViewField("FIRST_ADV_ID", $str);

	$str = "";
	if (intval($f_LAST_ADV_ID)>0)
	{
		$str = '<a href="adv_list.php?lang='.LANGUAGE_ID.'&find_id='.$f_LAST_ADV_ID.'&find_id_exact_match=Y&set_filter=Y">'.$f_LAST_ADV_ID.'</a>'.($f_LAST_ADV_BACK=="Y" ? "*" :"").' (<a title="'.GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_1").'" href="session_list.php?lang='.LANGUAGE_ID.'&find_referer1='.urlencode("\"".$f_LAST_REFERER1."\"").'&find_referer12_exact_match=Y&set_filter=Y">'.$f_LAST_REFERER1.'</a> / <a title="'.GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_2").'" href="session_list.php?lang='.LANGUAGE_ID.'&find_referer2='.urlencode("\"".$f_LAST_REFERER2."\"").'&find_referer12_exact_match=Y&set_filter=Y">'.$f_LAST_REFERER2.'</a> / <a title="'.GetMessage("STAT_VIEW_SESSIONS_LIST_BY_REF_3").'" href="session_list.php?lang='.LANGUAGE_ID.'&find_referer3='.urlencode("\"".$f_LAST_REFERER3."\"").'&find_referer3_exact_match=Y&set_filter=Y">'.$f_LAST_REFERER3.'</a>)';

	}
	$row->AddViewField("LAST_ADV_ID", $str);

	if($f_LAST_COUNTRY_ID <> '')
	{
		if ($f_LAST_COUNTRY_NAME <> '')
			$str = "[".$f_LAST_COUNTRY_ID."] ".$f_LAST_COUNTRY_NAME;
		else
			$str =  $f_LAST_COUNTRY_ID;

		$row->AddViewField("LAST_COUNTRY_ID", $str);
	}

	if($f_LAST_CITY_ID <> '')
	{
		$row->AddViewField("LAST_CITY_ID", "[".$f_LAST_CITY_ID."] ".$f_LAST_CITY_NAME);
	}

	$row->AddViewField("LAST_IP", GetWhoisLink($f_LAST_IP));


	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"list",
		"TEXT"=>GetMessage("STAT_DETAIL"),
		"ACTION"=>"javascript:CloseWaitWindow(); jsUtils.OpenWindow('guest_detail.php?lang=".LANG."&ID=".$f_ID."', '700', '550');",
		"DEFAULT" => "Y",
	);

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	)
);

$lAdmin->AddAdminContextMenu();
$lAdmin->CheckListMode();

$STORED_DAYS = COption::GetOptionString("statistic","GUEST_DAYS");

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#" => $STORED_DAYS)));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>
<?if(COption::GetOptionString("statistic", "dbnode_id") <= 0):?>
	<tr>
		<td><?echo GetMessage("STAT_F_USER")?>:</td>
		<td><input type="text" name="find_user" size="30" value="<?echo htmlspecialcharsbx($find_user)?>"><?=ShowExactMatchCheckbox("find_user")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>
<?endif?>
<tr>
	<td><?echo GetMessage("STAT_F_GUEST_ID")?>:</td>
	<td><input type="text" name="find_id" size="30" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_REGISTERED")?>:</td>
	<td><?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_registered", $arr, htmlspecialcharsbx($find_registered), GetMessage("MAIN_ALL"));
		?></td>
</tr>

<tr>
	<td><?echo GetMessage("STAT_F_USER_AGENT")?>:</td>
	<td><input type="text" name="find_user_agent" size="30" value="<?echo htmlspecialcharsbx($find_user_agent)?>"><?=ShowExactMatchCheckbox("find_user_agent")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_IP")?>:</td>
	<td><input type="text" name="find_ip" size="30" value="<?echo htmlspecialcharsbx($find_ip)?>"><?=ShowExactMatchCheckbox("find_ip")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_LANG")?>:</td>
	<td><input type="text" name="find_lang" size="30" value="<?echo htmlspecialcharsbx($find_lang)?>"><?=ShowExactMatchCheckbox("find_lang")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_COUNTRY")?>:</td>
	<td valign="center">
		[&nbsp;<input type="text" name="find_country_id" size="5" value="<?echo htmlspecialcharsbx($find_country_id)?>">&nbsp;]&nbsp;&nbsp;&nbsp;<input type="text" name="find_country" size="30" value="<?echo htmlspecialcharsbx($find_country)?>"><?echo ShowExactMatchCheckbox("find_country")?>&nbsp;<?echo ShowFilterLogicHelp()?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_REGION")?>:</td>
	<td><input type="text" name="find_region" size="30" value="<?echo htmlspecialcharsbx($find_region)?>"><?echo ShowExactMatchCheckbox("find_region")?>&nbsp;<?echo ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_CITY")?>:</td>
	<td valign="center">
		[&nbsp;<input type="text" name="find_city_id" size="5" value="<?echo htmlspecialcharsbx($find_city_id)?>">&nbsp;]&nbsp;&nbsp;&nbsp;<input type="text" name="find_city" size="30" value="<?echo htmlspecialcharsbx($find_city)?>"><?echo ShowExactMatchCheckbox("find_city")?>&nbsp;<?echo ShowFilterLogicHelp()?>
	</td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_FIRST_DATE").":"?></td>
	<td width="0%" nowrap><?
		echo CalendarPeriod("find_first_date1", $find_first_date1, "find_first_date2", $find_first_date2, "form1","Y");
		?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD_DATE").":"?></td>
	<td width="0%" nowrap><?
		echo CalendarPeriod("find_period_date1", $find_period_date1, "find_period_date2",$find_period_date2, "form1","Y");
		?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_LAST_DATE").":"?></td>
	<td width="0%" nowrap><?
		echo CalendarPeriod("find_last_date1", $find_last_date1, "find_last_date2", $find_last_date2, "form1","Y");
		?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_PAGE")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_url_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_url_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_url" size="33" value="<?echo htmlspecialcharsbx($find_url)?>"><?=ShowExactMatchCheckbox("find_url")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_FIRST_ADV")?>:</td>
	<td><?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_adv", $arr, htmlspecialcharsbx($find_adv), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ADV")?>:</td>
	<td><input type="text" name="find_adv_id" size="30" value="<?echo htmlspecialcharsbx($find_adv_id)?>"><?=ShowExactMatchCheckbox("find_adv_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("STAT_F_REFERER12")?>:</td>
	<td><input type="text" name="find_referer1" size="10" value="<?echo htmlspecialcharsbx($find_referer1)?>">&nbsp;/&nbsp;<input type="text" name="find_referer2" size="10" value="<?echo htmlspecialcharsbx($find_referer2)?>"><?=ShowExactMatchCheckbox("find_referer12")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("STAT_F_REFERER3")?>:</td>
	<td><input type="text" name="find_referer3" size="30" value="<?echo htmlspecialcharsbx($find_referer3)?>"><?=ShowExactMatchCheckbox("find_referer3")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_EVENTS_1_2")?>:</td>
	<td><input type="text" name="find_events1" size="10" value="<?echo htmlspecialcharsbx($find_events1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_events2" size="10" value="<?echo htmlspecialcharsbx($find_events2)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_SESS_1_2")?>:</td>
	<td><input type="text" name="find_sess1" size="10" value="<?echo htmlspecialcharsbx($find_sess1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_sess2" size="10" value="<?echo htmlspecialcharsbx($find_sess2)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_HITS_1_2")?>:</td>
	<td><input type="text" name="find_hits1" size="10" value="<?echo htmlspecialcharsbx($find_hits1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_hits2" size="10" value="<?echo htmlspecialcharsbx($find_hits2)?>"></td>
</tr>
<?=ShowLogicRadioBtn()?>
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?echo BeginNote();?>
* - <?echo GetMessage("STAT_ADV_BACK")?>
<?echo EndNote();?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
