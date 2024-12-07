<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if ($STAT_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

function hrefHtml()
{
	$result = '';
	$params = array();
	$key = '';
	$argNum = func_num_args();
	for ($i = 0; $i < $argNum; $i++)
	{
		if ($i == 0)
			$result = func_get_arg($i);
		elseif ($i % 2)
			$key = func_get_arg($i);
		else
			$params[$key] = func_get_arg($i);
	}
	$result = CHTTP::urlAddParams($result, $params, array("encode" => true));
	return htmlspecialcharsbx($result);
}

$ref = $ref_id = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
{
	$ref[] = "[".$ar["ID"]."] ".$ar["NAME"];
	$ref_id[] = $ar["ID"];
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

$sTableID = "t_stat_list";
$sFilterID = $sTableID."_filter_id";
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find_date1",
	"find_date2",
	"find_site_id"
);

$lAdmin->InitFilter($FilterArr);

$strError="";
AdminListCheckDate($strError, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"SITE_ID"	=> $find_site_id,
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2
);

if ($find_site_id <> '' && $find_site_id!="NOT_REF")
	$site_filter="Y";
else
	$site_filter="N";

if ($arFilter["DATE1"] <> '' || $arFilter["DATE2"] <> '')
	$is_filtered = true;
else
	$is_filtered = false;

$now_date = GetTime(time());
$yesterday_date = GetTime(time()-86400);
$bef_yesterday_date = GetTime(time()-172800);

$sTableID_tab1 = "t_stat_list_tab1";
$oSort_tab1 = new CAdminSorting($sTableID_tab1);
$lAdmin_tab1 = new CAdminList($sTableID_tab1, $oSort_tab1);
$lAdmin_tab1->BeginCustomContent();
if ($strError <> ''):
	CAdminMessage::ShowMessage($strError);
elseif ($_REQUEST["table_id"]=="" || $_REQUEST["table_id"]==$sTableID_tab1):
	$arComm = CTraffic::GetCommonValues($arFilter);
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="list-table">
<tr class="heading">
	<td width="30%">&nbsp;</td>
	<td><?echo GetMessage("STAT_TODAY")?><br><?echo $now_date?></td>
	<td><?echo GetMessage("STAT_YESTERDAY")?><br><?echo $yesterday_date?></td>
	<td><?echo GetMessage("STAT_BEFORE_YESTERDAY")?><br><?echo $bef_yesterday_date?></td>
	<?if ($is_filtered):?>
		<td><?echo GetMessage("STAT_PERIOD")?><br><?echo htmlspecialcharsEx($arFilter["DATE1"])?>&nbsp;- <?echo htmlspecialcharsEx($arFilter["DATE2"])?></td>
	<?endif;?>
	<td><?echo GetMessage("STAT_TOTAL_1")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_HITS")?></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("hit_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $now_date
		,"find_date2", $now_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["TODAY_HITS"])?></a></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("hit_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $yesterday_date
		,"find_date2", $yesterday_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["YESTERDAY_HITS"])?></a></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("hit_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $bef_yesterday_date
		,"find_date2", $bef_yesterday_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["B_YESTERDAY_HITS"])?></a></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell"><a href="<?echo hrefHtml("hit_list.php"
			,"lang", LANGUAGE_ID
			,"find_date1", $find_date1
			,"find_date2", $find_date2
			,"set_filter", "Y"
		)?>"><?echo htmlspecialcharsEx($arComm["PERIOD_HITS"])?></td>
	<?endif;?>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("hit_list.php"
		,"lang", LANGUAGE_ID
		,"del_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["TOTAL_HITS"])?></a></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_HOSTS")?></td>
	<td class="bx-digit-cell"><?echo htmlspecialcharsEx($arComm["TODAY_HOSTS"])?></td>
	<td class="bx-digit-cell"><?echo htmlspecialcharsEx($arComm["YESTERDAY_HOSTS"])?></td>
	<td class="bx-digit-cell"><?echo htmlspecialcharsEx($arComm["B_YESTERDAY_HOSTS"])?></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell">&nbsp;</td>
	<?endif;?>
	<td class="bx-digit-cell"><?echo htmlspecialcharsEx($arComm["TOTAL_HOSTS"])?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_SESSIONS")?></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("session_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $now_date
		,"find_date2", $now_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["TODAY_SESSIONS"])?></a></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("session_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $yesterday_date
		,"find_date2", $yesterday_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["YESTERDAY_SESSIONS"])?></a></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("session_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $bef_yesterday_date
		,"find_date2", $bef_yesterday_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["B_YESTERDAY_SESSIONS"])?></a></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell"><a href="<?echo hrefHtml("session_list.php"
			,"lang", LANGUAGE_ID
			,"find_date1", $find_date1
			,"find_date2", $find_date2
			,"set_filter", "Y"
		)?>"><?echo htmlspecialcharsEx($arComm["PERIOD_SESSIONS"])?></a></td>
	<?endif;?>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("session_list.php"
		,"lang", LANGUAGE_ID
		,"del_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["TOTAL_SESSIONS"])?></a></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_C_EVENTS")?></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("event_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $now_date
		,"find_date2", $now_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["TODAY_EVENTS"])?></a></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("event_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $yesterday_date
		,"find_date2", $yesterday_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["YESTERDAY_EVENTS"])?></a></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("event_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $bef_yesterday_date
		,"find_date2", $bef_yesterday_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["B_YESTERDAY_EVENTS"])?></a></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell"><a href="<?echo hrefHtml("event_list.php"
			,"lang", LANGUAGE_ID
			,"find_date1", $find_date1
			,"find_date2", $find_date2
			,"set_filter", "Y"
		)?>"><?echo htmlspecialcharsEx($arComm["PERIOD_EVENTS"])?></a></td>
	<?endif;?>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("event_list.php"
		,"lang", LANGUAGE_ID
		,"del_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["TOTAL_EVENTS"])?></a></td>
</tr>
<?if ($site_filter!="Y"):?>
<tr class="heading">
	<td colspan="<?echo ($is_filtered? "6": "5")?>"><?echo GetMessage("STAT_GUESTS")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_TOTAL")?></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("guest_list.php"
		,"lang", LANGUAGE_ID
		,"find_last_date1", $now_date
		,"find_last_date2", $now_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["TODAY_GUESTS"])?></a></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("guest_list.php"
		,"lang", LANGUAGE_ID
		,"find_period_date1", $yesterday_date
		,"find_period_date2", $yesterday_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["YESTERDAY_GUESTS"])?></a></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("guest_list.php"
		,"lang", LANGUAGE_ID
		,"find_period_date1", $bef_yesterday_date
		,"find_period_date2", $bef_yesterday_date
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["B_YESTERDAY_GUESTS"])?></a></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell">&nbsp;</td>
	<?endif;?>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("guest_list.php"
		,"lang", LANGUAGE_ID
		,"del_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["TOTAL_GUESTS"])?></a></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_NEW")?></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("guest_list.php"
		,"lang", LANGUAGE_ID
		,"find_period_date1", $now_date
		,"find_period_date2", $now_date
		,"find_sess2", "1"
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["TODAY_NEW_GUESTS"])?></a></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("guest_list.php"
		,"lang", LANGUAGE_ID
		,"find_period_date1", $yesterday_date
		,"find_period_date2", $yesterday_date
		,"find_sess2", "1"
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["YESTERDAY_NEW_GUESTS"])?></a></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("guest_list.php"
		,"lang", LANGUAGE_ID
		,"find_period_date1", $bef_yesterday_date
		,"find_period_date2", $bef_yesterday_date
		,"find_sess2", "1"
		,"set_filter", "Y"
	)?>"><?echo htmlspecialcharsEx($arComm["B_YESTERDAY_NEW_GUESTS"])?></a></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell"><a href="<?echo hrefHtml("guest_list.php"
			,"lang", LANGUAGE_ID
			,"find_period_date1", $find_date1
			,"find_period_date2", $find_date2
			,"find_sess2", "1"
			,"set_filter", "Y"
		)?>"><?echo htmlspecialcharsEx($arComm["PERIOD_NEW_GUESTS"])?></a></td>
	<?endif;?>
	<td class="bx-digit-cell">&nbsp;</td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_ONLINE")?></td>
	<td class="bx-digit-cell"><a href="<?echo hrefHtml("users_online.php"
		,"lang", LANGUAGE_ID
	)?>"><?echo $arComm["ONLINE_GUESTS"]?></a></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<?if ($is_filtered):?>
		<td>&nbsp;</td>
	<?endif;?>
	<td>&nbsp;</td>
</tr>
<?endif;?>
</table>
<?endif;
$lAdmin_tab1->EndCustomContent();
if ($_REQUEST["table_id"]=="" || $_REQUEST["table_id"]==$sTableID_tab1)
	$lAdmin_tab1->CheckListMode();

$sTableID_tab2 = "t_stat_list_tab2";
$oSort_tab2 = new CAdminSorting($sTableID_tab2);
$lAdmin_tab2 = new CAdminList($sTableID_tab2, $oSort_tab2);
$lAdmin_tab2->BeginCustomContent();
if ($strError <> ''):
	CAdminMessage::ShowMessage($strError);
elseif ($site_filter=="Y" && $_REQUEST["table_id"]==$sTableID_tab2):
	CAdminMessage::ShowMessage(GetMessage("STAT_NO_DATA"));
elseif ($_REQUEST["table_id"] == $sTableID_tab2):
	$arADVF["DATE1_PERIOD"] = $arFilter["DATE1"];
	$arADVF["DATE2_PERIOD"] = $arFilter["DATE2"];
	$adv = CAdv::GetList('', '', $arADVF, $is_filtered);
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="list-table">
<tr class="heading" valign="top">
	<td><?echo GetMessage("STAT_ADV_NAME")?></td>
	<td><a href="<?echo hrefHtml("session_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $now_date
		,"find_date2", $now_date
		,"find_adv_back", "N"
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_TODAY")?></a><br><?echo $now_date?></td>
	<td><a href="<?echo hrefHtml("session_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $yesterday_date
		,"find_date2", $yesterday_date
		,"find_adv_back", "N"
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_YESTERDAY")?></a><br><?echo $yesterday_date?></td>
	<td><a href="<?echo hrefHtml("session_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $bef_yesterday_date
		,"find_adv_back", "N"
		,"find_date2", $bef_yesterday_date
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_BEFORE_YESTERDAY")?></a><br><?echo $bef_yesterday_date?></td>
	<?if ($is_filtered):?>
		<td><a href="<?echo hrefHtml("session_list.php"
			,"lang", LANGUAGE_ID
			,"find_date1", $find_date1
			,"find_date2", $find_date2
			,"find_adv_back", "N"
			,"set_filter", "Y"
		)?>"><?echo GetMessage("STAT_PERIOD")?></a><br><?echo htmlspecialcharsEx($arFilter["DATE1"])?>&nbsp;- <?echo htmlspecialcharsEx($arFilter["DATE2"])?></td>
	<?endif;?>
	<td><a href="<?echo hrefHtml("session_list.php"
		,"lang", LANGUAGE_ID
		,"find_adv_back", "N"
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_TOTAL_1")?></a></td>
</tr>
<?
$i = 0;
$total_SESSIONS_TODAY = 0;
$total_SESSIONS_YESTERDAY = 0;
$total_SESSIONS_BEF_YESTERDAY = 0;
$total_SESSIONS_PERIOD = 0;
$total_SESSIONS = 0;
while ($ar = $adv->Fetch())
{
	$i++;
	$total_SESSIONS_TODAY += $ar["SESSIONS_TODAY"];
	$total_SESSIONS_YESTERDAY += $ar["SESSIONS_YESTERDAY"];
	$total_SESSIONS_BEF_YESTERDAY += $ar["SESSIONS_BEF_YESTERDAY"];
	$total_SESSIONS_PERIOD += $ar["SESSIONS_PERIOD"];
	$total_SESSIONS += $ar["SESSIONS"];
	if ($i <= COption::GetOptionInt("statistic","STAT_LIST_TOP_SIZE"))
	{
	?>
	<tr valign="top">
		<td>[<a href="<?echo hrefHtml("adv_list.php"
			,"lang", LANGUAGE_ID
			,"find_id", $ar["ID"]
			,"find_id_exact_match", "Y"
			,"set_filter", "Y"
		)?>"><?echo htmlspecialcharsEx($ar["ID"])?></a>]&nbsp;<?echo htmlspecialcharsEx($ar["REFERER1"])?>&nbsp;/&nbsp;<?echo htmlspecialcharsEx($ar["REFERER2"])?></td>
		<td class="bx-digit-cell">
			<?if ($ar["SESSIONS_TODAY"] > 0):?>
				<a href="<?echo hrefHtml("session_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $now_date
					,"find_date2", $now_date
					,"find_adv_id", $ar["ID"]
					,"find_adv_id_exact_match", "Y"
					,"find_adv_back", "N"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["SESSIONS_TODAY"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["SESSIONS_YESTERDAY"] > 0):?>
				<a href="<?echo hrefHtml("session_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $yesterday_date
					,"find_date2", $yesterday_date
					,"find_adv_id", $ar["ID"]
					,"find_adv_id_exact_match", "Y"
					,"find_adv_back", "N"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["SESSIONS_YESTERDAY"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["SESSIONS_BEF_YESTERDAY"] > 0):?>
				<a href="<?echo hrefHtml("session_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $bef_yesterday_date
					,"find_date2", $bef_yesterday_date
					,"find_adv_id", $ar["ID"]
					,"find_adv_id_exact_match", "Y"
					,"find_adv_back", "N"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["SESSIONS_BEF_YESTERDAY"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<?if ($is_filtered):?>
			<td class="bx-digit-cell">
				<?if ($ar["SESSIONS_PERIOD"] > 0):?>
					<a href="<?echo hrefHtml("session_list.php"
						,"lang", LANGUAGE_ID
						,"find_date1", $find_date1
						,"find_date2", $find_date2
						,"find_adv_id", $ar["ID"]
						,"find_adv_id_exact_match", "Y"
						,"find_adv_back", "N"
						,"set_filter", "Y"
					)?>"><?echo htmlspecialcharsEx($ar["SESSIONS_PERIOD"])?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
		<?endif;?>
		<td class="bx-digit-cell">
			<?if ($ar["SESSIONS"] > 0):?>
				<a href="<?echo hrefHtml("session_list.php"
					,"lang", LANGUAGE_ID
					,"find_adv_id", $ar["ID"]
					,"find_adv_id_exact_match", "Y"
					,"find_adv_back", "N"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["SESSIONS"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	</tr>
	<?
	}
}
?>
<tr>
	<td class="bx-digit-cell"><?echo GetMessage("STAT_TOTAL")?></td>
	<td class="bx-digit-cell"><?echo ($total_SESSIONS_TODAY > 0? $total_SESSIONS_TODAY: '&nbsp;')?></td>
	<td class="bx-digit-cell"><?echo ($total_SESSIONS_YESTERDAY > 0? $total_SESSIONS_YESTERDAY: '&nbsp;')?></td>
	<td class="bx-digit-cell"><?echo ($total_SESSIONS_BEF_YESTERDAY > 0? $total_SESSIONS_BEF_YESTERDAY: '&nbsp;')?></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell"><?echo ($total_SESSIONS_PERIOD > 0? $total_SESSIONS_PERIOD: '&nbsp;')?></td>
	<?endif;?>
	<td class="bx-digit-cell"><?echo ($total_SESSIONS > 0? $total_SESSIONS: '&nbsp;')?></td>
</tr>
</table>
<?
endif;
$lAdmin_tab2->EndCustomContent();
if ($_REQUEST["table_id"] == $sTableID_tab2)
	$lAdmin_tab2->CheckListMode();

$sTableID_tab3 = "t_stat_list_tab3";
$oSort_tab3 = new CAdminSorting($sTableID_tab3);
$lAdmin_tab3 = new CAdminList($sTableID_tab3, $oSort_tab3);
$lAdmin_tab3->BeginCustomContent();
if ($strError <> ''):
	CAdminMessage::ShowMessage($strError);
elseif ($site_filter=="Y" && $_REQUEST["table_id"]==$sTableID_tab3):
	CAdminMessage::ShowMessage(GetMessage("STAT_NO_DATA"));
elseif ($_REQUEST["table_id"] == $sTableID_tab3):
	$arEVENTF["DATE1_PERIOD"] = $arFilter["DATE1"];
	$arEVENTF["DATE2_PERIOD"] = $arFilter["DATE2"];
	if ($e_by == '') $e_by = "s_stat";
	if ($e_order == '') $e_order = "desc";
	$events = CStatEventType::GetList($e_by, $e_order, $arEVENTF, $is_filtered);
	if ($e_by=="s_stat") $e_by = "s_today_counter";
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="list-table">
<tr class="heading" valign="top">
	<td><?echo GetMessage("STAT_EVENT")?></td>
	<td><a href="<?echo hrefHtml("event_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $now_date
		,"find_date2", $now_date
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_TODAY")?></a><br><?echo $now_date?></td>
	<td><a href="<?echo hrefHtml("event_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $yesterday_date
		,"find_date2", $yesterday_date
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_YESTERDAY")?></a><br><?echo $yesterday_date?></td>
	<td><a href="<?echo hrefHtml("event_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $bef_yesterday_date
		,"find_date2", $bef_yesterday_date
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_BEFORE_YESTERDAY")?></a><br><?echo $bef_yesterday_date?></td>
<?if ($is_filtered):?>
	<td><a href="<?echo hrefHtml("event_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $find_date1
		,"find_date2", $find_date2
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_PERIOD")?></a><br><?echo htmlspecialcharsEx($arFilter["DATE1"])?>&nbsp;- <?echo htmlspecialcharsEx($arFilter["DATE2"])?></td>
<?endif;?>
	<td><a href="<?echo hrefHtml("event_list.php"
		,"lang", LANGUAGE_ID
		,"del_filter", "Y"
	)?>"><?echo GetMessage("STAT_TOTAL_1")?></a></td>
</tr>
<?
$i = 0;
$total_TODAY_COUNTER = 0;
$total_YESTERDAY_COUNTER = 0;
$total_B_YESTERDAY_COUNTER = 0;
$total_TOTAL_COUNTER = 0;
$total_PERIOD_COUNTER = 0;
while ($ar = $events->Fetch())
{
	$i++;
	$total_TODAY_COUNTER += $ar["TODAY_COUNTER"];
	$total_YESTERDAY_COUNTER += $ar["YESTERDAY_COUNTER"];
	$total_B_YESTERDAY_COUNTER += $ar["B_YESTERDAY_COUNTER"];
	$total_TOTAL_COUNTER += $ar["TOTAL_COUNTER"];
	$total_PERIOD_COUNTER += $ar["PERIOD_COUNTER"];
	if ($i <= COption::GetOptionInt("statistic","STAT_LIST_TOP_SIZE"))
	{
	?>
	<tr>
		<td>
			<?$dynamic_days = CStatEventType::DynamicDays($ar["ID"]);
			if ($dynamic_days >= 2 && function_exists("ImageCreate")):?>
				<a href="<?echo hrefHtml("event_graph_list.php"
					,"lang", LANGUAGE_ID
					,"find_events[]", $ar["ID"]
					,"set_filter", "Y"
				)?>" title="<?echo GetMessage("STAT_EVENT_GRAPH")?>"><?echo htmlspecialcharsEx($ar["EVENT"])?></a>
			<?else:
				echo htmlspecialcharsEx($ar["EVENT"]);
			endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["TODAY_COUNTER"] > 0):?>
				<a href="<?echo hrefHtml("event_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $now_date
					,"find_date2", $now_date
					,"find_event_id", $ar["ID"]
					,"find_event_id_exact_match", "Y"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["TODAY_COUNTER"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["YESTERDAY_COUNTER"] > 0):?>
				<a href="<?echo hrefHtml("event_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $yesterday_date
					,"find_date2", $yesterday_date
					,"find_event_id", $ar["ID"]
					,"find_event_id_exact_match", "Y"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["YESTERDAY_COUNTER"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["B_YESTERDAY_COUNTER"] > 0):?>
				<a href="<?echo hrefHtml("event_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $bef_yesterday_date
					,"find_date2", $bef_yesterday_date
					,"find_event_id", $ar["ID"]
					,"find_event_id_exact_match", "Y"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["B_YESTERDAY_COUNTER"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell">
			<?if ($ar["PERIOD_COUNTER"] > 0):?>
				<a href="<?echo hrefHtml("event_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $find_date1
					,"find_date2", $find_date2
					,"find_event_id", $ar["ID"]
					,"find_event_id_exact_match", "Y"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["PERIOD_COUNTER"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	<?endif;?>
		<td class="bx-digit-cell">
			<?if ($ar["TOTAL_COUNTER"] > 0):?>
				<a href="<?echo hrefHtml("event_list.php"
					,"lang", LANGUAGE_ID
					,"find_event_id", $ar["ID"]
					,"find_event_id_exact_match", "Y"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["TOTAL_COUNTER"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	</tr>
	<?
	}
}
?>
<tr>
	<td class="bx-digit-cell"><?echo GetMessage("STAT_TOTAL")?></td>
	<td class="bx-digit-cell"><?echo ($total_TODAY_COUNTER > 0? $total_TODAY_COUNTER: '&nbsp;')?></td>
	<td class="bx-digit-cell"><?echo ($total_YESTERDAY_COUNTER > 0? $total_YESTERDAY_COUNTER: '&nbsp;')?></td>
	<td class="bx-digit-cell"><?echo ($total_B_YESTERDAY_COUNTER > 0? $total_B_YESTERDAY_COUNTER: '&nbsp;')?></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell"><?echo ($total_PERIOD_COUNTER > 0? $total_PERIOD_COUNTER: '&nbsp;')?></td>
	<?endif;?>
	<td class="bx-digit-cell"><?echo ($total_TOTAL_COUNTER > 0? $total_TOTAL_COUNTER: '&nbsp;')?></td>
</tr>
</table>
<?
endif;
$lAdmin_tab3->EndCustomContent();
if ($_REQUEST["table_id"]==$sTableID_tab3)
	$lAdmin_tab3->CheckListMode();

$sTableID_tab4 = "t_stat_list_tab4";
$oSort_tab4 = new CAdminSorting($sTableID_tab4);
$lAdmin_tab4 = new CAdminList($sTableID_tab4, $oSort_tab4);
$lAdmin_tab4->BeginCustomContent();
if ($strError <> ''):
	CAdminMessage::ShowMessage($strError);
elseif ($_REQUEST["table_id"]==$sTableID_tab4):
	$referers = CTraffic::GetRefererList('', '', $arFilter, $is_filtered, false);
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="list-table">

<tr class="heading" valign="top">
	<td><?echo GetMessage("STAT_SERVER")?></td>
	<td><a href="<?echo hrefHtml("referer_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $now_date
		,"find_date2", $now_date
		,"group_by", "none"
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_TODAY")?></a><br><?echo $now_date?></td>
	<td><a href="<?echo hrefHtml("referer_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $yesterday_date
		,"find_date2", $yesterday_date
		,"group_by", "none"
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_YESTERDAY")?></a><br><?echo $yesterday_date?></td>
	<td><a href="<?echo hrefHtml("referer_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $bef_yesterday_date
		,"find_date2", $bef_yesterday_date
		,"group_by", "none"
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_BEFORE_YESTERDAY")?></a><br><?echo $bef_yesterday_date?></td>
<?if ($is_filtered):?>
	<td><a href="<?echo hrefHtml("referer_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $find_date1
		,"find_date2", $find_date2
		,"group_by", "none"
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_PERIOD")?></a><br> <?echo htmlspecialcharsEx($arFilter["DATE1"])?>&nbsp;- <?echo htmlspecialcharsEx($arFilter["DATE2"])?></td>
<?endif;?>
	<td><a href="<?echo hrefHtml("referer_list.php"
		,"lang", LANGUAGE_ID
		,"group_by", "none"
		,"del_filter", "Y"
	)?>"><?echo GetMessage("STAT_TOTAL_1")?></a></td>
</tr>
<?
$i = 0;
$total_TODAY_REFERERS = 0;
$total_YESTERDAY_REFERERS = 0;
$total_B_YESTERDAY_REFERERS = 0;
$total_TOTAL_REFERERS = 0;
$total_PERIOD_REFERERS = 0;
while ($ar = $referers->Fetch())
{
	$i++;
	$total_TODAY_REFERERS += $ar["TODAY_REFERERS"];
	$total_YESTERDAY_REFERERS += $ar["YESTERDAY_REFERERS"];
	$total_B_YESTERDAY_REFERERS += $ar["B_YESTERDAY_REFERERS"];
	$total_TOTAL_REFERERS += $ar["TOTAL_REFERERS"];
	$total_PERIOD_REFERERS += $ar["PERIOD_REFERERS"];
	if ($i <= COption::GetOptionInt("statistic","STAT_LIST_TOP_SIZE"))
	{
	?>
	<tr>
		<td><a href="<?echo hrefHtml("referer_list.php"
			,"lang", LANGUAGE_ID
			,"find_from_domain", '"'.$ar["SITE_NAME"].'"'
			,"set_filter", "Y"
		)?>"><?echo htmlspecialcharsEx($ar["SITE_NAME"])?></a></td>
		<td class="bx-digit-cell">
			<?if ($ar["TODAY_REFERERS"] > 0):?>
				<a href="<?echo hrefHtml("referer_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $now_date
					,"find_date2", $now_date
					,"find_from", '"'.$ar["SITE_NAME"].'"'
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["TODAY_REFERERS"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["YESTERDAY_REFERERS"] > 0):?>
				<a href="<?echo hrefHtml("referer_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $yesterday_date
					,"find_date2", $yesterday_date
					,"find_from", '"'.$ar["SITE_NAME"].'"'
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["YESTERDAY_REFERERS"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["B_YESTERDAY_REFERERS"] > 0):?>
				<a href="<?echo hrefHtml("referer_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $bef_yesterday_date
					,"find_date2", $bef_yesterday_date
					,"find_from", '"'.$ar["SITE_NAME"].'"'
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["B_YESTERDAY_REFERERS"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell">
			<?if ($ar["PERIOD_REFERERS"] > 0):?>
				<a href="<?echo hrefHtml("referer_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $find_date1
					,"find_date2", $find_date2
					,"find_from", '"'.$ar["SITE_NAME"].'"'
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["PERIOD_REFERERS"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	<?endif;?>
		<td class="bx-digit-cell">
			<?if ($ar["TOTAL_REFERERS"] > 0):?>
				<a href="<?echo hrefHtml("referer_list.php"
					,"lang", LANGUAGE_ID
					,"find_from", '"'.$ar["SITE_NAME"].'"'
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["TOTAL_REFERERS"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	</tr>
	<?
	}
}
?>
<tr>
	<td class="bx-digit-cell"><?echo GetMessage("STAT_TOTAL")?></td>
	<td class="bx-digit-cell"><?echo ($total_TODAY_REFERERS > 0? $total_TODAY_REFERERS: '&nbsp;')?></td>
	<td class="bx-digit-cell"><?echo ($total_YESTERDAY_REFERERS > 0? $total_YESTERDAY_REFERERS: '&nbsp;')?></td>
	<td class="bx-digit-cell"><?echo ($total_B_YESTERDAY_REFERERS > 0? $total_B_YESTERDAY_REFERERS: '&nbsp;')?></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell"><?echo ($total_PERIOD_REFERERS > 0? $total_PERIOD_REFERERS: '&nbsp;')?></td>
	<?endif;?>
	<td class="bx-digit-cell"><?echo ($total_TOTAL_REFERERS > 0? $total_TOTAL_REFERERS: '&nbsp;')?></td>
</tr>
</table>
<?
endif;
$lAdmin_tab4->EndCustomContent();
if ($_REQUEST["table_id"] == $sTableID_tab4)
	$lAdmin_tab4->CheckListMode();

$sTableID_tab5 = "t_stat_list_tab5";
$oSort_tab5 = new CAdminSorting($sTableID_tab5);
$lAdmin_tab5 = new CAdminList($sTableID_tab5, $oSort_tab5);
$lAdmin_tab5->BeginCustomContent();
if ($strError <> ''):
	CAdminMessage::ShowMessage($strError);
elseif ($_REQUEST["table_id"] == $sTableID_tab5):
	$phrases = CTraffic::GetPhraseList($s_by, $s_order, $arFilter, $is_filtered, false);
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="list-table">
<tr class="heading" valign="top">
	<td><?echo GetMessage("STAT_PHRASE")?></td>
	<td><a href="<?echo hrefHtml("phrase_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $now_date
		,"find_date2", $now_date
		,"set_filter", "Y"
		,"group_by", "none"
		,"menu_item_id", "1"
	)?>"><?echo GetMessage("STAT_TODAY")?></a><br><?echo $now_date?></td>
	<td><a href="<?echo hrefHtml("phrase_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $yesterday_date
		,"find_date2", $yesterday_date
		,"set_filter", "Y"
		,"group_by", "none"
		,"menu_item_id", "1"
	)?>"><?echo GetMessage("STAT_YESTERDAY")?></a><br><?echo $yesterday_date?></td>
	<td><a href="<?echo hrefHtml("phrase_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $bef_yesterday_date
		,"find_date2", $bef_yesterday_date
		,"set_filter", "Y"
		,"group_by", "none"
		,"menu_item_id", "1"
	)?>"><?echo GetMessage("STAT_BEFORE_YESTERDAY")?></a><br><?echo $bef_yesterday_date?></td>
	<?if ($is_filtered):?>
		<td><a href="<?echo hrefHtml("phrase_list.php"
			,"lang", LANGUAGE_ID
			,"find_date1", $find_date1
			,"find_date2", $find_date2
			,"set_filter", "Y"
			,"group_by", "none"
			,"menu_item_id", "1"
		)?>"><?echo GetMessage("STAT_PERIOD")?></a><br> <?echo htmlspecialcharsEx($arFilter["DATE1"])?>&nbsp;- <?echo htmlspecialcharsEx($arFilter["DATE2"])?></td>
	<?endif;?>
	<td><a href="<?echo hrefHtml("phrase_list.php"
		,"lang", LANGUAGE_ID
		,"del_filter", "Y"
		,"group_by", "none"
		,"menu_item_id", "1"
	)?>"><?echo GetMessage("STAT_TOTAL_1")?></a></td>
</tr>
<?
$i = 0;
$total_TODAY_PHRASES = 0;
$total_YESTERDAY_PHRASES = 0;
$total_B_YESTERDAY_PHRASES = 0;
$total_TOTAL_PHRASES = 0;
$total_PERIOD_PHRASES = 0;
while ($ar = $phrases->Fetch())
{
	$i++;
	$total_TODAY_PHRASES += $ar["TODAY_PHRASES"];
	$total_YESTERDAY_PHRASES += $ar["YESTERDAY_PHRASES"];
	$total_B_YESTERDAY_PHRASES += $ar["B_YESTERDAY_PHRASES"];
	$total_TOTAL_PHRASES += $ar["TOTAL_PHRASES"];
	$total_PERIOD_PHRASES += $ar["PERIOD_PHRASES"];
	if ($i <= COption::GetOptionInt("statistic","STAT_LIST_TOP_SIZE"))
	{
	?>
	<tr>
		<td><a href="<?echo hrefHtml("phrase_list.php"
			,"lang", LANGUAGE_ID
			,"find_phrase", '"'.$ar["PHRASE"].'"'
			,"set_filter", "Y"
			,"group_by", "none"
			,"menu_item_id", "1"
			,"find_phrase_exact_match", "Y"
		)?>"><?echo htmlspecialcharsEx(TruncateText($ar["PHRASE"], 50))?></a>&nbsp;</td>
		<td class="bx-digit-cell">
			<?if ($ar["TODAY_PHRASES"] > 0):?>
				<a href="<?echo hrefHtml("phrase_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $now_date
					,"find_date2", $now_date
					,"find_phrase", '"'.$ar["PHRASE"].'"'
					,"set_filter", "Y"
					,"group_by", "none"
					,"menu_item_id", "1"
					,"find_phrase_exact_match", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["TODAY_PHRASES"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["YESTERDAY_PHRASES"] > 0):?>
				<a href="<?echo hrefHtml("phrase_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $yesterday_date
					,"find_date2", $yesterday_date
					,"find_phrase", '"'.$ar["PHRASE"].'"'
					,"set_filter", "Y"
					,"group_by", "none"
					,"menu_item_id", "1"
					,"find_phrase_exact_match", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["YESTERDAY_PHRASES"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["B_YESTERDAY_PHRASES"] > 0):?>
				<a href="<?echo hrefHtml("phrase_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $bef_yesterday_date
					,"find_date2", $bef_yesterday_date
					,"find_phrase", '"'.$ar["PHRASE"].'"'
					,"set_filter", "Y"
					,"group_by", "none"
					,"menu_item_id", "1"
					,"find_phrase_exact_match", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["B_YESTERDAY_PHRASES"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell">
			<?if ($ar["PERIOD_PHRASES"] > 0):?>
				<a href="<?echo hrefHtml("phrase_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $find_date1
					,"find_date2", $find_date2
					,"find_phrase", '"'.$ar["PHRASE"].'"'
					,"set_filter", "Y"
					,"group_by", "none"
					,"menu_item_id", "1"
					,"find_phrase_exact_match", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["PERIOD_PHRASES"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	<?endif;?>
		<td class="bx-digit-cell">
			<?if ($ar["TOTAL_PHRASES"] > 0):?>
				<a href="<?echo hrefHtml("phrase_list.php"
					,"lang", LANGUAGE_ID
					,"find_phrase", '"'.$ar["PHRASE"].'"'
					,"set_filter", "Y"
					,"group_by", "none"
					,"menu_item_id", "1"
					,"find_phrase_exact_match", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["TOTAL_PHRASES"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	</tr>
	<?
	}
}
?>
<tr>
	<td class="bx-digit-cell"><?echo GetMessage("STAT_TOTAL")?></td>
	<td class="bx-digit-cell"><?echo ($total_TODAY_PHRASES > 0? $total_TODAY_PHRASES: '&nbsp;')?></td>
	<td class="bx-digit-cell"><?echo ($total_YESTERDAY_PHRASES > 0? $total_YESTERDAY_PHRASES: '&nbsp;')?></td>
	<td class="bx-digit-cell"><?echo ($total_B_YESTERDAY_PHRASES > 0? $total_B_YESTERDAY_PHRASES: '&nbsp;')?></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell"><?echo ($total_PERIOD_PHRASES > 0? $total_PERIOD_PHRASES: '&nbsp;')?></td>
	<?endif;?>
	<td class="bx-digit-cell"><?echo ($total_TOTAL_PHRASES > 0? $total_TOTAL_PHRASES: '&nbsp;')?></td>
</tr>
</table>
<?
endif;
$lAdmin_tab5->EndCustomContent();
if ($_REQUEST["table_id"] == $sTableID_tab5)
	$lAdmin_tab5->CheckListMode();

$sTableID_tab6 = "t_stat_list_tab6";
$oSort_tab6 = new CAdminSorting($sTableID_tab6);
$lAdmin_tab6 = new CAdminList($sTableID_tab6, $oSort_tab6);
$lAdmin_tab6->BeginCustomContent();
if ($strError <> ''):
	CAdminMessage::ShowMessage($strError);
elseif ($site_filter=="Y" && $_REQUEST["table_id"]==$sTableID_tab6):
	CAdminMessage::ShowMessage(GetMessage("STAT_NO_DATA"));
elseif ($_REQUEST["table_id"] == $sTableID_tab6):
	$arSEARCHERF["DATE1_PERIOD"] = $arFilter["DATE1"];
	$arSEARCHERF["DATE2_PERIOD"] = $arFilter["DATE2"];
	if ($f_by == '') $f_by = "s_stat";
	if ($f_order == '') $f_order = "desc";
	$searchers = CSearcher::GetList($f_by, $f_order, $arSEARCHERF, $is_filtered);
	if ($f_by=="s_stat") $f_by = "s_today_hits";
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="list-table">
<tr class="heading" valign="top">
	<td><?echo GetMessage("STAT_SEARCHER")?></td>
	<td><a href="<?echo hrefHtml("hit_searcher_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $now_date
		,"find_date2", $now_date
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_TODAY")?></a><br><?echo $now_date?></td>
	<td><a href="<?echo hrefHtml("hit_searcher_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $yesterday_date
		,"find_date2", $yesterday_date
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_YESTERDAY")?></a><br><?echo $yesterday_date?></td>

	<td><a href="<?echo hrefHtml("hit_searcher_list.php"
		,"lang", LANGUAGE_ID
		,"find_date1", $bef_yesterday_date
		,"find_date2", $bef_yesterday_date
		,"set_filter", "Y"
	)?>"><?echo GetMessage("STAT_BEFORE_YESTERDAY")?></a><br><?echo $bef_yesterday_date?></td>
	<?if ($is_filtered):?>
		<td><a href="<?echo hrefHtml("hit_searcher_list.php"
			,"lang", LANGUAGE_ID
			,"find_date1", $find_date1
			,"find_date2", $find_date2
			,"set_filter", "Y"
		)?>"><?echo GetMessage("STAT_PERIOD")?></a><br> <?echo htmlspecialcharsEx($arFilter["DATE1"])?>&nbsp;- <?echo htmlspecialcharsEx($arFilter["DATE2"])?></td>
	<?endif;?>
	<td><a href="<?echo hrefHtml("hit_searcher_list.php"
		,"lang", LANGUAGE_ID
		,"del_filter", "Y"
	)?>"><?echo GetMessage("STAT_TOTAL_1")?></a></td>
</tr>
<?
$i = 0;
$total_TODAY_HITS = 0;
$total_YESTERDAY_HITS = 0;
$total_B_YESTERDAY_HITS = 0;
$total_TOTAL_HITS = 0;
$total_PERIOD_HITS = 0;
while ($ar = $searchers->Fetch())
{
	$i++;
	$total_TODAY_HITS += $ar["TODAY_HITS"];
	$total_YESTERDAY_HITS += $ar["YESTERDAY_HITS"];
	$total_B_YESTERDAY_HITS += $ar["B_YESTERDAY_HITS"];
	$total_TOTAL_HITS += $ar["TOTAL_HITS"];
	$total_PERIOD_HITS += $ar["PERIOD_HITS"];
	if ($i <= COption::GetOptionInt("statistic","STAT_LIST_TOP_SIZE"))
	{
	?>
	<tr>
		<td>
			<?$dynamic_days = CSearcher::DynamicDays($ar["ID"]);
			if ($dynamic_days>=2 && function_exists("ImageCreate")):?>
				<a href="<?echo hrefHtml("searcher_graph_list.php"
					,"lang", LANGUAGE_ID
					,"find_searchers[]", $ar["ID"]
					,"set_filter", "Y"
				)?>" title="<?echo GetMessage("STAT_SEARCHER_GRAPH")?>"><?echo htmlspecialcharsEx($ar["NAME"])?></a>
			<?else:?>
				<?echo htmlspecialcharsEx($ar["NAME"])?>
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["TODAY_HITS"] > 0):?>
				<a href="<?echo hrefHtml("hit_searcher_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $now_date
					,"find_date2", $now_date
					,"find_searcher_id", $ar["ID"]
					,"find_searcher_id_exact_match", "Y"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["TODAY_HITS"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["YESTERDAY_HITS"] > 0):?>
				<a href="<?echo hrefHtml("hit_searcher_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $yesterday_date
					,"find_date2", $yesterday_date
					,"find_searcher_id", $ar["ID"]
					,"find_searcher_id_exact_match", "Y"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["YESTERDAY_HITS"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
		<td class="bx-digit-cell">
			<?if ($ar["B_YESTERDAY_HITS"] > 0):?>
				<a href="<?echo hrefHtml("hit_searcher_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $bef_yesterday_date
					,"find_date2", $bef_yesterday_date
					,"find_searcher_id", $ar["ID"]
					,"find_searcher_id_exact_match", "Y"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["B_YESTERDAY_HITS"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell">
			<?if ($ar["PERIOD_HITS"] > 0):?>
				<a href="<?echo hrefHtml("hit_searcher_list.php"
					,"lang", LANGUAGE_ID
					,"find_date1", $find_date1
					,"find_date2", $find_date2
					,"find_searcher_id", $ar["ID"]
					,"find_searcher_id_exact_match", "Y"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["PERIOD_HITS"])?></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	<?endif;?>
		<td class="bx-digit-cell">
			<?if ($ar["TOTAL_HITS"] > 0):?>
				<a href="<?echo hrefHtml("hit_searcher_list.php"
					,"lang", LANGUAGE_ID
					,"find_searcher_id", $ar["ID"]
					,"find_searcher_id_exact_match", "Y"
					,"set_filter", "Y"
				)?>"><?echo htmlspecialcharsEx($ar["TOTAL_HITS"])?></a></a>
			<?else:?>
				&nbsp;
			<?endif;?>
		</td>
	</tr>
	<?
	}
}
?>
<tr>
	<td class="bx-digit-cell"><?echo GetMessage("STAT_TOTAL")?></td>
	<td class="bx-digit-cell"><?echo ($total_TODAY_HITS > 0? $total_TODAY_HITS: '&nbsp;')?></td>
	<td class="bx-digit-cell"><?echo ($total_YESTERDAY_HITS > 0? $total_YESTERDAY_HITS: '&nbsp;')?></td>
	<td class="bx-digit-cell"><?echo ($total_B_YESTERDAY_HITS > 0? $total_B_YESTERDAY_HITS: '&nbsp;')?></td>
	<?if ($is_filtered):?>
		<td class="bx-digit-cell"><?echo ($total_PERIOD_HITS > 0? $total_PERIOD_HITS: '&nbsp;')?></td>
	<?endif;?>
	<td class="bx-digit-cell"><?echo ($total_TOTAL_HITS > 0? $total_TOTAL_HITS: '&nbsp;')?></td>
</tr>
</table>
<?
endif;
$lAdmin_tab6->EndCustomContent();
if ($_REQUEST["table_id"] == $sTableID_tab6)
	$lAdmin_tab6->CheckListMode();

$aTabs = array(
	array(
		"DIV" => "tab1",
		"TAB" => GetMessage("STAT_VISIT"),
		"ICON" => "",
		"TITLE"=>GetMessage("STAT_VISIT_TITLE"),
		"ONSELECT" => "selectTabWithFilter(".$sFilterID.", ".$sTableID_tab1.", 'stat_list.php');"
	),
	array(
		"DIV" => "tab2",
		"TAB" => GetMessage("STAT_ADV"),
		"ICON" => "",
		"TITLE" => GetMessage("STAT_ADV").' ('.GetMessage("STAT_DIRECT_SESSIONS").') (Top '.COption::GetOptionInt("statistic","STAT_LIST_TOP_SIZE").')',
		"ONSELECT" => "selectTabWithFilter(".$sFilterID.", ".$sTableID_tab2.", 'stat_list.php');"
	),
	array(
		"DIV" => "tab3",
		"TAB" => GetMessage("STAT_EVENTS"),
		"ICON" => "",
		"TITLE" => GetMessage("STAT_EVENTS_2").' (Top '.COption::GetOptionInt("statistic","STAT_LIST_TOP_SIZE").')',
		"ONSELECT" => "selectTabWithFilter(".$sFilterID.", ".$sTableID_tab3.", 'stat_list.php');"
	),
	array(
		"DIV" => "tab4",
		"TAB" => GetMessage("STAT_REFERERS"),
		"ICON" => "",
		"TITLE" => GetMessage("STAT_REFERERS").' (Top '.COption::GetOptionInt("statistic","STAT_LIST_TOP_SIZE").')',
		"ONSELECT" => "selectTabWithFilter(".$sFilterID.", ".$sTableID_tab4.", 'stat_list.php');"
	),
	array(
		"DIV" => "tab5",
		"TAB" => GetMessage("STAT_PHRASES"),
		"ICON" => "",
		"TITLE" => GetMessage("STAT_PHRASES").' (Top '.COption::GetOptionInt("statistic","STAT_LIST_TOP_SIZE").')',
		"ONSELECT" => "selectTabWithFilter(".$sFilterID.", ".$sTableID_tab5.", 'stat_list.php');"
	),
	array(
		"DIV" => "tab6",
		"TAB" => GetMessage("STAT_INDEXING"),
		"ICON" => "",
		"TITLE" => GetMessage("STAT_SITE_INDEXING").' (Top '.COption::GetOptionInt("statistic","STAT_LIST_TOP_SIZE").')',
		"ONSELECT" => "selectTabWithFilter(".$sFilterID.", ".$sTableID_tab6.", 'stat_list.php');"
	),
);

$tabControl = new CAdminViewTabControl("tabControl", $aTabs);

$lAdmin->BeginCustomContent();

$aContext = array(
	array(
		"TEXT" => GetMessage("STAT_GRAPH_ALT"),
		"LINK" => CHTTP::urlAddParams("traffic.php", array(
				"lang" => LANGUAGE_ID,
				"find_graph_type" => "date",
				"find_date1_DAYS_TO_BACK" => "90",
				"find_date2" => ConvertTimeStamp(time()-86400, "SHORT"),
				"find_host" => "Y",
				"find_session" => "Y",
				"find_event" => "Y",
				"find_guest" => "Y",
				"find_new_guest" => "Y",
				"set_filter" => "Y",
			), array("encode" => true)),
		"TITLE" => "",
	),
);

$lAdmin->AddAdminContextMenu($aContext, false, false);

$lAdmin->EndCustomContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter($sFilterID, array());
?>

<p><?echo GetMessage("STAT_SERVER_TIME")."&nbsp;&nbsp;".GetTime(time(),"FULL")?></p>

<script>
var currentTable = null;
var cached = [];
function selectTabWithFilter(filter, table, url, force)
{
	var resultDiv = document.getElementById(table.table_id+"_result_div");
	if (resultDiv)
	{
		if (force || !cached[table.table_id])
		{
			if (url.indexOf('?')>=0)
				url += '&lang=<?echo LANG?>&set_filter=Y'+filter.GetParameters();
			else
				url += '?lang=<?echo LANG?>&set_filter=Y'+filter.GetParameters();
			resultDiv.innerHTML='<?echo addslashes(GetMessage("STAT_LOADING_WAIT"))?>';

			filter.OnSet(table.table_id, url);

			cached[table.table_id]=true;
		}
		currentTable = table;
	}
}
function applyFilter(filter, url)
{
	cached=[];
	if (!currentTable)
		currentTable=t_stat_list_tab1;
	if (currentTable)
		selectTabWithFilter(filter, currentTable, url);

}
function clearFilter(filter, url)
{
	filter.ClearParameters();
	applyFilter(filter, url);
}
</script>

<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?$oFilter->Begin();?>
<tr valign="center">
	<td class="bx-digit-cell" width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></td>
</tr>

<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_SERVER")?>:</td>
	<td width="0%" nowrap><?echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("MAIN_ALL"));?></td>
</tr>
<?$oFilter->Buttons()?>
<span class="adm-btn-wrap"><input type="submit" class="adm-btn" name="set_filter" value="<?echo GetMessage("STAT_F_FIND")?>" title="<?echo GetMessage("STAT_F_FIND_TITLE")?>" onClick="BX.adminPanel.showWait(this); applyFilter(<?echo $sFilterID?>, 'stat_list.php?lang=<?echo LANG?>'); return false;"></span>
<span class="adm-btn-wrap"><input type="submit" class="adm-btn" name="del_filter" value="<?echo GetMessage("STAT_F_CLEAR")?>" title="<?echo GetMessage("STAT_F_CLEAR_TITLE")?>" onClick="BX.adminPanel.showWait(this); clearFilter(<?echo $sFilterID?>, 'stat_list.php?lang=<?echo LANG?>'); return false;"></span>
<?
$oFilter->End();
?>
</form>

<?
if ($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>
<div class="adm-detail-content-wrap">
	<div class="adm-detail-content">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
$lAdmin_tab1->DisplayList();
?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/adv_list.php?lang=<?echo LANG?>"><?echo GetMessage("STAT_VIEW_ALL_CAPMPAIGNS")?></a><br><br>
<?$lAdmin_tab2->DisplayList();?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/event_type_list.php?lang=<?echo LANG?>"><?echo GetMessage("STAT_VIEW_ALL_EVENTS")?></a><br><br>
<?$lAdmin_tab3->DisplayList();?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/referer_list.php?lang=<?echo LANG?>&amp;group_by=none&amp;del_filter=Y"><?echo GetMessage("STAT_VIEW_ALL_REFERERS")?></a><br><br>
<?$lAdmin_tab4->DisplayList();?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/phrase_list.php?lang=<?echo LANG?>&amp;set_default=Y&amp;group_by=none&amp;menu_item_id=1"><?echo GetMessage("STAT_VIEW_ALL_PHRASES")?></a><br><br>
<?$lAdmin_tab5->DisplayList();?>

<?$tabControl->BeginNextTab();?>
<a href="/bitrix/admin/searcher_list.php?lang=<?echo LANG?>"><?echo GetMessage("STAT_VIEW_ALL_SEACHERS")?></a><br><br>
<?$lAdmin_tab6->DisplayList();?>

<?$tabControl->End();?>
	</div>
	<br />
</div>
<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
