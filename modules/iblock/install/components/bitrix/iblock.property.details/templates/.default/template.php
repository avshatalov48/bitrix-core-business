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

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\Facade\Toolbar;

Extension::load([
	'ui.alerts',
	'ui.buttons',
	'ui.design-tokens',
	'ui.dialogs.messagebox',
	'ui.fonts.opensans',
	'ui.forms',
	'ui.hint',
	'main.loader',
	'date',
]);

if (Loader::includeModule('ui'))
{
	Toolbar::deleteFavoriteStar();
}

$propertyType = $arResult['VALUES']['PROPERTY_TYPE'] ?? null;

$isShowList = $propertyType === PropertyTable::TYPE_LIST;
$isShowDirectory = $propertyType === PropertyTable::USER_TYPE_DIRECTORY;

$title = $arResult['VALUES']['NAME'] ?? Loc::getMessage('IBLOCK_PROPERTY_DETAILS_NEW_RECORD_TITLE');
$APPLICATION->SetTitle($title);

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrappermenu',
	"",
	[
		'ID' => 'iblock-property-details-sidepanel-menu',
		'TITLE' => Loc::getMessage('IBLOCK_PROPERTY_DETAILS_MENU_TITLE'),
		'ITEMS' => [
			[
				'NAME' => Loc::getMessage('IBLOCK_PROPERTY_DETAILS_MENU_COMMON'),
				'ATTRIBUTES' => [
					'onclick' => "BX.Iblock.PropertyDetailsInstance.openTab('common')",
				],
				'ACTIVE' => true,
			],
			[
				'NAME' => Loc::getMessage('IBLOCK_PROPERTY_DETAILS_MENU_LIST'),
				'ATTRIBUTES' => [
					'onclick' => "BX.Iblock.PropertyDetailsInstance.openSlider('list-values'); event.stopImmediatePropagation()",
					'style' => $isShowList ? '' : 'display: none;',
					'data-slider' => 'list-values',
				],
			],
			[
				'NAME' => Loc::getMessage('IBLOCK_PROPERTY_DETAILS_MENU_DIRECTORY'),
				'ATTRIBUTES' => [
					'onclick' => "BX.Iblock.PropertyDetailsInstance.openSlider('directory-items'); event.stopImmediatePropagation()",
					'style' => $isShowDirectory ? '' : 'display: none;',
					'data-slider' => 'directory-items',
				],
			],
			[
				'NAME' => Loc::getMessage('IBLOCK_PROPERTY_DETAILS_MENU_ADDITIONAL'),
				'ATTRIBUTES' => [
					'onclick' => "BX.Iblock.PropertyDetailsInstance.openTab('additional')",
				],
			],
		],
	],
	$component
);

?>
<div id="iblock-property-details-container" class="iblock-property-details-container">
	<form onsubmit="return false;">
		<!-- errors -->
		<?php if (isset($arResult['ERROR'])): ?>
			<div id="iblock-property-details-errors" class="ui-alert ui-alert-danger">
				<div class="ui-alert-message">
					<?= HtmlFilter::encode($arResult['ERROR']) ?>
				</div>
			</div>
		<?php else: ?>
			<div id="iblock-property-details-errors" class="ui-alert ui-alert-danger" style="display: none;">
				<div class="ui-alert-message"></div>
			</div>
		<?php endif; ?>

		<!-- fields -->
		<div class="iblock-property-details-tab iblock-property-details-tab_current" data-tab="common">
			<?php
			foreach ($arResult['FIELDS'] as $field)
			{
				call_user_func($arResult['SHOW_FIELD_CALLBACK'], $field, $arResult['VALUES']);
			}
			?>
		</div>

		<!-- additional fields -->
		<div class="iblock-property-details-tab iblock-property-details-settings" data-tab="additional">
			<?php
			include __DIR__ . '/additional_fields.php';
			?>
		</div>
	</form>

	<!-- buttons -->
	<div class="iblock-property-details-buttons">
		<?php
		$APPLICATION->IncludeComponent(
			'bitrix:ui.button.panel',
			"",
			[
				'ALIGN' => 'center',
				'BUTTONS' => $arResult['BUTTONS'],
			],
			$component
		);
		?>
	</div>
</div>
<script>
	BX.ready(function()
	{
		BX.message(<?= CUtil::PhpToJSObject(
			Loc::loadLanguageFile(__FILE__)
		) ?>);

		BX.Iblock.PropertyDetailsInstance = new BX.Iblock.PropertyDetails(<?= CUtil::PhpToJSObject([
			'iblockId' => $arResult['IBLOCK_ID'],
			'propertyId' => $arResult['ID'],
			'detailPageUrlTemplate' => (string)($arParams['DETAIL_PAGE_URL'] ?? ''),
			'containerSelector' => '#iblock-property-details-container',
			'signedParameters' => $component->getSignedParameters(),
			//
			'sliders' => [
				'list-values' => [
					'url' => $arParams['LIST_VALUES_URL'],
					'allowChangeHistory' => false,
					'newPropertyConfirmMessage' => Loc::getMessage('IBLOCK_PROPERTY_DETAILS_POPUP_OPEN_SLIDER_CONFIRM_TEXT'),
				],
				'directory-items' => [
					'url' => $arParams['DIRECTORY_ITEMS_URL'],
					'allowChangeHistory' => false,
					'newPropertyConfirmMessage' => Loc::getMessage('IBLOCK_PROPERTY_DETAILS_POPUP_OPEN_SLIDER_CONFIRM_TEXT'),
				],
			],
		]) ?>);
	});
</script>
