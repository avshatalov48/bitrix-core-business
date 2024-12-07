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

$containerId = 'bx-sender-mail-link-editor';
?>
<script>
	BX.ready(function () {
		BX.Sender.Mail.LinkEditor.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUrl' => $arResult['ACTION_URL'],
			'defaultValue' => $arResult['DEFAULT_VALUE'],
			'placeholders' => $arParams['PLACEHOLDERS'],
			'useDefault' => $arParams['USE_DEFAULT'],
			'mess' => array(
				'title' => Loc::getMessage('SENDER_MAIL_LINK_EDITOR_TITLE'),
				'accept' => Loc::getMessage('SENDER_MAIL_LINK_EDITOR_ACCEPT'),
				'cancel' => Loc::getMessage('SENDER_MAIL_LINK_EDITOR_CANCEL'),
			)
		))?>);
	});
</script>
<span id="<?=htmlspecialcharsbx($containerId)?>" class="sender-mail-link-editor-wrap">
	<input class="bx-sender-form-control bx-sender-message-editor-field-input"
		data-role="input" type="text"
		name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>"
		value="<?=htmlspecialcharsbx($arResult['VALUE'])?>"
	>
	<span data-role="button" class="sender-mail-text-editor-utm"><?=Loc::getMessage('SENDER_MAIL_LINK_EDITOR_TITLE')?></span>
	<div data-role="popup-content" class="sender-popup-container" style="display: none;">
		<?
		$list = array(
			'UTM_SOURCE',
			'UTM_MEDIUM',
			'UTM_CAMPAIGN',
			'UTM_TERM',
			'UTM_CONTENT',
		);
		foreach ($list as $name):
			$nameLower = mb_strtolower($name);
		?>
			<div class="sender-popup-editor-row">
				<div class="sender-popup-editor-caption">
					<?=Loc::getMessage('SENDER_MAIL_LINK_EDITOR_' . $name)?> - <?=$nameLower?>
					<div class="sender-popup-editor-hint" data-hint="<?=Loc::getMessage('SENDER_MAIL_LINK_EDITOR_DESC_' . $name)?>"></div>
				</div>
				<div class="sender-popup-editor-input-box">
					<input class="sender-popup-editor-input" data-role="<?=$nameLower?>" type="text" style="width: 100%;">
				</div>
		</div>
		<?endforeach;?>
	</div>
</span>