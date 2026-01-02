<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>
<div id="bp-user-processes-errors-container"></div>
<?php

/**
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 */

\Bitrix\Main\UI\Extension::load([
	'bizproc.router',
	'bizproc.workflow.instances.widget',
	'tooltip',
	'ui',
	'ui.alerts',
	'ui.buttons',
	'ui.buttons.icons',
	'ui.icons',
	'ui.icon-set.main',
	'ui.viewer',
	'ui.counterpanel',
	'ui.hint',
]);

/** @var array $arResult */
global $APPLICATION;
/** @var \Bitrix\Main\UI\PageNavigation $pageNavigation */
$pageNavigation = $arResult['pageNavigation'];

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
		'AJAX_ID' => \CAjax::getComponentID('bitrix:bizproc.user.processes.start', '.default', ''),
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
		'AJAX_OPTION_HISTORY' => 'N',
	],
);

$messages = \Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);
?>
<script>
	BX.ready(function ()
	{
		BX.Bizproc.Router.init();
		BX.message(<?= \Bitrix\Main\Web\Json::encode($messages) ?>);

		const gridId = '<?= CUtil::JSEscape($arResult['gridId']) ?>';

		BX.Bizproc.Component.UserProcessesStart.Instance = new BX.Bizproc.Component.UserProcessesStart({
			gridId,
			errors: [],
		});
		BX.Event.EventEmitter.subscribe('Grid::updated', () => BX.Bizproc.Component.UserProcessesStart.Instance.init());
	})
</script>
