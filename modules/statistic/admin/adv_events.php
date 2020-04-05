<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$ID = intval($ID);
$FilterArr = Array(
	"find_id",
	"find_event1",
	"find_event2",
	"find_keywords",
	"find_date1",
	"find_date2",
	"find_group");
if (strlen($set_filter)>0) InitFilterEx($FilterArr,"ADV_EVENTS","set");
else InitFilterEx($FilterArr,"ADV_EVENTS","get");
if (strlen($del_filter)>0) DelFilterEx($FilterArr,"ADV_EVENTS");
if (strlen($find_id)>0				||
	strlen($find_event1)>0			||
	strlen($find_event2)>0			||
	strlen($find_keywords)>0		||
	strlen($find_date1)>0	||
	strlen($find_date2)>0	||
	$find_group!="NOT_REF")
{

	if(AdminListCheckDate($strError, array("find_date1"=>$find_date1, "find_date2"=>$find_date2)))
	{
		$arFilter = Array(
			"ID" => $find_id,
			"EVENT1" => $find_event1,
			"EVENT2" => $find_event2,
			"KEYWORDS" => $find_keywords,
			"DATE1_PERIOD" => $find_date1,
			"DATE2_PERIOD" => $find_date2,
			"GROUP" => $find_group,
		);
	}
}
$events = CAdv::GetEventList($ID, $by, $order, $arFilter, $is_filtered);
$find_group = (strlen($find_group)<=0) ? "NOT_REF" : $find_group;
/***************************************************************************
				HTML form
****************************************************************************/
$APPLICATION->SetTitle(str_replace("#ID#","$ID",GetMessage("STAT_TITLE")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php")
?>
<?echo ShowError($strError);?>
<?echo BeginFilter("ADV_EVENTS", $is_filtered);?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<tr>
	<td class="tablebody"><font class="tablefieldtext">ID:</font></td>
	<td class="tablebody"><input class="typeinput" type="text" name="find_id" size="47" value="<?echo htmlspecialchars($find_id)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td class="tablebody"><font class="tablefieldtext">event1:</font></td>
	<td class="tablebody"><input class="typeinput" type="text" name="find_event1" size="47" value="<?echo htmlspecialchars($find_event1)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td class="tablebody"><font class="tablefieldtext">event2:</font></td>
	<td class="tablebody"><input class="typeinput" type="text" name="find_event2" size="47" value="<?echo  htmlspecialchars($find_event2)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td class="tablebody"><font class="tablefieldtext"><?echo GetMessage("STAT_F_KEYWORDS")?></font></td>
	<td class="tablebody"><input class="typeinput" type="text" name="find_keywords" size="47" value="<?echo htmlspecialchars($find_keywords)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td class="tablebody" width="0%" nowrap><font class="tablefieldtext"><?echo GetMessage("STAT_F_PERIOD")." (".CLang::GetDateFormat("SHORT")."):"?></font></td>
	<td class="tablebody" width="0%" nowrap><font class="tablefieldtext"><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></font></td>
</tr>
<tr valign="center">
	<td class="tablebody" width="0%" nowrap><font class="tablefieldtext"><?echo GetMessage("STAT_F_GROUP_BY")?></font></td>
	<td class="tablebody" width="0%" nowrap><?
		$arr = array("reference"=>array("event1", "event2"), "reference_id"=>array("event1","event2"));
		echo SelectBoxFromArray("find_group", $arr, htmlspecialchars($find_group), "(".GetMessage("STAT_NO").")");
		?></td>
</tr>
<?=ShowLogicRadioBtn()?>
<tr>
	<td colspan="2" align="right" nowrap class="tablebody">
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td width="0%"><font class="tablebodytext"><input type="hidden" name="lang" value="<?=LANGUAGE_ID?>"><input type="hidden" name="ID" value="<?=$ID?>"><input type="hidden" name="set_filter" value="Y"><input class="button" type="submit" name="set_filter" value="<?echo GetMessage("STAT_F_SET_FILTER")?>"></font></td>
				<td width="0%"><font class="tablebodytext">&nbsp;</font></td>
				<td width="100%" align="left"><font class="tablebodytext"><input class="button" type="submit" name="del_filter" value="<?echo GetMessage("STAT_F_DEL_FILTER")?>"></font></td>
				<td width="0%"><?ShowAddFavorite()?></td>
			</tr>
		</table>
	</td>
</tr>
</form>
<?echo EndFilter();?>
<br>
<p><?
$total_COUNTER = 0;
$total_COUNTER_BACK = 0;
$arEvents = array();
while ($arEvent = $events->Fetch())
{
	$arEvents[] = $arEvent;
	$total_COUNTER += intval($arEvent["COUNTER"]);
	$total_COUNTER_BACK += intval($arEvent["COUNTER_BACK"]);
}
$events = new CDBResult;
$events->InitFromArray($arEvents);
$events->NavStart(); echo $events->NavPrint(GetMessage("STAT_ADV_EVENTS_PAGES"));
?></p>
<table border="0" width="100%" cellspacing="1" cellpadding="3">
	<tr>
		<?if ($find_group=="NOT_REF") : ?>
		<td valign="top" align="center" width="0%" nowrap class="tablehead1">
			<font class="tableheadtext">ID<br><?echo SortingEx("s_id")?></font></td>
		<?endif;?>
		<?if ($find_group=="NOT_REF" || $find_group=="event1") : ?>
		<td valign="top" align="center" nowrap class="tablehead<?echo $find_group=="event1" ? 1 : 2?>">
			<font class="tableheadtext">event1<br><?echo SortingEx("s_event1")?></font></td>
		<?endif;?>
		<?if ($find_group=="NOT_REF" || $find_group=="event2") : ?>
		<td valign="top" align="center" nowrap class="tablehead<?echo $find_group=="event2" ? 1 : 2?>">
			<font class="tableheadtext">event2<br><?echo SortingEx("s_event2")?></font></td>
		<?endif;?>
		<?if ($find_group=="NOT_REF") : ?>
		<td valign="top" align="center" nowrap class="tablehead2">
			<font class="tableheadtext"><?=GetMessage("STAT_SORT")?><br><?echo SortingEx("s_sort")?></font></td>
		<td valign="top" align="center" nowrap class="tablehead2" width="30%">
			<font class="tableheadtext"><?=GetMessage("STAT_NAME")?><br><?echo SortingEx("s_name")?></font></td>
		<td valign="top" align="center" nowrap class="tablehead2" width="70%">
			<font class="tableheadtext"><?=GetMessage("STAT_DESCRIPTION")?><br><?echo SortingEx("s_description")?></font></td>
		<?endif;?>
		<td valign="top" align="center" nowrap class="tablehead2">
			<font class="tableheadtext"><?=GetMessage("STAT_COUNTER")?><br><?echo SortingEx("s_counter")?></font></td>
		<td valign="top" align="center" class="tablehead3">
			<font class="tableheadtext"><?=GetMessage("STAT_COUNTER_BACK")?><br><?echo SortingEx("s_counter_back")?></font></td>
	</tr>
	<?
	$bs = 2;
	if ($find_group=="event1" || $find_group=="event2") $bs=1;
	while ($events->NavNext(true, "f_")) :
	?>
	<tr align="left" valign="top">
		<?if ($find_group=="NOT_REF") : ?>
		<td align="center" class="tablebody1"><font class="tablebodytext">&nbsp;<?echo $f_ID?></font></td>
		<?endif;?>
		<?if ($find_group=="NOT_REF" || $find_group=="event1") : ?>
		<td class="tablebody<?=$bs?>" <?if ($find_group!="NOT_REF") echo "width='100%'"?>><font class="tablebodytext">&nbsp;<?echo $f_EVENT1?></font></td>
		<?endif;?>
		<?if ($find_group=="NOT_REF" || $find_group=="event2") : ?>
		<td class="tablebody<?=$bs?>" <?if ($find_group!="NOT_REF") echo "width='100%'"?>><font class="tablebodytext">&nbsp;<?echo $f_EVENT2?></font></td>
		<?endif;?>
		<?if ($find_group=="NOT_REF") : ?>
		<td class="tablebody2"><font class="tablebodytext">&nbsp;<?echo $f_C_SORT?></font></td>
		<td class="tablebody2"><font class="tablebodytext">&nbsp;<?echo $f_NAME?></font></td>
		<td class="tablebody2"><font class="tablebodytext">&nbsp;<?echo $f_DESCRIPTION?></font></td>
		<?endif;?>
		<td align="right" class="tablebody2"><font class="tablebodytext">&nbsp;<?echo $f_COUNTER_PERIOD?></font></td>
		<td align="right" class="tablebody3"><font class="tablebodytext">&nbsp;<?echo $f_COUNTER_BACK_PERIOD?></font></td>
	</tr>
	<?
	endwhile;
	$s = "colspan=6";
	$s2 = "colspan=8";
	$str = GetMessage("STAT_TOTAL_EVENT_TYPES");
	if ($find_group=="event1" || $find_group=="event2")
	{
		$s = "";
		$s2 = "colspan=3";
		$str = GetMessage("STAT_TOTAL_RECORDS");
	}
	?>
	<tr valign="top">
		<td class="tablebody1 selectedbody" <?=$s?> align="right"><font class="tablebodytext"><?echo GetMessage("STAT_TOTAL")?></font></td>
		<td class="tablebody2 selectedbody" align="right"><font class="tablebodytext"><?echo $total_COUNTER?></font></td>
		<td class="tablebody3 selectedbody" align="right"><font class="tablebodytext"><?echo $total_COUNTER_BACK?></font></td>
	</tr>
	<tr valign="top">
		<td class="tablebody4 selectedbody" <?=$s2?>><font class="tablebodytext"><?=$str?>&nbsp;<?echo count($arEvents)?></font></td>
	</tr>
</table>
<p><?echo $events->NavPrint(GetMessage("STAT_ADV_EVENTS_PAGES"))?></p>
<div align="left">
<input class="button" type="button" onClick="window.close()" value="<?echo GetMessage("STAT_CLOSE")?>"></div>
</form>
<script>
<!--
document.form1.find_keywords.focus();
// -->
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php")?>