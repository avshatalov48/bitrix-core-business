<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;
use Bitrix\Main\Loader;

UI\Extension::load("ui.buttons");
UI\Extension::load("ui.buttons.icons");

Loc::loadMessages(__FILE__);

if (Loader::includeModule('bitrix24'))
{
	\CBitrix24::initLicenseInfoPopupJS();
}

?><script>
	BX.message({
		SGCSErrorSessionWrong: '<?=GetMessageJS("SONET_SGCS_T_SESSION_WRONG")?>',
		SGCSErrorCurrentUserNotAuthorized: '<?=GetMessageJS("SONET_SGCS_T_NOT_ATHORIZED")?>',
		SGCSErrorModuleNotInstalled: '<?=GetMessageJS("SONET_SGCS_T_MODULE_NOT_INSTALLED")?>',
		SGCSWaitTitle: '<?=GetMessageJS("SONET_SGCS_T_WAIT")?>',
		SGCSSubscribeButtonHintOn: '<?=GetMessageJS("SONET_SGCS_T_NOTIFY_HINT_ON")?>',
		SGCSSubscribeButtonHintOff: '<?=GetMessageJS("SONET_SGCS_T_NOTIFY_HINT_OFF")?>',
		SGCSSubscribeButtonTitleOn: '<?=GetMessageJS("SONET_SGCS_T_NOTIFY_TITLE_ON")?>',
		SGCSSubscribeButtonTitleOff: '<?=GetMessageJS("SONET_SGCS_T_NOTIFY_TITLE_OFF")?>',
		SGCSSubscribeTitleY: '<?=GetMessageJS("SONET_SGCS_T_SUBSCRIBE_BUTTON_Y")?>',
		SGCSSubscribeTitleN: '<?=GetMessageJS("SONET_SGCS_T_SUBSCRIBE_BUTTON_N")?>',
		SGCSPathToRequestUser: '<?=CUtil::JSUrlEscape(
			!empty($arResult["Urls"]["Invite"])
				? $arResult["Urls"]["Invite"]
				: $arResult["Urls"]["Edit"].(strpos($arResult["Urls"]["Edit"], "?") !== false ? "&" : '?')."tab=invite"
		)?>',
		SGCSPathToUserRequestGroup: '<?=CUtil::JSUrlEscape($arResult["Urls"]["UserRequestGroup"])?>',
		SGCSPathToUserLeaveGroup: '<?=CUtil::JSUrlEscape($arResult["Urls"]["UserLeaveGroup"])?>',
		SGCSPathToRequests: '<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupRequests"])?>',
		SGCSPathToRequestsOut: '<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupRequestsOut"])?>',
		SGCSPathToMembers: '<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupUsers"])?>',
		SGCSPathToEdit: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Edit"].(strpos($arResult["Urls"]["Edit"], "?") !== false ? "&" : '?')."tab=edit")?>',
		SGCSPathToDelete: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Delete"])?>',
		SGCSPathToFeatures: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Features"])?>'
	});
</script>
<div class="socialnetwork-group-title-buttons">
	<button class="ui-btn ui-btn-light-border ui-btn-dropdown" id="group_card_menu_button"><?=Loc::getMessage("SONET_SGCS_T_ACTIONS_BUTTON");?></button>
	<button id="group_card_subscribe_button" class="ui-btn ui-btn-light-border<?=($arResult['bSubscribed'] ? ' ui-btn-active ui-btn-icon-unfollow' : ' ui-btn-icon-follow')?>"><?=Loc::getMessage("SONET_SGCS_T_SUBSCRIBE_BUTTON_".($arResult['bSubscribed'] ? "Y" : "N"));?></button>
</div>