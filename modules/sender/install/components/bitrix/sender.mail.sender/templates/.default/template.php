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

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

$containerId = 'sender-ui-mailbox-selector';
?>
<script type="text/javascript">
	BX.ready(function () {
		BX.Sender.UI.Mailbox.Selector.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'list' => $arResult['LIST'],
			'mess' => array(
				'addAddress' => Loc::getMessage('SENDER_UI_MAILBOX_SELECTOR_ADD')
			)
		))?>);
	});
</script>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-ui-mailbox-selector-wrap">
	<span class="sender-ui-mailbox-icon" style="background-image: url(<?=htmlspecialcharsbx($arResult['CURRENT']['icon'])?>)"></span>
	<span data-role="mailbox" class="sender-ui-mailbox-name">
		<?=htmlspecialcharsbx($arResult['CURRENT']['name'])?>
		<?=htmlspecialcharsbx('<' . $arResult['CURRENT']['email'] . '>')?>
	</span>
	<input data-role="mailbox-input" type="hidden" name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>" value="<?=htmlspecialcharsbx($arResult['CURRENT']['sender'])?>">
</div>