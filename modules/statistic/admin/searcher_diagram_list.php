<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$sTableID = "tbl_diagram";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);
InitSorting();
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","searcher_list.php");

$arrDef = array();
$rs = CSearcher::GetList(($v1="s_total_hits"), ($v2="desc"), array(), $v3);
while ($ar = $rs->Fetch())
{
	if ($ar["DIAGRAM_DEFAULT"]=="Y") $arrDef[] = $ar["ID"];
	$arrSEARCHERS[$ar["ID"]] = $ar["NAME"]." [".$ar["ID"]."]";
}

if($lAdmin->IsDefaultFilter())
{
	if (is_array($arrSEARCHERS))
	{
		reset($arrSEARCHERS);
		while (list($key,$value)=each($arrSEARCHERS))
		{
			if ($i<=19 && in_array($key, $arrDef))
			{
				$find_searchers[] = $key;
				$i++;
			}
		}
	}
	$find_date1_DAYS_TO_BACK=90;
	$set_filter="Y";
}

$arFilterFields = array(
	"find_searchers",
	"find_date1",
	"find_date2"
	);
$lAdmin->InitFilter($arFilterFields);

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$str = (is_array($find_searchers)) ? implode(" | ",$find_searchers) : "";
$arFilter = Array(
	"ID"		=> $str,
	"DATE1_PERIOD"	=> $find_date1,
	"DATE2_PERIOD"	=> $find_date2
);

if (strlen($arFilter["DATE1_PERIOD"])>0 || strlen($arFilter["DATE2_PERIOD"])>0)
	$period = "Y";

##### graph
$sum = 0;
$arr = array();
if (is_array($find_searchers) && count($find_searchers) > 0)
{
	$by = ($period == "Y") ? "s_period_hits" : "s_total_hits";
	$w = CSearcher::GetList($by, ($order = "desc"), $arFilter, $is_filtered);
	while ($wr = $w->Fetch())
	{
		$total++;
		$count = ($period == "Y") ? $wr["PERIOD_HITS"] : $wr["TOTAL_HITS"];
		$sum += $count;
		if ($count > 0)
			$arr[] = array(
				"ID" => $wr["ID"],
				"NAME" => $wr["NAME"],
				"COUNTER" => $count,
			);
	}
}

$lAdmin->BeginCustomContent();
if ($sum > 0):?>
<div class="graph">
<table cellpadding="0" cellspacing="0" border="0" class="graph" align="center">
	<tr>
		<td><?
		$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");
		$url = "searcher_diagram.php?lang=".LANGUAGE_ID;
		foreach ($find_searchers as $sid)
			$url .= "&find_searchers[]=".urlencode($sid);
		$url .= "&find_date1=".$arFilter["DATE1_PERIOD"]."&find_date2=".$arFilter["DATE2_PERIOD"];

		?><img class="graph" src="<?echo htmlspecialcharsbx($url)?>" width="<?=$diameter?>" height="<?=$diameter?>">
		</td>
		<td>
		<table border="0" cellspacing="2" cellpadding="0" class="legend">
			<?
			$i = 0;
			foreach($arr as $key => $sector)
			{
				$i++;
				$id = $sector["ID"];
				$color = GetNextRGB($color, $total);
				$procent = round(($sector["COUNTER"]*100)/$sum,2);
			?>
			<tr>
				<td nowrap class="number"><?=$i."."?></td>
				<td valign="center" class="color">
					<div style="background-color: <?="#".$color?>"></div>
				</td>
				<td nowrap class="number"><?echo sprintf("%01.2f", $procent)."%"?></td>
				<td nowrap class="number">(<a title="<?echo GetMessage("STAT_VIEW_SEARCHER_HITS")?>" href="<?echo htmlspecialcharsbx("/bitrix/admin/hit_searcher_list.php?lang=".urlencode(LANGUAGE_ID)."&find_searcher_id=".urlencode($id)."&find_date1=".urlencode($arFilter["DATE1_PERIOD"])."&find_date2=".urlencode($arFilter["DATE2_PERIOD"])."&set_filter=Y")?>"><?=$sector["COUNTER"]?></a>)</td>
				<td nowrap>[<a title="<?=GetMessage("STAT_SEARCHER_LIST_OPEN")?>" href="searcher_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$id?>&set_filter=Y"><?=$id?></a>] <a title="<?echo GetMessage("STAT_VIEW_SEARCHER_GRAPH")?>" href="searcher_graph_list.php?lang=<?=LANGUAGE_ID?>&find_searchers[]=<?=$id?>&set_filter=Y"><?=htmlspecialcharsbx($sector["NAME"])?></a></td>
			</tr>
			<?
			}
			?>
		</table>
	</tr>
</table>
</div>
<?
else :
	$lAdmin->AddFilterError(GetMessage("STAT_NO_DATA"));
endif;
$lAdmin->EndCustomContent();
########### end of graph
$lAdmin->CheckListMode();
/***************************************************************************
			HTML form
***************************************************************************/
$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="form1" action="<?=$APPLICATION->GetCurPage()?>?">
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
if (is_array($arrSEARCHERS)):

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
			elseif (strtolower($ka)>strtolower($kb)) $ret=1;
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
	?>
	<tr valign="top">
		<td width="0%" nowrap><?echo GetMessage("STAT_F_SEACHERS")?><br><IMG SRC="/bitrix/images/statistic/mouse.gif" WIDTH="44" HEIGHT="21" BORDER=0 ALT=""></td>
		<td width="0%" nowrap><?echo SelectBoxMFromArray("find_searchers[]",array("REFERENCE"=>$ref, "REFERENCE_ID"=>$ref_id), $find_searchers,"",false,"11", "' style=\"width:100%\"");?></td>
	</tr>
	<?
endif;

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
		"TITLE"=>GetMessage("STAT_LIST_TITLE"),
		"LINK" => "searcher_list.php?lang=".LANG,
		"ICON" => "btn_list",
	),
	array(
		"LINK" => "searcher_graph_list.php?lang=".LANGUAGE_ID."&set_default=Y",
		"TEXT" => GetMessage("STAT_GRAPH_FULL_S"),
		"TITLE" => GetMessage("STAT_GRAPH_TITLE"),
	),
);
$context = new CAdminContextMenu($aMenu);
$context->Show();

$lAdmin->DisplayList();
?>

<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
