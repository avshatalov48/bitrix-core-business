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
use Bitrix\Socialnetwork\Copy\Integration\Helper;

Extension::load("ui.alerts");

$messages = Loc::loadLanguageFile(__FILE__);

$showProgress = $arResult["SHOW_PROGRESS"];

/**
 * @var Helper $helper
 */
$helper = $arResult["HELPER"];
$moduleId = $helper->getModuleId();
$idsWithErrors = $arResult["IDS_WITH_ERRORS"];

$errorAlertContainerId = "cc-ui-alert-container";
$errorAlertCloseButtonId = "cc-ui-alert-close-btn";
?>

<div class="tasks-copy-checker-container">
	<?php if ($showProgress): ?>
		<div class="tasks-copy-checker-progress">
			<?=Stepper::getHtml([
				$moduleId => [
					$helper->getLinkToStepperClass()
				],
			], $helper->getTextMap()["title"]);?>
		</div>
	<? endif; ?>
	<div class="tasks-copy-checker-errors">
		<?php if ($idsWithErrors): ?>
			<div id="<?=$errorAlertContainerId?>" class="ui-alert ui-alert-danger">
				<span class="ui-alert-message">
					<?=$helper->getTextMap()["error"].
						HtmlFilter::encode(implode(", ", $idsWithErrors))?>
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
			errorOptionName: "<?=$helper->getOptionNames()["error"]?>",
			errorAlertContainerId: "<?=$errorAlertContainerId?>",
			errorAlertCloseButtonId: "<?=$errorAlertCloseButtonId?>"
		});
	});
</script>