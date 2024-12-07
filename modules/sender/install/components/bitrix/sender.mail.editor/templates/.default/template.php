<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */


$fieldName = htmlspecialcharsbx($arParams['INPUT_NAME']);
$fieldValue = htmlspecialcharsbx($arParams['~VALUE']);

$isBlock = $arResult['DISPLAY_BLOCK_EDITOR'];
$containerId = 'bx-sender-message-editor-mail-' . $fieldName;

\Bitrix\Main\Loader::includeModule('ai');
Extension::load([
	'sender.personalization_selector',
	'ai.picker',
]);

?>
<script>
	BX.ready(function () {
		BX.Sender.Mail.Editor.init(<?=Json::encode(array(
			'id' => $arParams['INPUT_NAME'],
			'containerId' => $containerId,
			'inputId' => $arResult['INPUT_ID'],
			'placeHolders' => $arResult['PERSONALIZE_LIST'],
			'mess' => array(
				'placeHolderTitle' => Loc::getMessage('SENDER_COMP_EDITOR_MAIL_PERS_LIST'),
				'changeTemplate' => Loc::getMessage('SENDER_COMP_EDITOR_MAIL_CHANGE_TEMPLATE'),
			),
			'isAITextAvailable' => $arResult['isAITextAvailable'] ? 'Y' : 'N',
			'isAIImageAvailable' => $arResult['isAIImageAvailable'] ? 'Y' : 'N',
			'AITextContextId' => $arResult['AITextContextId'],
			'AIImageContextId' => $arResult['AIImageContextId'],
		))?>);
	});
</script>

<div id="<?=$containerId?>" class="sender-message-editor-mail-wrapper">
	<div data-bx-editor-plain="" style="<?=($isBlock ? 'display: none;' : '')?>">
		<textarea data-bx-input="" id="bxed_<?=$fieldName?>" name="<?=$fieldName?>"
			style="height: 320px; width: 100%;" class="typearea"
		><?=$fieldValue?></textarea>
	</div>

	<div data-bx-editor-block="" style="<?=(!$isBlock ? 'display: none;' : '')?>">
		<?=$arResult['~BLOCK_EDITOR'];?>
	</div>
</div>
