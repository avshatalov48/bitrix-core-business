<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

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

$sTableID = "t_hit_list";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		GetMessage("STAT_F_ID"),
		GetMessage("STAT_F_SESSION"),
		GetMessage("STAT_F_DATE"),
		GetMessage("STAT_F_GUEST_ID"),
		GetMessage("STAT_F_AUTH"),
		GetMessage("STAT_F_NEW_GUEST"),
		GetMessage("STAT_F_IP"),
		GetMessage("STAT_F_COUNTRY"),
		GetMessage("STAT_F_REGION"),
		GetMessage("STAT_F_CITY"),
		GetMessage("STAT_F_USER_AGENT"),
		GetMessage("STAT_F_COOKIE"),
		GetMessage("STAT_F_STOP"),
		GetMessage("STAT_F_STOP_LIST_ID"),
		GetMessage("STAT_F_PAGE"),
		GetMessage("STAT_F_LOGIC"),
	)
);



$arFilterFields = Array(
	"find_id","find_id_exact_match",
	"find_session_id","find_session_id_exact_match",
	"find_date1","find_date2",
	"find_guest_id","find_guest_id_exact_match",
	"find_user","find_user_exact_match",
	"find_registered",
	"find_new_guest",
	"find_ip", "find_ip_exact_match",
	"find_country_id", "find_country", "find_country_exact_match",
	"find_region", "find_region_exact_match",
	"find_city_id", "find_city", "find_city_exact_match",
	"find_user_agent","find_user_agent_exact_match",
	"find_cookie", "find_cookie_exact_match",
	"find_stop",
	"find_stop_list_id","find_stop_list_id_exact_match",
	"find_site_id", "find_url_404","find_url","find_url_exact_match",
	"FILTER_logic",
);

$lAdmin->InitFilter($arFilterFields);

InitBVar($find_id_exact_match);
InitBVar($find_url_exact_match);
InitBVar($find_user_exact_match);
InitBVar($find_guest_id_exact_match);
InitBVar($find_session_id_exact_match);
InitBVar($find_ip_exact_match);
InitBVar($find_user_agent_exact_match);
InitBVar($find_country_exact_match);
InitBVar($find_region_exact_match);
InitBVar($find_city_exact_match);
InitBVar($find_stop_list_id_exact_match);
InitBVar($find_cookie_exact_match);


AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"ID"		=> $find_id,
	"SITE_ID"	=> $find_site_id,
	"URL"		=> $find_url,
	"URL_404"	=> $find_url_404,
	"USER"		=> $find_user,
	"NEW_GUEST"	=> $find_new_guest,
	"REGISTERED"	=> $find_registered,
	"GUEST_ID"	=> $find_guest_id,
	"SESSION_ID"	=> $find_session_id,
	"DATE_1"	=> $find_date1,
	"DATE_2"	=> $find_date2,
	"IP"		=> $find_ip,
	"USER_AGENT"	=> $find_user_agent,
	"COUNTRY"	=> $find_country,
	"COUNTRY_ID"	=> $find_country_id,
	"REGION"	=> $find_region,
	"CITY"		=> $find_city,
	"CITY_ID"	=> $find_city_id,
	"STOP"		=> $find_stop,
	"STOP_LIST_ID"	=> $find_stop_list_id,
	"COOKIE"	=> $find_cookie,

	"ID_EXACT_MATCH"		=> $find_id_exact_match,
	"URL_EXACT_MATCH"		=>  $find_url_exact_match,
	"USER_EXACT_MATCH"		=>  $find_user_exact_match,
	"GUEST_ID_EXACT_MATCH"		=>  $find_guest_id_exact_match,
	"SESSION_ID_EXACT_MATCH"	=> $find_session_id_exact_match,
	"IP_EXACT_MATCH"		=> $find_ip_exact_match,
	"USER_AGENT_EXACT_MATCH"	=> $find_user_agent_exact_match,
	"COUNTRY_EXACT_MATCH"		=> $find_country_exact_match,
	"COUNTRY_ID_EXACT_MATCH"	=> $find_country_exact_match,
	"REGION_EXACT_MATCH"		=> $find_region_exact_match,
	"CITY_EXACT_MATCH"		=> $find_city_exact_match,
	"CITY_ID_EXACT_MATCH"		=> $find_city_exact_match,
	"STOP_LIST_ID_EXACT_MATCH"	=> $find_stop_list_id_exact_match,
	"COOKIE_EXACT_MATCH"		=> $find_cookie_exact_match,
);

global $by, $order;

$rsData = CHit::GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_HIT_PAGES")));

$arHeaders = Array();

$arHeaders[] = array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true,);

$arHeaders[] = array("id"=>"SESSION_ID", "content"=>GetMessage("STAT_SESSION"), "sort"=>"s_session_id", "default"=>false,);
$arHeaders[] = array("id"=>"DATE_HIT", "content"=>GetMessage("STAT_DATE"), "sort"=>"s_date_hit", "default"=>true,);
$arHeaders[] = array("id"=>"USER_ID", "content"=>GetMessage("STAT_USER"), "sort"=>"s_user_id", "default"=>true,);
$arHeaders[] = array("id"=>"GUEST_ID", "content"=>GetMessage("STAT_GUEST_ID"), "sort"=>"s_guest_id", "default"=>false,);
$arHeaders[] = array("id"=>"IP", "content"=>GetMessage("STAT_IP"), "sort"=>"s_ip", "default"=>true,);
$arHeaders[] = array("id"=>"COUNTRY_ID", "content"=>GetMessage("STAT_COUNTRY"), "sort"=>"s_country_id", "default"=>true,);
$arHeaders[] = array("id"=>"REGION_NAME", "content"=>GetMessage("STAT_REGION"), "sort"=>"s_region_name", "default"=>false,);
$arHeaders[] = array("id"=>"CITY_ID", "content"=>GetMessage("STAT_CITY"), "sort"=>"s_country_id", "default"=>true,);
$arHeaders[] = array("id"=>"URL", "content"=>GetMessage("STAT_PAGE"), "sort"=>"s_url", "default"=>true,);
$arHeaders[] = array("id"=>"SITE_ID", "content"=>GetMessage("STAT_SITE"), "sort"=>"s_site_id", "default"=>false,);



$lAdmin->AddHeaders($arHeaders);

$arrUsers = array();

while($arRes = $rsData->NavNext(true, "f_"))
{

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if ($HIT_ID==$f_ID)
		$row->AddViewField("ID",'<span class="stat_attention">'.$f_ID.'</span>');

	$str = "";
	if ($f_USER_ID>0) :
		if($arRes["LOGIN"] <> '') :
			$str .= "[<a title=\"".GetMessage("STAT_EDIT_USER")."\" href=\"user_edit.php?lang=".LANG."&amp;ID=".$f_USER_ID."\">".$f_USER_ID."</a>] (".$f_LOGIN.") ".$f_USER_NAME."";
		else :
			if(!in_array($f_USER_ID, array_keys($arrUsers)))
			{
				$rsUser = CUser::GetByID($f_USER_ID);
				$arUser = $rsUser->GetNext();
				$LOGIN = $arUser["LOGIN"];
				$USER_NAME = $arUser["NAME"]." ".$arUser["LAST_NAME"];
				$arrUsers[$f_USER_ID]["USER_NAME"] = $USER_NAME;
				$arrUsers[$f_USER_ID]["LOGIN"] = $LOGIN;
			}
			else
			{
				$USER_NAME = $arrUsers[$f_USER_ID]["USER_NAME"];
				$LOGIN = $arrUsers[$f_USER_ID]["LOGIN"];
			}
			$str .= "[<a title=\"".GetMessage("STAT_EDIT_USER")."\" href=\"user_edit.php?lang=".LANG."&amp;ID=".$f_USER_ID."\">".$f_USER_ID."</a>]&nbsp;";
			if($LOGIN <> '') :
				$str .= "(".$LOGIN.") ".$USER_NAME."";
			endif;
		endif;
			$str .= ($f_USER_AUTH!="Y" ? "<br><nobr><span class=\"stat_notauth\">".GetMessage("STAT_NOT_AUTH")."</span></nobr>" : "");
	else:
		$str .= GetMessage("STAT_NOT_REGISTERED");
	endif;
	$row->AddViewField("USER_ID", $str);

	$row->AddViewField("IP", GetWhoisLink($f_IP));

	$str = "<a title=\"".GetMessage("STAT_VIEW_SESSION_LIST")."\" href=\"guest_list.php?lang=".LANG."&amp;find_id=".$f_GUEST_ID."&amp;find_id_exact_match=Y&amp;set_filter=Y\">".$f_GUEST_ID."</a>";
	$row->AddViewField("GUEST_ID", $str);

	$str = "<a href=\"session_list.php?lang=".LANG."&amp;find_id=".$f_SESSION_ID."&amp;find_id_exact_match=Y&amp;set_filter=Y\">".$f_SESSION_ID."</a>";
	$row->AddViewField("SESSION_ID", $str);

	if($f_CITY_ID <> '')
	{
		$row->AddViewField("CITY_ID", "[".$f_CITY_ID."] ".$f_CITY_NAME);
	}

	$row->AddViewField("", $str);
	$row->AddViewField("URL", StatAdminListFormatURL($arRes["URL"], array(
		"new_window" => false,
		"attention" => $f_URL_404 == "Y",
		"max_display_chars" => "default",
		"chars_per_line" => "default",
		"kill_sessid" => $STAT_RIGHT < "W",
	)));

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"list",
		"TEXT"=>GetMessage("STAT_DETAIL"),
		"ACTION"=>"javascript:CloseWaitWindow(); jsUtils.OpenWindow('hit_detail.php?lang=".LANG."&ID=".$f_ID."', '700', '550');",
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

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#"=>COption::GetOptionString("statistic","HIT_DAYS"))));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>

<tr>
	<td><?echo GetMessage("STAT_F_LOGIN")?>:</td>
	<td><input type="text" name="find_user" size="30" value="<?echo htmlspecialcharsbx($find_user)?>"><?=ShowExactMatchCheckbox("find_user")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>


<tr>
	<td><?echo GetMessage("STAT_F_ID")?>:</td>
	<td><input type="text" name="find_id" size="30" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_SESSION")?>:</td>
	<td><input type="text" name="find_session_id" size="30" value="<?echo htmlspecialcharsbx($find_session_id)?>"><?=ShowExactMatchCheckbox("find_session_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_DATE").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_GUEST_ID")?>:</td>
	<td><input type="text" name="find_guest_id" size="30" value="<?echo htmlspecialcharsbx($find_guest_id)?>"><?=ShowExactMatchCheckbox("find_guest_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td><?echo GetMessage("STAT_F_AUTH")?>:</td>
	<td><?
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
	<td><?echo GetMessage("STAT_F_COUNTRY")?>:</td>
	<td valign="center">
		[&nbsp;<input type="text" name="find_country_id" size="5" value="<?echo htmlspecialcharsbx($find_country_id)?>">&nbsp;]&nbsp;&nbsp;&nbsp;<input type="text" name="find_country" size="50" value="<?echo htmlspecialcharsbx($find_country)?>"><?echo ShowExactMatchCheckbox("find_country")?>&nbsp;<?echo ShowFilterLogicHelp()?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_REGION")?>:</td>
	<td><input type="text" name="find_region" size="50" value="<?echo htmlspecialcharsbx($find_region)?>"><?echo ShowExactMatchCheckbox("find_region")?>&nbsp;<?echo ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_CITY")?>:</td>
	<td valign="center">
		[&nbsp;<input type="text" name="find_city_id" size="5" value="<?echo htmlspecialcharsbx($find_city_id)?>">&nbsp;]&nbsp;&nbsp;&nbsp;<input type="text" name="find_city" size="50" value="<?echo htmlspecialcharsbx($find_city)?>"><?echo ShowExactMatchCheckbox("find_city")?>&nbsp;<?echo ShowFilterLogicHelp()?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_USER_AGENT")?>:</td>
	<td><input type="text" name="find_user_agent" size="30" value="<?echo htmlspecialcharsbx($find_user_agent)?>"><?=ShowExactMatchCheckbox("find_user_agent")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>Cookie:</td>
	<td><input type="text" name="find_cookie" size="30" value="<?echo htmlspecialcharsbx($find_cookie)?>"><?=ShowExactMatchCheckbox("find_cookie")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
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
	<td><?echo GetMessage("STAT_F_PAGE")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_url_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_url_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_url" size="33" value="<?echo htmlspecialcharsbx($find_url)?>"><?=ShowExactMatchCheckbox("find_url")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?=ShowLogicRadioBtn()?>
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
