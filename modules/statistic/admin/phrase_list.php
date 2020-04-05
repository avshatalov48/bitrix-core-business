<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$find_group = ($group_by=="S" || $group_by=="P") ? $group_by : "";

if($find_searcher_id=="1" && $find_searcher_exact_match=="Y")
	$internal = "1";
else
	$internal = "0";

$sTableID = "tbl_phrase_list_".$find_group."_".$internal;

if ($group_by=="S" || $group_by=="P")
{
	$field="QUANTITY";
	$dir="DESC";
}
else
{
	$field="ID";
	$dir="DESC";
}
$oSort = new CAdminSorting($sTableID, $field, $dir);

$lAdmin = new CAdminList($sTableID, $oSort);

$err_mess = "File: ".__FILE__."<br>Line: ";

$arSites = array();
$ref = $ref_id = array();
$rs = CSite::GetList(($v1="sort"), ($v2="asc"));
while ($ar = $rs->Fetch())
{
	$ref[] = $ar["ID"];
	$ref_id[] = $ar["ID"];
	$arSites[$ar["ID"]] = "[<a title=\"".GetMessage("STAT_SITE_EDIT")."\" href=\"/bitrix/admin/site_edit.php?LID=".$ar["ID"]."&lang=".LANGUAGE_ID."\">".$ar["ID"]."</a>]&nbsp;";
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

if($lAdmin->IsDefaultFilter())
{
	$set_filter = "Y";
}

$arrExactMatch = array(
	"ID_EXACT_MATCH"		=> "find_id_exact_match",
	"SESSION_ID_EXACT_MATCH"	=> "find_session_id_exact_match",
	"REFERER_ID_EXACT_MATCH"	=> "find_referer_id_exact_match",
	"SEARCHER_ID_STR_EXACT_MATCH"	=> "find_searcher_exact_match",
	"SEARCHER_EXACT_MATCH"		=> "find_searcher_exact_match",
	"PHRASE_EXACT_MATCH"		=> "find_phrase_exact_match",
	"TO_EXACT_MATCH"		=> "find_to_exact_match"
	);

$arFilterFields = Array(
	"find_id",
	"find_session_id",
	"find_searcher_id",
	"find_searcher_id_str",
	"find_searcher",
	"find_referer_id",
	"find_date1",
	"find_date2",
	"find_phrase",
	"find_to",
	"find_site_id",
	"find_to_404",
);

if ($group_by=="S" || $group_by=="P" || $group_by=="none")
{
	InitFilterEx(array("group_by"), $sTableID."_settings", "set");
	InitFilterEx(array("group_by"), $sTableID."_settings", "get");
	$set_filter="Y";
}
InitFilterEx(array("group_by"), $sTableID."_settings", "get");

$arFilterFields = array_merge($arFilterFields, array_values($arrExactMatch));

$lAdmin->InitFilter($arFilterFields);

InitBVarFromArr($arrExactMatch);

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"ID"			=> $find_id,
	"SESSION_ID"		=> $find_session_id,
	"SEARCHER_ID"		=> $find_searcher_id,
	"SEARCHER_ID_STR"	=> $find_searcher_id_str,
	"SEARCHER"		=> $find_searcher,
	"REFERER_ID"		=> $find_referer_id,
	"DATE1"			=> $find_date1,
	"DATE2"			=> $find_date2,
	"PHRASE"		=> $find_phrase,
	"TO"			=> $find_to,
	"TO_404"		=> $find_to_404,
	"SITE_ID"		=> $find_site_id,
	"GROUP"			=> $find_group
	);
$arFilter = array_merge($arFilter, array_convert_name_2_value($arrExactMatch));

//////////////////////////////////////////////////////////////////////
// list init

$rsData = CPhrase::GetList($by, $order, $arFilter, $is_filtered, $total, $grby, $max);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation setup
$lAdmin->NavText($rsData->GetNavPrint(($grby=="P") ? GetMessage("STAT_PHRASES") : GetMessage("STAT_PHRASE_PAGES")));

$aContext=array();

$lAdmin->AddAdminContextMenu($aContext);
if ($grby=="S")
{
	$headers=array(
		array("id"=>"FAKE_NUM",  "content"=>GetMessage("STAT_NUM"),"default"=>true, "options"=>array("width"=>"0")),
		array("id"=>"SEARCHER_ID", "content"=>GetMessage("STAT_SERVER"), "sort"=>"s_name", "default"=>true),
		array("id"=>"QUANTITY", "content"=>GetMessage("STAT_QUANTITY"), "sort"=>"s_quantity", "default"=>true),
		array("id"=>"AVERAGE_HITS", "content"=>GetMessage("STAT_AVERAGE_HITS"), "sort"=>"s_average_hits", "default"=>true),
		array("id"=>"FAKE_GRAPH", "content"=>GetMessage("STAT_GRAPH"), "default"=>true),
	);
}
elseif ($grby=="P")
{
	$headers=array(
		array("id"=>"FAKE_NUM", "content"=>GetMessage("STAT_NUM"), "default"=>true),
		array("id"=>"PHRASE", "content"=>GetMessage("STAT_PHRASE"), "sort"=>"s_phrase", "default"=>true),
		array("id"=>"QUANTITY", "content"=>GetMessage("STAT_QUANTITY"), "sort"=>"s_quantity", "default"=>true),
		array("id"=>"FAKE_GRAPH", "content"=>GetMessage("STAT_GRAPH"), "default"=>true),
	);
}
else
{
	$headers=array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
		array("id"=>"PHRASE", "content"=>GetMessage("STAT_PHRASE"), "sort"=>"s_phrase", "default"=>true),
		array("id"=>"SEARCHER_ID", "content"=>GetMessage("STAT_SERVER"), "sort"=>"s_searcher_id", "default"=>true),
		array("id"=>"DATE_HIT", "content"=>GetMessage("STAT_DATE_INSERT"), "sort"=>"s_date_hit", "default"=>true),
		array("id"=>"PAGE_TO", "content"=>GetMessage("STAT_PAGE_TO"), "sort"=>"s_url_to", "default"=>true),
		array("id"=>"REFERER_ID", "content"=>GetMessage("STAT_REFERER"), "sort"=>"s_referer_id", "default"=>true),
		array("id"=>"SESSION_ID", "content"=>GetMessage("STAT_SESSION"), "sort"=>"s_session_id", "default"=>true),
	);
}
$lAdmin->AddHeaders($headers);

$i=0;
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("FAKE_NUM",($rsData->NavPageNomer-1)*$rsData->NavPageSize + (++$i));
	$row->AddViewField("SEARCHER_ID",
		(($f_SEARCHER_ID>1)?"[<a title=\"".GetMessage("STAT_SEARCHER_INDEXING")."\" href=\"searcher_list.php?lang=".LANGUAGE_ID."&find_id=$f_SEARCHER_ID&find_id_exact_match=Y&set_filter=Y\">$f_SEARCHER_ID</a>]":"[$f_SEARCHER_ID]").
		"&nbsp;<a title=\"".GetMessage("STAT_SEARCHER_PHRASES")."\" href=\"?lang=".LANGUAGE_ID."&group_by=none".(intval($f_SEARCHER_ID)>1? "&menu_item_id=1": "")."&find_searcher_id=$f_SEARCHER_ID&find_searcher_id_exact_match=Y&set_filter=Y\">".((intval($f_SEARCHER_ID)>1) ? $f_SEARCHER_NAME : "&lt;internal&gt;")."</a>");

	if ($grby=="P")
		$row->AddViewField("PHRASE", "<a title=\"".GetMessage("STAT_PHRASE_SORT")."\" href=\"?lang=".LANGUAGE_ID."&find_phrase=".urlencode(htmlspecialcharsback($f_PHRASE))."&find_phrase_exact_match=Y&set_filter=Y&group_by=none&menu_item_id=1\">$f_PHRASE</a>");

	$row->AddViewField("QUANTITY", $f_QUANTITY."&nbsp;(".sprintf("%01.2f", $f_C_PERCENT)."%)");
	$row->AddViewField("AVERAGE_HITS", sprintf("%01.2f",$f_AVERAGE_HITS));

	if ($max>0)
	{
		$w=round(100*$f_QUANTITY/$max);
		$row->AddViewField("FAKE_GRAPH", "<img src=\"/bitrix/images/statistic/votebar.gif\" style=width:".(($w==0) ? "0" : $w."%")." height=10 border=0 alt=\"\">");
	}
	$row->AddViewField("PAGE_TO", $arSites[$f_SITE_ID].' '.StatAdminListFormatURL($arRes["URL_TO"], array(
		"title" => GetMessage("STAT_LINK_OPEN"),
		"new_window" => false,
		"chars_per_line" => 100,
		"kill_sessid" => $STAT_RIGHT < "W",
	)));
	$row->AddViewField("REFERER_ID", "<a title=\"".GetMessage("STAT_REFERRING_SITE")."\" href=\"referer_list.php?lang=".LANGUAGE_ID."&find_id=$f_REFERER_ID&find_id_exact_match=Y&set_filter=Y\">$f_REFERER_ID</a>&nbsp;");
	$row->AddViewField("SESSION_ID", "<a title=\"".GetMessage("STAT_FIND_SESSION")."\" href=\"session_list.php?lang=".LANGUAGE_ID."&find_id=$f_SESSION_ID&find_id_exact_match=Y&set_filter=Y\">$f_SESSION_ID</a>&nbsp;");
}

$lAdmin->AddFooter(array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	)
);

$lAdmin->CheckListMode();
/***************************************************************************
			HTML form
****************************************************************************/
$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#" => $STORED_DAYS)));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<a name="tb"></a>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("STAT_FL_ID"),
		GetMessage("STAT_FL_SEARCHER"),
		GetMessage("STAT_FL_DATE"),
		GetMessage("STAT_FL_PAGE"),
		GetMessage("STAT_FL_ID_REF"),
		GetMessage("STAT_FL_ID_SESS"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td nowrap><b><?echo GetMessage("STAT_F_SEARCH_PHRASE")?></b></td>
	<td><input type="text" name="find_phrase" size="47" value="<?echo htmlspecialcharsbx($find_phrase)?>"><?=ShowExactMatchCheckbox("find_phrase")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ID")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign=top>
	<td nowrap><?echo GetMessage("STAT_F_SEARCH_SYSTEM")?></td>
	<td><?
		$z = CSearcher::GetDropDownList();
		echo SelectBox("find_searcher_id",$z,GetMessage("MAIN_ALL"), htmlspecialcharsbx($find_searcher_id));
		?><br>
		ID: <input type="text" name="find_searcher_id_str" size="20" value="<?echo htmlspecialcharsbx($find_searcher_id_str)?>"> <?=GetMessage("STAT_NAME")?>: <input type="text" name="find_searcher" size="40" value="<?echo htmlspecialcharsbx($find_searcher)?>"><?=ShowExactMatchCheckbox("find_searcher")?> <?=ShowFilterLogicHelp()?>
	</td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_DATE").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("STAT_F_PAGE_TO")?></td>
	<td><?
		echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_to_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_to_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_to" size="33" value="<?echo htmlspecialcharsbx($find_to)?>"><?=ShowExactMatchCheckbox("find_to")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("STAT_F_REFERER")?></td>
	<td><input type="text" name="find_referer_id" size="47" value="<?echo htmlspecialcharsbx($find_referer_id)?>"><?=ShowExactMatchCheckbox("find_referer_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("STAT_F_SESSION")?></td>
	<td><input type="text" name="find_session_id" size="47" value="<?echo htmlspecialcharsbx($find_session_id)?>"><?=ShowExactMatchCheckbox("find_session_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<input type="hidden" name="group_by" value="<?=$find_group?>">
<?if(intval($menu_item_id)>0):?>
<input type="hidden" name="menu_item_id" value="<?=intval($menu_item_id)?>">
<?endif?>
<?
ShowLogicRadioBtn();

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
