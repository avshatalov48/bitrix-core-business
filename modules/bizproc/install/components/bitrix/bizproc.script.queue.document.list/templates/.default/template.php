<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load(['bizproc.script', 'ui.tooltip', 'ui.label', 'ui.dialogs.messagebox']);


$formatDocumentCell = function($row)
{
	return sprintf(
		'<a href="%s">%s</a>',
		htmlspecialcharsbx($row['DOCUMENT_URL']),
		$row['DOCUMENT_NAME']? htmlspecialcharsbx($row['DOCUMENT_NAME']) : '-?-'
	);
};

$formatStatusCell = function ($row)
{
	$label = \Bitrix\Bizproc\Script\Queue\Status::getLabel($row['STATUS']);
	$message = $row['STATUS_MESSAGE'];
	$color = 'ui-label-warning';
	if ($row['STATUS'] == \Bitrix\Bizproc\Script\Queue\Status::COMPLETED)
	{
		$color = 'ui-label-success';
	}

	if (
		$row['STATUS'] == \Bitrix\Bizproc\Script\Queue\Status::TERMINATED
		||
		$row['STATUS'] == \Bitrix\Bizproc\Script\Queue\Status::FAULT
	)
	{
		$color = 'ui-label-danger';
	}

	return sprintf(
		'<div class="ui-label ui-label-fill %s" title="%s"><span class="ui-label-inner">%s</span></div>',
		$color,
		htmlspecialcharsbx($message),
		htmlspecialcharsbx($label)
	);
};

$formatWorkflowCell = function ($row)
{

	$id = CUtil::JSEscape($row['WORKFLOW_ID']);
	$js = <<<JS
if (top.BX.Bitrix24 && top.BX.Bitrix24.Slider)
			{
				top.BX.Bitrix24.Slider.open(
					'/bitrix/components/bitrix/bizproc.log/slider.php?site_id='+BX.message('SITE_ID')+'&WORKFLOW_ID=' + '{$id}'
				)
			};
return false;
JS;

	return sprintf(
		'<a href="#" onclick="%s">%s</a>',
		htmlspecialcharsbx($js),
		htmlspecialcharsbx($row['WORKFLOW_ID'])
	);

};

foreach ($arResult['GridRows'] as $index => $gridRow)
{
	$arResult['GridRows'][$index]['data']['DOCUMENT_ID'] = $formatDocumentCell($gridRow['data']);
	$arResult['GridRows'][$index]['data']['STATUS'] = $formatStatusCell($gridRow['data']);
	$arResult['GridRows'][$index]['data']['WORKFLOW_ID'] = $formatWorkflowCell($gridRow['data']);
}

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
		'AJAX_ID' => \CAjax::getComponentID('bitrix:bizproc.script.queue.list', '.default', ''),
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
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_SORT' => true,
		'ALLOW_PIN_HEADER' => true,
		'AJAX_OPTION_HISTORY' => 'N'
	]
);
?>
