<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>
<div id="bp-debugger-session-list-errors-container"></div>
<?php
\Bitrix\Main\UI\Extension::load(['ui', 'ui.buttons', 'ui.icons', 'ui.alerts', 'bizproc.debugger']);


/** @var array $arResult */
global $APPLICATION;

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => $arResult['gridId'],
		'COLUMNS' => $arResult['gridColumns'],
		'ROWS' => $arResult['gridData'],
		'SHOW_ROW_CHECKBOXES' => true,
		'NAV_OBJECT' => $arResult['pageNavigation'],
		'AJAX_MODE' => 'Y',
		'AJAX_ID' => \CAjax::getComponentID('bitrix:bizproc.debugger.session.list', '.default', ''),
		'PAGE_SIZES' => $arResult['pageSizes'],
		'AJAX_OPTION_JUMP' => 'N',
		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		'TOTAL_ROWS_COUNT' => $arResult['pageNavigation']->getRecordCount(),
		'SHOW_PAGESIZE' => true,
		'SHOW_ACTION_PANEL' => true,
		'ACTION_PANEL' => $arResult['gridActions'],
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
	BX.ready(function ()
	{
		var gridId = '<?=CUtil::JSEscape($arResult['gridId'])?>';

		BX.Bizproc.Component.DebuggerSessionList.Instance = new BX.Bizproc.Component.DebuggerSessionList({
			gridId: gridId,
			createDebuggerSessionButton: document.getElementById('bp-add-debugger-session'),
			errorsContainerDiv: document.getElementById('bp-debugger-session-list-errors-container'),
			documentSigned: <?= CUtil::PhpToJSObject($arResult['documentSigned']) ?>,
			signedParameters: '<?= $this->getComponent()->getSignedParameters() ?>'
		});

		BX.Bizproc.Component.DebuggerSessionList.Instance.init();
	});
</script>
