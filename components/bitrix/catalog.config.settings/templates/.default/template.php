<?php

/**
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;

\Bitrix\Main\UI\Extension::load([
	'catalog.config.settings',
	'ui.forms',
	'main.core',
]);
?>

<div id="catalogConfig" class="catalog-settings-wrapper"></div>

<?php

$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
	'BUTTONS' => [
		'save',
		[
			'type' => 'custom',
			'layout' => '<input type="submit" class="ui-btn ui-btn-link" name="cancel" value="' . Loc::getMessage('CAT_CONFIG_SETTINGS_CANCEL') . '">'
		]
	],
	'HIDE' => true,
]);
CJSCore::Init(['popup', 'date']);

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . "/components/bitrix/intranet.settings/templates/.main/template.php");
?>

<script>
	BX.ready(function() {
		BX.Loc.setMessage('INTRANET_SETTINGS_CANCEL_MORE', '<?= Loc::getMessage('INTRANET_SETTINGS_CANCEL_MORE') ?>');
		const settings = <?= CUtil::PhpToJSObject($arResult['settings']) ?>;
		document.getElementById('catalogConfig').appendChild((new BX.Catalog.Config.CatalogSettings(settings)).render());
	});
</script>
