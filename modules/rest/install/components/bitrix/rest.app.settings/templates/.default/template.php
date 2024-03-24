<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

$APPLICATION->SetTitle($arResult['TITLE'] ?? '');

Extension::load([
	'ui.stepbystep',
	'ui.forms',
	'ui.notification',
	'ui.icon-set.main',
	'ui.design-tokens',
	'rest.form-constructor',
	'main.loader',
]);
?>
<div data-id="einvoice-settings-wrapper">
</div>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:ui.button.panel',
	'',
	[
		'BUTTONS' => [
			[
				'TYPE' => 'save',
				'CAPTION' => $arResult['SAVE_BUTTON'] ?? null,
			],
			[
				'TYPE' => 'cancel',
				'CAPTION' => $arResult['CANCEL_BUTTON'] ?? null,
			],
		],
		'HIDE' => true,
	],
);
?>

<script>
	BX.ready(() => {
		BX.message(<?= \CUtil::phpToJsObject(Loc::loadLanguageFile(__FILE__)) ?>);
		const formConstructor = new BX.Rest.FormConstructor({
			steps: <?= CUtil::PhpToJSObject($arResult['STEPS'] ?? []) ?>,
		});

		const appSettings = new BX.Rest.AppSettings({
			formConstructor: formConstructor,
			clientId: '<?= CUtil::JSescape($arResult['CLIENT_ID'] ?? '') ?>',
			handler: '<?= CUtil::JSescape($arResult['HANDLER'] ?? '') ?>',
			redirect: '<?= CUtil::JSescape($arResult['REDIRECT'] ?? '') ?>',
			wrapper: document.querySelector('[data-id="einvoice-settings-wrapper"]'),
		});

		appSettings.show();
	});
</script>