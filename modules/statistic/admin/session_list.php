<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
$statDB = CDatabase::GetModuleConnection('statistic');

function CheckFilter()
{
	global $strError, $arFilterFields, $statDB;
	foreach ($arFilterFields as $f) global $$f;
	$str = "";
	$arMsg = Array();
	$arr = array();

	$arr[] = array(
		"date1" => $find_date1,
		"date2" => $find_date2,
		"mess1" => GetMessage("STAT_WRONG_DATE_FROM"),
		"mess2" => GetMessage("STAT_WRONG_DATE_TILL"),
		"mess3" => GetMessage("STAT_FROM_TILL_DATE")
		);

	$arr[] = array(
		"date1" => $find_date_end1,
		"date2" => $find_date_end2,
		"mess1" => GetMessage("STAT_WRONG_DATE_END_FROM"),
		"mess2" => GetMessage("STAT_WRONG_DATE_END_TILL"),
		"mess3" => GetMessage("STAT_FROM_TILL_DATE_END")
		);

	foreach($arr as $ar)
	{
		if ($ar["date1"] <> '' && !CheckDateTime($ar["date1"]))
			$arMsg[] = array("id"=>"find_date1", "text"=> $ar["mess1"]);
		elseif ($ar["date2"] <> '' && !CheckDateTime($ar["date2"]))
			$arMsg[] = array("id"=>"find_date2", "text"=> $ar["mess2"]);
		elseif ($ar["date1"] <> '' && $ar["date2"] <> '' && $statDB->CompareDates($ar["date1"], $ar["date2"])==1)
			$arMsg[] = array("id"=>"find_date2", "text"=> $ar["mess3"]);
	}

	// hits
	if (intval($find_hits1)>0 and intval($find_hits2)>0 and $find_hits1>$find_hits2)
		$arMsg[] = array("id"=>"find_hits2", "text"=> GetMessage("STAT_HITS1_HITS2"));

	// events
	if (intval($find_events1)>0 and intval($find_events2)>0 and $find_events1>$find_events2)
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


$sTableID = "t_session_list";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		GetMessage("STAT_F_ID"),
		GetMessage("STAT_F_START_DATE"),
		GetMessage("STAT_F_DATE_END"),
		GetMessage("STAT_F_GUEST_ID"),
		GetMessage("STAT_F_AUTH"),
		GetMessage("STAT_F_NEW_GUEST"),
		GetMessage("STAT_F_IP"),
		GetMessage("STAT_F_USER_AGENT"),
		GetMessage("STAT_F_COUNTRY"),
		GetMessage("STAT_F_REGION"),
		GetMessage("STAT_F_CITY"),
		GetMessage("STAT_F_STOP"),
		GetMessage("STAT_F_STOP_LIST_ID"),
		GetMessage("STAT_F_HITS"),
		GetMessage("STAT_F_EVENTS"),
		GetMessage("STAT_F_CAME_ADV"),
		GetMessage("STAT_F_ADV"),
		GetMessage("STAT_F_REFERER12"),
		GetMessage("STAT_F_REFERER3"),
		GetMessage("STAT_F_ADV_BACK"),
		GetMessage("STAT_F_URL_TO"),
		GetMessage("STAT_F_URL_LAST"),
		GetMessage("STAT_F_LOGIC"),
	)
);

$arFilterFields = Array(
	"find_id",
	"find_id_exact_match",
	"find_date1",
	"find_date2",
	"find_date_end1",
	"find_date_end2",
	"find_guest_id",
	"find_guest_id_exact_match",
	"find_registered",
	"find_new_guest",
	"find_ip",
	"find_ip_exact_match",
	"find_user_agent",
	"find_user_agent_exact_match",
	"find_country_id",
	"find_country",
	"find_country_exact_match",
	"find_region",
	"find_region_exact_match",
	"find_city_id",
	"find_city",
	"find_city_exact_match",
	"find_stop",
	"find_stop_list_id",
	"find_stop_list_id_exact_match",
	"find_hits1",
	"find_hits2",
	"find_events1",
	"find_events2",
	"find_adv",
	"find_adv_id",
	"find_adv_id_exact_match",
	"find_referer1",
	"find_referer2",
	"find_referer12_exact_match",
	"find_referer3",
	"find_referer3_exact_match",
	"find_adv_back",
	"find_first_site_id",
	"find_url_to_404",
	"find_url_to",
	"find_url_to_exact_match",
	"find_last_site_id",
	"find_url_last_404",
	"find_url_last",
	"find_url_last_exact_match",
	"FILTER_logic",
);

$lAdmin->InitFilter($arFilterFields);

InitBVar($find_id_exact_match);
InitBVar($find_user_exact_match);
InitBVar($find_guest_id_exact_match);
InitBVar($find_ip_exact_match);
InitBVar($find_adv_id_exact_match);
InitBVar($find_referer12_exact_match);
InitBVar($find_referer12_exact_match);
InitBVar($find_referer3_exact_match);
InitBVar($find_user_agent_exact_match);
InitBVar($find_country_exact_match);
InitBVar($find_region_exact_match);
InitBVar($find_city_exact_match);
InitBVar($find_stop_list_id_exact_match);
InitBVar($find_url_last_exact_match);
InitBVar($find_url_to_exact_match);

if (CheckFilter())
{
	$arFilter = Array(
		"ID"		=> $find_id,
		"FIRST_SITE_ID"	=> $find_first_site_id,
		"LAST_SITE_ID"	=> $find_last_site_id,
		"DATE_START_1"	=> $find_date1,
		"DATE_START_2"	=> $find_date2,
		"DATE_END_1"	=> $find_date_end1,
		"DATE_END_2"	=> $find_date_end2,
		"USER"		=> $find_user,
		"NEW_GUEST"	=> $find_new_guest,
		"GUEST_ID"	=> $find_guest_id,
		"IP"		=> $find_ip,
		"REGISTERED"	=> $find_registered,
		"EVENTS1"	=> $find_events1,
		"EVENTS2"	=> $find_events2,
		"HITS1"		=> $find_hits1,
		"HITS2"		=> $find_hits2,
		"ADV"		=> $find_adv,
		"ADV_ID"	=> $find_adv_id,
		"ADV_BACK"	=> $find_adv_back,
		"REFERER1"	=> $find_referer1,
		"REFERER2"	=> $find_referer2,
		"REFERER3"	=> $find_referer3,
		"USER_AGENT"	=> $find_user_agent,
		"COUNTRY_ID"	=> $find_country_id,
		"COUNTRY"	=> $find_country,
		"REGION"	=> $find_region,
		"CITY_ID"	=> $find_city_id,
		"CITY"		=> $find_city,
		"STOP"		=> $find_stop,
		"STOP_LIST_ID"	=> $find_stop_list_id,
		"URL_LAST"	=> $find_url_last,
		"URL_LAST_404"	=> $find_url_last_404,
		"URL_TO"	=> $find_url_to,
		"URL_TO_404"	=> $find_url_to_404,

		"ID_EXACT_MATCH"		=> $find_id_exact_match,
		"USER_EXACT_MATCH"		=> $find_user_exact_match,
		"GUEST_ID_EXACT_MATCH"		=> $find_guest_id_exact_match,
		"IP_EXACT_MATCH"		=> $find_ip_exact_match,
		"ADV_ID_EXACT_MATCH"		=> $find_adv_id_exact_match,
		"REFERER1_EXACT_MATCH"		=> $find_referer12_exact_match,
		"REFERER2_EXACT_MATCH"		=> $find_referer12_exact_match,
		"REFERER3_EXACT_MATCH"		=> $find_referer3_exact_match,
		"USER_AGENT_EXACT_MATCH"	=> $find_user_agent_exact_match,
		"COUNTRY_EXACT_MATCH"		=> $find_country_exact_match,
		"COUNTRY_ID_EXACT_MATCH"	=> $find_country_exact_match,
		"REGION_EXACT_MATCH"		=> $find_region_exact_match,
		"CITY_EXACT_MATCH"		=> $find_city_exact_match,
		"CITY_ID_EXACT_MATCH"		=> $find_city_exact_match,
		"STOP_LIST_ID_EXACT_MATCH"	=> $find_stop_list_id_exact_match,
		"URL_LAST_EXACT_MATCH"		=> $find_url_last_exact_match,
		"URL_TO_EXACT_MATCH"		=> $find_url_to_exact_match,
		);
}
else
{
	if($e = $APPLICATION->GetException())
		$GLOBALS["lAdmin"]->AddFilterError(GetMessage("STAT_FILTER_ERROR").": ".$e->GetString());
}

global $by, $order;

$rsData = CSession::GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_SESS_PAGES")));

$arHeaders = Array();

$arHeaders[] = array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true,);
$arHeaders[] = array("id"=>"USER_ID", "content"=>GetMessage("STAT_USER"), "sort"=>"s_user_id", "default"=>true,);


$arHeaders[] = array("id"=>"DATE_FIRST", "content"=>GetMessage("STAT_START"), "sort"=>"s_date_first", "default"=>true,);
$arHeaders[] = array("id"=>"DATE_LAST", "content"=>GetMessage("STAT_END"), "sort"=>"s_date_last", "default"=>false,);
$arHeaders[] = array("id"=>"SESSION_TIME", "content"=>GetMessage("STAT_SESSION_PERIOD"), "sort"=>"", "default"=>true,);
$arHeaders[] = array("id"=>"IP_LAST", "content"=>GetMessage("STAT_IP"), "sort"=>"s_ip", "default"=>true,);
$arHeaders[] = array("id"=>"HITS", "content"=>GetMessage("STAT_NUM_PAGES"), "sort"=>"s_hits", "default"=>true,"align" => "right");
$arHeaders[] = array("id"=>"C_EVENTS", "content"=>GetMessage("STAT_EVENTS"), "sort"=>"s_events", "default"=>true,"align" => "right");
$arHeaders[] = array("id"=>"ADV_ID", "content"=>GetMessage("STAT_ADV"), "sort"=>"s_adv_id", "default"=>true,);
$arHeaders[] = array("id"=>"URL_TO", "content"=>GetMessage("STAT_FIRST_PAGE"), "sort"=>"s_url_to", "default"=>false,);
$arHeaders[] = array("id"=>"URL_LAST", "content"=>GetMessage("STAT_LAST_PAGE"), "sort"=>"s_url_last", "default"=>false,);
$arHeaders[] = array("id"=>"COUNTRY_ID", "content"=>GetMessage("STAT_COUNTRY"), "sort"=>"s_country_id", "default"=>false,);
$arHeaders[] = array("id"=>"REGION_NAME", "content"=>GetMessage("STAT_REGION"), "sort"=>"s_region_name", "default"=>false,);
$arHeaders[] = array("id"=>"CITY_ID", "content"=>GetMessage("STAT_CITY"), "sort"=>"s_city_id", "default"=>false,);

$lAdmin->AddHeaders($arHeaders);

$arrUsers = array();

while($arRes = $rsData->NavNext(true, "f_"))
{


	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if ($_SESSION["SESS_SESSION_ID"]==$f_ID)
		$row->AddViewField("ID",'<span class="stat_attention">'.$f_ID.'</span>');


	$str = "";
	if ($f_USER_ID>0) :
		if ($f_LOGIN <> '') :
			$str .= "[<a title=\"".GetMessage("STAT_EDIT_USER")."\" href=\"user_edit.php?lang=".LANG."&amp;ID=".$f_USER_ID."\">".$f_USER_ID."</a>] ($f_LOGIN) $f_USER_NAME";
		else :
			if(!array_key_exists($f_USER_ID, $arrUsers))
			{
				$rsUser = CUser::GetByID($f_USER_ID);
				$arUser = $rsUser->GetNext();
				$arrUsers[$f_USER_ID] = array(
					"USER_NAME" => $arUser["NAME"]." ".$arUser["LAST_NAME"],
					"LOGIN" => $arUser["LOGIN"],
				);
			}
			$USER_NAME = $arrUsers[$f_USER_ID]["USER_NAME"];
			$LOGIN = $arrUsers[$f_USER_ID]["LOGIN"];

			$str .= "[<a title=\"".GetMessage("STAT_EDIT_USER")."\" href=\"user_edit.php?lang=".LANG."&amp;ID=".$f_USER_ID."\">".$f_USER_ID."</a>] ";
			if ($LOGIN <> '') :
				$str .= "(".$LOGIN.") ".$USER_NAME."";
			endif;
		endif;
		$str .= ($f_USER_AUTH!="Y") ? " <span class=\"stat_notauth\">".GetMessage("STAT_NOT_AUTH")."</span>" : "";
	else :
		$str .= GetMessage("STAT_NOT_REGISTERED");
	endif;
	$str .= "<br>[<a href=\"guest_list.php?lang=".LANG."&amp;find_id=".$f_GUEST_ID."&amp;find_id_exact_match=Y&amp;set_filter=Y\">".$f_GUEST_ID."</a>]&nbsp;";

	$str .=  ($f_NEW_GUEST=="Y") ? "<span class=\"stat_newguest\">".GetMessage("STAT_NEW_GUEST")."</span>" : "<span class='stat_oldguest'>".GetMessage("STAT_OLD_GUEST")."</span>";

	$row->AddViewField("USER_ID", $str);

	$str = "";
	$hours = intval($f_SESSION_TIME/3600);
	if ($hours>0) :
		$str .= $hours."&nbsp;".GetMessage("STAT_HOURS")."&nbsp;";
		$f_SESSION_TIME = $f_SESSION_TIME - $hours*3600;
	endif;
		$str .= intval($f_SESSION_TIME/60)."&nbsp;".GetMessage("STAT_MIN");
		$str .= " ".($f_SESSION_TIME%60)."&nbsp;".GetMessage("STAT_SEC");

	$row->AddViewField("SESSION_TIME", $str);

	$row->AddViewField("IP_LAST", GetWhoisLink($f_IP_LAST));

	if($f_CITY_ID <> '')
	{
		$row->AddViewField("CITY_ID", "[".$f_CITY_ID."] ".$f_CITY_NAME);
	}

	$str = "<a title=\"".GetMessage("STAT_VIEW_HITS_LIST_2")."\"  href=\"hit_list.php?lang=".LANGUAGE_ID."&amp;find_session_id=".$f_ID."&amp;find_session_id_exact_match=Y&amp;set_filter=Y&amp;rand=".rand()."\">".$f_HITS."</a>";
	$row->AddViewField("HITS", $str);

	$str = "<a title=\"".GetMessage("STAT_VIEW_EVENTS")."\"  href=\"event_list.php?lang=".LANGUAGE_ID."&amp;find_session_id=".$f_ID."&amp;find_session_id_exact_match=Y&amp;set_filter=Y&amp;rand=".rand()."\">".$f_C_EVENTS."</a>";
	$row->AddViewField("C_EVENTS", $str);

	if (intval($f_ADV_ID)>0) :
		$str = "<a href=\"adv_list.php?lang=".LANGUAGE_ID."&find_id=".$f_ADV_ID."&find_id_exact_match=Y&set_filter=Y\">".$f_ADV_ID."</a>";
		if ($f_ADV_BACK=="Y")
			$str .= "*";
		$str .= "<br>".$f_REFERER1." / ".$f_REFERER2."<br>".$f_REFERER3;
		$row->AddViewField("ADV_ID", $str);
	endif;


	$str = "";
	if ($f_FIRST_SITE_ID <> ''):
		$str .= "[<a title=\"".GetMessage("STAT_SITE")."\" href=\"/bitrix/admin/site_edit.php?LID=".$f_FIRST_SITE_ID."&lang=".LANGUAGE_ID."\">".$f_FIRST_SITE_ID."</a>]&nbsp;";
	endif;

	$row->AddViewField("URL_TO", $str.StatAdminListFormatURL($arRes["URL_TO"], array(
		"new_window" => false,
		"attention" => $f_URL_TO_404 == "Y",
		"max_display_chars" => "default",
		"chars_per_line" => "default",
		"kill_sessid" => $STAT_RIGHT < "W",
	)));

	$str = "";
	if ($f_LAST_SITE_ID <> ''):
		$str .= '[<a title="'.GetMessage("STAT_SITE").'" href="/bitrix/admin/site_edit.php?LID='.$f_LAST_SITE_ID.'&lang='.LANGUAGE_ID.'">'.$f_LAST_SITE_ID.'</a>]&nbsp;';
	endif;

	$row->AddViewField("", $str);
	$row->AddViewField("URL_LAST", $str.StatAdminListFormatURL($arRes["URL_LAST"], array(
		"new_window" => false,
		"attention" => $f_URL_LAST_404 == "Y",
		"max_display_chars" => "default",
		"chars_per_line" => "default",
		"kill_sessid" => $STAT_RIGHT < "W",
	)));

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"list",
		"TEXT"=>GetMessage("STAT_DETAIL"),
		"ACTION"=>"javascript:CloseWaitWindow(); jsUtils.OpenWindow('session_detail.php?lang=".LANG."&ID=".$f_ID."', '700', '550');",
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

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#"=>COption::GetOptionString("statistic","SESSION_DAYS"))));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>


<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>

<tr>
	<td><?echo GetMessage("STAT_F_USER")?>:</td>
	<td><input type="text" name="find_user" size="30" value="<?echo htmlspecialcharsbx($find_user)?>"><?=ShowExactMatchCheckbox("find_user")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td><?echo GetMessage("STAT_F_ID")?>:</td>
	<td><input type="text" name="find_id" size="30" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_START_DATE").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_DATE_END").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date_end1", $find_date_end1, "find_date_end2", $find_date_end2, "form1","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_GUEST_ID")?>:</td>
	<td><input type="text" name="find_guest_id" size="30" value="<?echo htmlspecialcharsbx($find_guest_id)?>"><?=ShowExactMatchCheckbox("find_guest_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td>
		<?echo GetMessage("STAT_F_AUTH")?>:</td>
	<td>
		<?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_registered", $arr, htmlspecialcharsbx($find_registered), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_NEW_GUEST")?>:</td>
	<td><?
		$arr = array("reference"=>array(GetMessage("STAT_NEW_GUEST_1"), GetMessage("STAT_OLD_GUEST_1")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_new_guest", $arr, htmlspecialcharsbx($find_new_guest), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_IP")?>:</td>
	<td><input type="text" name="find_ip" size="30" value="<?echo htmlspecialcharsbx($find_ip)?>"><?=ShowExactMatchCheckbox("find_ip")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td valign="top"><?echo GetMessage("STAT_F_USER_AGENT")?>:</td>
	<td><textarea class="typearea" name="find_user_agent" cols="30" rows="5"><?echo htmlspecialcharsbx($find_user_agent)?></textarea><?=ShowExactMatchCheckbox("find_user_agent")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_COUNTRY")?>:</td>
	<td valign="center">
		[&nbsp;<input type="text" name="find_country_id" size="5" value="<?echo htmlspecialcharsbx($find_country_id)?>">&nbsp;]&nbsp;&nbsp;&nbsp;<input type="text" name="find_country" size="30" value="<?echo htmlspecialcharsbx($find_country)?>"><?echo ShowExactMatchCheckbox("find_country")?>&nbsp;<?echo ShowFilterLogicHelp()?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_REGION")?>:</td>
	<td><input type="text" name="find_region" size="50" value="<?echo htmlspecialcharsbx($find_region)?>"><?echo ShowExactMatchCheckbox("find_region")?>&nbsp;<?echo ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_CITY")?>:</td>
	<td valign="center">
		[&nbsp;<input type="text" name="find_city_id" size="5" value="<?echo htmlspecialcharsbx($find_city_id)?>">&nbsp;]&nbsp;&nbsp;&nbsp;<input type="text" name="find_city" size="30" value="<?echo htmlspecialcharsbx($find_city)?>"><?echo ShowExactMatchCheckbox("find_city")?>&nbsp;<?echo ShowFilterLogicHelp()?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_STOP")?>:</td>
	<td><?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_stop", $arr, htmlspecialcharsbx($find_stop), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_STOP_LIST_ID")?>:</td>
	<td><input type="text" name="find_stop_list_id" size="30" value="<?echo htmlspecialcharsbx($find_stop_list_id)?>"><?=ShowExactMatchCheckbox("find_stop_list_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>
		<?echo GetMessage("STAT_F_HITS")?>:</td>
	<td>
		<input type="text" name="find_hits1" size="10" value="<?echo htmlspecialcharsbx($find_hits1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_hits2" size="10" value="<?echo htmlspecialcharsbx($find_hits2)?>"></td>
</tr>
<tr>
	<td>
		<?echo GetMessage("STAT_F_EVENTS")?>:</td>
	<td>
		<input type="text" name="find_events1" size="10" value="<?echo htmlspecialcharsbx($find_events1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_events2" size="10" value="<?echo htmlspecialcharsbx($find_events2)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_CAME_ADV")?>:</td>
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
	<td><?echo GetMessage("STAT_F_REFERER12")?>:</td>
	<td><input type="text" name="find_referer1" size="14" value="<?echo htmlspecialcharsbx($find_referer1)?>">&nbsp;/&nbsp;<input type="text" name="find_referer2" size="14" value="<?echo htmlspecialcharsbx($find_referer2)?>"><?=ShowExactMatchCheckbox("find_referer12")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_REFERER3")?>:</td>
	<td><input type="text" name="find_referer3" size="30" value="<?echo htmlspecialcharsbx($find_referer3)?>"><?=ShowExactMatchCheckbox("find_referer3")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>
		<?echo GetMessage("STAT_F_ADV_BACK")?>:</td>
	<td>
		<?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_adv_back", $arr, htmlspecialcharsbx($find_adv_back), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_URL_TO")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_first_site_id", $arSiteDropdown, $find_first_site_id, GetMessage("STAT_F_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_url_to_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_url_to_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_url_to" size="34" value="<?echo htmlspecialcharsbx($find_url_to)?>"><?=ShowExactMatchCheckbox("find_url_to")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_URL_LAST")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_last_site_id", $arSiteDropdown, $find_last_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_url_last_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_url_last_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_url_last" size="34" value="<?echo htmlspecialcharsbx($find_url_last)?>"><?=ShowExactMatchCheckbox("find_url_last")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?=ShowLogicRadioBtn()?>
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?
if($message)
	echo $message->Show();
?>

<?$lAdmin->DisplayList();?>

<?echo BeginNote();?>
* - <?echo GetMessage("STAT_ADV_BACK_ALT")?>
<?echo EndNote();?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
