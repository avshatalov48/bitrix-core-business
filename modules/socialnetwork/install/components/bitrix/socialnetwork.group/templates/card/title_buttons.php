<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\UI\Buttons;

UI\Extension::load([
	'ui.buttons',
	'ui.buttons.icons',
]);

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
		SGCSPathToRequestUser: '<?= CUtil::JSUrlEscape(
			!empty($arResult['Urls']['Invite'])
				? $arResult['Urls']['Invite']
				: $arResult['Urls']['Edit'] . (mb_strpos($arResult['Urls']['Edit'], '?') !== false ? '&' : '?') . 'tab=invite'
		) ?>',
		SGCSPathToUserRequestGroup: '<?= CUtil::JSUrlEscape($arResult['Urls']['UserRequestGroup']) ?>',
		SGCSPathToUserLeaveGroup: '<?= CUtil::JSUrlEscape($arResult['Urls']['UserLeaveGroup']) ?>',
		SGCSPathToRequests: '<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupRequests"])?>',
		SGCSPathToRequestsOut: '<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupRequestsOut"])?>',
		SGCSPathToMembers: '<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupUsers"])?>',
		SGCSPathToEdit: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Edit"].(mb_strpos($arResult["Urls"]["Edit"], "?") !== false ? "&" : '?')."tab=edit")?>',
		SGCSPathToDelete: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Delete"])?>',
		SGCSPathToFeatures: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Features"])?>',
		SGCSPathToCopy: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Copy"])?>'
	});
</script>
<?php

$actionsButton = new Buttons\Button([
	'color' => ($arResult['IS_IFRAME'] ? Buttons\Color::SUCCESS : Buttons\Color::LIGHT_BORDER),
	'dropdown' => true,
	'text' => Loc::getMessage('SONET_SGCS_T_ACTIONS_BUTTON'),
]);
$actionsButton->addAttribute('id', 'group_card_menu_button');
Toolbar::addButton($actionsButton);

if (in_array($arResult['CurrentUserPerms']['UserRole'], UserToGroupTable::getRolesMember(), true))
{
	$actionsButton = new Buttons\Button([
		'color' => Buttons\Color::LIGHT_BORDER,
		'style' => Buttons\Style::DROPDOWN,
	]);

	$actionsButton->addAttribute('id', 'group_card_subscribe_button');
	$actionsButton->addClass(($arResult['bSubscribed'] ? ' ui-btn-active ui-btn-icon-follow ' : ' ui-btn-icon-unfollow '));
	Toolbar::addButton($actionsButton);
}
