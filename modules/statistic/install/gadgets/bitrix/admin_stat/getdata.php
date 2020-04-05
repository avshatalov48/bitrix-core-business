<?
define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
__IncludeLang(dirname(__FILE__)."/lang/".LANGUAGE_ID."/getdata.php");

if(!CModule::IncludeModule("statistic"))
	die();

if($GLOBALS["APPLICATION"]->GetGroupRight("statistic")=="D")
	die();

$arFilter = array(
	"SITE_ID" => $_REQUEST["site_id"]
);

$now_date = GetTime(time());
$yesterday_date = GetTime(time()-86400);
$bef_yesterday_date = GetTime(time()-172800);

if($_REQUEST["table_id"] == "adv"):
	if(strlen($_REQUEST["site_id"]))
		die();
	$rsAdv = CAdv::GetList($a_by, $a_order, $arFilter, $is_filtered, 10, $arrGROUP_DAYS, $v);

	?><table class="bx-gadgets-table">
	<tbody>
	<tr>
		<th><?echo GetMessage("GD_STAT_ADV_NAME")?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/session_list.php", array(
				'lang' => $_REQUEST["lang"],
				'find_date1' => $now_date,
				'find_date2' => $now_date,
				'find_adv_back' => 'N',
				'set_filter' => 'Y',
			), array("encode" => true)))?>"><?=GetMessage("GD_STAT_TODAY")?></a><br><?=$now_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/session_list.php", array(
				'lang' => $_REQUEST["lang"],
				'find_date1' => $yesterday_date,
				'find_date2' => $yesterday_date,
				'find_adv_back' => 'N',
				'set_filter' => 'Y',
			), array("encode" => true)))?>"><?=GetMessage("GD_STAT_YESTERDAY")?></a><br><?=$yesterday_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/session_list.php", array(
				'lang' => $_REQUEST["lang"],
				'find_date1' => $bef_yesterday_date,
				'find_date2' => $bef_yesterday_date,
				'find_adv_back' => 'N',
				'set_filter' => 'Y',
			), array("encode" => true)))?>"><?=GetMessage("GD_STAT_BEFORE_YESTERDAY")?></a><br><?=$bef_yesterday_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/session_list.php", array(
				'lang' => $_REQUEST["lang"],
				'find_adv_back' => 'N',
				'del_filter' => 'Y',
			), array("encode" => true)))?>"><?=GetMessage("GD_STAT_TOTAL_1")?></a></th>
	</tr><?

	$bFound = false;
	while ($arAdv = $rsAdv->GetNext()):
		?><tr>
			<td>[<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/adv_list.php", array(
				'lang' => $_REQUEST["lang"],
				'find_id' => $arAdv["ID"],
				'find_id_exact_match' => 'Y',
				'set_filter' => 'Y',
			), array("encode" => true)))?>"><?=$arAdv["ID"]?></a>]&nbsp;<?=$arAdv["REFERER1"]?>&nbsp;/&nbsp;<?=$arAdv["REFERER2"]?></td>
			<td align="right"><?
				if (intval($arAdv["SESSIONS_TODAY"]) > 0):
					?><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/session_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_date1' => $now_date,
						'find_date2' => $now_date,
						'find_adv_id' => $arAdv["ID"],
						'find_adv_id_exact_match' => 'Y',
						'find_adv_back' => 'N',
						'set_filter' => 'Y',
					), array("encode" => true)))?>"><?=$arAdv["SESSIONS_TODAY"]?></a><?
				else:
					?>&nbsp;<?
				endif;
			?></td>
			<td align="right"><?
				if (intval($arAdv["SESSIONS_YESTERDAY"])>0):
					?><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/session_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_date1' => $yesterday_date,
						'find_date2' => $yesterday_date,
						'find_adv_id' => $arAdv["ID"],
						'find_adv_id_exact_match' => 'Y',
						'find_adv_back' => 'N',
						'set_filter' => 'Y',
					), array("encode" => true)))?>"><?=$arAdv["SESSIONS_YESTERDAY"]?></a><?
				else:
					?>&nbsp;<?
				endif;
			?></td>
			<td align="right"><?
				if (intval($arAdv["SESSIONS_BEF_YESTERDAY"])>0):
					?><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/session_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_date1' => $bef_yesterday_date,
						'find_date2' => $bef_yesterday_date,
						'find_adv_id' => $arAdv["ID"],
						'find_adv_id_exact_match' => 'Y',
						'find_adv_back' => 'N',
						'set_filter' => 'Y',
					), array("encode" => true)))?>"><?=$arAdv["SESSIONS_BEF_YESTERDAY"]?></a><?
				else:
					?>&nbsp;<?
				endif;
			?></td>
			<td align="right"><?
				if (intval($arAdv["SESSIONS"])>0):
					?><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/session_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_adv_id' => $arAdv["ID"],
						'find_adv_id_exact_match' => 'Y',
						'find_adv_back' => 'N',
						'set_filter' => 'Y',
					), array("encode" => true)))?>"><?=$arAdv["SESSIONS"]?></a><?
				else:
					?>&nbsp;<?
				endif;
			?></td>
		</tr><?
		$bFound = true;
	endwhile;

	if (!$bFound):
		?><tr><td align="center" colspan="5"><?=GetMessage("GD_STAT_NO_DATA")?></td></tr><?
	endif;

	?></tbody>
	</table><?

elseif($_REQUEST["table_id"] == "event"):
	if(strlen($_REQUEST["site_id"]))
		die();

	$e_by = "s_stat";
	$e_order = "desc";
	$rsEvents = CStatEventType::GetList($e_by, $e_order, $arEVENTF, $is_filtered, 10);

	?><table class="bx-gadgets-table">
	<tbody>
	<tr>
		<th><?echo GetMessage("GD_STAT_EVENT")?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/event_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_date1' => $now_date,
			'find_date2' => $now_date,
			'set_filter' => 'Y',
		), array("encode" => true)))?>"><?=GetMessage("GD_STAT_TODAY")?></a><br><?=$now_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/event_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_date1' => $yesterday_date,
			'find_date2' => $yesterday_date,
			'set_filter' => 'Y',
		), array("encode" => true)))?>"><?=GetMessage("GD_STAT_YESTERDAY")?></a><br><?=$yesterday_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/event_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_date1' => $bef_yesterday_date,
			'find_date2' => $bef_yesterday_date,
			'set_filter' => 'Y',
		), array("encode" => true)))?>?>"><?=GetMessage("GD_STAT_BEFORE_YESTERDAY")?></a><br><?=$bef_yesterday_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/event_list.php", array(
			'lang' => $_REQUEST["lang"],
			'del_filter' => 'Y',
		), array("encode" => true)))?>"><?=GetMessage("GD_STAT_TOTAL_1")?></a></th>
	</tr><?

	$bFound = false;
	while ($arEvent = $rsEvents->GetNext()):
		?><tr>
			<td><?
				$dynamic_days = CStatEventType::DynamicDays($arEvent["ID"]);
				if ($dynamic_days >= 2 && function_exists("ImageCreate")):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/event_graph_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_events[]' => $arEvent["ID"],
						'set_filter' => 'Y',
					), array("encode" => true)))?>" title="<?=GetMessage("GD_STAT_EVENT_GRAPH")?>"><?=$arEvent["EVENT"]?></a>
				<?else:?>
					<?=$arEvent["EVENT"]?>
				<?endif;?>
			</td>
			<td align="right"><?
				if (intval($arEvent["TODAY_COUNTER"]) > 0):
					?><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/event_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_date1' => $now_date,
						'find_date2' => $now_date,
						'find_event_id' => $arEvent["ID"],
						'find_event_id_exact_match' => 'Y',
						'set_filter' => 'Y',
					), array("encode" => true)))?>"><?=$arEvent["TODAY_COUNTER"]?></a><?
				else:
					?>&nbsp;<?
				endif;
			?></td>
			<td align="right">
				<?if (intval($arEvent["YESTERDAY_COUNTER"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/event_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_date1' => $yesterday_date,
						'find_date2' => $yesterday_date,
						'find_event_id' => $arEvent["ID"],
						'find_event_id_exact_match' => 'Y',
						'set_filter' => 'Y',
					), array("encode" => true)))?>"><?=$arEvent["YESTERDAY_COUNTER"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
			<td align="right">
				<?if (intval($arEvent["B_YESTERDAY_COUNTER"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/event_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_date1' => $bef_yesterday_date,
						'find_date2' => $bef_yesterday_date,
						'find_event_id' => $arEvent["ID"],
						'find_event_id_exact_match' => 'Y',
						'set_filter' => 'Y',
					), array("encode" => true)))?>"><?=$arEvent["B_YESTERDAY_COUNTER"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
			<td align="right">
				<?if (intval($arEvent["TOTAL_COUNTER"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/event_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_event_id' => $arEvent["ID"],
						'find_event_id_exact_match' => 'Y',
						'set_filter' => 'Y',
					), array("encode" => true)))?>"><?=$arEvent["TOTAL_COUNTER"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
		</tr><?
		$bFound = true;
	endwhile;

	if (!$bFound):
		?><tr><td align="center" colspan="5"><?=GetMessage("GD_STAT_NO_DATA")?></td></tr><?
	endif;

	?></tbody>
	</table><?

elseif($_REQUEST["table_id"] == "referer"):
	$rsReferers = CTraffic::GetRefererList($by, $order, $arFilter, $is_filtered, 10);

	?><table class="bx-gadgets-table">
	<tbody>
	<tr>
		<th><?=GetMessage("GD_STAT_SERVER")?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/referer_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_site_id' => $_REQUEST["site_id"],
			'find_date1' => $now_date,
			'find_date2' => $now_date,
			'group_by' => 'none',
			'set_filter' => 'Y',
		), array("encode" => true, "skip_empty" => true)))?>"><?=GetMessage("GD_STAT_TODAY")?></a><br><?=$now_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/referer_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_site_id' => $_REQUEST["site_id"],
			'find_date1' => $yesterday_date,
			'find_date2' => $yesterday_date,
			'group_by' => 'none',
			'set_filter' => 'Y',
		), array("encode" => true, "skip_empty" => true)))?>"><?=GetMessage("GD_STAT_YESTERDAY")?></a><br><?=$yesterday_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/referer_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_site_id' => $_REQUEST["site_id"],
			'find_date1' => $bef_yesterday_date,
			'find_date2' => $bef_yesterday_date,
			'group_by' => 'none',
			'set_filter' => 'Y',
		), array("encode" => true, "skip_empty" => true)))?>"><?=GetMessage("GD_STAT_BEFORE_YESTERDAY")?></a><br><?=$bef_yesterday_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/referer_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_site_id' => $_REQUEST["site_id"],
			'group_by' => 'none',
			'del_filter' => 'Y',
		), array("encode" => true, "skip_empty" => true)))?>"><?=GetMessage("GD_STAT_TOTAL_1")?></a></th>
	</tr><?

	$bFound = false;
	while ($arReferer = $rsReferers->GetNext()):
		?><tr>
			<td><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/referer_list.php", array(
				'lang' => $_REQUEST["lang"],
				'find_site_id' => $_REQUEST["site_id"],
				'find_from_domain' => "\"".$arReferer["SITE_NAME"]."\"",
				'set_filter' => 'Y',
			), array("encode" => true, "skip_empty" => true)))?>"><?=$arReferer["SITE_NAME"]?></a></td>
			<td align="center">
				<?if(intval($arReferer["TODAY_REFERERS"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/referer_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_site_id' => $_REQUEST["site_id"],
						'find_date1' => $now_date,
						'find_date2' => $now_date,
						'find_from' => "\"".$arReferer["SITE_NAME"]."\"",
						'set_filter' => 'Y',
					), array("encode" => true, "skip_empty" => true)))?>"><?=$arReferer["TODAY_REFERERS"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
			<td align="center">
				<?if(intval($arReferer["YESTERDAY_REFERERS"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/referer_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_site_id' => $_REQUEST["site_id"],
						'find_date1' => $yesterday_date,
						'find_date2' => $yesterday_date,
						'find_from' => "\"".$arReferer["SITE_NAME"]."\"",
						'set_filter' => 'Y',
					), array("encode" => true, "skip_empty" => true)))?>"><?=$arReferer["YESTERDAY_REFERERS"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
			<td align="center">
				<?if(intval($arReferer["B_YESTERDAY_REFERERS"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/referer_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_site_id' => $_REQUEST["site_id"],
						'find_date1' => $bef_yesterday_date,
						'find_date2' => $bef_yesterday_date,
						'find_from' => "\"".$arReferer["SITE_NAME"]."\"",
						'set_filter' => 'Y',
					), array("encode" => true, "skip_empty" => true)))?>"><?=$arReferer["B_YESTERDAY_REFERERS"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
			<td align="center">
				<?if(intval($arReferer["TOTAL_REFERERS"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/referer_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_site_id' => $_REQUEST["site_id"],
						'find_from' => "\"".$arReferer["SITE_NAME"]."\"",
						'set_filter' => 'Y',
					), array("encode" => true, "skip_empty" => true)))?>"><?=$arReferer["TOTAL_REFERERS"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
		</tr><?
		$bFound = true;
	endwhile;

	if (!$bFound):
		?><tr><td align="center" colspan="5"><?=GetMessage("GD_STAT_NO_DATA")?></td></tr><?
	endif;

	?></tbody>
	</table><?

elseif($_REQUEST["table_id"] == "phrase"):
	$rsPhrases = CTraffic::GetPhraseList($s_by, $s_order, $arFilter, $is_filtered, 10);

	?><table class="bx-gadgets-table">
	<tbody>
	<tr>
		<th><?=GetMessage("GD_STAT_PHRASE")?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/phrase_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_site_id' => $_REQUEST["site_id"],
			'find_date1' => $now_date,
			'find_date2' => $now_date,
			'group_by' => 'none',
			'menu_item_id' => 1,
			'set_filter' => 'Y',
		), array("encode" => true, "skip_empty" => true)))?>"><?=GetMessage("GD_STAT_TODAY")?></a><br><?=$now_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/phrase_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_site_id' => $_REQUEST["site_id"],
			'find_date1' => $yesterday_date,
			'find_date2' => $yesterday_date,
			'group_by' => 'none',
			'menu_item_id' => 1,
			'set_filter' => 'Y',
		), array("encode" => true, "skip_empty" => true)))?>"><?=GetMessage("GD_STAT_YESTERDAY")?></a><br><?=$yesterday_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/phrase_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_site_id' => $_REQUEST["site_id"],
			'find_date1' => $bef_yesterday_date,
			'find_date2' => $bef_yesterday_date,
			'group_by' => 'none',
			'menu_item_id' => 1,
			'set_filter' => 'Y',
		), array("encode" => true, "skip_empty" => true)))?>"><?=GetMessage("GD_STAT_BEFORE_YESTERDAY")?></a><br><?=$bef_yesterday_date?></th>
		<th><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/phrase_list.php", array(
			'lang' => $_REQUEST["lang"],
			'find_site_id' => $_REQUEST["site_id"],
			'group_by' => 'none',
			'menu_item_id' => 1,
			'del_filter' => 'Y',
		), array("encode" => true, "skip_empty" => true)))?>"><?=GetMessage("GD_STAT_TOTAL_1")?></a></th>
	</tr><?
	$bFound = false;
	while ($arPhrase = $rsPhrases->Fetch()):
		?><tr>
			<td><a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/phrase_list.php", array(
				'lang' => $_REQUEST["lang"],
				'find_site_id' => $_REQUEST["site_id"],
				'find_phrase' => htmlspecialcharsback($arPhrase["PHRASE"]),
				'find_phrase_exact_match' => 'Y',
				'group_by' => 'none',
				'menu_item_id' => 1,
				'set_filter' => 'Y',
			), array("encode" => true, "skip_empty" => true)))?>"><?echo htmlspecialcharsbx(TruncateText($arPhrase["PHRASE"], 50))?></a>&nbsp;</td>
			<td align="center">
				<?if(intval($arReferer["TODAY_PHRASES"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/phrase_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_site_id' => $_REQUEST["site_id"],
						'find_date1' => $now_date,
						'find_date2' => $now_date,
						'find_phrase' => htmlspecialcharsback($arPhrase["PHRASE"]),
						'find_phrase_exact_match' => 'Y',
						'group_by' => 'none',
						'menu_item_id' => 1,
						'set_filter' => 'Y',
					), array("encode" => true, "skip_empty" => true)))?>"><?=$arPhrase["TODAY_PHRASES"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
			<td align="center">
				<?if(intval($arReferer["YESTERDAY_PHRASES"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/phrase_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_site_id' => $_REQUEST["site_id"],
						'find_date1' => $yesterday_date,
						'find_date2' => $yesterday_date,
						'find_phrase' => htmlspecialcharsback($arPhrase["PHRASE"]),
						'find_phrase_exact_match' => 'Y',
						'group_by' => 'none',
						'menu_item_id' => 1,
						'set_filter' => 'Y',
					), array("encode" => true, "skip_empty" => true)))?>"><?=$arPhrase["YESTERDAY_PHRASES"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
			<td align="center">
				<?if(intval($arReferer["B_YESTERDAY_PHRASES"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/phrase_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_site_id' => $_REQUEST["site_id"],
						'find_date1' => $bef_yesterday_date,
						'find_date2' => $bef_yesterday_date,
						'find_phrase' => htmlspecialcharsback($arPhrase["PHRASE"]),
						'find_phrase_exact_match' => 'Y',
						'group_by' => 'none',
						'menu_item_id' => 1,
						'set_filter' => 'Y',
					), array("encode" => true, "skip_empty" => true)))?>"><?=$arPhrase["B_YESTERDAY_PHRASES"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
			<td align="center">
				<?if(intval($arReferer["TOTAL_PHRASES"]) > 0):?>
					<a href="<?echo htmlspecialcharsbx(CHTTP::urlAddParams("/bitrix/admin/phrase_list.php", array(
						'lang' => $_REQUEST["lang"],
						'find_site_id' => $_REQUEST["site_id"],
						'find_phrase' => htmlspecialcharsback($arPhrase["PHRASE"]),
						'find_phrase_exact_match' => 'Y',
						'group_by' => 'none',
						'menu_item_id' => 1,
						'set_filter' => 'Y',
					), array("encode" => true, "skip_empty" => true)))?>"><?=$arPhrase["TOTAL_PHRASES"]?></a>
				<?else:?>
					&nbsp;
				<?endif;?>
			</td>
		</tr><?
		$bFound = true;
	endwhile;

	if (!$bFound):
		?><tr><td align="center" colspan="5"><?=GetMessage("GD_STAT_NO_DATA")?></td></tr><?
	endif;

	?></tbody>
	</table><?
endif;
?>