<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
CJSCore::Init(array('report.js.dashboard'));
CJSCore::Init(array('report.js.activitywidget')); //TODO remove here when can move it to ajax handler
CJSCore::Init(array('report_visual_constructor'));
CJSCore::Init(array('sidepanel'));
$APPLICATION->IncludeComponent(
	'bitrix:report.visualconstructor.board.header',
	'',
	array(
		'BOARD_ID' => $arResult['BOARD_ID'],
		'REPORTS_CATEGORIES' => $arResult['REPORTS_CATEGORIES'],
		'FILTER' => $arResult['FILTER']
	),
	$component,
	array()
);
$rows = $arResult['ROWS'];
?>
<div id="report-visualconstructor-board"></div>
<script>
	BX.ready(function ()
	{
		BX.message({'DASHBOARD_WIDGET_PROPERTIES_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('DASHBOARD_WIDGET_PROPERTIES_TITLE')?>"});
		BX.message({'DASHBOARD_DEMO_FLAG_TEXT': "<?=\Bitrix\Main\Localization\Loc::getMessage('DASHBOARD_DEMO_FLAG_TEXT')?>"});
		BX.message({'DASHBOARD_DEMO_FLAG_HIDE_LINK': "<?=\Bitrix\Main\Localization\Loc::getMessage('DASHBOARD_DEMO_FLAG_HIDE_LINK')?>"});
		BX.message({'DASHBOARD_WIDGET_PROPERTIES_BUTTON_HEAD_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('DASHBOARD_WIDGET_PROPERTIES_BUTTON_HEAD_TITLE')?>"});
		new BX.VisualConstructor.BoardBase({
			renderTo: BX('report-visualconstructor-board'),
			boardId: <?=CUtil::PhpToJSObject($arResult['BOARD_ID'])?>,
			rows: <?=CUtil::PhpToJSObject($rows, false, false, true)?>,
			demoMode: "<?=$arResult['IS_BOARD_DEMO'];?>"
		});
	});
</script>