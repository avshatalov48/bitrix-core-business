<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$err_mess = "File: ".__FILE__."<br>Line: ";

$arrParams = array(
	"SESSION_SUMMA" => array(GetMessage("STAT_SESSION_SUMMA"), GetMessage("STAT_GRAPH_SESSION_SUMMA")),
	"SESSION" => array(GetMessage("STAT_SESSION"), GetMessage("STAT_GRAPH_SESSION")),
	"SESSION_BACK" => array(GetMessage("STAT_SESSION_BACK"), GetMessage("STAT_GRAPH_SESSION_BACK")),
	"VISITOR_SUMMA" => array(GetMessage("STAT_VISITOR_SUMMA"), GetMessage("STAT_GRAPH_VISITOR_SUMMA")),
	"VISITOR" => array(GetMessage("STAT_VISITOR"), GetMessage("STAT_GRAPH_VISITOR")),
	"VISITOR_BACK" => array(GetMessage("STAT_VISITOR_BACK"), GetMessage("STAT_GRAPH_VISITOR_BACK")),
	"NEW_VISITOR" => array(GetMessage("STAT_NEW_VISITOR"), GetMessage("STAT_GRAPH_NEW_VISITOR")),
	"HOST_SUMMA" => array(GetMessage("STAT_HOST_SUMMA"), GetMessage("STAT_GRAPH_HOST_SUMMA")),
	"HOST" => array(GetMessage("STAT_HOST"), GetMessage("STAT_GRAPH_HOST")),
	"HOST_BACK" => array(GetMessage("STAT_HOST_BACK"), GetMessage("STAT_GRAPH_HOST_BACK")),
	"HIT_SUMMA" => array(GetMessage("STAT_HIT_SUMMA"), GetMessage("STAT_GRAPH_HIT_SUMMA")),
	"HIT" => array(GetMessage("STAT_HIT"), GetMessage("STAT_GRAPH_HIT")),
	"HIT_BACK" => array(GetMessage("STAT_HIT_BACK"), GetMessage("STAT_GRAPH_HIT_BACK")),
	"EVENT_SUMMA" => array(GetMessage("STAT_EVENT_SUMMA"), GetMessage("STAT_GRAPH_EVENT_SUMMA")),
	"EVENT" => array(GetMessage("STAT_EVENT"), GetMessage("STAT_GRAPH_EVENT")),
	"EVENT_BACK" => array(GetMessage("STAT_EVENT_BACK"), GetMessage("STAT_GRAPH_EVENT_BACK")),
);

/***************************************************************************
				GET | POST handlers
****************************************************************************/

$rs = CAdv::GetList($v1="", $v2="", Array(), $v3, "", $v4, $v5);
while($ar = $rs->Fetch())
{
	$arrADV[$ar["ID"]] = $ar["REFERER1"]." / ".$ar["REFERER2"]." [".$ar["ID"]."]";
}

$rs = CStatEventType::GetSimpleList($v1="", $v2="", array(), $v3);
while($ar = $rs->Fetch())
{
	$arrEVENT[$ar["ID"]] = htmlspecialcharsbx($ar["EVENT"])." [".$ar["ID"]."]";
}

$sTableID = "t_adv_analysis";
$oSort = new CAdminSorting($sTableID);// Sorting init
$lAdmin = new CAdminList($sTableID, $oSort);// List init

if(isset($find_data_type))
{
	if(!array_key_exists($find_data_type, $arrParams))
		$find_data_type = "SESSION_SUMMA";
}
else
{
	$find_data_type=false;
}

if($lAdmin->IsDefaultFilter())
{
	$i=0;
	$find_adv=array();
	if (is_array($arrADV))
	{
		reset($arrADV);
		while (list($key,$value)=each($arrADV))
		{
			$i++;
			if ($i<=10) $find_adv[] = $key;
		}
	}
	$set_filter = "Y";
	$find_data_type = "SESSION_SUMMA";
}

//echo "!".$find_data_type."!";

$arFilterEvent = Array(
	Array(
		GetMessage("STAT_F_ID2"),
		"event1",
		"event2",
		GetMessage("STAT_F_EVENT_TYPIES"),
	),
	Array(
		"find_event_type_id", "find_event_type_id_exact_match",
		"find_event1","find_event1_exact_match",
		"find_event2","find_event2_exact_match",
		"find_events",
	)
);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array_merge(
		Array(GetMessage("STAT_F_ID"),"referer1","referer2"),
		Array(GetMessage("STAT_F_SELECT_ADV")),
		//Array(GetMessage("STAT_F_DATA_TYPE")),
		(is_array($arrEVENT) ? $arFilterEvent[0] : Array())

	)

);

$FilterArr = array_merge(

	Array(
		"find_date1", "find_date2",
		"find_adv_id","find_adv_id_exact_match",
		"find_referer1","find_referer1_exact_match",
		"find_referer2","find_referer2_exact_match"
	),
	Array("find_adv"),
	//Array("find_data_type"),
	(is_array($arrEVENT) ? $arFilterEvent[1] : Array()));

$lAdmin->InitFilter($FilterArr);//Filter init


//Restore & Save settings (windows registry like)
$arSettings = array("saved_group_by");
InitFilterEx($arSettings, $sTableID."_settings", "get");

if($find_data_type===false)//Restore saved setting
{
	if (strlen($saved_group_by) > 0)
		$find_data_type = $saved_group_by;
	else
		$find_data_type = "SESSION_SUMMA";
}
elseif($saved_group_by!=$find_data_type)//Set if changed
	$saved_group_by=$find_data_type;

InitFilterEx($arSettings, $sTableID."_settings", "set");

if(is_array($find_adv))
{
	$find_adv_names = array();
	foreach($find_adv as $value)
	{
		$find_adv_names[]=$arrADV[$value];
	}
}
else
{
	$find_adv=array();
	$find_adv_names = array();
}


if(is_array($find_events))
{
	$find_events_names = array();
	foreach($find_events as $value)
	{
		$find_events_names[]=$arrEVENT[$value];
	}
}
else
{
	$find_events =array();
	$find_events_names = array();
}


InitBVar($find_adv_id_exact_match);
InitBVar($find_referer1_exact_match);
InitBVar($find_referer2_exact_match);
InitBVar($find_event_type_id_exact_match);
InitBVar($find_event1_exact_match);
InitBVar($find_event2_exact_match);

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = array(
	"DATE1" => $find_date1,
	"DATE2" => $find_date2,
	"ADV_ID" => $find_adv_id,
	"REFERER1" => $find_referer1,
	"REFERER2" => $find_referer2,
	"EVENT_TYPE_ID" => $find_event_type_id,
	"EVENT1" => $find_event1,
	"EVENT2" => $find_event2,
	"ADV" => $find_adv,
	"EVENT_TYPE" => $find_events,


	"ADV_ID_EXACT_MATCH" => $find_adv_id_exact_match,
	"REFERER1_EXACT_MATCH" => $find_referer1_exact_match,
	"REFERER2_EXACT_MATCH" => $find_referer2_exact_match,
	"EVENT_TYPE_ID_EXACT_MATCH" => $find_event_type_id_exact_match,
	"EVENT1_EXACT_MATCH" => $find_event1_exact_match,
	"EVENT2_EXACT_MATCH" => $find_event2_exact_match,
);

$lAdmin->BeginPrologContent();

$arrDays = CAdv::GetAnalysisGraphArray($arFilter, $is_filtered, $find_data_type, $arrLegend, $total, $max);
if(count($arrDays) < 2) :
	$m = new CAdminMessage(GetMessage("STAT_NOT_ENOUGH_DATA"));
	echo $m->Show();
else:
?>
<div class="graph">
<?echo $arrParams[$find_data_type][1]?>
<table cellspacing="0" cellpadding="0" class="graph" border="0" align="center"><tr>
<td valign="center" class="graph"><?
	$width = COption::GetOptionString("statistic", "GRAPH_WEIGHT");
	$height = COption::GetOptionString("statistic", "GRAPH_HEIGHT");
	?><img class="graph" src="/bitrix/admin/adv_analysis_graph.php?rand=<?=rand()?>&find_data_type=<?=$find_data_type?><?=GetFilterParams($FilterArr)?>&width=<?=$width?>&height=<?=$height?>&lang=<?=LANGUAGE_ID?>" width="<?=$width?>" height="<?=$height?>">
</td>
</tr>
</table>
</div>
<br>
<?endif;
$lAdmin->EndPrologContent();


$column_title = $arrParams[$find_data_type][0];

$arHeaders = Array();

$arHeaders[] = array("id"=>"COLOR", "content"=>GetMessage("STAT_COLOR"),"default"=>true,"align"=>"center");
$arHeaders[] = array("id"=>"ADV", "content"=>GetMessage("STAT_ADV"), "default"=>true);
$arHeaders[] = array("id"=>"COUNTER", "content"=>$column_title, "default"=>true, "align"=>"right");
$arHeaders[] = array("id"=>"PERCENT", "content"=>GetMessage("STAT_PERCENT"), "default"=>true, "align"=>"right");

$lAdmin->AddHeaders($arHeaders);

function sup_sort($a,$b)
{
	$sort1 = intval($a["SM"]);
	$sort2 = intval($b["SM"]);
	if ($sort1 == $sort2)
		return 0;
	if ($sort1 < $sort2)
		return 1;
	else
		return -1;
}
uasort($arrLegend, "sup_sort");
$rsAdv = new CDBResult;
$rsAdv->InitFromArray($arrLegend);

$rsData = new CAdminResult($rsAdv, $sTableID);
$rsData->NavStart();

$number = (intval($rsData->NavPageNomer)-1)*intval($rsData->NavPageSize);
$max_width = 90;
$max_relation = ($max*100)/$max_width;

while($arRes = $rsData->NavNext(true, "f_"))
{
	if ($max_relation>0) $w = round(($f_SM*100)/$max_relation);
	if ($total>0) $q = number_format(($f_SM*100)/$total, 2, '.', '');
	$number++;

	$row =& $lAdmin->AddRow($number, $arRes);

	$row->AddViewField("COLOR", "&nbsp;<img src=\"/bitrix/admin/graph_legend.php?color=".$f_CLR."\" width=\"45\" height=\"2\">&nbsp;");
	$row->AddViewField("ADV", "[<a href=\"/bitrix/admin/adv_list.php?lang=".LANG."&amp;find_id=".$f_ID."&amp;find_id_exact_match=Y&amp;set_filter=Y\">".$f_ID."</a>]&nbsp;".$f_R1."&nbsp;/&nbsp;".$f_R2);
	$row->AddViewField("PERCENT", $q."%");
	$row->AddViewField("COUNTER", "<a href=\"/bitrix/admin/adv_dynamic_list.php?lang=".LANG."&amp;find_adv_id=".$f_ID."&amp;find_adv_id_exact_match=Y&amp;find_date1=".urlencode($find_date1)."&amp;find_date2=".urlencode($find_date2)."&amp;set_filter=Y\">".$f_SM."</a>");
}

// List footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	)
);

$arSubMenu = Array();

foreach($arrParams as $key=>$value)
{
	$arSubMenu[] = Array(
			"TEXT" => $value[0],
			"ACTION" => $lAdmin->ActionDoGroup(0, "", "set_filter=Y&find_data_type=".$key),
			"ICON" => ($find_data_type==$key? "checked": ""),
	);
}

$aContext = array(
	array(
		"TEXT"=>$column_title,
		"MENU"=>$arSubMenu,
	),
);


$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_PAGE_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>

<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
</tr>


<tr>
	<td><?=GetMessage("STAT_F_ID")?>:</td>
	<td><input type="text" name="find_adv_id" size="35" value="<?echo htmlspecialcharsbx($find_adv_id)?>"><?=ShowExactMatchCheckbox("find_adv_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>referer1:</td>
	<td><input type="text" name="find_referer1" size="35" value="<?echo htmlspecialcharsbx($find_referer1)?>"><?=ShowExactMatchCheckbox("find_referer1")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>referer2:</td>
	<td><input type="text" name="find_referer2" size="35" value="<?echo htmlspecialcharsbx($find_referer2)?>"><?=ShowExactMatchCheckbox("find_referer2")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<?//if (is_array($arrADV)):?>
<tr valign="top">
	<td width="0%" nowrap valign="top"><?
		echo GetMessage("STAT_F_SELECT_ADV")?>:<br><img src="/bitrix/images/statistic/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td width="100%" nowrap><?
		echo SelectBoxMFromArray("find_adv[]",array("REFERENCE"=>$find_adv_names, "REFERENCE_ID"=>$find_adv), $find_adv,"",false,"10", "style=\"width:300px;\"");
		?>
	<script language="Javascript">
	function selectEventType(form, field)
	{
		jsUtils.OpenWindow('adv_multiselect.php?lang=<?echo LANGUAGE_ID?>&form='+form+'&field='+field, 600, 600);
	}
	jsSelectUtils.sortSelect('find_adv[]');
	jsSelectUtils.selectAllOptions('find_adv[]');
	</script>
	<br>
	<input type="button" OnClick="selectEventType('find_form','find_adv[]')" value="<?=GetMessage("MAIN_ADMIN_MENU_ADD")?>...">&nbsp;
	<input type="button" OnClick="jsSelectUtils.deleteSelectedOptions('find_adv[]');" value="<?=GetMessage("MAIN_ADMIN_MENU_DELETE")?>">

		</td>
</tr>

<?//endif;?>

<script language="JavaScript">
<!--
function OnChangeAnalysisParam()
{
	var sbParam = document.forms['form1'].elements['find_data_type'];
	switch (sbParam[sbParam.selectedIndex].value)
	{
		case "EVENT_SUMMA":
		case "EVENT":
		case "EVENT_BACK":
			document.getElementById("events_tr").style.display = "inline";
			break;
		default:
			document.getElementById("events_tr").style.display = "none";
	}
}
//-->
</script>


<?if (is_array($arrEVENT)):?>

<tr>
	<td><?=GetMessage("STAT_F_ID2")?>:</td>
	<td><input type="text" name="find_event_type_id" size="35" value="<?echo htmlspecialcharsbx($find_event_type_id)?>"><?=ShowExactMatchCheckbox("find_event_type_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>event1:</td>
	<td><input type="text" name="find_event1" size="35" value="<?echo htmlspecialcharsbx($find_event1)?>"><?=ShowExactMatchCheckbox("find_event1")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>event2:</td>
	<td><input type="text" name="find_event2" size="35" value="<?echo htmlspecialcharsbx($find_event2)?>"><?=ShowExactMatchCheckbox("find_event2")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr valign="top">
	<td><?=GetMessage("STAT_F_SELECT_EVENTS")?>:</td>
	<td>
	<?
		echo SelectBoxMFromArray("find_events[]",array("REFERENCE"=>$find_events_names, "REFERENCE_ID"=>$find_events), $find_events,"",false,"10", "style=\"width:300px;\"");
	?>
	<script language="Javascript">
	function selectEvent(form, field)
	{
		jsUtils.OpenWindow('event_multiselect.php?lang=<?echo LANGUAGE_ID?>&form='+form+'&field='+field, 600, 600);
	}
	jsSelectUtils.sortSelect('find_events[]');
	jsSelectUtils.selectAllOptions('find_events[]');
	</script>
	<br>
	<input type="button" OnClick="selectEvent('find_form','find_events[]')" value="<?=GetMessage("MAIN_ADMIN_MENU_ADD")?>...">&nbsp;
	<input type="button" OnClick="jsSelectUtils.deleteSelectedOptions('find_events[]');" value="<?=GetMessage("MAIN_ADMIN_MENU_DELETE")?>">


	</td>
</tr>
<?endif;?>


<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>


<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>