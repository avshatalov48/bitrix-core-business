<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_event_dynamic_list";
$oSort = new CAdminSorting($sTableID, "DATE_STAT", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

if($set_default=="Y")
{
	$find_date1_DAYS_TO_BACK=90;
}

$FilterArr = array(
	"find_event_id",
	"find_date1",
	"find_date2"
	);

$lAdmin->InitFilter($FilterArr);

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2,
);

global $by, $order;

$cData = new CStatEventType;
$rsData = $cData->GetDynamicList($find_event_id, $by, $order, $arMaxMin, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_EVENT_DYN_PAGES")));

$arHeaders = array(
		array(	"id"		=>"DATE_STAT",
			"content"	=>GetMessage("STAT_DATE"),
			"sort"		=>"s_date",
			"default"	=>true,
		),
		array(	"id"		=>"COUNTER",
			"content"	=>GetMessage("STAT_COUNTER"),
			"align"		=>"right",
			"default"	=>true,
		),
	);
$lAdmin->AddHeaders($arHeaders);

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);
	if($f_COUNTER > 0)
	{
		$href = htmlspecialcharsbx("event_list.php?lang=".LANGUAGE_ID."&find_event_id=".urlencode($find_event_id)."&find_event_id_exact_match=Y&find_date1=".urlencode($f_DATE_STAT)."&find_date2=".urlencode($f_DATE_STAT)."&set_filter=Y");
		$strHTML = "<a href=\"".$href."\">".$f_COUNTER."</a>";
	}
	else
	{
		$strHTML = "&nbsp;";
	}
	$row->AddViewField("COUNTER", $strHTML);
endwhile;

$max_date = mktime(24,59,59,$arMaxMin["MAX_MONTH"], $arMaxMin["MAX_DAY"], $arMaxMin["MAX_YEAR"]);
$min_date = mktime(0,0,0,$arMaxMin["MIN_MONTH"], $arMaxMin["MIN_DAY"], $arMaxMin["MIN_YEAR"]);
if($arFilter["DATE1"] <> '')
	$mindate = $arFilter["DATE1"];
else
	$mindate = GetTime($min_date);
if ($arFilter["DATE2"] <> '')
	$maxdate = $arFilter["DATE2"];
else $maxdate = GetTime($max_date);
$arF = Array(
	"ID"		=> $find_event_id,
	"DATE1_PERIOD"	=> $mindate,
	"DATE2_PERIOD"	=> $maxdate
	);
$rsEventType = CStatEventType::GetList('', '', $arF);
$arEventType = $rsEventType->Fetch();

$arFooter = array();
$arFooter[] = array(
	"title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"),
	"value"=>$rsData->SelectedRowsCount(),
	);
$arFooter[] = array(
	"title"=>GetMessage("STAT_TOTAL"),
	"value"=>($arF["DATE1_PERIOD"] <> '' || $arF["DATE2_PERIOD"] <> '')?intval($arEventType["PERIOD_COUNTER"]):intval($arEventType["TOTAL_COUNTER"]),
	);
$arFooter[] = array(
	"title"=>GetMessage("STAT_TOTAL_TIME"),
	"value"=>intval(($max_date-$min_date)/86400),
	);

$lAdmin->AddFooter($arFooter);

$dynamic_days = CStatEventType::DynamicDays($find_event_id, $arFilter["DATE1"], $arFilter["DATE2"]);
if($dynamic_days>=2 && function_exists("ImageCreate")):

	$aContext = array(
		array(
			"TEXT"=>GetMessage("STAT_MNU_GRAPH"),
			"LINK"=>htmlspecialcharsbx("event_graph_list.php?lang=".LANGUAGE_ID."&find_events[]=".$find_event_id."&find_date1=".$arFilter["DATE1"]."&find_date2=".$arFilter["DATE2"]."&set_filter=Y"),
			"TITLE"=>GetMessage("STAT_GRAPH"),
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
endif;

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST", array("#STATISTIC_DAYS#" => $STORED_DAYS)));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arFilterDropDown = array(
	GetMessage("STAT_F_PERIOD"),
);

$oFilter = new CAdminFilter($sTableID."_filter",$arFilterDropDown);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?
$oFilter->Begin();
?>
<tr>
	<td><?echo GetMessage("STAT_F_EVENT_ID")?></td>
	<td>
	<input name="find_event_id" id="find_event_id" value="<?echo htmlspecialcharsbx($find_event_id)?>">
	<input type="button" OnClick="selectEventType('find_form','find_event_id')" value="<?=GetMessage("STAT_CHOOSE_BTN");?>">
	<script>
	function selectEventType(form, field)
	{
		jsUtils.OpenWindow('event_multiselect.php?target_control=text&lang=<?echo LANGUAGE_ID?>&form='+form+'&field='+field, 600, 600);
	}
	</script>
	</td>
</tr>
<tr valign="center">
	<td><?echo GetMessage("STAT_F_PERIOD")." (".FORMAT_DATE."):"?></td>
	<td><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "find_form", "Y")?></td>
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

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
