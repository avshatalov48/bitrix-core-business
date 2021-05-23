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
?>
<script type="text/javascript">
	BX.ready(function () {
		BX.Sender.Call.TextEditor.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'speedInputName' => $arParams['SPEED_INPUT_NAME'],
			'speechRates' => $arResult['SPEECH_RATES'],
			'speechRateInterval' => $arResult['SPEECH_RATE_INTERVAL'],
			'mess' => array()
		))?>);
	});
</script>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-call-text-editor-wrap">
	<textarea class="sender-call-text-editor-textarea"
		data-role="input"
		name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>"
		><?=htmlspecialcharsbx($arResult['VALUE'])?></textarea>
	<div class="sender-call-text-editor-count">
		<div class="sender-call-text-editor-count-inner">
			<span class="sender-call-text-editor-count-name"><?=Loc::getMessage('SENDER_CALL_TEXT_EDITOR_DURATION')?>: </span>
			<span data-role="counter" class="sender-call-text-editor-count-number"></span>
		</div>
	</div>
</div>