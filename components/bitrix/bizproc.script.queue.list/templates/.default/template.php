<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load(['bizproc.script', 'ui.tooltip', 'ui.label', 'ui.dialogs.messagebox']);


$formatStartedByCell = function($row)
{
	$format = \CSite::getNameFormat();
	$name = \CUser::FormatName($format, [
		'NAME' => $row['STARTED_USER_NAME'],
		'SECOND_NAME' => $row['STARTED_USER_SECOND_NAME'],
		'LAST_NAME' => $row['STARTED_USER_LAST_NAME'],
		'LOGIN' => $row['STARTED_USER_LOGIN'],
	],
		false,
		false
	);
	$url = "/company/personal/user/{$row['STARTED_BY']}/";

	return sprintf(
		'<a href="%s" bx-tooltip-user-id="%s" bx-tooltip-classname="intrantet-user-selector-tooltip">%s</a>',
		$url,
		$row['STARTED_BY'],
		htmlspecialcharsbx($name)
	);
};

$formatStatusCell = function ($row)
{
	$label = \Bitrix\Bizproc\Script\Queue\Status::getLabel($row['STATUS']);
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
		'<div class="ui-label ui-label-fill %s"><span class="ui-label-inner">%s</span></div>',
		$color,
		htmlspecialcharsbx($label)
	);
};

$formatQueuedCntCell = function ($row)
{
	return sprintf(
		'<a class="ui-btn-link" onclick="BX.Bizproc.Script.Manager.Instance.showScriptQueueDocumentList(%d);" href="#">%s</a>',
		$row['ID'],
		htmlspecialcharsbx($row['QUEUED_CNT'])
	);
};

foreach ($arResult['GridRows'] as $index => $gridRow)
{
	$arResult['GridRows'][$index]['data']['STARTED_BY'] = $formatStartedByCell($gridRow['data']);
	$arResult['GridRows'][$index]['data']['STATUS'] = $formatStatusCell($gridRow['data']);
	$arResult['GridRows'][$index]['data']['QUEUED_CNT'] = $formatQueuedCntCell($gridRow['data']);
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
<script>
	BX.ready(function(){
		var messages = <?=\Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__))?>;
		var gridId = '<?=CUtil::JSEscape($arResult['GridId'])?>';

		BX.message(messages);
		var cmp = new BX.Bizproc.ScriptQueueListComponent({gridId: gridId});
		BX.Bizproc.ScriptQueueListComponent.Instance = cmp;
	});
</script>
