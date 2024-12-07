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

$containerId = 'bx-sender-sms-sender';

\Bitrix\Main\UI\Extension::load('ui.design-tokens');
?>
<script>
	BX.ready(function () {
		BX.Sender.SMS.Sender.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'manageUrl' => $arResult['MANAGE_URL'],
			'senderId' => $arResult['CURRENT']['senderId'],
			'list' => $arResult['LIST'],
			'hasRest' => $arResult['HAS_REST'],
			'mess' => array(
				'marketplaceSendersList' => Loc::getMessage('SENDER_SMS_SENDER_MARKETPLACE_LINK'),
			)
		))?>);
	});
</script>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-sms-container">
	<div class="sender-sms-informer">
		<span class="sender-sms-name"><?=Loc::getMessage('SENDER_SMS_SENDER_TEMPLATE_SERVICE')?></span>
		<a class="sender-sms-link" data-role="sender-selector" data-value="<?=htmlspecialcharsbx($arResult['CURRENT']['senderId'])?>">
			<?=htmlspecialcharsbx($arResult['CURRENT']['shortName'])?>
		</a>
		<span data-role="from-container" style="display: none;">
			<span class="sender-sms-name"><?=Loc::getMessage('SENDER_SMS_SENDER_TEMPLATE_FROM_NUMBER')?></span>
			<a data-role="from-selector" class="sender-sms-link"></a>
		</span>

		<a data-role="setup" class="sender-sms-link" target="_top" style="display: none;"><?=Loc::getMessage('SENDER_SMS_SENDER_TEMPLATE_SETUP')?></a>

		<div data-hint="<?=Loc::getMessage('SENDER_SMS_SENDER_TEMPLATE_HINT')?>" class="sender-sms-informer-hint"></div>
		<input data-role="sender-input" type="hidden" name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>" value="<?=htmlspecialcharsbx($arParams['SENDER'])?>">
	</div>
</div>