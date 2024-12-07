<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
IncludeModuleLangFile(__FILE__);

$is_dir = $_REQUEST["is_dir"] == "Y"? "Y": "N";
$section = is_string($_REQUEST["section"]) && preg_match('#^(http://|https://|/)#', $_REQUEST["section"])? $_REQUEST["section"]: "";

if (isset($set_default) && $set_default=="Y" &&
	$find_hits == '' &&
	$find_enter_points == '' &&
	$find_exit_points == '')
{
	$find_hits = "Y";
	$find_enter_points = "Y";
	$find_exit_points = "Y";
}

if(isset($find_adv) && is_array($find_adv) && count($find_adv)>0)
	$find_adv_str = implode(" | ",$find_adv);
else
	$find_adv_str = "";

$arFilter = array(
	"DATE1" => $date1,
	"DATE2" => $date2,
	"ADV" => $find_adv_str,
	"ADV_DATA_TYPE" => $adv_data_type,
	"IS_DIR" => ($is_dir=="Y"? "Y": "N"),
);
$days = 0;
$rs = CPage::GetDynamicList($section, $by, $order, $arFilter);
while($ar = $rs->Fetch())
{
	$days++;
	$SUM_COUNTER += intval($ar["COUNTER"]);
	$SUM_ENTER_COUNTER += intval($ar["ENTER_COUNTER"]);
	$SUM_EXIT_COUNTER += intval($ar["EXIT_COUNTER"]);
}

$strTitle = ($is_dir=="Y") ? GetMessage("STAT_TITLE_SECTION") : GetMessage("STAT_TITLE_PAGE");
$APPLICATION->SetTitle($strTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

if ($find_adv_str <> '') :
	echo "<h2>".GetMessage("STAT_ADV_LIST")."</h2><p>";
	$rsAdv = CAdv::GetList("s_dropdown", "asc", Array("ID" => $str));
	while ($arAdv = $rsAdv->GetNext()) :
		echo "[".$arAdv["ID"]."]&nbsp;".$arAdv["REFERER1"]."&nbsp;/&nbsp;".$arAdv["REFERER2"]."<br>";
	endwhile;
	if ($find_adv_data_type!="B" && $find_adv_data_type!="S") $find_adv_data_type="P";
	$arr = array(
		"P" => GetMessage("STAT_ADV_NO_BACK"),
		"B" => GetMessage("STAT_ADV_BACK"),
		"S" => GetMessage("STAT_ADV_SUMMA"),
	);
	echo "<img src=\"/bitrix/images/1.gif\" width=\"1\" height=\"5\" border=\"0\" alt=\"\"><br>(".$arr[$find_adv_data_type].")<br></p>";
endif;
$s = "";
$width = COption::GetOptionString("statistic", "GRAPH_WEIGHT");
$height = COption::GetOptionString("statistic", "GRAPH_HEIGHT");


if(isset($find_adv) && is_array($find_adv) && count($find_adv)>0)
{
	foreach($find_adv as $adv_id)
		$s .= "&amp;adv[]=".urlencode($adv_id);
}

if ($site_id <> '')
	$show_site_id = "[<a target=\"_blank\" href=\"".htmlspecialcharsbx("/bitrix/admin/site_edit.php?LID=".urlencode($site_id)."&lang=".LANGUAGE_ID)."\">".htmlspecialcharsbx($site_id)."</a>]&nbsp;";
else
	$show_site_id = "";
?>

<p><?=$show_site_id?><?
	if ($public != "Y"):
		?><a target="_blank" href="<?=htmlspecialcharsbx($section)?>" title="<?=GetMessage("STAT_GO_LINK")?>"><?=htmlspecialcharsbx(TruncateText($section,100))?></a><?
	else:
		echo htmlspecialcharsbx($section);
	endif;
?></p>

<?if ($days>=2):?>
<div class="graph">
<?=$strTitle?>
<table border="0" cellspacing="0" cellpadding="0" class="graph" align="center">
	<tr>
		<td valign="center">
		<img width=<?=$width?> height=<?=$height?> src="<?echo htmlspecialcharsbx("/bitrix/admin/section_graph.php?lang=".urlencode(LANGUAGE_ID)."&date1=".urlencode($date1)."&date2=".urlencode($date2).$s."&is_dir=".urlencode($is_dir)."&adv_data_type=".urlencode($find_adv_data_type)."&width=".intval($width)."&height=".intval($height)."&section=".urlencode($section)."&find_hits=".urlencode($find_hits)."&find_enter_points=".urlencode($find_enter_points)."&find_exit_points=".urlencode($find_exit_points))?>"></td>
		</td>
		<td valign="center">
			<table border="0" cellspacing="1" cellpadding="2" width="0%" class="legend">
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><?=GetMessage("STAT_TOTAL")?></td>
			</tr>
			<?if ($find_enter_points=="Y"):?>
			<tr>
				<td valign="center" class="color-line">
					<div style="background-color: <?="#".$arrColor["GREEN"]?>"></div>
				</td>
				<td nowrap><?=GetMessage("STAT_ENTER_POINTS")?></td>
				<td  class="number"><?=intval($SUM_ENTER_COUNTER)?></td>
			</tr>
			<?endif;?>
			<?if ($find_exit_points=="Y"):?>
			<tr>
				<td valign="center" class="color-line">
					<div style="background-color: <?="#".$arrColor["BLUE"]?>"></div>
				</td>
				<td nowrap><?=GetMessage("STAT_EXIT_POINTS")?></td>
				<td  class="number"><?=intval($SUM_EXIT_COUNTER)?></td>
			</tr>
			<?endif;?>
			<?if ($find_hits=="Y"):?>
			<tr>
				<td valign="center" class="color-line">
					<div style="background-color: <?="#".$arrColor["RED"]?>"></div>
				</td>
				<td nowrap><?=GetMessage("STAT_HITS")?></td>
				<td  class="number"><?=intval($SUM_COUNTER)?></td>
			</tr>
			<?endif;?>
			</table>
		</td>
	</tr>
</table>
</div>

<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="section" value="<?=htmlspecialcharsbx($section)?>">
	<input type="hidden" name="date1" value="<?=htmlspecialcharsbx($date1)?>">
	<input type="hidden" name="date2" value="<?=htmlspecialcharsbx($date2)?>">
	<input type="hidden" name="width" value="<?=$width?>">
	<input type="hidden" name="height" value="<?=$height?>">
	<p><?echo InputType("checkbox","find_enter_points","Y",$find_enter_points,false); ?>&nbsp;<?=GetMessage("STAT_ENTER_POINTS"); ?></p>
	<p><?echo InputType("checkbox","find_exit_points","Y",$find_exit_points,false); ?>&nbsp;<?=GetMessage("STAT_EXIT_POINTS"); ?></p>
	<p><?echo InputType("checkbox","find_hits","Y",$find_hits,false);?>&nbsp;<?=GetMessage("STAT_HITS")?></p>
	<input type="submit" name="set_filter" value="<?echo GetMessage("STAT_CREATE_GRAPH")?>">
	<input type="hidden" name="set_filter" value="Y">
	<input type="button" onClick="window.close()" value="<?echo GetMessage("STAT_CLOSE")?>">
</form>

<?
else:
	$message = new CAdminMessage([
		'MESSAGE' => GetMessage('STAT_NOT_ENOUGH_DATA'),
		'TYPE' => 'ERROR',
		'SKIP_PUBLIC_MODE' => true,
	]);
	echo $message->Show();
?>
<form><input type="button" onClick="window.close()" value="<?echo GetMessage("STAT_CLOSE")?>"></form>
<?endif?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
