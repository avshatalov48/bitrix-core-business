<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load(['ui.buttons', 'ui.buttons.icons', 'bizproc.script']);

$useMarket = \Bitrix\Main\Loader::includeModule('rest');
if ($useMarket)
{
	CJSCore::Init(['marketplace', 'applayout']);
	$marketImportUrl = \Bitrix\Rest\Marketplace\Url::getConfigurationImportManifestUrl($arParams['PLACEMENT']);
	$marketExportUrl = \Bitrix\Rest\Marketplace\Url::getConfigurationExportElementUrl(
		$arParams['PLACEMENT'],
		$arParams['DOCUMENT_TYPE'][2]
	);
}

$canCreateScript = $arResult['canCreateScript'];

global $APPLICATION;
?>

<?php $this->SetViewTarget('pagetitle') ?>
<div class="ui-btn-container">
	<?if ($useMarket):?>
	<a class="ui-btn ui-btn-light-border" href="<?= htmlspecialcharsbx($marketImportUrl) ?>">
		<?= GetMessage('BIZPROC_SCRIPT_LIST_TITLE_MARKETPLACE_IMPORT') ?>
	</a>
	<button class="ui-btn ui-btn-light-border" data-url="<?= htmlspecialcharsbx($marketExportUrl) ?>" id="bp_export_scenario">
		<?= GetMessage('BIZPROC_SCRIPT_LIST_TITLE_MARKETPLACE_EXPORT') ?>
	</button>
	<?endif;?>

	<?php if ($canCreateScript): ?>
		<button class="ui-btn ui-btn-icon-add ui-btn-primary" id="bp_add_scenario">
			<?= GetMessage('BIZPROC_SCRIPT_LIST_TITLE_BUTTON_ADD_SCENARIO') ?>
		</button>
	<?php else:?>
		<button class="ui-btn ui-btn-icon-add ui-btn-primary" disabled>
			<?= GetMessage('BIZPROC_SCRIPT_LIST_TITLE_BUTTON_ADD_SCENARIO') ?>
		</button>
	<?php endif ?>
</div>
<?php $this->EndViewTarget() ?>

<?php
$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => $arResult['GridId'],
		'COLUMNS' => $arResult['GridColumns'],
		'ROWS' => $arResult['GridRows'],
		'SHOW_ROW_CHECKBOXES' => false,
		'NAV_OBJECT' => $arResult['PageNavigation'],
		'AJAX_MODE' => 'Y',
		'AJAX_ID' => \CAjax::getComponentID('bitrix:bizproc.script.list', '.default', ''),
		'PAGE_SIZES' => $arResult['PageSizes'],
		'AJAX_OPTION_JUMP' => 'N',
		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		'TOTAL_ROWS_COUNT' => $arResult['PageNavigation']->getRecordCount(),
		'SHOW_PAGESIZE' => true,
		'SHOW_ACTION_PANEL' => true,
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_SORT' => true,
		'ALLOW_PIN_HEADER' => true,
		'AJAX_OPTION_HISTORY' => 'N'
	]
);
?>

<script>
	BX.ready(function ()
	{
		var messages = <?=\Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__))?>;
		var gridId = '<?=CUtil::JSEscape($arResult['GridId'])?>';

		BX.message(messages);
		BX.Bizproc.ScriptListComponent.Instance = new BX.Bizproc.ScriptListComponent({
			gridId: gridId,
			createScriptButton: document.getElementById('bp_add_scenario'),
			exportScriptButton: document.getElementById('bp_export_scenario'),
			documentType: '<?= CUtil::JSEscape($arParams['~DOCUMENT_TYPE_SIGNED']) ?>'
		});

		BX.Bizproc.ScriptListComponent.Instance.init();
	});
</script>