<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (IsModuleInstalled("im")) 
{ 
	$APPLICATION->IncludeComponent("bitrix:im.messenger", "", Array(), null, array("HIDE_ICONS" => "Y")); 
	return; 
}

CAjax::Init();
if (!array_key_exists("USE_TOOLTIP", $arResult) || $arResult["USE_TOOLTIP"])
	CUtil::InitJSCore(array("ajax", "window", "fx",  "tooltip"));
else
	CUtil::InitJSCore(array("ajax", "window", "fx"));

$GLOBALS["APPLICATION"]->AddHeadScript("/bitrix/js/main/utils.js");
$GLOBALS["APPLICATION"]->AddHeadScript("/bitrix/components/bitrix/socialnetwork.events_dyn/templates/.default/script_ed.js");

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

?><script language="JavaScript">
<!--
	BX.message({
		sonetDynevMsgGetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.events_dyn/get_message_2.php')?>',
		sonetDynevMsgSetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.events_dyn/set_message_2.php')?>',
		sonetDynevSessid: '<?=bitrix_sessid_get()?>',
		sonetDynevUserId: <?=CUtil::JSEscape(IntVal($GLOBALS["USER"]->GetID()))?>,
		sonetDynevSiteId: '<?=CUtil::JSEscape(SITE_ID)?>',
		sonetDynevTimeout: <?=IntVal($arParams["AJAX_LONG_TIMEOUT"])?>,
		sonetDynevPath2User: '<?=CUtil::JSEscape($arParams["PATH_TO_USER"])?>',
		sonetDynevPath2Group: '<?=CUtil::JSEscape($arParams["PATH_TO_GROUP"])?>',
		sonetDynevPath2MessageMess: '<?=CUtil::JSEscape($arParams["PATH_TO_MESSAGE_FORM_MESS"])?>',
		sonetDynevUserNameTemplate: '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>',
		sonetDynevUserShowLogin: '<?=CUtil::JSEscape($arParams["SHOW_LOGIN"])?>',
		sonetDynevMULAjaxPage: '<?=CUtil::JSEscape($ajax_page);?>',
		sonetDynevTrOnline: '<?=CUtil::JSEscape(GetMessage("SONET_C2_ONLINE"))?>',
		sonetDynevMsTitle: '<?=CUtil::JSEscape(GetMessage("SONET_C2_MS_TITLE"))?>',
		sonetDynevGrInv: '<?=CUtil::JSEscape(GetMessage("SONET_C2_GR_INV"))?>',
		sonetDynevDateToday: '<?=CUtil::JSEscape(GetMessage("SONET_C2_DATE_TODAY"))?>',
		sonetDynevDateYesterday: '<?=CUtil::JSEscape(GetMessage("SONET_C2_DATE_YESTERDAY"))?>',
		sonetDynevClose: '<?=CUtil::JSEscape(GetMessage("SONET_C2_CLOSE"))?>',
		sonetDynevPrev: '<?=CUtil::JSEscape(GetMessage("SONET_C2_PREV"))?>',
		sonetDynevNext: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NEXT"))?>',
		sonetDynevRead: '<?=CUtil::JSEscape(GetMessage("SONET_C2_READ"))?>',
		sonetDynevPagerFrom: '<?=CUtil::JSEscape(GetMessage("SONET_C2_PAGER_FROM"))?>',
		sonetDynevUnreadCntId: '<?=CUtil::JSEscape($arParams["UNREAD_CNT_ID"])?>',
		sonetDynevUnreadCntStrBefore: '<?=CUtil::JSEscape($arParams["UNREAD_CNT_STR_BEFORE"])?>',
		sonetDynevUnreadCntStrAfter: '<?=CUtil::JSEscape($arParams["UNREAD_CNT_STR_AFTER"])?>',
		sonetDynevUseAutoSubscribe: 'N',
		sonetDynevUseTooltip: '<?=(!array_key_exists("USE_TOOLTIP", $arResult) || $arResult["USE_TOOLTIP"] ? "Y" : "N") ?>',
		sonetDynevDivTitleMP: '<?=CUtil::JSEscape(GetMessage("SONET_C2_DIV_TITLE_M_P"))?>',
		sonetDynevDivTitleMS: '<?=CUtil::JSEscape(GetMessage("SONET_C2_DIV_TITLE_M_S"))?>',
		sonetDynevDivTitleGR: '<?=CUtil::JSEscape(GetMessage("SONET_C2_DIV_TITLE_GR"))?>',
		sonetDynevDivTitleFR: '<?=CUtil::JSEscape(GetMessage("SONET_C2_DIV_TITLE_FR"))?>',
		sonetDynevNfier_0: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_0"))?>',
		sonetDynevNfier_1: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_1"))?>',
		sonetDynevNfier_2: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_2"))?>',
		sonetDynevNfier_3: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_3"))?>',
		sonetDynevNfier_4: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_4"))?>',
		sonetDynevNfier_5: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_5"))?>',
		sonetDynevNfier_6: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_6"))?>',
		sonetDynevNfier_7: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_7"))?>',
		sonetDynevNfier_8: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_8"))?>',
		sonetDynevNfier_9: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_9"))?>',
		sonetDynevNfier_0x: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_0x"))?>',
		sonetDynevNfier_1x: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_1x"))?>',
		sonetDynevNfier_2x: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_2x"))?>',
		sonetDynevNfier_3x: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_3x"))?>',
		sonetDynevNfier_4x: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_4x"))?>',
		sonetDynevNfier_5x: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_5x"))?>',
		sonetDynevNfier_6x: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_6x"))?>',
		sonetDynevNfier_7x: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_7x"))?>',
		sonetDynevNfier_8x: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_8x"))?>',
		sonetDynevNfier_9x: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_9x"))?>',
		sonetDynevNfier_0xx: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_0xx"))?>',
		sonetDynevNfier_1xx: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_1xx"))?>',
		sonetDynevNfier_2xx: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_2xx"))?>',
		sonetDynevNfier_3xx: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_3xx"))?>',
		sonetDynevNfier_4xx: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_4xx"))?>',
		sonetDynevNfier_5xx: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_5xx"))?>',
		sonetDynevNfier_6xx: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_6xx"))?>',
		sonetDynevNfier_7xx: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_7xx"))?>',
		sonetDynevNfier_8xx: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_8xx"))?>',
		sonetDynevNfier_9xx: '<?=CUtil::JSEscape(GetMessage("SONET_C2_NFIER_MESSAGES_9xx"))?>'
	});	
//-->
</script><?

if (isset($arParams["JAVASCRIPT_ONLY"]) && $arParams["JAVASCRIPT_ONLY"] == "Y")
	return;

if (strlen(trim($arParams["UNREAD_CNT_ID"])) > 0)
	echo '<i id="'.$arParams["UNREAD_CNT_ID"].'">'.(array_key_exists("ITEMS_TOTAL", $arResult) && intval($arResult["ITEMS_TOTAL"]) > 0 ? $arResult["ITEMS_TOTAL"] : "").'</i>';
?>