<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$GLOBALS["APPLICATION"]->AddHeadScript("/bitrix/js/main/utils.js");

CAjax::Init();
if (!array_key_exists("USE_TOOLTIP", $arResult) || $arResult["USE_TOOLTIP"])
	CUtil::InitJSCore(array("ajax", "tooltip"));
else
	CUtil::InitJSCore(array("ajax"));

$APPLICATION->IncludeComponent("bitrix:main.user.link",
	'',
	array(
		"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
		"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"SHOW_YEAR" => $arParams["SHOW_YEAR"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
		"AJAX_ONLY" => "Y",
	),
	false, 
	array("HIDE_ICONS" => "Y")
);

$ajax_page = $APPLICATION->GetCurPageParam("", array("bxajaxid", "logout"));

?>
<script language="JavaScript">
<!--
	var sonetDynevMsgGetPath = '<?= $arResult["MsgGetPath"] ?>';
	var sonetDynevMsgSetPath = '<?= $arResult["MsgSetPath"] ?>';
	var sonetDynevSessid = '<?= bitrix_sessid_get() ?>';
	var sonetDynevUserId = <?= IntVal($GLOBALS["USER"]->GetID()) ?>;
	var sonetDynevSiteId = '<?= CUtil::JSEscape(SITE_ID) ?>';
	var sonetDynevTimeout = <?= IntVal($arParams["AJAX_LONG_TIMEOUT"]) ?>;
	var sonetDynevPath2User = '<?= CUtil::JSEscape($arParams["PATH_TO_USER"]) ?>';
	var sonetDynevPath2Group = '<?= CUtil::JSEscape($arParams["PATH_TO_GROUP"]) ?>';
	var sonetDynevPath2Message = '<?= CUtil::JSEscape($arParams["PATH_TO_MESSAGE_FORM"]) ?>';
	var sonetDynevPath2MessageMess = '<?= CUtil::JSEscape($arParams["PATH_TO_MESSAGE_FORM_MESS"]) ?>';
	var sonetDynevPath2Chat = '<?= CUtil::JSEscape($arParams["PATH_TO_MESSAGES_CHAT"]) ?>';
	var sonetDynevUserNameTemplate = '<?= CUtil::JSEscape($arParams["NAME_TEMPLATE"]) ?>';
	var sonetDynevUserShowLogin = '<?= CUtil::JSEscape($arParams["SHOW_LOGIN"]) ?>';
	var sonetDynevUserPopup = '<?= CUtil::JSEscape($arParams["POPUP"]) ?>';	
	
	var sonetDynevCurPage = '<?= CUtil::JSEscape($GLOBALS["APPLICATION"]->GetCurPage());?>';
	var sonetDynevCurParam = '<?= CUtil::JSEscape($GLOBALS["APPLICATION"]->GetCurParam()); ?>';
	var sonetDynevMULAjaxPage = '<?= CUtil::JSEscape($ajax_page);?>';

	var sonetDynevTrOnline = '<?= GetMessage("SONET_C2_ONLINE") ?>';
	var sonetDynevTrFrTitle = '<?= GetMessage("SONET_C2_FR_TITLE") ?>';
	var sonetDynevTrGrInv = '<?= GetMessage("SONET_C2_GR_INV") ?>';
	var sonetDynevTrGrTitle = '<?= GetMessage("SONET_C2_GR_TITLE") ?>';
	var sonetDynevTrMsTitle = '<?= GetMessage("SONET_C2_MS_TITLE") ?>';
	var sonetDynevUseTooltip = '<?=(!array_key_exists("USE_TOOLTIP", $arResult) || $arResult["USE_TOOLTIP"] ? "Y" : "N") ?>';
//-->
</script>

<div id="sonet_events_div">
<div id="sonet_events_err" class='errortext' style="display:none">
</div>

<div id="sonet_events_fr" style="display:none" class="sonet-events-blur">
	<div class="sonet-events-shadow">
	<div class="sonet-events-content">
	<table class="sonet-user-profile-friends data-table">
		<tr>
			<th align="left"><?= GetMessage("SONET_C2_FR_TITLE") ?></th>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0" class="sonet-user-profile-friend-box">
				<tr>
					<td valign="top" width="0%" id="sonet_events_fr_sender_photo">
					</td>
					<td valign="top" width="100%">
						<div id="sonet_events_fr_sender_desc">
						</div>
						<div id="sonet_events_fr_date">
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<?= GetMessage("SONET_C2_FR_TEXT") ?>.
					</td>
				</tr>
				<tr>
					<td id="sonet_events_fr_message" colspan="2">
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="button" id="sonet_events_fr_add" name="do_friend_add" value="<?= GetMessage("SONET_C2_FR_ADD") ?>">
						<input type="button" id="sonet_events_fr_reject" name="do_friend_reject" value="<?= GetMessage("SONET_C2_REJECT") ?>">
					</td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
	</div>
	</div>
</div>

<div id="sonet_events_gr" style="display:none" class="sonet-events-blur">
	<div class="sonet-events-shadow">
	<div class="sonet-events-content">	
	<table class="sonet-user-profile-friends data-table">
		<tr>
			<th align="left"><?= GetMessage("SONET_C2_GR_TITLE") ?></th>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0" class="sonet-user-profile-friend-box">
				<tr>
					<td width="0%" valign="top" id="sonet_events_gr_group_photo">
					</td>
					<td width="100%" valign="top">
						<div id="sonet_events_gr_group_desc">
						</div>
						<div id="sonet_events_gr_date">
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" id="sonet_events_gr_sender">
					</td>
				</tr>
				<tr>
					<td colspan="2" id="sonet_events_gr_message">
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="button" id="sonet_events_gr_add" name="do_friend_add" value="<?= GetMessage("SONET_C2_GR_ADD") ?>">
						<input type="button" id="sonet_events_gr_reject" name="do_friend_reject" value="<?= GetMessage("SONET_C2_REJECT") ?>">
					</td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
	</div>
	</div>
</div>

<div id="sonet_events_ms" style="display:none" class="sonet-events-blur">
	<div class="sonet-events-shadow">
	<div class="sonet-events-content">	
	<table class="sonet-user-profile-friends data-table" cellspacing="0" cellpadding="0">
		<tr>
			<th align="left"><?= GetMessage("SONET_C2_MS_TITLE") ?></th>
		</tr>
		<tr>
			<td id="sonet_events_message_td">
				<table width="100%" border="0" class="sonet-user-profile-friend-box">
				<tr>
					<td valign="top" width="0%" id="sonet_events_ms_sender_photo">
					</td>
					<td valign="top" width="100%">
						<div id="sonet_events_ms_sender_desc">
						</div>
						<div id="sonet_events_ms_date">
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div id="sonet_events_ms_message"></div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<table border="0" class="sonet-user-profile-friend-box">
						<tr>
							<td>
							<span id="sonet_events_ms_ban">
								<a href="#" id="sonet_events_ms_ban_link"><?= GetMessage("SONET_C2_BAN") ?></a>
							</span>
							</td>
							<td>
								<input type="button" id="sonet_events_ms_answer" name="do_message_answer" value="<?= GetMessage("SONET_C2_ANSWER") ?>">
							</td>
							<td>
								<input type="button" id="sonet_events_ms_close" name="do_message_close" value="<?=GetMessage("SONET_C2_CLOSE") ?>">
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
	</div>
	</div>
</div>
</div>
<a href="<?= $arParams["PATH_TO_MESSAGES"] ?>" title="<?=GetMessage("SONET_C2_MESSAGES")?>"><?=GetMessage("SONET_C2_MESSAGES")?></a> 
<? if (array_key_exists("ITEMS_TOTAL", $arResult) && intval($arResult["ITEMS_TOTAL"]) > 0):?>
	(<?= $arResult["ITEMS_TOTAL"]?>)
<? endif; ?>