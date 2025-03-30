<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>
<div id="bp-workflow-start-list-errors-container"></div>
<?php
\Bitrix\Main\UI\Extension::load([
	'ui',
	'ui.buttons',
	'ui.icons',
	'ui.alerts',
	'bizproc.workflow.starter',
	'bizproc.workflow.instances.widget',
	'ui.feedback.form',
	'sidepanel',
]);

/** @var array $arResult */
global $APPLICATION;

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => $arResult['gridId'],
		'COLUMNS' => $arResult['gridColumns'],
		'ROWS' => $arResult['gridData'],
		'SHOW_ROW_CHECKBOXES' => false,
		'NAV_OBJECT' => $arResult['pageNavigation'],
		'AJAX_MODE' => 'Y',
		'AJAX_ID' => \CAjax::getComponentID('bitrix:bizproc.workflow.start.list', '.default', ''),
		'AJAX_OPTION_JUMP' => 'N',
		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => false,
		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_PAGINATION' => false,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		'TOTAL_ROWS_COUNT' => count($arResult['gridData']),
		'SHOW_PAGESIZE' => false,
		'SHOW_ACTION_PANEL' => false,
		'ACTION_PANEL' => [],
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_INLINE_EDIT' => true,
		'ALLOW_SORT' => true,
		'ALLOW_PIN_HEADER' => true,
		'AJAX_OPTION_HISTORY' => 'N'
	]
);
?>

<script>
	BX.ready(() => {
		BX.message(<?= \Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)) ?>);
		const gridId = '<?= CUtil::JSEscape($arResult['gridId']) ?>';

		BX.Bizproc.Component.WorkflowStartList.Instance = new BX.Bizproc.Component.WorkflowStartList({
			gridId: gridId,
			createTemplateButton: document.getElementById('bp-add-template'),
			errorsContainerDiv: document.getElementById('bp-workflow-start-list-errors-container'),
			canEdit: <?= $arResult['canEdit'] ?>,
			bizprocEditorUrl: '<?= CUtil::JSEscape($arResult['bizprocEditorUrl']) ?>',
			signedDocumentType: '<?= CUtil::JSEscape($arResult['signedDocumentType']) ?>',
			signedDocumentId:  '<?= CUtil::JSEscape($arResult['signedDocumentId']) ?>',
		});

		BX.Bizproc.Component.WorkflowStartList.Instance.init();
	});
</script>
