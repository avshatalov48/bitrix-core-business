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

if(isset($show_money))
{
	if($show_money!="Y")
		$show_money="N";
}
else
	$show_money=false;//no setting (will be read later from session)
if(isset($summa))
{
	if($summa!="Y")
		$summa="N";
}
else
	$summa=false;//no setting (will be read later from session)

define("HELP_FILE","event_type_list.php");

$sTableID = "tbl_event_graph_list";
$lAdmin = new CAdminList($sTableID);

$arrDef = array();
$rs = CStatEventType::GetList(($v1="s_total_counter"), ($v2="desc"), $arF, $v3);
while ($ar = $rs->Fetch())
{
	if ($ar["DIAGRAM_DEFAULT"]=="Y") $arrDef[] = $ar["ID"];
	$arrEVENTS[$ar["ID"]] = $ar["EVENT"]." [".$ar["ID"]."]";
}

if($lAdmin->IsDefaultFilter())
{
	$find_events=array();
	if (is_array($arrEVENTS))
		foreach($arrEVENTS as $key=>$value)
		{
			if ($i<=9 && in_array($key, $arrDef))
			{
				$find_events[] = $key;
				$i++;
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

//Restore & Save settings
$arSettings = array("saved_show_money", "saved_summa");
InitFilterEx($arSettings, $sTableID."_settings", "get");
if($show_money===false)//Restore saved setting
	$show_money=$saved_show_money;
$saved_show_money=$show_money;
if($summa===false)//Restore saved setting
	$summa=$saved_summa;
$saved_summa=$summa;
InitFilterEx($arSettings, $sTableID."_settings", "set");

//Compatibility only TODO:remove this code
$FilterArr[]="find_show_money";
$find_show_money=$show_money;
$FilterArr[]="find_summa";
$find_summa=$summa;

$strError = "";
AdminListCheckDate($strError, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$str = (is_array($find_events)) ? implode(" | ",$find_events) : "";
$arFilter = Array(
	"EVENT_ID"	=> $str,
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2,
	"SUMMA"		=> $find_summa
);

$lAdmin->BeginCustomContent();

if(is_array($find_events) && count($find_events)>0)
{
	$arrDays = CStatEventType::GetGraphArray($arFilter, $arrLegend);

	if (function_exists("ImageCreate"))
	{
		if (strlen($strError)<=0 && count($arrLegend)>0 && count($arrDays) > 1) :
			$width = COption::GetOptionString("statistic", "GRAPH_WEIGHT");
			$height = COption::GetOptionString("statistic", "GRAPH_HEIGHT");
			?>
			<div class="graph">
			<?
			if ($summa == "Y")
				echo GetMessage("STAT_SUMMARIZED");
			?>
			<table cellpadding="0" cellspacing="0" border="0" class="graph" align="center"><tr><td>
				<img class="graph" src="event_graph.php?<?=GetFilterParams($FilterArr)?>&width=<?=$width?>&height=<?=$height?>&lang=<?=LANG?>" width="<?=$width?>" height="<?=$height?>">
			</td>
			<?if ($summa != "Y") :?>
			<td>
				<table border="0" cellspacing="0" cellpadding="0" class="legend">
					<?
					foreach($arrLegend as $keyL=>$arrL):
						$color = $arrL["COLOR"];
					?>
					<tr>
						<td class="color">
							<div style="background-color: <?="#".$color?>"></div>
						</td>
						<td>
							[<a class="stat_link" href="<?echo htmlspecialcharsbx("/bitrix/admin/event_type_list.php?lang=".urlencode(LANGUAGE_ID)."&find_id=".urlencode($keyL)."&set_filter=Y")?>"><?=$keyL?></a>]&nbsp;<a class="stat_link" title="<?echo GetMessage("STAT_EVENT_DYNAMIC")?>" href="<?echo htmlspecialcharsbx("/bitrix/admin/event_dynamic_list.php?lang=".urlencode(LANGUAGE_ID)."&find_event_id=".urlencode($keyL)."&find_date1=".urlencode($arFilter["DATE1"])."&find_date2=".urlencode($arFilter["DATE2"])."&set_filter=Y")?>"><?=htmlspecialcharsEx($arrL["NAME"])?></a>
						</td>
					</tr>
					<?endforeach;?>
				</table>
			</td>
			<?endif;?>
			</tr></table></div>
		<?
		else:
			CAdminMessage::ShowMessage(GetMessage("STAT_NOT_ENOUGH_DATA"));
		endif;
	}
	else
	{
		CAdminMessage::ShowMessage(GetMessage("STAT_GD_NOT_INSTALLED"));
	}
}
$lAdmin->EndCustomContent();

$aContext = array();
$aContext[] =
	array(
		"TEXT"=>($summa=="Y"?GetMessage("STAT_SUMMARIZED_GRAPH"):GetMessage("STAT_MULTI_GRAPH")),
		"MENU"=>array(
			array(
				"TEXT"=>GetMessage("STAT_SUMMARIZED_GRAPH"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "summa=Y"),
				"ICON"=>($summa=="Y"?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_MULTI_GRAPH"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "summa=N"),
				"ICON"=>($summa!="Y"?"checked":""),
			),
		),
	);

if ($STAT_RIGHT>"M")
	$aContext[] =
		array(
			"TEXT"=>($show_money=="Y"?GetMessage("STAT_SHOW_MONEY"):GetMessage("STAT_SHOW_COUNT")),
			"MENU"=>array(
				array(
					"TEXT"=>GetMessage("STAT_MONEY"),
					"ACTION"=>$lAdmin->ActionDoGroup(0, "", "show_money=Y"),
					"ICON"=>($show_money=="Y"?"checked":""),
				),
				array(
					"TEXT"=>GetMessage("STAT_COUNT"),
					"ACTION"=>$lAdmin->ActionDoGroup(0, "", "show_money=N"),
					"ICON"=>($show_money!="Y"?"checked":""),
				),
			),
		);


$lAdmin->AddAdminContextMenu($aContext, false, false);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter($sTableID."_filter",array(
	GetMessage("STAT_F_EVENTS"),
));
?>

<form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter->Begin();
?>
<tr>
	<td><?echo GetMessage("STAT_F_PERIOD")." (".FORMAT_DATE."):"?></td>
	<td><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "find_form", "Y")?></td>
</tr>
<tr valign="top">
	<td><?echo GetMessage("STAT_F_EVENTS")?><br><IMG SRC="/bitrix/images/statistic/mouse.gif" WIDTH="44" HEIGHT="21" BORDER=0 ALT=""></td>
	<td><?
	echo SelectBoxMFromArray("find_events[]",array("REFERENCE"=>$find_events_names, "REFERENCE_ID"=>$find_events), false, "", false, "11", 'id="find_events[]"');
	?>
	<script language="Javascript">
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
<span class="adm-btn-wrap"><input type="submit" class="adm-btn" name="set_filter" value="<?=GetMessage("STAT_F_FIND")?>" title="<?=GetMessage("STAT_F_FIND_TITLE")?>" onClick="<?echo htmlspecialcharsbx("jsSelectUtils.selectAllOptions('find_events[]');".$oFilter->id.".OnSet('".CUtil::JSEscape($sTableID)."', '".CUtil::JSEscape($APPLICATION->GetCurPage()."?lang=".LANG."&")."'); return false;")?>"></span>
<span class="adm-btn-wrap"><input type="submit" class="adm-btn" name="del_filter" value="<?=GetMessage("STAT_F_CLEAR")?>" title="<?=GetMessage("STAT_F_CLEAR_TITLE")?>" onClick="<?echo htmlspecialcharsbx("jsSelectUtils.selectAllOptions('find_events[]');jsSelectUtils.deleteSelectedOptions('find_events[]');".$oFilter->id.".OnClear('".CUtil::JSEscape($sTableID)."', '".CUtil::JSEscape($APPLICATION->GetCurPage()."?lang=".LANG."&")."'); return false;")?>"></span>

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
