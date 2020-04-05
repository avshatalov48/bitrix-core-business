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

/***************************************************************************
			GET | POST handlers
****************************************************************************/
$rs = CCountry::GetList($v1="s_dropdown", $v2="asc", array(), $v);
while ($ar = $rs->Fetch()) $arrCOUNTRY[$ar["ID"]] = $ar["NAME"]." [".$ar["ID"]."]";

$sTableID = "t_country_list";
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);

if (!isset($find_data_type) && !in_array($find_data_type,Array("NEW_GUESTS","HITS","C_EVENTS","SESSIONS")))
	$find_data_type = false;

if($lAdmin->IsDefaultFilter())
{
	//$find_data_type = "SESSIONS";
	$find_date1_DAYS_TO_BACK=90;
	$find_country_id = array();
	$i = 0;
	$find_country_id = array();

	if (is_array($arrCOUNTRY))
	{
		reset($arrCOUNTRY);
		while (list($key,$value)=each($arrCOUNTRY))
		{
			$i++;
			if ($i<=20) $find_country_id[] = $key;
		}
	}
	$set_filter = "Y";
}




$FilterArr1 = array(
	"find_date1","find_date2"

	);
$FilterArr2 = array(
	"find_country_id"
	);
$FilterArr = array_merge($FilterArr1, $FilterArr2);

$lAdmin->InitFilter($FilterArr);


$arSettings = array("saved_group_by");
InitFilterEx($arSettings, $sTableID."_settings", "get");

if($find_data_type===false)//Restore saved setting
{
	if (strlen($saved_group_by) > 0)
		$find_data_type = $saved_group_by;
	else
		$find_data_type = "SESSIONS";
}
elseif($saved_group_by!=$find_data_type)//Set if changed
	$saved_group_by=$find_data_type;

InitFilterEx($arSettings, $sTableID."_settings", "set");

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	(is_array($arrCOUNTRY) ? Array(GetMessage("STAT_F_COUNTRY_ID")) : Array())
);

$strError="";
AdminListCheckDate($strError, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$str = (is_array($find_country_id)) ? implode(" | ",$find_country_id) : "";
$arFilter = Array(
	"COUNTRY_ID" => $str,
	"DATE1" => $find_date1,
	"DATE2" => $find_date2
);

$arrDays = CCountry::GetGraphArray($arFilter, $arrLegend);

$lAdmin->BeginCustomContent();

if(strlen($strError)>0)
	CAdminMessage::ShowMessage($strError);

$found = false;
foreach($arrLegend as $key => $val)
{
	if ($val[$find_data_type] > 0)
	{
		$found = true;
		break;
	}
}

if (function_exists("ImageCreate")) :

if ((strlen($strError)==0) && count($arrLegend)>0) :
?>
<?
	$width = COption::GetOptionString("statistic", "GRAPH_WEIGHT");
	$height = COption::GetOptionString("statistic", "GRAPH_HEIGHT");
	$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");
?>
<?if($found):?>
	<?if(count($arrDays) > 1):?>
	<div class="graph">
	<table border="0" cellspacing="1" cellpadding="0" align="center">
		<tr>
			<td valign="center">
				<img src="/bitrix/admin/country_graph.php?find_data_type=<?=$find_data_type?><?=GetFilterParams($FilterArr)?>&width=<?=$width?>&height=<?=$height?>&lang=<? echo LANG?>" width="<?=$width?>" height="<?=$height?>"></td>
			</td>
		</tr>
	</table>
	</div>
	<?endif;?>
<div class="graph">
<?echo GetMessage("STAT_DYNAMIC_GRAPH2")?>
<table cellspacing=0 cellpadding=10 class="graph" align="center">
	<tr>
		<td valign="center"><img src="/bitrix/admin/country_diagram.php?<?=GetFilterParams($FilterArr)?>&lang=<?=LANG?>&find_data_type=<?=$find_data_type?>" width="<?=$diameter?>" height="<?=$diameter?>"></td>
		<td valign="center">
			<table cellpadding=2 cellspacing=0 border=0 class="legend">
				<?
				function data_sort($ar1, $ar2)
				{
					global $find_data_type;
					if ($ar1[$find_data_type]<$ar2[$find_data_type]) return 1;
					if ($ar1[$find_data_type]>$ar2[$find_data_type]) return -1;
					return 0;
				}
				uasort($arrLegend, "data_sort");

				$sum = 0;
				reset($arrLegend);
				while(list($keyL, $arrL) = each($arrLegend)) $sum += $arrL[$find_data_type];

				$i=0;
				reset($arrLegend);
				while(list($keyL, $arrL) = each($arrLegend)) :
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
					?><a href="/bitrix/admin/session_list.php?lang=<?=LANGUAGE_ID?>&amp;find_country_id=<?echo urlencode($id)?>&amp;find_country_id_exact_match=Y&amp;find_date1=<?echo urlencode($arFilter["DATE1"])?>&amp;find_date2=<?echo urlencode($arFilter["DATE2"])?>&amp;set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="NEW_GUESTS") :
					?><a href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&amp;find_country_id=<?echo urlencode($id)?>&amp;find_country_id_exact_match=Y&amp;find_sess2=1&amp;find_period_date1=<?echo urlencode($arFilter["DATE1"])?>&amp;find_period_date2=<?echo urlencode($arFilter["DATE2"])?>&amp;set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="HITS") :
					?><a href="/bitrix/admin/hit_list.php?lang=<?=LANGUAGE_ID?>&amp;find_country_id=<?echo urlencode($id)?>&amp;find_country_id_exact_match=Y&amp;find_date1=<?echo urlencode($arFilter["DATE1"])?>&amp;find_date2=<?echo urlencode($arFilter["DATE2"])?>&amp;set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="C_EVENTS") :
					?><a href="/bitrix/admin/event_list.php?lang=<?=LANGUAGE_ID?>&amp;find_country_id=<?echo urlencode($id)?>&amp;find_country_id_exact_match=Y&amp;find_date1=<?echo urlencode($arFilter["DATE1"])?>&amp;find_date2=<?echo urlencode($arFilter["DATE2"])?>&amp;set_filter=Y"><?=$counter?></a><?
					endif;
					?>)</td>
					<td class="number" nowrap><?
					$flag = "/bitrix/images/statistic/flags/".strtolower($id).".gif";
					if (file_exists($_SERVER["DOCUMENT_ROOT"].$flag)) :
						?><img src="<?=$flag?>" width="20" height="13" border=0 alt=""><?
					endif;
					?></td>
					<td nowrap><a href="city_list.php?lang=<?=LANGUAGE_ID?>&amp;find_country_id=<?echo urlencode($id)?>&amp;find_date1=<?echo urlencode($arFilter["DATE1"])?>&amp;find_date2=<?echo urlencode($arFilter["DATE2"])?>&amp;find_data_type=<?=$find_data_type?>&amp;set_filter=Y">[<?=htmlspecialcharsbx($id)?>] <?=htmlspecialcharsbx($name)?></a></td>
				</tr>
				<?endwhile;?>
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
foreach($arrLegend as $key => $val)
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
		<td valign="center"><img src="/bitrix/admin/country_diagram.php?<?=GetFilterParams($FilterArr)?>&lang=<?=LANGUAGE_ID?>&find_data_type=<?=$find_data_type?>&diagram_type=TOTAL" width="<?=$diameter?>" height="<?=$diameter?>"></td>
		<td valign="center">
			<table cellpadding=2 cellspacing=0 border=0 class="legend">
				<?
				function total_data_sort($ar1, $ar2)
				{
					global $find_data_type;
					if ($ar1["TOTAL_".$find_data_type]<$ar2["TOTAL_".$find_data_type]) return 1;
					if ($ar1["TOTAL_".$find_data_type]>$ar2["TOTAL_".$find_data_type]) return -1;
					return 0;
				}
				uasort($arrLegend, "total_data_sort");

				$sum = 0;
				reset($arrLegend);
				while(list($keyL, $arrL) = each($arrLegend)) $sum += $arrL["TOTAL_".$find_data_type];

				$i=0;
				reset($arrLegend);
				while(list($keyL, $arrL) = each($arrLegend)) :
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
					?><a href="/bitrix/admin/session_list.php?lang=<?=LANGUAGE_ID?>&find_country_id=<?echo urlencode($id)?>&find_country_id_exact_match=Y&set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="NEW_GUESTS") :
					?><a href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&find_country_id=<?echo urlencode($id)?>&find_country_id_exact_match=Y&find_sess2=1&set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="HITS") :
					?><a href="/bitrix/admin/hit_list.php?lang=<?=LANGUAGE_ID?>&find_country_id=<?echo urlencode($id)?>&find_country_id_exact_match=Y&set_filter=Y"><?=$counter?></a><?
					elseif ($find_data_type=="C_EVENTS") :
					?><a href="/bitrix/admin/event_list.php?lang=<?=LANGUAGE_ID?>&find_country_id=<?echo urlencode($id)?>&find_country_id_exact_match=Y&set_filter=Y"><?=$counter?></a><?
					endif;
					?>)</td>
					<td class="number" nowrap><?
					$flag = "/bitrix/images/statistic/flags/".strtolower($id).".gif";
					if (file_exists($_SERVER["DOCUMENT_ROOT"].$flag)) :
						?><img src="<?=$flag?>" width="20" height="13" border=0 alt=""><?
					endif;
					?></td>
					<td nowrap>[<?=htmlspecialcharsbx($id)?>] <?=htmlspecialcharsbx($name)?></td>
				</tr>
				<?endwhile;?>
			</table>
		</td>
	</tr>
</table>
</div>
<?else:
	CAdminMessage::ShowMessage(GetMessage("STAT_NO_DATA"));
endif?>
<?endif;?>
<?
else:
	ShowError(GetMessage("STAT_GD_NOT_INSTALLED"));
endif;
$lAdmin->EndCustomContent();

switch ($find_data_type)
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
				"TEXT" => GetMessage("STAT_SESSIONS"),
				"ACTION" => $lAdmin->ActionDoGroup(0, "", "find_data_type=SESSIONS"),
				"ICON" => ($find_data_type=="SESSIONS"?"checked":""),
			),
			array(
				"TEXT" => GetMessage("STAT_NEW_GUESTS"),
				"ACTION" => $lAdmin->ActionDoGroup(0, "", "find_data_type=NEW_GUESTS"),
				"ICON" => ($find_data_type=="NEW_GUESTS"?"checked":""),
			),
			array(
				"TEXT" => GetMessage("STAT_HITS"),
				"ACTION" => $lAdmin->ActionDoGroup(0, "", "find_data_type=HITS"),
				"ICON" => ($find_data_type=="HITS"?"checked":""),
			),
			array(
				"TEXT" => GetMessage("STAT_EVENTS"),
				"ACTION" => $lAdmin->ActionDoGroup(0, "", "find_data_type=C_EVENTS"),
				"ICON" => ($find_data_type=="C_EVENTS"?"checked":""),
			),
		),
	),
);

$lAdmin->AddAdminContextMenu($aContext, false, false);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST").': '.$group_title);
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/***************************************************************************
			HTML form
***************************************************************************/
?>

<form name="form1" method="post" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>
<!-- <tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_DATA_TYPE")?></td>
	<td width="0%" nowrap><?
		$arr = array(
			"reference"=>array(
				GetMessage("STAT_SESSIONS"),
				GetMessage("STAT_NEW_GUESTS"),
				GetMessage("STAT_HITS"),
				GetMessage("STAT_EVENTS")
				),
			"reference_id"=>array(
				"SESSIONS",
				"NEW_GUESTS",
				"HITS",
				"C_EVENTS"));
		echo SelectBoxFromArray("find_data_type", $arr, htmlspecialcharsbx($find_data_type));
		?></td>
</tr> -->
<tr valign="top">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
</tr>
</tr>
<?
if (is_array($arrCOUNTRY)):

	reset($arrCOUNTRY);
	$arrCOUNTRYlow = array_map("strtolower", $arrCOUNTRY);
	function multiselect_sort($a,$b)
	{
		global $find_country_id, $arrCOUNTRYlow;
		$ret=0;
		$ka = false;
		$kb = false;
		if (is_array($find_country_id))
		{
			$ka = array_search($a, $find_country_id);
			$kb = array_search($b, $find_country_id);
		}
		if ($ka!==false && $kb!==false)
		{
			if ($ka==$kb) $ret=0;
			elseif (strtolower($ka)>strtolower($kb)) $ret=1;
			else $ret=-1;
		}
		if ($ka===false && $kb!==false) $ret=1;
		if ($ka!==false && $kb===false) $ret=-1;
		if ($ret==0)
		{
			if ($arrCOUNTRYlow[$a] > $arrCOUNTRYlow[$b]) $ret=1;
			if ($arrCOUNTRYlow[$a] < $arrCOUNTRYlow[$b]) $ret=-1;
		}
		return $ret;
	}
	uksort($arrCOUNTRY, "multiselect_sort");
	$ref = array();
	$ref_id = array();
	if (is_array($arrCOUNTRY))
	{
		$ref = array_values($arrCOUNTRY);
		$ref_id = array_keys($arrCOUNTRY);
	}
?>
<tr valign="top">
	<td valign="top"><?echo GetMessage("STAT_F_COUNTRY_ID")?>:<br><img src="/bitrix/images/statistic/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td><?
	echo SelectBoxMFromArray("find_country_id[]",array("REFERENCE"=>$ref, "REFERENCE_ID"=>$ref_id), $find_country_id,"",false,"11", "style=\"width:100%\"");
	?></td>
</tr>
<?endif;?>

<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>



<?$lAdmin->DisplayList();?>

<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
