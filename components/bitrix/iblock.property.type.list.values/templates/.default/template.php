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
	'ui.alerts',
	'ui.buttons',
	'ui.design-tokens',
]);

if (Loader::includeModule('ui'))
{
	Toolbar::deleteFavoriteStar();
}

$APPLICATION->SetTitle(
	Loc::getMessage('IBLOCK_PROPERTY_TYPE_LIST_VALUES_TITLE', [
		'#NAME#' => $arResult['PROPERTY_NAME'],
	])
);

$APPLICATION->SetPageProperty(
	'BodyClass',
	$APPLICATION->GetPageProperty('BodyClass') . ' no-background'
);

?>
<div class="iblock-property-type-list-values-buttons">
	<button class="ui ui-btn ui-btn-primary iblock-property-type-list-values-append-row"><?= Loc::getMessage('IBLOCK_PROPERTY_TYPE_LIST_VALUES_APPEND_ROW') ?></button>
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
	BX.ready(function() {
		new BX.Iblock.PropertyListValues(<?= CUtil::PhpToJSObject([
			'gridId' => $arResult['GRID']['GRID_ID'],
			'signedParameters' => $component->getSignedParameters(),
		]) ?>);
	});
</script>
