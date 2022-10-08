<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"ui.fonts.opensans",
	"ui.buttons.icons",
]);

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

/** @var \Bitrix\Report\VisualConstructor\Form $form */
$form = $arResult['ADD_FORM'];
$showAllButtonTitle = $arResult['SHOW_ALL_BUTTON_TITLE'];
$hideButtonTitle = $arResult['SHOW_HIDDEN_BUTTON_TITLE'];
$removePatternWidgetDialogContent = $arResult['REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CONTENT'];
$removePatternWidgetDialogConfirmText = $arResult['REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CONFIRM_TEXT'];
$removePatternWidgetDialogCancelText = $arResult['REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CANCEL_TEXT'];
?>
<div class="report-visualconstructor-add-form-container">

	<div class="report-visualconstructor-add-header-container">
		<div class="report-visualconstructor-add-title-container"><?= Loc::getMessage('SELECT_REPORT_TYPE_TITLE_IN_ADD_FORM') ?></div>
		<div class="report-visualconstructor-add-control-container">
			<!--            <div data-type="create-widget-button" class="ui-btn ui-btn-primary ui-btn-md ui-btn-icon-add">-->
			<? //=Loc::getMessage('CREATE_REPORT_TYPE_TITLE_IN_ADD_FORM')?><!--</div>-->
		</div>
	</div>
	<div class="report-visualconstructor-add-content">
		<?php $form->render(); ?>
	</div>
</div>

<script>
	BX.message({'REPORT_ADD_FORM_SHOW_ALL_BUTTON_TITLE': "<?=$showAllButtonTitle?>"});
	BX.message({'REPORT_ADD_FORM_HIDDEN_BUTTON_TITLE': "<?=$hideButtonTitle?>"});
	BX.message({'REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CONTENT': "<?=$removePatternWidgetDialogContent?>"});
	BX.message({'REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CONFIRM_TEXT': "<?=$removePatternWidgetDialogConfirmText?>"});
	BX.message({'REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CANCEL_TEXT': "<?=$removePatternWidgetDialogCancelText?>"});
	new BX.Report.VisualConstructor.Board.AddForm();

	BX.Report.VisualConstructor.Board.ClipText.createFabric(document.querySelectorAll('.report-visualconstructor-view-miniature-text'));


</script>