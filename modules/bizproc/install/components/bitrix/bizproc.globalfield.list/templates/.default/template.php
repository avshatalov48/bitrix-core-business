<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult  */
/** @var array $arParams  */

global $APPLICATION;

\Bitrix\Main\UI\Extension::load(['ui.buttons', 'ui.dialogs.messagebox', 'ui.icons', 'bizproc.globals']);

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => $arResult['GridId'],
		'COLUMNS' => $arResult['GridColumns'],
		'ROWS' => $arResult['GridRows'],

		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_SELECTED_COUNTER' => true,
		'SHOW_TOTAL_COUNTER' => true,

		'SHOW_PAGINATION' => true,
		'NAV_OBJECT' => $arResult['PageNavigation'],
		'PAGE_SIZES' => $arResult['PageNavigation']->getPageSizes(),
		'TOTAL_ROWS_COUNT' => $arResult['PageNavigation']->getRecordCount(),
		'DEFAULT_PAGE_SIZE' => $arResult['PageNavigation']->getPageSize(),
		'PAGE_COUNT' => $arResult['PageNavigation']->getPageCount(),

		'SHOW_ACTION_PANEL' => true,
		'ACTION_PANEL' => $arResult['ActionPanel'],

		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_ROW_CHECKBOXES' => true,

		'SHOW_PAGESIZE' => true,

		'AJAX_MODE' => 'Y',
		'AJAX_ID' => \CAjax::getComponentID('bitrix:bizproc.globalfield.list', '.default', ''),
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_HISTORY' => 'N',
	]
);
?>

<script>
	BX.ready(function ()
	{
		BX.message(<?= \Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)) ?>);

		BX.Bizproc.Component.GlobalFieldListComponent.Instance = new BX.Bizproc.Component.GlobalFieldListComponent({
			componentName: '<?= CUtil::JSEscape('bitrix:bizproc.globalfield.list') ?>',
			signedParameters: '<?= CUtil::JSEscape($this->getComponent()->getSignedParameters())?>',
			gridId: '<?= CUtil::JSEscape($arResult['GridId'])?>',
			signedDocumentType: '<?= CUtil::JSEscape($arParams['~DOCUMENT_TYPE_SIGNED']) ?>',
			mode: '<?= CUtil::JSEscape($arResult['Mode']) ?>',
			slider: BX.getClass('BX.SidePanel.Instance') ? BX.SidePanel.Instance.getSliderByWindow(window) : null
		});

		BX.Bizproc.Component.GlobalFieldListComponent.Instance.init();
	});
</script>
