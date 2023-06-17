<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

/**
 * @var array $arResult;
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<?php
	/** @var CMain $APPLICATION */
	Loc::loadMessages(__FILE__);
	$APPLICATION->ShowHead();
	$APPLICATION->ShowCSS(true, true);
	$APPLICATION->ShowHeadStrings();
	$APPLICATION->ShowHeadScripts();

	Extension::load(['ui.layout-form', 'popup', 'ui.buttons', 'ui.forms', 'ui.alerts', 'ui.notification','ui.buttons.icons', 'loader', 'ui.fonts.montserrat', 'event-emitter']);
	?>
	<title><?=Loc::getMessage('MAIN_COUPON_ACTIVATION_HEADER_TITLE')?></title>
</head>
<body id="workarea-content">
	<div class="logo-container main-logo-<?if (LANGUAGE_ID === "ru"):?>ru<?elseif(LANGUAGE_ID === "ua"):?>ua<?else:?>en<?endif?>"></div>
	<div class="copyright"><?= Loc::getMessage('MAIN_COUPON_ACTIVATION_COPYRIGHT', ['#YEAR#' => (new \Bitrix\Main\Type\Date(null))->format('Y')]) ?></div>
<script>
	BX.ready(() => {
		// A hack to recalculate copyright element
		BX.Event.EventEmitter.subscribe(
			BX.Event.EventEmitter.GLOBAL_TARGET,
			'MainCouponActivation:onAfterChangeContent',
			() => { window.dispatchEvent(new Event('resize')); }
		);

		BX.message(<?= \Bitrix\Main\Web\Json::encode(Loc::loadLanguageFile(__FILE__)) ?>);
		const parameters = <?= \CUtil::PhpToJSObject($arResult); ?>;
		const popup = BX.Main.LicensePopup.createExpiredLicensePopup(parameters);
		popup.init();
	});
</script>
</body>
</html>



