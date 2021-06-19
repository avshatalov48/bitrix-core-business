<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$sTableID = "tbl_graph_list";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);
InitSorting();
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","searcher_list.php");

/***************************************************************************
				Functions
***************************************************************************/

$arrDef = array();
$rs = CSearcher::GetList("s_total_hits", "desc");
while ($ar = $rs->Fetch())
{
	if ($ar["DIAGRAM_DEFAULT"]=="Y") $arrDef[] = $ar["ID"];
	$arrSEARCHERS[$ar["ID"]] = $ar["NAME"]." [".$ar["ID"]."]";
}

if($lAdmin->IsDefaultFilter())
{
	if (is_array($arrSEARCHERS))
	{
		foreach ($arrSEARCHERS as $key => $value)
		{
			if ($i<=9 && in_array($key, $arrDef))
			{
				$find_searchers[] = $key;
				$i++;
			}
		}
	}
	$find_date1_DAYS_TO_BACK = 90;
	$set_filter="Y";
}

if (is_array($find_searchers)) $find_searchers = array_unique($find_searchers);

$arFilterFields = array(
	"find_searchers",
	"find_summa",
	"find_date1",
	"find_date2"
	);

$lAdmin->InitFilter($arFilterFields);
AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$str = (is_array($find_searchers)) ? implode(" | ",$find_searchers) : "";
$arFilter = Array(
	"SEARCHER_ID"	=> $str,
	"DATE1"			=> $find_date1,
	"DATE2"			=> $find_date2,
	"SUMMA"			=> $find_summa
);

$arrLegend=array();
$arrDays=array();
$strError="";

$arrDays = CSearcher::GetGraphArray($arFilter, $arrLegend);

##### graf
$lAdmin->BeginCustomContent();

$summa = "Y";
foreach($arrLegend as $keyL => $arrL)
	if ($arrL["COUNTER_TYPE"]=="DETAIL")
		$summa = "N";


if (function_exists("ImageCreate")) :
if ($strError == '' && count($arrLegend)>0 && count($arrDays)>1) :
	$width = COption::GetOptionString("statistic", "GRAPH_WEIGHT");
	$height = COption::GetOptionString("statistic", "GRAPH_HEIGHT");
?>
<div class="graph">
	<?
	if($summa == "Y")
		echo GetMessage("STAT_SUMMARIZED");
	?>
	<table cellpadding="0" cellspacing="0" border="0" class="graph" align="center">
	<tr>
		<td>
			<img class="graph" src="searcher_graph.php?<?=GetFilterParams($arFilterFields)?>&width=<?=$width?>&height=<?=$height?>&lang=<?=LANGUAGE_ID?>" width="<?=$width?>" height="<?=$height?>">
		</td>
		<?if($summa == "N"):?>
		<td>
			<table border="0" cellspacing="0" cellpadding="0" class="legend">
			<?
			foreach ($arrLegend as $keyL => $arrL):
				$color = $arrL["COLOR"];
			?>
				<tr>
					<td valign="center" class="color-line">
						<div style="background-color: <?="#".$color?>"></div>
					</td>
					<td nowrap>
					<?
					if ($arrL["COUNTER_TYPE"]=="DETAIL") :
						?>[<a title="<?=GetMessage("STAT_SEARCHER_LIST_OPEN")?> " href="<?= htmlspecialcharsbx("/bitrix/admin/searcher_list.php?lang=".urlencode(LANGUAGE_ID)."&find_id=".urlencode($keyL)."&set_filter=Y")?>"><?=$keyL?></a>]&nbsp;<a title="<?=GetMessage("STAT_SEARCHER_DYNAMIC")?>" href="<?= htmlspecialcharsbx("/bitrix/admin/searcher_dynamic_list.php?lang=".urlencode(LANGUAGE_ID)."&find_searcher_id=".urlencode($keyL)."&find_date1=".urlencode($arFilter["DATE1"])."&find_date2=".urlencode($arFilter["DATE2"])."&set_filter=Y")?>"><?=$arrL["NAME"]?></a><?
					else :
						?><?=GetMessage("STAT_SUMMARIZED")?><?
					endif;
					?></td>
				</tr>
			<?endforeach;?>
			</table>
		</td>
		<?endif?>
	</tr>
	</table>
</div>

<?
else :
	$lAdmin->AddFilterError(GetMessage("STAT_NO_DATA"));
endif;?>
<?
else:
	$lAdmin->AddFilterError(GetMessage("STAT_GD_NOT_INSTALLED"));
endif;

$lAdmin->EndCustomContent();
########### end of graph
$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("STAT_FL_SEACHERS"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
</tr>
<?
if (is_array($arrSEARCHERS))
{
	$arrSEARCHERS_lower = array_map("strtolower", $arrSEARCHERS);
	function multiselect_sort($a,$b)
	{
		global $find_searchers, $arrSEARCHERS_lower;
		$ret=0;
		$ka = false;
		$kb = false;
		if (is_array($find_searchers))
		{
			$ka = array_search($a, $find_searchers);
			$kb = array_search($b, $find_searchers);
		}
		if ($ka!==false && $kb!==false)
		{
			if ($ka==$kb) $ret=0;
			elseif (mb_strtolower($ka) > mb_strtolower($kb)) $ret=1;
			else $ret=-1;
		}
		if ($ka===false && $kb!==false) $ret=1;
		if ($ka!==false && $kb===false) $ret=-1;
		if ($ret==0)
		{
			if ($arrSEARCHERS_lower[$a] > $arrSEARCHERS_lower[$b]) $ret=1;
			if ($arrSEARCHERS_lower[$a] < $arrSEARCHERS_lower[$b]) $ret=-1;
		}
		return $ret;
	}
	uksort($arrSEARCHERS, "multiselect_sort");
	$ref = array();
	$ref_id = array();
	if (is_array($arrSEARCHERS))
	{
		$ref = array_values($arrSEARCHERS);
		$ref_id = array_keys($arrSEARCHERS);
	}
}
?>
<tr valign="top">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_SEACHERS")?><br><IMG SRC="/bitrix/images/statistic/mouse.gif" WIDTH="44" HEIGHT="21" BORDER=0 ALT=""></td>
	<td width="0%" nowrap><?
	$arr = array("reference"=>array(GetMessage("STAT_SEPARATED"), GetMessage("STAT_SUMMA")), "reference_id"=>array("N","Y"));
	echo SelectBoxFromArray("find_summa", $arr, htmlspecialcharsbx($find_summa), "", " style=\"width:100%\"")."<br>";
	echo SelectBoxMFromArray("find_searchers[]",array("REFERENCE"=>$ref, "REFERENCE_ID"=>$ref_id), $find_searchers,"",false,"11", "class=\"typeselect\" style=\"width:100%\"");?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()));
$oFilter->End();
?>
</form>

<?
if($message)
	echo $message->Show();

$aMenu = array(
	array(
		"TEXT" => GetMessage("STAT_LIST"),
		"TITLE" =>GetMessage("STAT_LIST_TITLE"),
		"LINK" =>"searcher_list.php?lang=".LANG,
		"ICON" => "btn_list",
	),
	array(
		"LINK" => "searcher_diagram_list.php?lang=".LANGUAGE_ID."&set_default=Y",
		"TEXT" => GetMessage("STAT_DIAGRAM_S"),
		"TITLE" => GetMessage("STAT_DIAGRAM_TITLE"),
	),
);
$context = new CAdminContextMenu($aMenu);
$context->Show();

$lAdmin->DisplayList();
?>

<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
