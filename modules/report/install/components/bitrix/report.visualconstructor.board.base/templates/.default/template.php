<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
CJSCore::Init(array('report.js.dashboard'));
CJSCore::Init(array('report.js.activitywidget')); //TODO remove here when can move it to ajax handler
CJSCore::Init(array('report_visual_constructor'));
CJSCore::Init(array('sidepanel'));

$APPLICATION->IncludeComponent(
	'bitrix:report.visualconstructor.board.header',
	$arResult['HEADER_TEMPLATE_NAME'],
	array(
		'BOARD_ID' => $arResult['BOARD_ID'],
		'REPORTS_CATEGORIES' => $arResult['REPORTS_CATEGORIES'],
		'FILTER' => $arResult['FILTER'],
		'DEFAULT_BOARD' => $arResult['IS_BOARD_DEFAULT'],
		'BOARD_BUTTONS' => $arResult['BOARD_BUTTONS']
	),
	$component,
	array()
);
$rows = $arResult['ROWS'];
?>

<?if($arResult['IS_ENABLED_STEPPER']):?>
	<div class="report-analytics-stepper-wrapper">
		<?=\Bitrix\Main\Update\Stepper::getHtml($arResult['STEPPER_IDS'])?>
	</div>
<?endif;?>

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
			demoMode: "<?=$arResult['IS_BOARD_DEMO'];?>",
			defaultBoard: "<?=$arResult['IS_BOARD_DEFAULT'];?>",
			filterId: "<?=CUtil::JSEscape($arResult['FILTER']->getFilterParameters()['FILTER_ID'])?>"

		});
	});
</script>