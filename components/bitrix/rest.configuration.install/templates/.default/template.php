<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

Extension::load(
	[
		'ui.buttons',
		'ui.progressbar',
		'ui.notification',
		'ui.common',
		'ui.dialogs.messagebox',
	]
);

Loc::loadMessages(__FILE__);

$messageList = [
	'REST_CONFIGURATION_IMPORT_INSTALL_FATAL_ERROR' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_FATAL_ERROR'),
	'REST_CONFIGURATION_IMPORT_INSTALL_STEP_MSGVER_1' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_STEP_MSGVER_1'),
	'REST_CONFIGURATION_IMPORT_INSTALL_FINISH_TEXT' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_FINISH_TEXT'),
	'REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_BTN_CONTINUE' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_BTN_CONTINUE'),
	'REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_BTN_CANCEL' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_BTN_CANCEL'),
	'REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_TEXT' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_TEXT'),
	'REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_CHECKBOX_LABEL' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_CONFIRM_POPUP_CHECKBOX_LABEL'),
	'REST_CONFIGURATION_IMPORT_INSTALL_LATER_POPUP_CLOSE_BTN' => Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_LATER_POPUP_CLOSE_BTN'),
	'REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TEXT_LABEL' => Loc::getMessage('REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TEXT_LABEL'),
	'REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TEXT_PLACEHOLDER' => Loc::getMessage('REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TEXT_PLACEHOLDER'),
	'REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TITLE' => Loc::getMessage('REST_CONFIGURATION_IMPORT_ERRORS_POPUP_TITLE'),
	'REST_CONFIGURATION_IMPORT_ERRORS_POPUP_BTN_COPY' => Loc::getMessage('REST_CONFIGURATION_IMPORT_ERRORS_POPUP_BTN_COPY'),
	'REST_CONFIGURATION_IMPORT_FINISH_DESCRIPTION_MSGVER_1' => Loc::getMessage('REST_CONFIGURATION_IMPORT_FINISH_DESCRIPTION_MSGVER_1'),
	'REST_CONFIGURATION_IMPORT_FINISH_ERROR_DESCRIPTION' => Loc::getMessage('REST_CONFIGURATION_IMPORT_FINISH_ERROR_DESCRIPTION'),
	'REST_CONFIGURATION_IMPORT_ERRORS_REPORT_BTN' => Loc::getMessage('REST_CONFIGURATION_IMPORT_ERRORS_REPORT_BTN'),
	'REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_TITLE' => Loc::getMessage('REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_TITLE'),
	'REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_DESCRIPTION' => Loc::getMessage('REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_DESCRIPTION'),
	'REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_BTN_CONTINUE' => Loc::getMessage('REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_BTN_CONTINUE'),
	'REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_BTN_CLOSE' => Loc::getMessage('REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_BTN_CLOSE'),
	'REST_CONFIGURATION_IMPORT_PRE_INSTALL_LATER_APP_POPUP_DESCRIPTION' => '',
];

$containerId = 'rest-configuration-import-install';
if ($arResult['PRE_INSTALL_APP_MODE'])
{
	$appName = empty($arParams['APP']['APP_NAME']) ? "" : htmlspecialcharsbx($arParams['APP']['APP_NAME']);
	$description = Loc::getMessage(
		"REST_CONFIGURATION_IMPORT_PRE_INSTALL_APP_DESCRIPTION",
		[
			'#APP_NAME#' => $appName
		]
	);

	$messageList['REST_CONFIGURATION_IMPORT_PRE_INSTALL_LATER_APP_POPUP_DESCRIPTION'] = Loc::getMessage(
		"REST_CONFIGURATION_IMPORT_PRE_INSTALL_LATER_APP_POPUP_DESCRIPTION",
		[
			'#APP_NAME#' => $appName,
			'#HELP_DESK_LINK#' => '<a href="javascript:void(0);" onclick="top.BX.Helper.show(\'redirect=detail&code=11473330\')">'
				.Loc::getMessage("REST_CONFIGURATION_IMPORT_PRE_INSTALL_LATER_APP_POPUP_HELP_DESK_LINK_LABEL")
				.'</a>'
		]
	);
}
elseif (!empty($arResult['MANIFEST']['IMPORT_DESCRIPTION_START']))
{
	$description = $arResult['MANIFEST']['IMPORT_DESCRIPTION_START'];
}
else
{
	$description =
		(!empty($arParams['APP']))
		? 'REST_CONFIGURATION_IMPORT_INSTALL_APP_DESCRIPTION'
		: 'REST_CONFIGURATION_IMPORT_INSTALL_DESCRIPTION'
	;
	if (isset($arParams['MODE']) && $arParams['MODE'])
	{
		$description .= '_' . $arParams['MODE'];
	}
	$description = Loc::getMessage($description);
}

if (isset($arResult['MANIFEST']['IMPORT_INSTALL_FINISH_TEXT']))
{
	$messageList['REST_CONFIGURATION_IMPORT_INSTALL_FINISH_TEXT'] = htmlspecialcharsbx($arResult['MANIFEST']['IMPORT_INSTALL_FINISH_TEXT']);
}
if (isset($arResult['MANIFEST']['IMPORT_FINISH_DESCRIPTION']))
{
	$messageList['REST_CONFIGURATION_IMPORT_FINISH_DESCRIPTION_MSGVER_1'] = htmlspecialcharsbx($arResult['MANIFEST']['IMPORT_FINISH_DESCRIPTION']);
}
if (isset($arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_TITLE']) && $arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_TITLE'])
{
	$messageList['REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_TITLE'] = htmlspecialcharsbx($arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_TITLE']);
}
if (isset($arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_DESCRIPTION']) && $arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_DESCRIPTION'])
{
	$messageList['REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_DESCRIPTION'] = htmlspecialcharsbx($arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_DESCRIPTION']);
}
if (isset($arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_BTN_CONTINUE']) && $arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_BTN_CONTINUE'])
{
	$messageList['REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_BTN_CONTINUE'] = htmlspecialcharsbx($arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_BTN_CONTINUE']);
}
if (isset($arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_BTN_CLOSE']) && $arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_BTN_CLOSE'])
{
	$messageList['REST_CONFIGURATION_IMPORT_HOLD_CLOSE_POPUP_BTN_CLOSE'] = htmlspecialcharsbx($arResult['MANIFEST']['MESSAGE_HOLD_CLOSE_POPUP_BTN_CLOSE']);
}
if (isset($arResult['MANIFEST']['INSTALL_STEP']) && $arResult['MANIFEST']['INSTALL_STEP'])
{
	$messageList['REST_CONFIGURATION_IMPORT_INSTALL_STEP_MSGVER_1'] = htmlspecialcharsbx($arResult['MANIFEST']['INSTALL_STEP']);
}
?>
<? if(isset($arResult['NOTIFY']) && is_array($arResult['NOTIFY'])):?>
	<div class="rest-configuration-alert">
		<? foreach ($arResult['NOTIFY'] as $notify): ?>
			<div class="rest-configuration-alert-text"><?=$notify?></div>
		<? endforeach;?>
	</div>
<? endif?>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="rest-configuration-import-install">
	<div class="rest-configuration-start-icon-main-container">
		<div class="rest-configuration-start-icon-main rest-configuration-start-icon-main-zip">
			<div class="rest-configuration-start-icon-refresh"></div>
			<div class="rest-configuration-start-icon"></div>
			<div class="rest-configuration-start-icon-circle"></div>
		</div>
	</div>
	<div class="rest-configuration-controls start-btn-block">
		<? if($arResult['NEED_START_BTN']): ?>
			<span class="ui-btn ui-btn-lg ui-btn-primary start_btn"><?=Loc::getMessage('REST_CONFIGURATION_IMPORT_INSTALL_START_BTN')?></span>
		<? endif;?>
		<? if($arResult['PRE_INSTALL_APP_MODE']):?>
			<span class="ui-btn ui-btn-lg ui-btn-default start_later_btn"><?=Loc::getMessage("REST_CONFIGURATION_IMPORT_INSTALL_LATER_BTN") ?></span>
		<? endif;?>
	</div>
	<div class="rest-configuration-info"><?=htmlspecialcharsbx($description)?></div>
	<div class="rest-configuration-errors"></div>
	<script>
		BX.ready(function () {
			BX.Rest.Configuration.Install.init(<?=Json::encode([
				'id' => $containerId,
				'signedParameters' => $this->getComponent()->getSignedParameters(),
				'needClearFull' => $arResult['NEED_CLEAR_FULL'],
				'needClearFullConfirm' => $arResult['NEED_CLEAR_FULL_CONFIRM'],
				'importByProcessId' => $arResult['IMPORT_BY_PROCESS_ID'],
				'skipClearing' => $arResult['SKIP_CLEARING'],
				'from' => $arResult['FROM'],
			])?>);
		});
		BX.message(<?=Json::encode($messageList)?>);
	</script>
</div>
