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

$containerId = 'bx-sender-call-number';
?>
<script type="text/javascript">
	BX.ready(function () {
		BX.Sender.Call.Number.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'list' => $arResult['LIST'],
			'mess' => array()
		))?>);
	});
</script>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-call-number-container">
	<div class="sender-call-number-informer">
		<span class="sender-call-number-name">
			<?=Loc::getMessage('SENDER_CALL_NUMBER_SELECT')?>
		</span>
		<a
			data-role="number-selector"
			data-setup-name="<?=Loc::getMessage('SENDER_CALL_NUMBER_SETUP')?>"
			data-setup-uri="<?=htmlspecialcharsbx($arResult['SETUP_URI'])?>"
			class="sender-call-number-link"
		></a>

		<div data-hint="<?=Loc::getMessage('SENDER_CALL_NUMBER_HINT')?>" class="sender-call-number-informer-hint"></div>
		<input data-role="number-input" type="hidden" name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>" value="<?=htmlspecialcharsbx($arResult['VALUE'])?>">
	</div>
</div>