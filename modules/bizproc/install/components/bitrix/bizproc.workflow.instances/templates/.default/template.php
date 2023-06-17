<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');

if (!empty($arResult["FatalErrorMessage"]))
{
	?>
	<div class="bp-errortext">
		<?= $arResult["FatalErrorMessage"] ?>
	</div>
	<?
}
else
{
	if (!empty($arResult["ErrorMessage"]))
	{
		?>
		<div class="bp-errortext">
			<p><?= $arResult["ErrorMessage"] ?></p>
		</div>
		<?
	}

	\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter([
		"FILTER" => $arResult["FILTER"],
		"FILTER_PRESETS" => $arResult['FILTER_PRESETS'],
		'FILTER_ID' => $arResult['FILTER_ID'],
		'GRID_ID' => $arResult["GRID_ID"],
		'ENABLE_LIVE_SEARCH' => false,
		"ENABLE_LABEL" => true,
		'RESET_TO_DEFAULT_MODE' => true,
		'THEME' => \Bitrix\Main\UI\Filter\Theme::MUTED,
	]);

	foreach ($arResult["RECORDS"] as $key => $record)
	{
		if ($record['data']['IS_LOCKED'])
			$record['rowClass'] = 'bp-row-warning';

		$record['data']['IS_LOCKED'] = $record['data']['IS_LOCKED'] ? '<span class="bp-warning">'.getMessage('BPWI_YES').'</span>' : getMessage('BPWI_NO');

		if (!empty($record['data']['WS_MODULE_ID']))
			$record['data']['WS_MODULE_ID'] = BizprocWorkflowInstances::getModuleName($record['data']['WS_MODULE_ID'], $record['data']['WS_ENTITY']);

		foreach (array('WS_MODULE_ID','WS_DOCUMENT_NAME', 'WS_STARTED', 'WS_STARTED_BY', 'WS_WORKFLOW_TEMPLATE_ID') as $field)
		{
			if (empty($record['data'][$field]))
				$record['data'][$field] = '<span class="bp-warning">'.getMessage('BPWIT_UNKNOWN').'</span>';
			elseif ($field === 'WS_DOCUMENT_NAME')
			{
				$record['data'][$field] = htmlspecialcharsbx($record['data'][$field]);
			}
		}
		$arResult["RECORDS"][$key] = $record;
	}

	$gridParams = [
		"GRID_ID" => $arResult["GRID_ID"],
		"COLUMNS" => $arResult["HEADERS"],
		"SORT" => $arResult["SORT"],
		"ROWS" => $arResult["RECORDS"],
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_JUMP" => "Y",
		'ERROR_MESSAGES' => $arResult['ERRORS'] ?? [],

		'AJAX_ID' => \CAjax::getComponentID('bitrix:bizproc.workflow.instances', '.default', ''),
		'NAV_OBJECT' => $arResult['NAV_OBJECT'],
		'TOTAL_ROWS_COUNT' => $arResult['NAV_OBJECT']->getRecordCount(),

		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		'SHOW_PAGESIZE' => true,
		'PAGE_SIZES' => [
			["NAME" => "5", "VALUE" => "5"],
			["NAME" => "10", "VALUE" => "10"],
			["NAME" => "20", "VALUE" => "20"],
			["NAME" => "50", "VALUE" => "50"],
			["NAME" => "100", "VALUE" => "100"],
		],
		'SHOW_ACTION_PANEL' => true,
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_SORT' => true,
		'ALLOW_PIN_HEADER' => true,
		'AJAX_OPTION_HISTORY' => 'N',
	];

	if ($arResult['EDITABLE'])
	{
		$gridParams['SHOW_ACTION_PANEL'] = true;
		$gridParams['ACTION_PANEL'] = [
			"GROUPS" => [
				[
					"ITEMS" => [
						(new \Bitrix\Main\Grid\Panel\Snippet())->getRemoveButton(),
					]
				]
			]
		];

		$gridParams['SHOW_ROW_CHECKBOXES'] = true;
		$gridParams['EDITABLE'] = true;
		$gridParams['ACTIONS'] = array(
			'delete' => true,
		);
	}

	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.grid',
		"",
		$gridParams,
		$component
	);
	?>

	<script>
		BX.ready(function ()
		{
			var gridId = '<?= CUtil::JSEscape($arResult["GRID_ID"]) ?>';

			BX.message({
				BPWI_DELETE_BTN_LABEL: '<?= CUtil::JSEscape(getMessage('BPWI_DELETE_LABEL')) ?>',
				BPWI_DELETE_MESS_CONFIRM: '<?= CUtil::JSEscape(getMessage('BPWI_DELETE_CONFIRM')) ?>',
			});

			BX.Bizproc.Component.WorkflowInstances.Instance = new BX.Bizproc.Component.WorkflowInstances({
				gridId: gridId,
			});
		});
	</script>

<?php
}
