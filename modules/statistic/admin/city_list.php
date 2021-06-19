<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);

$err_mess = "File: ".__FILE__."<br>Line: ";

/***************************************************************************
			GET | POST handlers
****************************************************************************/
$arrCOUNTRY = array();
$rs = CCountry::GetList();
while ($ar = $rs->Fetch())
	$arrCOUNTRY[$ar["ID"]] = $ar["NAME"]." [".$ar["ID"]."]";

$sTableID = "t_city_list";
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);

if (!isset($find_data_type) || !in_array($find_data_type,Array("NEW_GUESTS","HITS","C_EVENTS","SESSIONS")))
	$find_data_type = false;

if($lAdmin->IsDefaultFilter())
{
	//$find_data_type = "SESSIONS";
	$find_date1_DAYS_TO_BACK = 90;
	$find_country_id = "";
	$set_filter = "Y";
}

$FilterArr = array(
	"find_date1",
	"find_date2",
	"find_country_id",
);

$lAdmin->InitFilter($FilterArr);

$arSettings = array("saved_group_by");
InitFilterEx($arSettings, $sTableID."_settings", "get");

if($find_data_type===false)//Restore saved setting
{
	if ($saved_group_by <> '')
		$find_data_type = $saved_group_by;
	else
		$find_data_type = "SESSIONS";
}
elseif($saved_group_by!=$find_data_type)//Set if changed
	$saved_group_by=$find_data_type;

InitFilterEx($arSettings, $sTableID."_settings", "set");

$arrDays = array();
AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arrLegend = array();
$arrTotalLegend = array();
$arFilter = Array(
	"COUNTRY_ID" => $find_country_id < 0? "": $find_country_id,
	"DATE1" => $find_date1,
	"DATE2" => $find_date2
);
if(mb_strlen($arFilter["COUNTRY_ID"]) == 2)
{
	$arrDays = CCity::GetGraphArray($arFilter, $arrLegend, $find_data_type, 20);
	$arrTotalDays = CCity::GetGraphArray($arFilter, $arrTotalLegend, "TOTAL_".$find_data_type, 20);
}
else
{
	$lAdmin->AddFilterError(GetMessage("STAT_NO_COUNTRY_ID"));
}

$lAdmin->BeginCustomContent();

if(!function_exists("ImageCreate")):
	ShowError(GetMessage("STAT_GD_NOT_INSTALLED"));
else:

	$width = COption::GetOptionString("statistic", "GRAPH_WEIGHT");
	$height = COption::GetOptionString("statistic", "GRAPH_HEIGHT");
	$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");
?>
<?
$found = false;
foreach($arrLegend as $key => $val)
{
	if ($val[$find_data_type] > 0)
	{
		$found = true; break;
	}
}

?>
<?if($found):?>
	<?if(count($arrDays) > 1):?>
	<div class="graph">
		<table border="0" cellspacing="1" cellpadding="0" align="center">
			<tr>
				<td valign="center">
					<img src="/bitrix/admin/city_graph.php?find_data_type=<?=$find_data_type?><?=GetFilterParams($FilterArr)?>&width=<?=$width?>&height=<?=$height?>&lang=<?echo LANG?>" width="<?=$width?>" height="<?=$height?>">
				</td>
			</tr>
		</table>
	</div>
	<?endif?>
<div class="graph">
	<?echo GetMessage("STAT_DYNAMIC_GRAPH2")?>
	<table cellspacing=0 cellpadding=10 align="center">
	<tr>
		<td valign="center"><img src="/bitrix/admin/city_diagram.php?<?=GetFilterParams($FilterArr)?>&lang=<?=LANG?>&find_data_type=<?=$find_data_type?>" width="<?=$diameter?>" height="<?=$diameter?>"></td>
		<td valign="center">
			<table cellpadding=2 cellspacing=0 border=0 class="legend">
				<?
				$sum = 0;
				foreach($arrLegend as $keyL => $arrL)
					$sum += $arrL[$find_data_type];

				$i=0;
				foreach($arrLegend as $keyL => $arrL) :
					$i++;
					$id = $keyL;
					$name = $arrL["NAME"];
					$counter = intval($arrL[$find_data_type]);
					$procent = (intval($sum)>0) ? round(($counter*100)/$sum,2) : 0;
					$color = $arrL["COLOR"];

				?>
				<tr>
					<td class="number" nowrap><?=$i."."?></td>
					<td valign="center" class="color">
						<div style="background-color: <?="#".$color?>"></div>
					</td>
					<td class="number" nowrap><?echo sprintf("%01.2f", $procent)."%"?></td>
					<td  nowrap>(<?
					if ($find_data_type=="SESSIONS") :
					?><a href="/bitrix/admin/session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_city_id=<?echo urlencode($id)?>&amp;find_city_exact_match=Y&amp;find_date1=<?echo urlencode($arFilter["DATE1"])?>&amp;find_date2=<?echo urlencode($arFilter["DATE2"])?>&amp;set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="NEW_GUESTS") :
					?><a href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&amp;find_city_id=<?echo urlencode($id)?>&amp;find_city_exact_match=Y&amp;find_sess2=1&amp;find_period_date1=<?echo urlencode($arFilter["DATE1"])?>&amp;find_period_date2=<?echo urlencode($arFilter["DATE2"])?>&amp;set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="HITS") :
					?><a href="/bitrix/admin/hit_list.php?lang=<?=LANGUAGE_ID?>&amp;find_city_id=<?echo urlencode($id)?>&amp;find_city_exact_match=Y&amp;find_date1=<?echo urlencode($arFilter["DATE1"])?>&amp;find_date2=<?echo urlencode($arFilter["DATE2"])?>&amp;set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="C_EVENTS") :
					?><?=$counter?><?
					endif;
					?>)</td>
					<td nowrap><?=htmlspecialcharsbx($name)?></td>
				</tr>
				<?endforeach;?>
			</table>
		</td>
	</tr>
</table>
</div>
<?else:
	CAdminMessage::ShowMessage(GetMessage("STAT_NO_DATA"));
endif?>

<?
$found = false;
foreach($arrTotalLegend as $key => $val)
{
	if ($val["TOTAL_".$find_data_type] > 0)
	{
		$found = true; break;
	}
}
if ($found):
?>
<div class="graph">
<?echo GetMessage("STAT_STATIC_GRAPH")?>
<table cellspacing=0 cellpadding=10 class="graph" align="center">
	<tr>
		<td valign="center"><img src="/bitrix/admin/city_diagram.php?<?=GetFilterParams($FilterArr)?>&lang=<?=LANGUAGE_ID?>&find_data_type=<?=$find_data_type?>&diagram_type=TOTAL" width="<?=$diameter?>" height="<?=$diameter?>"></td>
		<td valign="center">
			<table cellpadding=2 cellspacing=0 border=0 class="legend">
				<?
				$sum = 0;
				foreach($arrTotalLegend as $keyL => $arrL)
					$sum += $arrL["TOTAL_".$find_data_type];

				$i=0;
				foreach($arrTotalLegend as $keyL => $arrL) :
					$i++;
					$id = $keyL;
					$name = $arrL["NAME"];
					$counter = intval($arrL["TOTAL_".$find_data_type]);
					$procent = (intval($sum)>0) ? round(($counter*100)/$sum,2) : 0;
					$color = $arrL["COLOR"];
				?>
				<tr>
					<td class="number" nowrap><?=$i."."?></td>
					<td valign="center" class="color">
						<div style="background-color: <?="#".$color?>"></div>
					</td>
					<td class="number" nowrap><?echo sprintf("%01.2f", $procent)."%"?></td>
					<td  nowrap>(<?
					if ($find_data_type=="SESSIONS") :
					?><a href="/bitrix/admin/session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_city_id=<?echo urlencode($id)?>&amp;find_city_exact_match=Y&amp;set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="NEW_GUESTS") :
					?><a href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&amp;find_city_id=<?echo urlencode($id)?>&amp;find_city_exact_match=Y&amp;find_sess2=1&amp;set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="HITS") :
					?><a href="/bitrix/admin/hit_list.php?lang=<?=LANGUAGE_ID?>&amp;find_city_id=<?echo urlencode($id)?>&amp;find_city_exact_match=Y&amp;set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="C_EVENTS") :
					?><?=$counter?><?
					endif;
					?>)</td>
					<td nowrap><?=htmlspecialcharsbx($name)?></td>
				</tr>
				<?endforeach;?>
			</table>
		</td>
	</tr>
</table>
</div>
<?else:
	CAdminMessage::ShowMessage(GetMessage("STAT_NO_DATA"));
endif?>

<?
endif;
$lAdmin->EndCustomContent();

switch($find_data_type)
{
	case "NEW_GUESTS":
		$group_title = GetMessage("STAT_NEW_GUESTS");
		break;

	case "HITS":
		$group_title = GetMessage("STAT_HITS");
		break;

	case "C_EVENTS":
		$group_title = GetMessage("STAT_EVENTS");
		break;

	case "SESSIONS":
		$group_title = GetMessage("STAT_SESSIONS");
		break;

	default:
		$group_title = "";
		break;
}

$aContext = array(
	array(
		"TEXT" => $group_title,
		"MENU" => array(
			array(
				"TEXT"=>GetMessage("STAT_SESSIONS"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "find_data_type=SESSIONS"),
				"ICON"=>($find_data_type=="SESSIONS"?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_NEW_GUESTS"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "find_data_type=NEW_GUESTS"),
				"ICON"=>($find_data_type=="NEW_GUESTS"?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_HITS"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "find_data_type=HITS"),
				"ICON"=>($find_data_type=="HITS"?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_EVENTS"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "find_data_type=C_EVENTS"),
				"ICON"=>($find_data_type=="C_EVENTS"?"checked":""),
			),
		),
	),
);


$lAdmin->AddAdminContextMenu($aContext, false, false);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST").': '.$group_title);
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$filter = new CAdminFilter($sTableID."_filter_id", array(GetMessage("STAT_F_COUNTRY_ID")));
?>

<form name="form1" method="post" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>
<tr valign="top">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
</tr>
</tr>
<?
$ref = array_values($arrCOUNTRY);
array_unshift($ref, GetMessage("MAIN_NO"));
$ref_id = array_keys($arrCOUNTRY);
array_unshift($ref_id, "-1");
?>
<tr valign="top">
	<td valign="top"><?echo GetMessage("STAT_F_COUNTRY_ID")?>:</td>
	<td><?echo SelectBoxFromArray(
			"find_country_id",
			array("REFERENCE"=>$ref, "REFERENCE_ID"=>$ref_id),
			$find_country_id? $find_country_id: "-1",
			"",
			"style=\"width:100%\""
	);?></td>
</tr>

<?
$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$filter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>