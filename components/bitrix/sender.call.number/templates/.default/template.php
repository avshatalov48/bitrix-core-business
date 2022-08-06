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
\Bitrix\Main\UI\Extension::load(['ui.hint', 'ui.design-tokens']);
$containerId = 'bx-sender-call-number';
?>
<script type="text/javascript">
	BX.ready(function () {
		BX.Sender.Call.Number.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'list' => $arResult['LIST'],
			'hasRest' => $arResult['HAS_REST'],
			'mess' => array(
				'marketplaceSendersList' => Loc::getMessage('SENDER_CALL_NUMBER_MARKETPLACE_LINK'),
			)
		))?>);
	});
</script>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-call-number-container">
	<div class="sender-call-number-informer">
		<span class="sender-call-number-name">
			<?=Loc::getMessage('SENDER_CALL_NUMBER_PROVIDER')?>
		</span>
		<a
			data-role="provider-selector"
			data-setup-name="<?=Loc::getMessage('SENDER_CALL_NUMBER_SETUP')?>"
			data-setup-uri="<?=htmlspecialcharsbx($arResult['SETUP_URI'])?>"
			class="sender-call-number-link"
		></a>
		<div data-role="number-selector-block">
			<span class="number-selector-title">
				<?=Loc::getMessage('SENDER_CALL_NUMBER_PHONE')?>
			</span>
			<a data-role="number-selector" class="sender-call-number-link"></a>
		</div>
		<div data-hint="<?=Loc::getMessage('SENDER_CALL_NUMBER_HINT')?>" class="sender-call-number-informer-hint"></div>
		<input data-role="number-input" type="hidden" name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>" value="<?=htmlspecialcharsbx($arResult['VALUE'])?>">
	</div>
</div>