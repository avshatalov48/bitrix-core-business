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
define("HELP_FILE","attentiveness_list.php");

if($find_diagram_type!="ACTIVITY")
	$find_diagram_type="DURATION";

$ref = $ref_id = array();
$rs = CSite::GetList(($v1="sort"), ($v2="asc"));
while ($ar = $rs->Fetch())
{
	$ref[] = "[".$ar["ID"]."] ".$ar["NAME"];
	$ref_id[] = $ar["ID"];
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

$sTableID = "t_attent_list_".$find_diagram_type;
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		GetMessage("STAT_F_SITE"),
	)
);

if($lAdmin->IsDefaultFilter())
{
	//$find_date1_DAYS_TO_BACK=90;
	$find_date2 = ConvertTimeStamp(time()-86400, "SHORT");
	$set_filter = "Y";
}

$arFilterFields = array(
	"find_date1",
	"find_date2",
	"find_site_id",
);

$lAdmin->InitFilter($arFilterFields);

$strError = "";
AdminListCheckDate($strError, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2,
	"SITE_ID"	=> $find_site_id,
);

$arrTime = array("AM_1", "AM_1_3", "AM_3_6", "AM_6_9", "AM_9_12", "AM_12_15", "AM_15_18", "AM_18_21", "AM_21_24", "AM_24");
$arrHits = array("AH_1", "AH_2_5", "AH_6_9", "AH_10_13", "AH_14_17", "AH_18_21", "AH_22_25", "AH_26_29", "AH_30_33", "AH_34");

$days = $hits_sum = $time_sum = 0;
$rs = CTraffic::GetDailyList(($by="s_date"), ($order="asc"), $arMaxMin, $arFilter, $is_filtered);
while ($ar = $rs->Fetch())
{
	$days++;
	if($find_diagram_type=="DURATION")
		foreach($arrTime as $key)
		{
			$arSum[$key] = intval($arSum[$key]) + intval($ar[$key]);
			$time_sum = intval($time_sum) + intval($ar[$key]);
		}
	else
		foreach($arrHits as $key)
		{
			$arSum[$key] = intval($arSum[$key]) + intval($ar[$key]);
			$hits_sum = intval($hits_sum) + intval($ar[$key]);
		}
}

$lAdmin->BeginCustomContent();

$width = COption::GetOptionString("statistic", "GRAPH_WEIGHT");
$height = COption::GetOptionString("statistic", "GRAPH_HEIGHT");
$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");

if(strlen($strError)>0):
	CAdminMessage::ShowMessage($strError);
elseif(!function_exists("ImageCreate")) :
	CAdminMessage::ShowMessage(GetMessage("STAT_GD_NOT_INSTALLED"));
elseif($days<2):
	CAdminMessage::ShowMessage(GetMessage("STAT_NOT_ENOUGH_DATA"));
elseif($find_diagram_type=="DURATION"):
?>
	<div class="graph">
	<?=GetMessage("STAT_TIME_GRAPH")?>
	<table cellspacing="0" cellpadding="0" class="graph" border="0" align="center"><tr>
		<td valign="center" class="graph">
			<img class="graph" src="/bitrix/admin/attentiveness_graph.php?<?=GetFilterParams($arFilterFields)?>&show=time&width=<?=$width?>&height=<?=$height?>&lang=<?=LANGUAGE_ID?>" width="<?=$width?>" height="<?=$height?>">
		</td>
	</tr></table>
	</div>

	<div class="graph">
	<?=GetMessage("STAT_TIME_DIAGRAM")?>
	<table cellspacing=0 cellpadding="0" border="0" class="graph" align="center"><tr>
		<td valign="center" class="graph">
			<img class="graph" src="/bitrix/admin/attentiveness_diagram.php?<?=GetFilterParams($arFilterFields)?>&show=time&lang=<?=LANGUAGE_ID?>" width="<?=$diameter?>" height="<?=$diameter?>">
		</td>
		<td valign="center">
			<table cellpadding="0" cellspacing="0" border="0" class="legend">
				<?
				$i=0;
				foreach($arrTime as $key):
					$i++;
					$procent = ($time_sum>0) ? round(($arSum[$key]*100)/$time_sum,2) : 0;
					$color = $arrColor[$key];
				?>
				<tr>
					<td align="right" nowrap class="number"><?=$i."."?></td>
					<td valign="center" class="color">
						<div style="background-color: <?="#".$color?>"></div>
					</td>
					<td align="right" nowrap class="number"><?echo sprintf("%01.2f", $procent)."%"?></td>
					<td align="right" nowrap class="number">(<?=$arSum[$key]?>)</td>
					<td nowrap><?echo GetMessage("STAT_".$key);?></td>
				</tr>
				<?endforeach;?>
			</table>
		</td>
	</tr></table>
	</div>
<?else:?>
	<div class="graph">
	<?=GetMessage("STAT_HITS_GRAPH")?>
	<table cellspacing="0" cellpadding="0" class="graph" border="0" align="center"><tr>
		<td valign="center" class="graph">
			<img class="graph" src="/bitrix/admin/attentiveness_graph.php?find_date1=<?echo urlencode($find_date1)?>&find_date2=<?=urlencode($find_date2)?>&find_site_id=<?=urlencode($find_site_id)?>&width=<?=$width?>&height=<?=$height?>&lang=<?=LANGUAGE_ID?>" width="<?=$width?>" height="<?=$height?>">
		</td>
	</tr></table>
	</div>

	<div class="graph">
	<?=GetMessage("STAT_HITS_DIAGRAM")?>
	<table cellspacing="0" cellpadding="0" border="0" class="graph" align="center"><tr>
		<td valign="center" class="graph">
			<img class="graph" src="/bitrix/admin/attentiveness_diagram.php?<?=GetFilterParams($arFilterFields)?>&lang=<?=LANGUAGE_ID?>" width="<?=$diameter?>" height="<?=$diameter?>">
		</td>
		<td valign="center">
			<table cellpadding="0" cellspacing="0" border="0" class="legend">
				<?
				$i=0;
				foreach($arrHits as $key):
					$i++;
					$procent = (intval($hits_sum)>0) ? round(($arSum[$key]*100)/$hits_sum,2) : 0;
					$color = $arrColor[$key];
					$ar = explode("_",$key);
					$hits1 = intval($ar[1])>0 ? intval($ar[1]) : "";
					$hits2 = intval($ar[2])>0 ? intval($ar[2]) : "";
				?>
				<tr>
					<td align="right" nowrap class="number"><?=$i."."?></td>
					<td valign="center" class="color">
						<div style="background-color: <?="#".$color?>"></div>
					</td>
					<td align="right" nowrap class="number"><?echo sprintf("%01.2f", $procent)."%"?></td>
					<td align="right" nowrap class="number">(<a href="/bitrix/admin/session_list.php?find_hits1=<?=$hits1?>&find_hits2=<?=$hits2?>&set_filter=Y"><?echo $arSum[$key]?></a>)</td>
					<td nowrap><?echo GetMessage("STAT_".$key);?></td>
				</tr>
				<?endforeach;?>
			</table>
		</td>
	</tr></table>
	</div>
<?
endif;

$lAdmin->EndCustomContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_PAGE_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="form1" method="GET" action="<?$APPLICATION->GetCurPage();?>">
<?$filter->Begin();?>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_SITE")?>:</td>
	<td width="0%" nowrap><?echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("MAIN_ALL"), "");?></td>
</tr>
<input type="hidden" name="find_diagram_type" value="<?=$find_diagram_type?>">
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?
$lAdmin->DisplayList();
?>

<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
