<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"popup",
	"ui.buttons.icons",
	'report.integration.toolbar',
]);

$buttonSettings = new \Bitrix\UI\Buttons\SettingsButton();
$buttonSettings->addAttribute('id', 'visualconstrctor_board_configuration_button');
\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($buttonSettings);

if (!$arResult['IS_FRAME_MODE'])
{
	$buttonAdd = new \Bitrix\UI\Buttons\Button([
		'text' => \Bitrix\Main\Localization\Loc::getMessage('VISUALCONSTUCTOR_ADD_WIDGET_TO_BOARD'),
		'color' => \Bitrix\UI\Buttons\Color::PRIMARY,
		'dataset' => [
			'toolbar-collapsed-icon' => Bitrix\UI\Buttons\Icon::ADD,
		]
	]);
	$buttonAdd->addAttribute('id', 'add_report_popup_button');
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($buttonAdd);
}
?>

<div id="add_report_to_board"></div>

<script>
	BX.message({
		'VISUALCONSTRUCTOR_DASHBOARD_DEMO_MODE_ON_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('VISUALCONSTRUCTOR_DASHBOARD_DEMO_MODE_ON_TITLE')?>",
		'VISUALCONSTRUCTOR_DASHBOARD_DEMO_MODE_OFF_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('VISUALCONSTRUCTOR_DASHBOARD_DEMO_MODE_OFF_TITLE')?>",
		'VISUALCONSTRUCTOR_DASHBOARD_DESIGN_MODE_ON_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('VISUALCONSTRUCTOR_DASHBOARD_DESIGN_MODE_ON_TITLE')?>",
		'VISUALCONSTRUCTOR_DASHBOARD_DESIGN_MODE_OFF_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('VISUALCONSTRUCTOR_DASHBOARD_DESIGN_MODE_OFF_TITLE')?>",
		'VISUALCONSTRUCTOR_DASHBOARD_GO_TO_DEFAULT': "<?=\Bitrix\Main\Localization\Loc::getMessage('VISUALCONSTRUCTOR_DASHBOARD_GO_TO_DEFAULT')?>",
	});
	new BX.Report.VisualConstructor.Board.Controls({
		reportCategories: <?=CUtil::PhpToJSObject($arResult['REPORTS_CATEGORIES'])?>,
		boardId: <?=CUtil::PhpToJSObject($arResult['BOARD_ID'])?>,
		configurationButton: BX('visualconstrctor_board_configuration_button'),
		demoToggle: <?=CUtil::PhpToJSObject($arResult['DEMO_TOGGLE'])?>
	});
</script>