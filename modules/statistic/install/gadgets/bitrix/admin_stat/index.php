<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<?
if(!CModule::IncludeModule("statistic"))
	return false;

if($GLOBALS["APPLICATION"]->GetGroupRight("statistic")=="D")
	return false;

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");

$arGadgetParams["RND_STRING"] = randString(8);

$arGadgetParams["HIDE_GRAPH"] = ($arGadgetParams["HIDE_GRAPH"] == "Y" ? "Y" : "N");

if ($arGadgetParams["HIDE_GRAPH"] != "Y")
{
	if (intval($arGadgetParams["GRAPH_DAYS"]) <= 0 || intval($arGadgetParams["GRAPH_DAYS"]) > 400)
		$arGadgetParams["GRAPH_DAYS"] = 30;

	if (!is_array($arGadgetParams["GRAPH_PARAMS"])
		|| count($arGadgetParams["GRAPH_PARAMS"]) <= 0
	)
		$arGadgetParams["GRAPH_PARAMS"] = array("HOST", "SESSION", "EVENT", "GUEST");

	if (intval($arGadgetParams["GRAPH_WIDTH"]) <= 50 || intval($arGadgetParams["GRAPH_WIDTH"]) > 1000)
		$arGadgetParams["GRAPH_WIDTH"] = 400;
	if (intval($arGadgetParams["GRAPH_HEIGHT"]) <= 50 || intval($arGadgetParams["GRAPH_HEIGHT"]) > 1000)
		$arGadgetParams["GRAPH_HEIGHT"] = 300;
}

if ($arGadgetParams["SITE_ID"] == '')
	$arGadgetParams["SITE_ID"] = false;
elseif ($arGadgetParams["TITLE_STD"] == '')
{
	$rsSites = CSite::GetByID($arGadgetParams["SITE_ID"]);
	if ($arSite = $rsSites->GetNext())
		$arGadget["TITLE"] .= " / [".$arSite["ID"]."] ".$arSite["NAME"];
}

$now_date = GetTime(time());
$yesterday_date = GetTime(time()-86400);
$bef_yesterday_date = GetTime(time()-172800);

$arFilter = array();
if ($arGadgetParams["SITE_ID"])
{
	$arFilter["SITE_ID"] = $arGadgetParams["SITE_ID"];
	$strFilterSite = "&site_id=".$arGadgetParams["SITE_ID"];
}
else
	$strFilterSite = "";

$arComm = CTraffic::GetCommonValues($arFilter);

$arRows = array(
	"HITS" => array("NAME" => GetMessage("GD_STAT_HITS"), "LINK" => "hit_list.php"),
	"HOSTS" => array("NAME" => GetMessage("GD_STAT_HOSTS")),
	"SESSIONS" => array("NAME" => GetMessage("GD_STAT_SESSIONS"), "LINK" => "session_list.php"),
	"EVENTS" => array("NAME" => GetMessage("GD_STAT_EVENTS"), "LINK" => "event_list.php"),
);

if (!array_key_exists("SITE_ID", $arFilter))
	$arRows["GUESTS"] = array("NAME" => GetMessage("GD_STAT_VISITORS"), "LINK" => "guest_list.php");

$date_beforeyesterday = ConvertTimeStamp(AddToTimeStamp(array("DD" => -2, "MM" => 0, "YYYY" => 0, "HH" => 0, "MI" => 0, "SS" => 0), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "SHORT");
$date_yesterday = ConvertTimeStamp(AddToTimeStamp(array("DD" => -1, "MM" => 0, "YYYY" => 0, "HH" => 0, "MI" => 0, "SS" => 0), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "SHORT");
$date_today = ConvertTimeStamp(mktime(0, 0, 0, date("n"), date("j"), date("Y")), "SHORT");

if ($arGadgetParams["HIDE_GRAPH"] != "Y")
{
	$iGraphWidth = $arGadgetParams["GRAPH_WIDTH"];
	$iGraphHeight = $arGadgetParams["GRAPH_HEIGHT"];
	$dateGraph1 = ConvertTimeStamp(AddToTimeStamp(array("DD" => -($arGadgetParams["GRAPH_DAYS"]), "MM" => 0, "YYYY" => 0, "HH" => 0, "MI" => 0, "SS" => 0), time()), "SHORT");
	$dateGraph2 = ConvertTimeStamp(time(), "SHORT");

	$days = CTraffic::DynamicDays($dateGraph1, $dateGraph2, $arFilter["SITE_ID"]);
	if ($days < 2)
	{
		?><div class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t"><?CAdminMessage::ShowMessage(GetMessage("STAT_NOT_ENOUGH_DATA"));?></div><?
	}
	else
	{
		$strGraphParams = "";
		if (in_array("HIT", $arGadgetParams["GRAPH_PARAMS"]))
			$strGraphParams .= "find_hit=Y&";
		if (in_array("HOST", $arGadgetParams["GRAPH_PARAMS"]))
			$strGraphParams .= "find_host=Y&";
		if (in_array("SESSION", $arGadgetParams["GRAPH_PARAMS"]))
			$strGraphParams .= "find_session=Y&";
		if (in_array("EVENT", $arGadgetParams["GRAPH_PARAMS"]))
			$strGraphParams .= "find_event=Y&";
		if (in_array("GUEST", $arGadgetParams["GRAPH_PARAMS"]) && !array_key_exists("SITE_ID", $arFilter))
			$strGraphParams .= "find_guest=Y&";
		if (array_key_exists("SITE_ID", $arFilter))
			$strGraphParams .= "find_site_id[]=".$arFilter["SITE_ID"]."&";
		$strGraphParams .= "&find_date1=".$dateGraph1."&find_date2=".$dateGraph2;
		$strGraphParams .= "&max_grid=10&min_grid=5";

		?><div class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t"><?
			?><img src="/bitrix/admin/traffic_graph.php?<?=$strGraphParams?>&width=<?=$iGraphWidth?>&height=<?=$iGraphHeight?>&rand=<?=rand()?>&find_graph_type=date" width="<?=$iGraphWidth?>" height="<?=$iGraphHeight?>"><?
			?><div style="padding: 0 0 10px 0;">
			<table cellpadding="2" cellspacing="0" border="0">
				<?if (in_array("HIT", $arGadgetParams["GRAPH_PARAMS"])):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["HITS"]?>" width="45" height="2"></td>
					<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_STAT_HITS")?></td>
				</tr>
				<?endif;?>
				<?if (in_array("HOST", $arGadgetParams["GRAPH_PARAMS"])):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["HOSTS"]?>" width="45" height="2"></td>
					<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_STAT_HOSTS")?></td>
				</tr>
				<?endif;?>
				<?if (in_array("SESSION", $arGadgetParams["GRAPH_PARAMS"])):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["SESSIONS"]?>" width="45" height="2"></td>
					<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_STAT_SESSIONS")?></td>
				</tr>
				<?endif;?>
				<?if (in_array("EVENT", $arGadgetParams["GRAPH_PARAMS"])):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["EVENTS"]?>" width="45" height="2"></td>
					<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_STAT_EVENTS")?></td>
				</tr>
				<?endif;?>
				<?if (in_array("GUEST", $arGadgetParams["GRAPH_PARAMS"]) && !array_key_exists("SITE_ID", $arFilter)):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["GUESTS"]?>" width="45" height="2"></td>
					<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_STAT_VISITORS")?></td>
				</tr>
				<?endif;?>
			</table>
			</div><?
		?></div><?
	}
}
?>
<script type="text/javascript">
	var gdStatsTabControl_<?=$arGadgetParams["RND_STRING"]?> = false;
</script><?
$aTabs = array(
	array(
		"DIV" => "bx_gd_stat_common_".$arGadgetParams["RND_STRING"],
		"TAB" => GetMessage("GD_STAT_TAB_COMMON"),
		"ICON" => "",
		"TITLE" => "",
		"ONSELECT" => "gdStatsTabControl_".$arGadgetParams["RND_STRING"].".SelectTab(BX('bx_gd_stat_common_".$arGadgetParams["RND_STRING"]."'));"
	)
);

if (!$arGadgetParams["SITE_ID"])
{
	$aTabs[] = array(
		"DIV" => "bx_gd_stat_adv_".$arGadgetParams["RND_STRING"],
		"TAB" => GetMessage("GD_STAT_TAB_ADV"),
		"ICON" => "",
		"TITLE" => "",
		"ONSELECT" => "gdStatsTabControl_".$arGadgetParams["RND_STRING"].".LoadTab(BX('bx_gd_stat_adv_".$arGadgetParams["RND_STRING"]."'), '/bitrix/gadgets/bitrix/admin_stat/getdata.php?lang=".LANGUAGE_ID."&table_id=adv', gdStatsTabControl_".$arGadgetParams["RND_STRING"].");"
	);

	$aTabs[] = array(
		"DIV" => "bx_gd_stat_event_".$arGadgetParams["RND_STRING"],
		"TAB" => GetMessage("GD_STAT_TAB_EVENT"),
		"ICON" => "",
		"TITLE" => "",
		"ONSELECT" => "gdStatsTabControl_".$arGadgetParams["RND_STRING"].".LoadTab(BX('bx_gd_stat_event_".$arGadgetParams["RND_STRING"]."'), '/bitrix/gadgets/bitrix/admin_stat/getdata.php?lang=".LANGUAGE_ID."&table_id=event', gdStatsTabControl_".$arGadgetParams["RND_STRING"].");"
	);
}

$aTabs[] = array(
	"DIV" => "bx_gd_stat_ref_".$arGadgetParams["RND_STRING"],
	"TAB" => GetMessage("GD_STAT_TAB_REF"),
	"ICON" => "",
	"TITLE" => "",
	"ONSELECT" => "gdStatsTabControl_".$arGadgetParams["RND_STRING"].".LoadTab(BX('bx_gd_stat_ref_".$arGadgetParams["RND_STRING"]."'), '/bitrix/gadgets/bitrix/admin_stat/getdata.php?lang=".LANGUAGE_ID."&".($arGadgetParams["SITE_ID"] ? "site_id=".$arGadgetParams["SITE_ID"]."&" : "")."table_id=referer', gdStatsTabControl_".$arGadgetParams["RND_STRING"].");"
);
/*
$aTabs[] = array(
	"DIV" => "bx_gd_stat_phrase_".$arGadgetParams["RND_STRING"],
	"TAB" => GetMessage("GD_STAT_TAB_PHRASE"),
	"ICON" => "",
	"TITLE" => "",
	"ONSELECT" => "gdStatsTabControl_".$arGadgetParams["RND_STRING"].".LoadTab(BX('bx_gd_stat_phrase_".$arGadgetParams["RND_STRING"]."'), '/bitrix/gadgets/bitrix/admin_stat/getdata.php?lang=".LANGUAGE_ID."&".($arGadgetParams["SITE_ID"] ? "site_id=".$arGadgetParams["SITE_ID"]."&" : "")."table_id=phrase', gdStatsTabControl_".$arGadgetParams["RND_STRING"].");"
);
*/
$tabControl = new CAdminViewTabControl("statsTabControl_".$arGadgetParams["RND_STRING"], $aTabs);

?><div class="bx-gadgets-tabs-wrap" id="bx_gd_tabset_stat_<?=$arGadgetParams["RND_STRING"]?>"><?
	$tabControl->Begin();
	foreach($aTabs as $i => $tab)
		$tabControl->BeginNextTab();
	$tabControl->End();

	?><div class="bx-gadgets-tabs-cont"><?
		foreach($aTabs as $i => $tab)
		{
			?><div id="<?=$tab["DIV"]?>_content" style="display: <?=($i==0 ? "block" : "none")?>;" class="adm-gadgets-tab-container"><?
				if ($i == 0)
				{
					?><table class="bx-gadgets-table">
						<tbody>
							<tr>
								<th>&nbsp;</th>
								<th><?=GetMessage("GD_STAT_TODAY")?><br><?=$now_date?></th>
								<th><?=GetMessage("GD_STAT_YESTERDAY")?><br><?=$date_yesterday?></th>
								<th><?=GetMessage("GD_STAT_B_YESTERDAY")?><br><?=$date_beforeyesterday?></th>
								<th><?=GetMessage("GD_STAT_TOTAL")?></th>
							</tr><?
							foreach($arRows as $row_code => $arRow):
								?><tr>
									<td><?=$arRow["NAME"]?></td><?
									if (array_key_exists("TODAY_".$row_code, $arComm)):
										?><td align="right"><?if (array_key_exists("LINK", $arRow)):?><a href="/bitrix/admin/<?=$arRow["LINK"]?>?find_date1=<?=$date_today?>&find_date2=<?=$date_today?><?=$strFilterSite?>&set_filter=Y&lang=<?=LANGUAGE_ID?>"><?endif;?><?=intval($arComm["TODAY_".$row_code])?><?if (array_key_exists("LINK", $arRow)):?></a><?endif;?></td><?
									else:
										?><td>&nbsp;</td><?
									endif;
									if (array_key_exists("YESTERDAY_".$row_code, $arComm)):
										?><td align="right"><?if (array_key_exists("LINK", $arRow)):?><a href="/bitrix/admin/<?=$arRow["LINK"]?>?find_date1=<?=$date_yesterday?>&find_date2=<?=$date_yesterday?><?=$strFilterSite?>&set_filter=Y&lang=<?=LANGUAGE_ID?>"><?endif;?><?=intval($arComm["YESTERDAY_".$row_code])?><?if (array_key_exists("LINK", $arRow)):?></a><?endif;?></td><?
									else:
										?><td>&nbsp;</td><?
									endif;
									if (array_key_exists("B_YESTERDAY_".$row_code, $arComm)):
										?><td align="right"><?if (array_key_exists("LINK", $arRow)):?><a href="/bitrix/admin/<?=$arRow["LINK"]?>?find_date1=<?=$date_beforeyesterday?>&find_date2=<?=$date_beforeyesterday?><?=$strFilterSite?>&set_filter=Y&lang=<?=LANGUAGE_ID?>"><?endif;?><?=intval($arComm["B_YESTERDAY_".$row_code])?><?if (array_key_exists("LINK", $arRow)):?></a><?endif;?></td><?
									else:
										?><td>&nbsp;</td><?
									endif;
									if (array_key_exists("TOTAL_".$row_code, $arComm)):
										?><td align="right"><?if (array_key_exists("LINK", $arRow)):?><a href="/bitrix/admin/<?=$arRow["LINK"]?><?=$strFilterSite?>?set_filter=Y&lang=<?=LANGUAGE_ID?>"><?endif;?><?=intval($arComm["TOTAL_".$row_code])?><?if (array_key_exists("LINK", $arRow)):?></a><?endif;?></td><?
									else:
										?><td>&nbsp;</td><?
									endif;
								?></tr><?
							endforeach;
						?></tbody>
					</table><?
				}
				else
				{
					?><div id="<?=$tab["DIV"]?>_content_node"></div><?
				}
			?></div><?
		}
	?></div><?
?></div>
<script type="text/javascript">
	BX.ready(function(){
		gdStatsTabControl_<?=$arGadgetParams["RND_STRING"]?> = new gdTabControl('bx_gd_tabset_stat_<?=$arGadgetParams["RND_STRING"]?>');
	});
</script>