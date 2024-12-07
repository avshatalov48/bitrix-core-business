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

$containerId = 'bx-sender-call-text-editor';

\Bitrix\Main\UI\Extension::load([
	'ai.picker',
]);
?>
<script>
	BX.ready(function () {
		BX.Sender.Call.TextEditor.init(<?=Json::encode([
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'speedInputName' => $arParams['SPEED_INPUT_NAME'],
			'speechRates' => $arResult['SPEECH_RATES'],
			'speechRateInterval' => $arResult['SPEECH_RATE_INTERVAL'],
			'mess' => [],
			'isAITextAvailable' => $arResult['isAITextAvailable'] ? 'Y' : 'N',
			'AITextContextId' => $arResult['AITextContextId'],
		])?>);
	});
</script>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-call-text-editor-wrap">
	<textarea class="sender-call-text-editor-textarea"
		data-role="input"
		name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>"
		><?=htmlspecialcharsbx($arResult['VALUE'])?></textarea>
	<div class="sender-call-text-editor-count">
		<div class="sender-sms-text-editor-panel-tools">
			<?php if ($arResult['isAITextAvailable']): ?>
				<span class="sender-call-text-editor-panel-tools-item sender-call-text-editor-panel-tools-ai-text" data-bx-call-panel-tools-button="ai-text"></span>
			<?php endif; ?>
		</div>
		<div class="sender-call-text-editor-count-inner">
			<span class="sender-call-text-editor-count-name"><?=Loc::getMessage('SENDER_CALL_TEXT_EDITOR_DURATION')?>: </span>
			<span data-role="counter" class="sender-call-text-editor-count-number"></span>
		</div>
	</div>
</div>
<? if($arResult['TEMPLATE_OPTIONS_SELECTOR']): ?>
	<span data-tag="<?=htmlspecialcharsbx(Json::encode($arResult['TEMPLATE_OPTIONS_SELECTOR']));?>"></span>
<? endif; ?>
