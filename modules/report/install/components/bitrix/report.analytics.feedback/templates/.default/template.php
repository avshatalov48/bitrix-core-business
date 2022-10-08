<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;
Extension::load(["ui.buttons", "ui.buttons.icons", "ui.fonts.opensans"]);

$buttonId = $arResult['BOARD_KEY'] . '_feedback_button';
?>

<div id="<?=$buttonId?>" data-board-key="<?=$arResult['BOARD_KEY']?>" class="ui-btn ui-btn-light-border"><?=Loc::getMessage('REPORT_FEEDBACK_BUTTON_TITLE');?></div>
<div class="report-analytic-feedback-container" style="display: none; padding: 20px">
	<div class="report-analytic-feedback-title-container" style="
    margin-bottom: 18px;
    font: 24px var(--ui-font-family-secondary, var(--ui-font-family-open-sans));
    font-weight: var(--ui-font-weight-light, 300);
	"><?=Loc::getMessage('REPORT_FEEDBACK_TITLE');?></div>
</div>

<script>
	new BX.Report.Analytics.Feedback({
		button: BX(<?=CUtil::PhpToJSObject($buttonId)?>),
		feedbackContainer: document.querySelector('.report-analytic-feedback-container')
	});
</script>