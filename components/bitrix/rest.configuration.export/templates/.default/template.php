<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

Extension::load([
	"ui.design-tokens",
	"ui.fonts.opensans",
	"ui.buttons",
	"ui.common",
	"ui.progressbar",
]);

$containerId = 'rest-configuration-export';
?>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="rest-configuration">
	<div class="rest-configuration-wrapper">
		<div class="rest-configuration-title"><?=($arResult['MANIFEST']['EXPORT_TITLE_BLOCK'])?:Loc::getMessage('REST_CONFIGURATION_EXPORT_TITLE_BLOCK')?></div>
		<? if($arResult['ENABLED_ZIP_MODE'] != 'Y'):?>
			<div class="rest-configuration-alert">
				<div class="rest-configuration-alert-text"><?=Loc::getMessage('REST_CONFIGURATION_EXPORT_ERRORS_SETTINGS_NEEDED', [ '#SETTING_HREF#' => $arResult['REST_SETTING_PATH'] ])?></div>
			</div>
		<? endif;?>
		<div class="rest-configuration-start-icon-main rest-configuration-start-icon-main-zip">
			<div class="rest-configuration-start-icon-refresh"></div>
			<div class="rest-configuration-start-icon"></div>
			<div class="rest-configuration-start-icon-circle"></div>
		</div>
		<div class="rest-configuration-controls start-btn-block">
			<button class="ui-btn ui-btn-lg ui-btn-primary start-btn" <?=($arResult['ENABLED_EXPORT'] != 'Y')?'disabled="disabled" ':'';?>><?=Loc::getMessage('REST_CONFIGURATION_EXPORT_START_BTN')?></button>
		</div>

		<div class="rest-configuration-info" ><p><?=($arResult['MANIFEST']['EXPORT_ACTION_DESCRIPTION'])?:Loc::getMessage('REST_CONFIGURATION_EXPORT_DESCRIPTION')?></p></div>
	</div>
</div>

<script>
	BX.ready(function () {
		BX.Rest.Configuration.Export.init(<?=Json::encode(
			[
				'id' => $containerId,
				'signedParameters' => $this->getComponent()->getSignedParameters()
			]
		)?>);
		BX.message(
			<?=Json::encode(
				[
					'REST_CONFIGURATION_DOWNLOAD_BTN' => Loc::getMessage('REST_CONFIGURATION_EXPORT_DOWNLOAD_BTN'),
					'REST_CONFIGURATION_FATAL_ERROR' => Loc::getMessage('REST_CONFIGURATION_EXPORT_FATAL_ERROR'),
					'REST_CONFIGURATION_EXPORT_FINISH_DESCRIPTION' => Loc::getMessage(
						'REST_CONFIGURATION_EXPORT_FINISH_DESCRIPTION'
					),
					'REST_CONFIGURATION_EXPORT_INSTALL_PROGRESSBAR_TITLE' => Loc::getMessage(
						'REST_CONFIGURATION_EXPORT_INSTALL_PROGRESSBAR_TITLE'
					),
					'REST_CONFIGURATION_EXPORT_START_DESCRIPTION' => Loc::getMessage(
						'REST_CONFIGURATION_EXPORT_START_DESCRIPTION'
					),
					'REST_CONFIGURATION_EXPORT_FINISH_ERROR_DESCRIPTION' => Loc::getMessage(
						'REST_CONFIGURATION_EXPORT_FINISH_ERROR_DESCRIPTION'
					),
					'REST_CONFIGURATION_EXPORT_ERRORS_REPORT_BTN' => Loc::getMessage(
						'REST_CONFIGURATION_EXPORT_ERRORS_REPORT_BTN'
					),
					'REST_CONFIGURATION_EXPORT_ERRORS_POPUP_TEXT_LABEL' => Loc::getMessage(
						'REST_CONFIGURATION_EXPORT_ERRORS_POPUP_TEXT_LABEL'
					),
					'REST_CONFIGURATION_EXPORT_ERRORS_POPUP_TEXT_PLACEHOLDER' => Loc::getMessage(
						'REST_CONFIGURATION_EXPORT_ERRORS_POPUP_TEXT_PLACEHOLDER'
					),
					'REST_CONFIGURATION_EXPORT_ERRORS_POPUP_BTN_COPY' => Loc::getMessage(
						'REST_CONFIGURATION_EXPORT_ERRORS_POPUP_BTN_COPY'
					),
					'REST_CONFIGURATION_EXPORT_ERRORS_POPUP_TITLE' => Loc::getMessage(
						'REST_CONFIGURATION_EXPORT_ERRORS_POPUP_TITLE'
					),
				]

			);
				?>);
	});
</script>