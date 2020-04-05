<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
CJSCore::Init(array("popup"));
\Bitrix\Main\UI\Extension::load("ui.buttons.icons");
?>
<div class="pagetitle-container pagetitle-align-right-container">
	<div class="visual-constructor-contols">
		<div id="visualconstrctor_board_configuration_button" class="webform-small-button webform-small-button-transparent webform-cogwheel">
			<span class="webform-button-icon"></span>
		</div>
		<div id="add_report_popup_button" class="ui-btn ui-btn-primary">
			<?= \Bitrix\Main\Localization\Loc::getMessage('VISUALCONSTUCTOR_ADD_WIDGET_TO_BOARD') ?>
		</div>
	</div>

	<div id="add_report_to_board"></div>
</div>

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
		configurationButton: BX('visualconstrctor_board_configuration_button')
	});
</script>