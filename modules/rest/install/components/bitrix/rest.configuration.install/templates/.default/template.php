<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CAllMain $APPLICATION */
/** @global CAllUser $USER */
/** @global CAllDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

	Extension::load(["ui.buttons", "ui.progressbar", "ui.notification", "ui.common", "ui.dialogs.messagebox"]);

Loc::loadMessages(__FILE__);
$containerId = 'rest-configuration-import-install';
$description = (!empty($arParams['APP']))?'REST_CONFIGURATION_IMPORT_INSTALL_APP_DESCRIPTION':'REST_CONFIGURATION_IMPORT_INSTALL_DESCRIPTION';
if($arParams['MODE'])
{
	$description .= '_'.$arParams['MODE'];
}
?>
<? if(is_array($arResult['NOTIFY'])):?>
	<div class="rest-configuration-alert">
		<? foreach ($arResult['NOTIFY'] as $notify): ?>
			<div class="rest-configuration-alert-text"><?=$notify?></div>
		<? endforeach;?>
	</div>
<? endif?>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="rest-configuration-import-install">
	<div class="rest-configuration-start-icon-main rest-configuration-start-icon-main-zip">
		<div class="rest-configuration-start-icon-refresh"></div>
		<div class="rest-configuration-start-icon"></div>
		<div class="rest-configuration-start-icon-circle"></div>
	</div>
	<div class="rest-configuration-controls start_btn_block">

		<span class="ui-btn ui-btn-lg ui-btn-primary start_btn"><?=Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_START_BTN')?></span>
	</div>
	<div class="rest-configuration-info"><?=Loc::getMessage($description)?></div>
	<div class="rest-configuration-errors"></div>
	<script type="text/javascript">
		BX.ready(function () {
			BX.Rest.Configuration.Install.init(<?=Json::encode([
				'id' => $containerId,
				'signedParameters' => $this->getComponent()->getSignedParameters()
			])?>);
		});
		BX.message(<?=Json::encode(
				[
					'REST_CONFIGURATION_IMPORT_INSTALL_FATAL_ERROR' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_FATAL_ERROR'),
					'REST_CONFIGURATION_IMPORT_INSTALL_STEP_START' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_STEP_START'),
					'REST_CONFIGURATION_IMPORT_INSTALL_STEP_CLEAR' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_STEP_CLEAR'),
					'REST_CONFIGURATION_IMPORT_INSTALL_STEP' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_STEP'),
					'REST_CONFIGURATION_IMPORT_INSTALL_STEP_FINISH' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_STEP_FINISH'),
					'REST_CONFIGURATION_IMPORT_INSTALL_FINISH_TEXT' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_FINISH_TEXT'),
					'REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_BTN_CONTINUE' => Loc::getMessage("REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_BTN_CONTINUE"),
					'REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_BTN_CANCEL' => Loc::getMessage("REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_BTN_CANCEL"),
					'REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_TEXT' => Loc::getMessage("REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_TEXT"),
					'REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_CHECKBOX_LABEL' => Loc::getMessage("REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_CHECKBOX_LABEL"),

					'REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TEXT_LABEL' => Loc::getMessage("REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TEXT_LABEL"),
					'REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TEXT_PLACEHOLDER' => Loc::getMessage("REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TEXT_PLACEHOLDER"),
					'REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TITLE' => Loc::getMessage("REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TITLE"),
					'REST_CONFIGURATION_IMPORT_ERRORS_POPUP_BTN_COPY' => Loc::getMessage("REST_CONFIGURATION_IMPORT_ERRORS_POPUP_BTN_COPY"),
					'REST_CONFIGURATION_IMPORT_FINISH_DESCRIPTION' => Loc::getMessage("REST_CONFIGURATION_IMPORT_FINISH_DESCRIPTION"),
					'REST_CONFIGURATION_IMPORT_FINISH_ERROR_DESCRIPTION' => Loc::getMessage("REST_CONFIGURATION_IMPORT_FINISH_ERROR_DESCRIPTION"),
					'REST_CONFIGURATION_IMPORT_ERRORS_REPORT_BTN' => Loc::getMessage("REST_CONFIGURATION_IMPORT_ERRORS_REPORT_BTN"),
				]
			);
			?>);
	</script>
</div>