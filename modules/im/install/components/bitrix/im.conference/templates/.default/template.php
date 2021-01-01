<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

\Bitrix\Main\UI\Extension::load("im.application.call");

$darkClass = \CIMSettings::GetSetting(CIMSettings::SETTINGS, 'enableDarkTheme')? 'bx-messenger-dark': '';
$GLOBALS["APPLICATION"]->SetPageProperty("BodyClass", "im-desktop $darkClass");

$APPLICATION->IncludeComponent("bitrix:ui.info.helper", "", array());
?>
<div id="placeholder"></div>
<script type="text/javascript">
	BX.Messenger.Application.Launch('call', {
		node: '#placeholder',
		chatId: '<?=$arResult['CHAT_ID']?>',
		alias: '<?=CUtil::JSEscape($arResult['ALIAS'])?>',
		userId: '<?=$arResult['USER_ID']?>',
		siteId: '<?=CUtil::JSEscape($arResult['SITE_ID'])?>',
		userCount: '<?=$arResult['USER_COUNT']?>',
		startupErrorCode: '<?=$arResult['STARTUP_ERROR_CODE']?>',
		isIntranetOrExtranet: '<?=$arResult['IS_INTRANET_OR_EXTRANET']?>',
		language: '<?=$arResult['LANGUAGE']?>',
		passwordRequired: '<?=$arResult['PASSWORD_REQUIRED']?>',
		conferenceId: '<?=$arResult['CONFERENCE_ID']?>',
		conferenceTitle: '<?=CUtil::JSEscape($arResult['CONFERENCE_TITLE'])?>',
		featureConfig: <?=\Bitrix\Main\Web\Json::encode($arResult['FEATURE_CONFIG'])?>,
		formatRecordDate: '<?=\Bitrix\Main\Context::getCurrent()->getCulture()->getShortDateFormat()?>',
	});
</script>