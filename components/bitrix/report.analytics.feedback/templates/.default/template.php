<?php
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;
Extension::load("ui.buttons");
Extension::load("ui.buttons.icons");
$buttonId = $arResult['BOARD_KEY'] . '_feedback_button';
?>

<div id="<?=$buttonId?>" data-board-key="<?=$arResult['BOARD_KEY']?>" class="ui-btn ui-btn-light-border"><?=Loc::getMessage('REPORT_FEEDBACK_BUTTON_TITLE');?></div>
<div class="report-analytic-feedback-container" style="display: none; padding: 20px">
	<div class="report-analytic-feedback-title-container" style="
    margin-bottom: 18px;
    font: 24px 'OpenSans-Light','Helvetica Neue',Arial,Helvetica,sans-serif;
	"><?=Loc::getMessage('REPORT_FEEDBACK_TITLE');?></div>
</div>

<script>
	new BX.Report.Analytics.Feedback({
		button: BX(<?=CUtil::PhpToJSObject($buttonId)?>),
		feedbackContainer: document.querySelector('.report-analytic-feedback-container')
	});
</script>