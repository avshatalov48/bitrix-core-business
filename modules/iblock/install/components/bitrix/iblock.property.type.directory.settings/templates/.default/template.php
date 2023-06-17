<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\Facade\Toolbar;

Extension::load([
	'popup',
	'translit',
	'ui.alerts',
	'ui.buttons',
	'ui.vue3',
]);

if (Loader::includeModule('ui'))
{
	Toolbar::deleteFavoriteStar();
}

$APPLICATION->SetTitle(
	Loc::getMessage('IBLOCK_PROPERTY_TYPE_DIRECTORY_SETTINGS_TITLE', [
		'#NAME#' => $arResult['PROPERTY_NAME'],
	])
);

$APPLICATION->SetPageProperty(
	'BodyClass',
	$APPLICATION->GetPageProperty('BodyClass') . ' no-background'
);

$this->addExternalCss('/bitrix/js/ui/install/js/ui/entity-editor/entity-editor.css');

?>
<div id="iblock-property-type-directory-settings" class="iblock-property-type-directory-settings">
	<div class="iblock-property-type-directory-settings-group">
		<div class="ui-ctl-label-text"><?= Loc::getMessage('IBLOCK_PROPERTY_TYPE_DIRECTORY_SETTINGS_SELECT_DIRECTORY') ?></div>
		<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100" @click="toggleDirectoryDropdown">
			<div class="ui-ctl-after ui-ctl-icon-angle"></div>
			<div class="ui-ctl-element">{{ selectedDirectoryName }}</div>
		</div>
	</div>

	<div  v-if="isNewDirectory" class="iblock-property-type-directory-settings-group">
		<div class="ui-ctl-label-text"><?= Loc::getMessage('IBLOCK_PROPERTY_TYPE_DIRECTORY_SETTINGS_NEW_DIRECTORY_LABEL') ?></div>
		<div class="ui-ctl ui-ctl-w100">
		<input class="ui-ctl-element" type="text" v-model="directoryName" name="DIRECTORY_NAME" @change.prevent="normalizeName">
		</div>
	</div>

	<div class="iblock-property-type-directory-settings-group">
		<button class="ui ui-btn ui-btn-primary" @click.prevent="addNewRow"><?= Loc::getMessage('IBLOCK_PROPERTY_TYPE_DIRECTORY_SETTINGS_APPEND_ROW') ?></button>
	</div>
</div>
<?php

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID'],
	$component
);

$APPLICATION->IncludeComponent(
	'bitrix:ui.button.panel',
	"",
	[
		'ALIGN' => 'center',
		'BUTTONS' => [
			[
				'TYPE' => 'save',
			],
			[
				'TYPE' => 'cancel',
			],
		],
	],
	$component
);

?>
<script>
	BX.message(<?= CUtil::PhpToJSObject(Loc::loadLanguageFile(__FILE__)) ?>);

	BX.ready(function() {
		BX.Iblock.PropertyDirectorySettings.instance = new BX.Iblock.PropertyDirectorySettings(<?= CUtil::PhpToJSObject([
			'gridId' => $arResult['GRID']['GRID_ID'],
			'settingsFormSelector' => '#iblock-property-type-directory-settings',
			'directoryItems' => $arResult['DIRECTORIES'],
			'selectedDirectory' => $arResult['SELECTED_DIRECTORY'],
			'signedParameters' => $component->getSignedParameters(),
		]) ?>);
	});
</script>
