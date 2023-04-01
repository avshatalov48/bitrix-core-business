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

$containerId = 'sender-im-message-editor';

\Bitrix\Main\UI\Extension::load(['ui.design-tokens', 'ui.fonts.opensans']);
?>
<script type="text/javascript">
	BX.ready(function () {
		BX.Sender.Im.Message.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'mess' => array()
		))?>);
	});
</script>
<div class="bx-im-sender-value">
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-im-message-wrap">
	<div class="sender-im-message-title"><?=Loc::getMessage('SENDER_IM_MESSAGE_TEXT_TITLE')?></div>
	<div class="sender-im-message-text">
		<textarea class="sender-im-message-textarea"
			data-role="input"
			name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>"
			><?=htmlspecialcharsbx($arResult['VALUE'])?></textarea>
		<div class="sender-im-message-count">
			<div class="sender-im-message-count-inner">
				<span class="sender-im-message-count-name"><?=Loc::getMessage('SENDER_IM_MESSAGE_TEXT_COUNT')?></span>
				<span data-role="counter" class="sender-im-message-count-number"></span>
			</div>
		</div>
	</div>
</div>
<? if($arResult['TEMPLATE_OPTIONS_SELECTOR']): ?>
	<span data-tag="<?=htmlspecialcharsbx(Json::encode($arResult['TEMPLATE_OPTIONS_SELECTOR']));?>"></span>
<? endif; ?>
</div>
