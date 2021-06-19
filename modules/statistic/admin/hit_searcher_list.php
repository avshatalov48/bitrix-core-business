<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_hit_searcher_list";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);


InitSorting();
$err_mess = "File: ".__FILE__."<br>Line: ";

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

$arrExactMatch = array(
	"ID_EXACT_MATCH"			=> "find_id_exact_match",
	"SEARCHER_EXACT_MATCH"		=> "find_searcher_exact_match",
	"URL_EXACT_MATCH"			=> "find_url_exact_match",
	"USER_AGENT_EXACT_MATCH"	=> "find_user_agent_exact_match",
	"IP_EXACT_MATCH"			=> "find_ip_exact_match"
	);
$FilterArr = Array(
	"find_id",
	"find_url",
	"find_url_404",
	"find_site_id",
	"find_searcher",
	"find_searcher_id",
	"find_date1",
	"find_date2",
	"find_ip",
	"find_user_agent");
$arFilterFields = array_merge($FilterArr, array_values($arrExactMatch));
$lAdmin->InitFilter($arFilterFields);
InitBVarFromArr($arrExactMatch);

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"ID"			=> $find_id,
	"URL"			=> $find_url,
	"SITE_ID"		=> $find_site_id,
	"URL_404"		=> $find_url_404,
	"SEARCHER"		=> $find_searcher,
	"SEARCHER_ID"	=> $find_searcher_id,
	"DATE1"			=> $find_date1,
	"DATE2"			=> $find_date2,
	"IP"			=> $find_ip,
	"USER_AGENT"	=> $find_user_agent
	);
$arFilter = array_merge($arFilter, array_convert_name_2_value($arrExactMatch));

global $by, $order;

$rsData = CSearcherHit::GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_HIT_PAGES")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
	array("id"=>"DATE_HIT", "content"=>GetMessage("STAT_DATE"), "sort"=>"s_date_hit", "default"=>true),
	array("id"=>"SEARCHER_ID", "content"=>GetMessage("STAT_SEARCHER"), "sort"=>"s_searcher_id", "default"=>true),
	array("id"=>"USER_AGENT", "content"=>GetMessage("STAT_USER_AGENT"), "sort"=>"s_user_agent", "default"=>true),
	array("id"=>"IP", "content"=>GetMessage("STAT_IP"), "sort"=>"s_ip", "default"=>true),
	array("id"=>"SITE_ID", "content"=>GetMessage("STAT_PAGE"), "sort"=>"s_url", "default"=>true),
	)
);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$txt = "[<a title=\"".GetMessage("STAT_SRCH_LIST")."\" href=\"searcher_list.php?lang=".LANGUAGE_ID."&amp;find_id=$f_SEARCHER_ID&amp;find_id_exact_match=Y&amp;set_filter=Y\">$f_SEARCHER_ID</a>]&nbsp;$f_SEARCHER_NAME";
	$row->AddViewField("SEARCHER_ID", $txt);

	$row->AddViewField("USER_AGENT", TxtToHTML($f_USER_AGENT));

	$arr = explode(".",$f_IP);
	$txt = GetWhoisLink($f_IP)." [<a title=\"".GetMessage("STAT_ADD_TO_STOPLIST_TITLE")."\" href=\"stoplist_edit.php?lang=".LANGUAGE_ID."&amp;net1=$arr[0]&amp;net2=$arr[1]&amp;net3=$arr[2]&amp;net4=$arr[3]\">".GetMessage("STAT_STOP")."</a>]";
	$row->AddViewField("IP", $txt);

	$row->AddViewField("SITE_ID", '['.$arSites[$f_SITE_ID].'] '.StatAdminListFormatURL($arRes["URL"], array(
		"title" => GetMessage("STAT_LINK_OPEN"),
		"new_window" => false,
		"max_display_chars" => "default",
		"chars_per_line" => "default",
		"kill_sessid" => $STAT_RIGHT < "W",
	)));
}

$lAdmin->AddFooter(array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	)
);

$lAdmin->AddAdminContextMenu();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#"=>COption::GetOptionString("statistic","SEARCHER_HIT_DAYS"))));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
<a name="tb"></a>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("STAT_FL_ID"),
		GetMessage("STAT_FL_DATE"),
		GetMessage("STAT_FL_PAGE"),
		GetMessage("STAT_FL_UA"),
		GetMessage("STAT_FL_IP"),
		GetMessage("STAT_FL_LOGIC"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td nowrap><b><?echo GetMessage("STAT_F_SEARCH_SYSTEM")?></b></td>
	<td><input type="text" name="find_searcher" size="67" value="<?echo htmlspecialcharsbx($find_searcher)?>"><?=ShowExactMatchCheckbox("find_searcher")?>&nbsp;<?=ShowFilterLogicHelp()?><br><?echo SelectBox("find_searcher_id", CSearcher::GetDropDownList(), GetMessage("MAIN_ALL"), htmlspecialcharsbx($find_searcher_id));?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ID")?></td>
	<td><input type="text" name="find_id" size="67" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_DATE").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_PAGE")?></td>
	<td><?
		echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_url_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_url_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_url" size="34" value="<?echo htmlspecialcharsbx($find_url)?>"><?=ShowExactMatchCheckbox("find_url")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_USER_AGENT")?></td>
	<td><input type="text" name="find_user_agent" size="67" value="<?echo htmlspecialcharsbx($find_user_agent)?>"><?=ShowExactMatchCheckbox("find_user_agent")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_IP")?></td>
	<td><input type="text" name="find_ip" size="67" value="<?echo htmlspecialcharsbx($find_ip)?>"><?=ShowExactMatchCheckbox("find_ip")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?=ShowLogicRadioBtn()?>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()));
$oFilter->End();
#############################################################
?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
