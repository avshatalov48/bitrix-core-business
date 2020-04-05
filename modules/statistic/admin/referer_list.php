<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

// Unique Table ID
$sTableID = "tbl_referer_list";

if(isset($group_by))
{
	if($group_by!="S" && $group_by!="U")
		$group_by="none";
}
else
	$group_by=false;//no setting (will be read later from session)

// Sort init
if ($group_by=="S" || $group_by=="U")
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
// List init
$lAdmin = new CAdminList($sTableID, $oSort);

$arSites = array();
$ref = $ref_id = array();
$rs = CSite::GetList(($v1="sort"), ($v2="asc"));
while ($ar = $rs->Fetch())
{
	$ref[] = $ar["ID"];
	$ref_id[] = $ar["ID"];
	$arSites[$ar["ID"]] = "[<a title=\"".GetMessage("STAT_EDIT_SITE")."\" href=\"/bitrix/admin/site_edit.php?LID=".$ar["ID"]."&lang=".LANGUAGE_ID."\">".$ar["ID"]."</a>]&nbsp;";
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

/*if($lAdmin->IsDefaultFilter())
{
	$find_group = "S";
	$set_filter = "Y";
}*/

$arrExactMatch = array(
	"ID_EXACT_MATCH"		=> "find_id_exact_match",
	"SESSION_ID_EXACT_MATCH"	=> "find_session_id_exact_match",
	"FROM_EXACT_MATCH"		=> "find_from_exact_match",
	"FROM_PROTOCOL_EXACT_MATCH"	=> "find_from_detail_exact_match",
	"FROM_DOMAIN_EXACT_MATCH"	=> "find_from_detail_exact_match",
	"FROM_PAGE_EXACT_MATCH"		=> "find_from_detail_exact_match",
	"TO_EXACT_MATCH"		=> "find_to_exact_match",
	);
$FilterArr = Array(
	"find_id",
	"find_session_id",
	"find_date1",
	"find_date2",
	"find_from",
	"find_from_protocol",
	"find_from_domain",
	"find_from_url",
	"find_site_id",
	"find_to",
	"find_to_404",
	"find_group");
$arFilterFields = array_merge($FilterArr, array_values($arrExactMatch));

//Restore & Save settings (windows registry like)
$arSettings = array ("saved_group_by");
InitFilterEx($arSettings, $sTableID."_settings", "get");
if($group_by===false)//Restore saved setting
	$group_by=$saved_group_by;
elseif($saved_group_by!=$group_by)//Set if changed
	$saved_group_by=$group_by;
InitFilterEx($arSettings, $sTableID."_settings", "set");

$lAdmin->InitFilter($arFilterFields);
$find_group = ($group_by=="S" || $group_by=="U") ? $group_by : "";

InitBVarFromArr($arrExactMatch);

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"ID"			=> $find_id,
	"SESSION_ID"		=> $find_session_id,
	"DATE1"			=> $find_date1,
	"DATE2"			=> $find_date2,
	"FROM"			=> $find_from,
	"FROM_PROTOCOL"		=> $find_from_protocol,
	"FROM_DOMAIN"		=> $find_from_domain,
	"FROM_PAGE"		=> $find_from_url,
	"SITE_ID"		=> $find_site_id,
	"TO"			=> $find_to,
	"TO_404"		=> $find_to_404,
	"GROUP"			=> $find_group
	);
$arFilter = array_merge($arFilter, array_convert_name_2_value($arrExactMatch));

//////////////////////////////////////////////////////////////////////
// Quering data

$rsData = CReferer::GetList($by, $order, $arFilter, $is_filtered, $total, $grby, $max);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$aContext = array();
$aContext[] =
	array(
		"TEXT"=>GetMessage("STAT_F_GROUP"),
		"MENU"=>array(
			array(
				"TEXT"=>GetMessage("STAT_NO_GROUP"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "group_by=none"),
				"ICON"=>(($grby!="S" && $grby!="U")?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_BY_SERVER"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "group_by=S"),
				"ICON"=>($grby=="S"?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_BY_LINK"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "group_by=U"),
				"ICON"=>($grby=="U"?"checked":""),
			),
		),
	);


$lAdmin->AddAdminContextMenu($aContext);
// Navigation setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_REF_PAGES")));

if ($grby=="S")
{
	$headers=array(
		array("id"=>"FAKE_NUM", "content"=>GetMessage("STAT_NUM"), "default"=>true, "options"=>array("width"=>"0"), "align"=>"right"),
		array("id"=>"URL_FROM", "content"=>GetMessage("STAT_URL_FROM"), "sort"=>"s_url_from", "default"=>true),
		array("id"=>"QUANTITY", "content"=>GetMessage("STAT_QUANTITY"), "sort"=>"s_quantity", "default"=>true, "align"=>"right"),
		array("id"=>"PERCENT", "content"=>GetMessage("STAT_PERCENT"), "sort"=>"s_quantity", "default"=>true, "align"=>"right"),
		array("id"=>"AVERAGE_HITS", "content"=>GetMessage("STAT_AVERAGE_HITS"), "sort"=>"s_average_hits", "default"=>true, "align"=>"right"),
		array("id"=>"FAKE_GRAPH", "content"=>GetMessage("STAT_GRAPH"), "default"=>true),
	);
}
elseif ($grby=="U")
{
	$headers=array(
		array("id"=>"FAKE_NUM",  "content"=>GetMessage("STAT_NUM"),"default"=>true, "options"=>array("width"=>"0"), "align"=>"right"),
		array("id"=>"URL_FROM", "content"=>GetMessage("STAT_URL_FROM"), "sort"=>"s_url_from", "default"=>true),
		array("id"=>"QUANTITY", "content"=>GetMessage("STAT_QUANTITY"), "sort"=>"s_quantity", "default"=>true, "align"=>"right"),
		array("id"=>"PERCENT", "content"=>GetMessage("STAT_PERCENT"), "sort"=>"s_quantity", "default"=>true, "align"=>"right"),
		array("id"=>"AVERAGE_HITS", "content"=>GetMessage("STAT_AVERAGE_HITS"), "sort"=>"s_average_hits", "default"=>true, "align"=>"right"),
		array("id"=>"FAKE_GRAPH", "content"=>GetMessage("STAT_GRAPH"), "default"=>true),
	);
}
else
{
	$headers=array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true, "align"=>"right"),
		array("id"=>"URL_FROM", "content"=>GetMessage("STAT_URL_FROM"), "sort"=>"s_url_from", "default"=>true),
		array("id"=>"URL_TO", "content"=>GetMessage("STAT_PAGE_TO"), "sort"=>"s_url_to", "default"=>true),
		array("id"=>"DATE_HIT", "content"=>GetMessage("STAT_DATE_HIT"), "sort"=>"s_date_hit", "default"=>true),
		array("id"=>"SESSION_ID", "content"=>GetMessage("STAT_SESSION"), "sort"=>"s_session_id", "default"=>true, "align"=>"right"),
	);
}


$lAdmin->AddHeaders($headers);
$i=0;
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("FAKE_NUM",($rsData->NavPageNomer-1)*$rsData->NavPageSize + (++$i));

	if (strlen($f_URL_FROM) >=55)
	{
		$uri_type="";
		$stripped=$f_URL_FROM;

		if (preg_match("#^(http|ftp|https|news)://(\S+)$#i", $f_URL_FROM, $match));
		{
			$uri_type = $match[1];
			$stripped = $match[2];
		}

		$txt = '<span class="'.($f_URL_404=="Y" ? "stat_attention" : "").'">'.$uri_type.'://'.substr($stripped, 0, 30).'...'.substr($stripped, -10).'</span>';
	}
	else
		$txt = '<span class="'.($f_URL_404=="Y" ? "stat_attention" : "").'">'.$f_URL_FROM.'</span>';

	if ($grby=="S")
		$row->AddViewField("URL_FROM",'<a title="'.GetMessage("STAT_GO_LINK").'" href="http://'.$f_URL_FROM.'">&raquo;</a>&nbsp;<a href="'.htmlspecialcharsbx('referer_list.php?find_from_domain='.urlencode(htmlspecialcharsback($f_URL_FROM)).'&group_by=U&set_filter=Y').'">'.$txt.'</a>');
	elseif ($grby=="U")
		$row->AddViewField("URL_FROM",'<a title="'.GetMessage("STAT_GO_LINK").'" href="'.$f_URL_FROM.'">&raquo;</a>&nbsp;<a href="'.htmlspecialcharsbx('referer_list.php?find_from='.urlencode(htmlspecialcharsback($f_URL_FROM)).'&group_by=none&set_filter=Y').'">'.$txt.'</a>');
	else
		$row->AddViewField("URL_FROM", "<a title=\"".GetMessage("STAT_GO_LINK")."\" href=\"".$f_URL_FROM."\">$txt</a>");

	$row->AddViewField("URL_TO", $arSites[$f_SITE_ID].' '.StatAdminListFormatURL($arRes["URL_TO"], array(
		"title" => GetMessage("STAT_GO_LINK"),
		"new_window" => true,
		"chars_per_line" => 100,
		"kill_sessid" => $STAT_RIGHT < "W",
	)));
	$row->AddViewField("SESSION_ID", "<a title=\"".GetMessage("STAT_SESS_OPEN")."\" href=\"session_list.php?lang=".LANGUAGE_ID."&find_id=$f_SESSION_ID&find_id_exact_match=Y&set_filter=Y\">$f_SESSION_ID</a></td>");

	$row->AddViewField("QUANTITY", "$f_QUANTITY");
	$row->AddViewField("PERCENT", "$f_C_PERCENT");

	if ($max>0)
	{
		$w=round(100*$f_QUANTITY/$max);
		$row->AddViewField("FAKE_GRAPH", "<img src=\"/bitrix/images/statistic/votebar.gif\" style=width:".(($w==0) ? "0" : $w."%")." height=10 border=0 alt=\"\">");
	}
}

// list footer
$lAdmin->AddFooter(array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	)
);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#" => $STORED_DAYS)));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/***************************************************************************
				HTML form
****************************************************************************/
?>

<a name="tb"></a>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("STAT_FL_SID"),
		GetMessage("STAT_F_FROM"),
		GetMessage("STAT_F_PROTOCOL"),
		GetMessage("STAT_F_DOMAIN"),
		GetMessage("STAT_F_PAGE"),
		GetMessage("STAT_FL_URL"),
		GetMessage("STAT_FL_PRD"),
		GetMessage("STAT_FL_LGC"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?echo GetMessage("STAT_F_ID")?></b></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_SESSION")?></td>
	<td><input type="text" name="find_session_id" size="47" value="<?echo htmlspecialcharsbx($find_session_id)?>"><?=ShowExactMatchCheckbox("find_session_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_FROM")?>:</td>
	<td><input type="text" name="find_from" size="47" value="<?echo htmlspecialcharsbx($find_from)?>"><?=ShowExactMatchCheckbox("find_from")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("STAT_F_PROTOCOL")?>:</td>
	<td><input type="text" name="find_from_protocol" size="47" value="<?echo htmlspecialcharsbx($find_from_protocol)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("STAT_F_DOMAIN")?>:</td>
	<td><input type="text" name="find_from_domain" size="47" value="<?echo htmlspecialcharsbx($find_from_domain)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("STAT_F_PAGE")?>:</td>
	<td><input type="text" name="find_from_url" size="47" value="<?echo htmlspecialcharsbx($find_from_url)?>"><?=ShowExactMatchCheckbox("find_from_detail")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("STAT_F_TO")?></td>
	<td><?
		echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_to_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_to_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_to" size="34" value="<?echo htmlspecialcharsbx($find_to)?>"><?=ShowExactMatchCheckbox("find_to")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></td>
</tr>
<?=ShowLogicRadioBtn();

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
