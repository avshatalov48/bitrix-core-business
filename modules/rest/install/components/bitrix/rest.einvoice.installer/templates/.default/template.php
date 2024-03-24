<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load([
	'ui.design-tokens',
	'ui.popupcomponentsmaker',
	'ui.notification',
	'ui.buttons',
	'ui.icon-set.main',
	'ui.feedback.form',
	'rest.listener',
	'rest.app-form'
]);

?>
<div data-id="bitrix-einvoice-installer-wrapper" class="bitrix-einvoice-installer-wrapper"></div>

<script>
	BX.ready(() => {
		BX.message(<?= \CUtil::phpToJsObject(Loc::loadLanguageFile(__FILE__)) ?>);
		new BX.Rest.EInvoiceInstaller({
			wrapper: document.querySelector('[data-id="bitrix-einvoice-installer-wrapper"]'),
			apps: <?= \CUtil::PhpToJSObject($arResult['applications'] ?? []) ?>,
			formConfiguration: <?= \CUtil::PhpToJSObject($arResult['formConfiguration'] ?? [])?>
		});
	});
</script>