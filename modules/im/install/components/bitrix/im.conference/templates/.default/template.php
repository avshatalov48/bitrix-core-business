<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$darkClass = \CIMSettings::GetSetting(CIMSettings::SETTINGS, 'enableDarkTheme')? 'bx-messenger-dark': '';
$GLOBALS["APPLICATION"]->SetPageProperty("BodyClass", "im-desktop $darkClass");

?>
<div id="placeholder"></div>
<script type="text/javascript">
	BX.Messenger.Application.Launch('call', {
		node: '#placeholder',
		chatId: '<?=$arResult['CHAT_ID']?>',
		alias: '<?=$arResult['ALIAS']?>',
		userId: '<?=$arResult['USER_ID']?>',
		siteId: '<?=CUtil::JSEscape($arResult['SITE_ID'])?>',
		userCount: '<?=$arResult['USER_COUNT']?>',
		startupErrorCode: '<?=$arResult['STARTUP_ERROR_CODE']?>',
		isIntranetOrExtranet: '<?=$arResult['IS_INTRANET_OR_EXTRANET']?>',
		language: '<?=$arResult['LANGUAGE']?>'
	});
</script>
