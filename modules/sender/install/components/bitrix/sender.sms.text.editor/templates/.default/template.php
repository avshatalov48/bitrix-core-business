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

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

$containerId = 'bx-sender-sms-text-editor';

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ai.picker',
]);

?>
<script>
	BX.ready(function () {
		BX.Sender.SMS.TextEditor.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'mess' => array(),
			'isAITextAvailable' => $arResult['isAITextAvailable'] ? 'Y' : 'N',
			'AITextContextId' => $arResult['AITextContextId'],
		))?>);
	});
</script>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-sms-text-editor-wrap">
	<textarea class="sender-sms-text-editor-textarea"
		data-role="input"
		name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>"
		><?=htmlspecialcharsbx($arResult['VALUE'])?></textarea>
	<div class="sender-sms-text-editor-count">
		<div class="sender-sms-text-editor-panel-tools">
			<?php if ($arResult['isAITextAvailable']): ?>
				<span class="sender-sms-text-editor-panel-tools-item sender-sms-text-editor-panel-tools-ai-text" data-bx-sms-panel-tools-button="ai-text"></span>
			<?php endif; ?>
		</div>
		<div class="sender-sms-text-editor-count-inner">
			<span class="sender-sms-text-editor-count-name"><?=Loc::getMessage('SENDER_SMS_TEXT_EDITOR_COUNT_SYMBOLS')?></span>
			<span data-role="counter" class="sender-sms-text-editor-count-number"><?=$arResult['COUNT']?></span>
			<span class="sender-sms-text-editor-count-name"><?=Loc::getMessage('SENDER_SMS_TEXT_EDITOR_FROM')?></span>
			<span data-role="num" class="sender-sms-text-editor-count-number">140</span>
			<span class="sender-sms-text-editor-count-name">, </span>
			<span data-role="sms" class="sender-sms-text-editor-count-number">1</span>
			<span class="sender-sms-text-editor-count-name"><?=Loc::getMessage('SENDER_SMS_TEXT_EDITOR_SMS')?></span>
		</div>
	</div>
</div>
<? if($arResult['TEMPLATE_OPTIONS_SELECTOR']): ?>
	<span data-tag="<?=htmlspecialcharsbx(Json::encode($arResult['TEMPLATE_OPTIONS_SELECTOR']));?>"></span>
<? endif; ?>
