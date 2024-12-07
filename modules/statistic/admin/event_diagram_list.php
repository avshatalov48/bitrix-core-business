<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$sTableID = "tbl_event_diagram_list";
$sFilterID = $sTableID."_filter";
$lAdmin = new CAdminList($sTableID);

$arrDef = array();
$rs = CStatEventType::GetList("s_total_counter", "desc");
while ($ar = $rs->Fetch())
{
	if ($ar["DIAGRAM_DEFAULT"]=="Y") $arrDef[] = $ar["ID"];
	$arrEVENTS[$ar["ID"]] = $ar["EVENT"]." [".$ar["ID"]."]";
}

if($lAdmin->IsDefaultFilter())
{
	$find_events=array();
	if (is_array($arrEVENTS))
	{
		$i=0;
		foreach ($arrEVENTS as $key => $value)
		{
			if ($i<=9 && in_array($key, $arrDef))
			{
				$find_events[] = $key;
				$i++;
			}
		}
	}
	$find_date1_DAYS_TO_BACK=90;
	$set_filter="Y";
}

$FilterArr = array(
	"find_events",
	"find_date1",
	"find_date2"
	);

$lAdmin->InitFilter($FilterArr);

if(!is_array($find_events))
	$find_events=array();
else
	foreach($find_events as $key=>$value)
		$find_events[$key]=intval($value);
$find_events_names = array();
	foreach($find_events as $value)
		$find_events_names[]=$arrEVENTS[$value];

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$str = (is_array($find_events)) ? implode(" | ",$find_events) : "";
$arFilter = Array(
	"ID" => $str,
	"DATE1_PERIOD" => $find_date1,
	"DATE2_PERIOD" => $find_date2
);

if($arFilter["DATE1_PERIOD"] <> '' || $arFilter["DATE2_PERIOD"] <> '')
	$period = "Y";

$lAdmin->BeginCustomContent();

if (is_array($find_events) && count($find_events)>0):
	$arr = array();
	$by = ($period=="Y") ? "s_period_counter" : "s_total_counter";
	$w = CStatEventType::GetList($by, "desc", $arFilter);
	$total=0;
	while ($wr=$w->Fetch())
	{
		++$total;
		$count = ($period=="Y") ? $wr["PERIOD_COUNTER"] : $wr["TOTAL_COUNTER"];
		$sum += $count;
		if ($count>0)
			$arr[] = array("ID"=>$wr["ID"], "NAME"=>$wr["NAME"], "EVENT1" => $wr["EVENT1"], "EVENT2" => $wr["EVENT2"], "COUNTER"=>$count);
	}
?>
<div class="graph">
<table cellspacing="0" cellpadding="0" class="graph" border="0" align="center"><tr>
<td valign="top" class="graph"><?
	$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");
	$s = "";
	foreach ($find_events as $sid) $s .= "&find_events[]=".$sid;
	?><img class="graph" src="<?echo htmlspecialcharsbx("event_diagram.php?rand=".urlencode(rand())."&lang=".urlencode(LANGUAGE_ID).$s."&find_date1=".urlencode($arFilter["DATE1_PERIOD"])."&find_date2=".urlencode($arFilter["DATE2_PERIOD"]))?>" width="<?=$diameter?>" height="<?=$diameter?>">
</td>
<td valign="center">
	<table cellpadding="2" cellspacing="0" border="0" class="legend">
		<?
		$i = 0;
		foreach($arr as $key=>$sector):
			$i++;
			$id = $sector["ID"];
			$name = "(".$sector["EVENT1"]." / ".$sector["EVENT2"].") ".$sector["NAME"];
			$color = GetNextRGB($color, $total);
			$counter = $sector["COUNTER"];
			$procent = round(($counter*100)/$sum,2);
		?>
			<tr>
			<td align="right" class="number"><?=$i."."?></td>
			<td valign="center" class="color">
				<div style="background-color: <?="#".$color?>"></div>
			</td>
			<td align="right" class="number"><?echo sprintf("%01.2f", $procent)."%"?></td>
			<td align="right" class="number">(<a title="<?echo GetMessage("STAT_VIEW_EVENT_LIST")?>" class="stat_link" href="<?echo htmlspecialcharsbx("event_list.php?lang=".urlencode(LANGUAGE_ID)."&find_event_id=".urlencode($id)."&find_date1=".urlencode($arFilter["DATE1_PERIOD"])."&find_date2=".urlencode($arFilter["DATE2_PERIOD"])."&set_filter=Y")?>"><?=$counter?></a>)</td>
			<td>[<a class="stat_link" href="event_type_list.php?lang=<?=LANG?>&amp;find_id=<?=$id?>&amp;set_filter=Y"><?=$id?></a>] <a title="<?echo GetMessage("STAT_VIEW_GRAPH")?>" class="stat_link" href="event_graph_list.php?lang=<?=LANG?>&amp;find_events[]=<?=$id?>&amp;set_filter=Y"><?echo htmlspecialcharsbx($name)?></a></font></td>
			</tr>
		<?endforeach;?>
	</table>
</td>
</tr>
</table>
</div>
<?endif;

$lAdmin->EndCustomContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter($sFilterID,array(
	GetMessage("STAT_F_EVENTS"),
));
?>
<script>
function applyFilter()
{
	jsSelectUtils.selectAllOptions('find_events[]');
	<?=$sFilterID?>.OnSet('<?=CUtil::JSEscape($sTableID)?>', '<?=CUtil::JSEscape($APPLICATION->GetCurPage()."?lang=".LANG."&")?>');
	return false;
}
function deleteFilter()
{
	jsSelectUtils.selectAllOptions('find_events[]');
	jsSelectUtils.deleteSelectedOptions('find_events[]');
	<?=$sFilterID?>.OnClear('<?=CUtil::JSEscape($sTableID)?>', '<?=CUtil::JSEscape($APPLICATION->GetCurPage()."?lang=".LANG."&")?>');
	return false;
}
</script>
<form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter->Begin();
?>
<tr>
	<td><?echo GetMessage("STAT_F_PERIOD")." (".FORMAT_DATE."):"?></td>
	<td><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "find_form", "Y")?></td>
</tr>
<tr valign="top">
	<td><?echo GetMessage("STAT_F_EVENTS")?><br><img src="/bitrix/images/statistic/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td><?
	echo SelectBoxMFromArray("find_events[]",array("REFERENCE"=>$find_events_names, "REFERENCE_ID"=>$find_events), false, "", false, "11", 'id="find_events[]"');
	?>
	<script>
	function selectEventType(form, field)
	{
		jsUtils.OpenWindow('event_multiselect.php?target_control=select&lang=<?=LANG?>&form='+form+'&field='+field, 600, 600);
	}
	jsSelectUtils.sortSelect('find_events[]');
	jsSelectUtils.selectAllOptions('find_events[]');
	</script>
	<br>
	<input type="button" OnClick="selectEventType('find_form','find_events[]')" value="<?=GetMessage("STAT_ADD")?>...">
	<input type="button" OnClick="jsSelectUtils.deleteSelectedOptions('find_events[]');" value="<?=GetMessage("STAT_DELETE")?>">
	</td>
</tr>
<?$oFilter->Buttons()?>
<span class="adm-btn-wrap"><input type="submit" class="adm-btn" name="set_filter" value="<?=GetMessage("STAT_F_FIND")?>" title="<?=GetMessage("STAT_F_FIND_TITLE")?>" onClick="return applyFilter();"></span>
<span class="adm-btn-wrap"><input type="submit" class="adm-btn" name="del_filter" value="<?=GetMessage("STAT_F_CLEAR")?>" title="<?=GetMessage("STAT_F_CLEAR_TITLE")?>" onClick="return deleteFilter();"></span>
<?
$oFilter->End();
?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
