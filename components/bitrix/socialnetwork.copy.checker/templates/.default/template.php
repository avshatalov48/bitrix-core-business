<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Web\Json;

Extension::load("ui.alerts");

$messages = Loc::loadLanguageFile(__FILE__);

$moduleId = $arResult["moduleId"];
$stepperClassName = $arResult["stepperClassName"];
$errorOption = $arResult["errorOption"];

$showProgress = $arResult["showProgress"];
$showError = $arResult["showError"];
$titleMessage = $arResult["titleMessage"];
$errorMessage = $arResult["errorMessage"];

$errorAlertContainerId = "cc-ui-alert-container";
$errorAlertCloseButtonId = "cc-ui-alert-close-btn";
?>

<div class="tasks-copy-checker-container">
	<?php if ($showProgress): ?>
		<div class="tasks-copy-checker-progress">
			<?=Stepper::getHtml([$moduleId => [$stepperClassName]], $titleMessage);?>
		</div>
	<? endif; ?>
	<div class="tasks-copy-checker-errors">
		<?php if ($showError): ?>
			<div id="<?=$errorAlertContainerId?>" class="ui-alert ui-alert-danger">
				<span class="ui-alert-message">
					<?=HtmlFilter::encode($errorMessage)?>
				</span>
				<span id="<?=$errorAlertCloseButtonId?>" class="ui-alert-close-btn"></span>
			</div>
		<? endif; ?>
	</div>
</div>

<script>
	BX.ready(function() {
		BX.message(<?=Json::encode($messages)?>);
		new BX.Socialnetwork.CopyChecker({
			signedParameters: "<?=$this->getComponent()->getSignedParameters()?>",
			moduleId: "<?=$moduleId?>",
			errorOption: "<?=HtmlFilter::encode($errorOption)?>",
			errorAlertContainerId: "<?=$errorAlertContainerId?>",
			errorAlertCloseButtonId: "<?=$errorAlertCloseButtonId?>"
		});
	});
</script>