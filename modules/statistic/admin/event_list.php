<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
$statDB = CDatabase::GetModuleConnection('statistic');
$sTableID = "tbl_event_list";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arSites = array();
$ref = $ref_id = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
{
	$ref[] = $ar["ID"];
	$ref_id[] = $ar["ID"];
	$arSites[$ar["ID"]] = "[<a class=\"tablebodylink\" href=\"/bitrix/admin/site_edit.php?LID=".$ar["ID"]."&lang=".LANGUAGE_ID."\">".$ar["ID"]."</a>]&nbsp;";
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

if (is_array($ARR_DELETE) && $STAT_RIGHT>="W" && check_bitrix_sessid())
{
	foreach ($ARR_DELETE as $del_id)
	{
		$del_id = intval($del_id);
		if ($del_id>0) CStatEvent::Delete($del_id);
	}
}
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


$arrExactMatch = array(
	"ID_EXACT_MATCH"		=> "find_id_exact_match",
	"EVENT_ID_EXACT_MATCH"		=> "find_event_id_exact_match",
	"EVENT_NAME_EXACT_MATCH"	=> "find_event_name_exact_match",
	"EVENT1_EXACT_MATCH"		=> "find_event12_exact_match",
	"EVENT2_EXACT_MATCH"		=> "find_event12_exact_match",
	"EVENT3_EXACT_MATCH"		=> "find_event3_exact_match",
	"REDIRECT_URL_EXACT_MATCH"	=> "find_redirect_url_exact_match",
	"SESSION_ID_EXACT_MATCH"	=> "find_session_id_exact_match",
	"GUEST_ID_EXACT_MATCH"		=> "find_guest_id_exact_match",
	"ADV_ID_EXACT_MATCH"		=> "find_adv_id_exact_match",
	"HIT_ID_EXACT_MATCH"		=> "find_hit_id_exact_match",
	"COUNTRY_ID_EXACT_MATCH"	=> "find_country_exact_match",
	"REFERER_URL_EXACT_MATCH"	=> "find_referer_url_exact_match",
	"URL_EXACT_MATCH"		=> "find_url_exact_match",
	"COUNTRY_EXACT_MATCH"		=> "find_country_exact_match",
	);
$FilterArr = Array(
	"find",
	"find_type",
	"find_id",
	"find_event_id",
	"find_event_name",
	"find_event1",
	"find_event2",
	"find_event3",
	"find_date1",
	"find_date2",
	"find_site_id",
	"find_redirect_url",
	"find_session_id",
	"find_money1",
	"find_money2",
	"find_currency",
	"find_guest_id",
	"find_adv_id",
	"find_adv_back",
	"find_hit_id",
	"find_referer_site_id",
	"find_referer_url",
	"find_url",
	"find_country",
	"find_country_id",
	);
$FilterArr = array_merge($FilterArr, array_values($arrExactMatch));

$lAdmin->InitFilter($FilterArr);

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"ID"			=> $find_id,
	"EVENT_ID"		=> $find_event_id,
	"EVENT_NAME"		=> $find_event_name,
	"EVENT1"		=> ($find!="" && $find_type == "event1"? $find:$find_event1),
	"EVENT2"		=> ($find!="" && $find_type == "event2"? $find:$find_event2),
	"EVENT3"		=> $find_event3,
	"DATE1"			=> $find_date1,
	"DATE2"			=> $find_date2,
	"MONEY1"		=> (($STAT_RIGHT>"M") ? $find_money1 : ""),
	"MONEY2"		=> (($STAT_RIGHT>"M") ? $find_money2 : ""),
	"CURRENCY"		=> $find_currency,
	"SESSION_ID"		=> $find_session_id,
	"GUEST_ID"		=> $find_guest_id,
	"ADV_ID"		=> ($find!="" && $find_type == "adv_id"? $find:$find_adv_id),
	"ADV_BACK"		=> $find_adv_back,
	"HIT_ID"		=> $find_hit_id,
	"REFERER_URL"		=> $find_referer_url,
	"URL"			=> $find_url,
	"REDIRECT_URL"		=> $find_redirect_url,
	"COUNTRY"		=> $find_country,
	"COUNTRY_ID"		=> $find_country_id,
	"SITE_ID"		=> $find_site_id,
	"REFERER_SITE_ID"	=> $find_referer_site_id,
);
$arFilter = array_merge($arFilter, array_convert_name_2_value($arrExactMatch));

if(($arID = $lAdmin->GroupAction()) && $STAT_RIGHT=="W")
{
	if($_REQUEST['action_target'] == "selected")
	{
		$cData = new CStatEvent;
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
			if(!CStatEvent::Delete($ID))
			{
				$statDB->Rollback();
				$lAdmin->AddGroupError(GetMessage("STAT_DELETE_ERROR"), $ID);
			}
			$statDB->Commit();
			break;
		}
	}
}

$cData = new CStatEvent;

global $by, $order;

$rsData = $cData->GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_EVENT_PAGES")));

$arHeaders = array(
	array(	"id"		=>"ID",
		"content"	=>"ID",
		"sort"		=>"s_id",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"TYPE_ID",
		"content"	=>GetMessage("STAT_EVENT"),
		"sort"		=>"s_event_id",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"EVENT1",
		"content"	=>"event1",
		"sort"		=>"",
		"default"	=>true,
	),
	array(	"id"		=>"EVENT2",
		"content"	=>"event2",
		"sort"		=>"",
		"default"	=>true,
	),
	array(	"id"		=>"EVENT3",
		"content"	=>"event3",
		"sort"		=>"",
	),
	array(	"id"		=>"DATE_ENTER",
		"content"	=>GetMessage("STAT_DATE"),
		"sort"		=>"s_date_enter",
		"default"	=>true,
	),
	array(	"id"		=>"SESSION_ID",
		"content"	=>GetMessage("STAT_SESSION"),
		"sort"		=>"s_session_id",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"GUEST_ID",
		"content"	=>GetMessage("STAT_GUEST"),
		"sort"		=>"s_guest_id",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"COUNTRY_ID",
		"content"	=>GetMessage("STAT_COUNTRY"),
		"sort"		=>"s_country_id",
		"default"	=>true,
	),
	array(	"id"		=>"ADV_ID",
		"content"	=>GetMessage("STAT_ADV"),
		"sort"		=>"s_adv_id",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"HIT_ID",
		"content"	=>GetMessage("STAT_HIT"),
		"sort"		=>"s_hit_id",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"SITE_ID",
		"content"	=>GetMessage("STAT_SITE"),
		"sort"		=>"s_site_id",
		"default"	=>true,
	),
	array(	"id"		=>"REFERER_URL",
		"content"	=>GetMessage("STAT_REFERER_URL"),
		"sort"		=>"s_referer_url",
	),
	array(	"id"		=>"URL",
		"content"	=>GetMessage("STAT_URL"),
		"sort"		=>"s_url",
	),
	array(	"id"		=>"REDIRECT_URL",
		"content"	=>GetMessage("STAT_REDIRECT_URL"),
		"sort"		=>"s_redirect_url",
	),
);
if($STAT_RIGHT>"M")
	$arHeaders[]=
		array(	"id"		=>"MONEY",
			"content"	=>GetMessage("STAT_MONEY").($view_currency <> ''?"<br>(".htmlspecialcharsEx($view_currency).")":""),
			"sort"		=>"s_money",
			"align"		=>"right",
			"default"	=>true,
		);
$lAdmin->AddHeaders($arHeaders);

$thousand_sep = ($_REQUEST["mode"] == "excel")? "": "&nbsp;";

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if($f_TYPE_ID>0):
		$strHTML='<a href="event_type_list.php?lang='.LANG.'&amp;find_id='.$f_TYPE_ID.'&amp;find_id_exact_match=Y&amp;set_filter=Y" title="ID = '.$f_TYPE_ID.($f_EVENT1 <> ''?"\nevent1 = ".$f_EVENT1 : "").($f_EVENT2 <> ''?"\nevent2 = ".$f_EVENT2:"").($f_NAME <> ''?"\n".GetMessage("STAT_NAME")." ".$f_NAME:"").($f_DESCRIPTION <> ''?"\n".GetMessage("STAT_DESCRIPTION")." ".$f_DESCRIPTION:"").'">'.$f_TYPE_ID.'</a>';
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("TYPE_ID", $strHTML);

	$f_SESSION_ID=intval($f_SESSION_ID);
	if($f_SESSION_ID>0):
		$strHTML='<a href="session_list.php?lang='.LANG.'&amp;find_id='.$f_SESSION_ID.'&amp;find_id_exact_match=Y&amp;set_filter=Y">'.$f_SESSION_ID.'</a>';
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("SESSION_ID", $strHTML);

	$f_GUEST_ID=intval($f_GUEST_ID);
	if($f_GUEST_ID>0):
		$strHTML='<a href="guest_list.php?lang='.LANG.'&amp;find_id='.$f_GUEST_ID.'&amp;find_id_exact_match=Y&amp;set_filter=Y">'.$f_GUEST_ID.'</a>';
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("GUEST_ID", $strHTML);

	if($f_COUNTRY_ID <> ''):
		$strHTML="[".$f_COUNTRY_ID."] ".$f_COUNTRY_NAME;
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("COUNTRY_ID", $strHTML);

	$f_ADV_ID=intval($f_ADV_ID);
	if($f_ADV_ID>0):
		$strHTML='<a href="adv_list.php?lang='.LANG.'&amp;find_id='.$f_ADV_ID.'&amp;find_id_exact_match=Y&amp;set_filter=Y">'.$f_ADV_ID.'</a>'.($f_ADV_BACK=="Y"?'<span class="required">*</span>':"");
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("ADV_ID", $strHTML);

	$f_HIT_ID=intval($f_HIT_ID);
	if($f_HIT_ID>0):
		$strHTML='<a href="hit_list.php?lang='.LANG.'&amp;find_id='.$f_HIT_ID.'&amp;find_id_exact_match=Y&amp;set_filter=Y">'.$f_HIT_ID.'</a>';
	else:
		$strHTML='&nbsp;';
	endif;
	$row->AddViewField("HIT_ID", $strHTML);

	$strHTML=$arSites[$f_REFERER_SITE_ID].' '.StatAdminListFormatURL($arRes["REFERER_URL"], array(
		"max_display_chars" => "default",
		"chars_per_line" => "default",
		"kill_sessid" => $STAT_RIGHT < "W",
	));
	$row->AddViewField("REFERER_URL", $strHTML);

	$strHTML=$arSites[$f_SITE_ID].' '.StatAdminListFormatURL($arRes["URL"], array(
		"max_display_chars" => "default",
		"chars_per_line" => "default",
		"kill_sessid" => $STAT_RIGHT < "W",
	));
	$row->AddViewField("URL", $strHTML);

	$strHTML=StatAdminListFormatURL($arRes["REDIRECT_URL"], array(
		"max_display_chars" => "default",
		"chars_per_line" => "default",
		"kill_sessid" => $STAT_RIGHT < "W",
	));
	$row->AddViewField("REDIRECT_URL", $strHTML);

	if($STAT_RIGHT>"M")
	{
		$strHTML=($f_CHARGEBACK=="Y"?"- ":"").($f_MONEY>0?str_replace(" ", $thousand_sep, number_format($f_MONEY, 2, ".", " ")):"&nbsp;");
		$row->AddViewField("MONEY", $strHTML);
	}

endwhile;

//Totals
$arTotalFilter = $arFilter;
$arTotalFilter["GROUP"]="total";
$rsTotalData = $cData->GetList('', '', $arTotalFilter);
$arTotal = $rsTotalData->Fetch();

$arTotal["COUNTER"] = intval($arTotal["COUNTER"]);
$arTotal["MONEY"] = round(doubleval($arTotal["MONEY"]),2);

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
if($STAT_RIGHT>"M")
	$arFooter[] = array(
		"title"=>GetMessage("STAT_TOTAL_MONEY"),
		"value"=>str_replace(" ", $thousand_sep, number_format($arTotal["MONEY"], 2, ".", " ")),
		);
$arFooter[] = array(
	"title"=>GetMessage("STAT_TOTAL"),
	"value"=>$arTotal["COUNTER"],
	);
$lAdmin->AddFooter($arFooter);

if($STAT_RIGHT>="W")
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("STAT_DELETE"),
	));

$aContext = array(
	array(
		"TEXT"=>GetMessage("STAT_ADD"),
		"LINK"=>"event_edit.php?lang=".LANG,
		"TITLE"=>GetMessage("STAT_ADD_TITLE"),
		"ICON"=>"btn_new",
	),
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arFilterDropDown = array(
	GetMessage("STAT_F_ID"),
	GetMessage("STAT_F_EVENT_ID"),
	GetMessage("STAT_F_EVENT_TYPE_NAME"),
	"event1 / event2",
	"event3",
	GetMessage("STAT_F_DATE"),
	GetMessage("STAT_F_SESSION_ID"),
	GetMessage("STAT_F_GUEST_ID"),
	GetMessage("STAT_COUNTRY"),
	GetMessage("STAT_F_ADV_ID"),
	GetMessage("STAT_F_ADV_BACK"),
	GetMessage("STAT_F_HIT_ID"),
	GetMessage("STAT_F_REFERER_URL"),
	GetMessage("STAT_F_URL"),
	GetMessage("STAT_F_REDIRECT_URL"),
);
if($STAT_RIGHT>"M")
{
	$arFilterDropDown[]=GetMessage("STAT_F_MONEY");
	if($currency_module=="Y")
		$arFilterDropDown[]=GetMessage("STAT_F_CURRENCY");
}
$arFilterDropDown[] = GetMessage("STAT_F_FILTER_LOGIC");

$oFilter = new CAdminFilter($sTableID."_filter",$arFilterDropDown);

$oFilter = new CAdminFilter($sTableID."_filter",$arFilterDropDown);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?
$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage("STAT_F_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("STAT_F_FIND_ENTER")?>">
		<?
		$arr = array(
			"reference" => array(
				"event1",
				"event2",
				GetMessage('STAT_F_ADV_ID'),
			),
			"reference_id" => array(
				"event1",
				"event2",
				"adv_id",
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
	<td><?echo GetMessage("STAT_F_EVENT_ID")?>:</td>
	<td><input type="text" name="find_event_id" size="47" value="<?echo htmlspecialcharsbx($find_event_id)?>"><?=ShowExactMatchCheckbox("find_event_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_EVENT_TYPE_NAME")?>:</td>
	<td><input type="text" name="find_event_name" size="47" value="<?echo htmlspecialcharsbx($find_event_name)?>"><?=ShowExactMatchCheckbox("find_event_name")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>event1 / event2:</td>
	<td><input type="text" name="find_event1" size="14" value="<?echo htmlspecialcharsbx($find_event1)?>">&nbsp;/&nbsp;<input type="text" name="find_event2" size="14" value="<?echo htmlspecialcharsbx($find_event2)?>"><?=ShowExactMatchCheckbox("find_event12")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>event3:</td>
	<td><input type="text" name="find_event3" size="47" value="<?echo htmlspecialcharsbx($find_event3)?>"><?=ShowExactMatchCheckbox("find_event3")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_DATE")." (".FORMAT_DATE."):"?></td>
	<td><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "find_form","Y")?></font></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_SESSION_ID")?>:</td>
	<td><input type="text" name="find_session_id" size="47" value="<?echo htmlspecialcharsbx($find_session_id)?>"><?=ShowExactMatchCheckbox("find_session_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_GUEST_ID")?>:</td>
	<td><input type="text" name="find_guest_id" size="47" value="<?echo htmlspecialcharsbx($find_guest_id)?>"><?=ShowExactMatchCheckbox("find_guest_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_COUNTRY")?>:</td>
	<td>[&nbsp;<input type="text" name="find_country_id" size="5" value="<?echo htmlspecialcharsbx($find_country_id)?>">&nbsp;]&nbsp;&nbsp;&nbsp;<input type="text" name="find_country" size="34" value="<?echo htmlspecialcharsbx($find_country)?>"><?=ShowExactMatchCheckbox("find_country")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ADV_ID")?>:</td>
	<td><input type="text" name="find_adv_id" size="47" value="<?echo htmlspecialcharsbx($find_adv_id)?>"><?=ShowExactMatchCheckbox("find_adv_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ADV_BACK")?>:</td>
	<td><?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_adv_back", $arr, htmlspecialcharsbx($find_adv_back), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_HIT_ID")?>:</td>
	<td><input type="text" name="find_hit_id" size="47" value="<?echo htmlspecialcharsbx($find_hit_id)?>"><?=ShowExactMatchCheckbox("find_hit_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_REFERER_URL")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_referer_site_id", $arSiteDropdown, $find_referer_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<input type="text" name="find_referer_url" size="34" value="<?echo htmlspecialcharsbx($find_referer_url)?>"><?=ShowExactMatchCheckbox("find_referer_url")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_URL")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<input type="text" name="find_url" size="34" value="<?echo htmlspecialcharsbx($find_url)?>"><?=ShowExactMatchCheckbox("find_url")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_REDIRECT_URL")?>:</td>
	<td><input type="text" name="find_redirect_url" size="47" value="<?echo htmlspecialcharsbx($find_redirect_url)?>"><?=ShowExactMatchCheckbox("find_redirect_url")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?if($STAT_RIGHT>"M"):?>
<tr>
	<td><?echo GetMessage("STAT_F_MONEY")?>:</td>
	<td><input type="text" maxlength="10" name="find_money1" value="<?echo htmlspecialcharsbx($find_money1)?>" size="9"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" maxlength="10" name="find_money2" value="<?echo htmlspecialcharsbx($find_money2)?>" size="9"></td>
</tr>
<?if ($currency_module=="Y") : ?>
<tr>
	<td><?echo GetMessage("STAT_F_CURRENCY")?>:</td>
	<td><?
	echo SelectBoxFromArray("find_currency", $arrCurrency, htmlspecialcharsbx($find_currency), GetMessage("STAT_F_BASE_CURRENCY"));?></td>
</tr>
<?endif;?>
<?endif;?>
<?=ShowLogicRadioBtn()?>
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

<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("STAT_ADV_BACK_ALT")?>
<?echo EndNote();?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
