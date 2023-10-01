<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

if (is_array($arParams['SAVE']))
{
	$arParams['SAVE']['NAME'] = !empty($arParams['SAVE']['NAME']) ? $arParams['SAVE']['NAME'] : 'save';
	$arParams['SAVE']['CAPTION'] = !empty($arParams['SAVE']['CAPTION']) ? $arParams['SAVE']['CAPTION'] : Loc::getMessage('SENDER_UI_BUTTON_PANEL_SAVE');
}

if (!empty($arParams['CANCEL']))
{
	$arParams['CANCEL']['CAPTION'] = !empty($arParams['CANCEL']['CAPTION']) ? $arParams['CANCEL']['CAPTION'] : Loc::getMessage('SENDER_UI_BUTTON_PANEL_CANCEL');
}

if (!empty($arParams['CLOSE']))
{
	$arParams['CLOSE']['CAPTION'] = !empty($arParams['CLOSE']['CAPTION']) ? $arParams['CANCEL']['CAPTION'] : Loc::getMessage('SENDER_UI_BUTTON_PANEL_CLOSE');
}

if (!empty($arParams['CHECKBOX']))
{
	$arParams['CHECKBOX']['CHECKED'] = !empty($arParams['CHECKBOX']['CHECKED']) ? $arParams['CHECKBOX']['CHECKED'] : false;
	$arParams['CHECKBOX']['DISPLAY'] = isset($arParams['CHECKBOX']['DISPLAY']) ? $arParams['CHECKBOX']['DISPLAY'] : true;
}

Extension::load("ui.buttons");
Extension::load("ui.buttons.icons");

?>
<div class="sender-footer-buttons sender-footer-buttons-fixed"></div>
<div class="webform-buttons sender-footer-fixed">
	<div class="sender-footer-container">

		<?if(!empty($arParams['CHECKBOX'])):?>
		<div id="sender-ui-button-panel-checkbox" class="sender-add-template-container" style="<?=(!$arParams['CHECKBOX']['DISPLAY'] ? 'display: none;' :'')?>">
			<label class="sender-add-template-label">
				<input
					type="checkbox"
					name="<?=htmlspecialcharsbx($arParams['CHECKBOX']['NAME'])?>"
					value="Y"
					<?=($arParams['CHECKBOX']['CHECKED'] ? 'checked' : '')?>
					class="sender-add-template-checkbox"
				>
				<?=htmlspecialcharsbx($arParams['CHECKBOX']['CAPTION'])?>
			</label>
			<?if(!empty($arParams['CHECKBOX']['HINT'])):?>
				<span data-hint="<?=htmlspecialcharsbx($arParams['CHECKBOX']['HINT'])?>"></span>
			<?endif;?>
		</div>
		<?endif;?>

		<?if(is_array($arParams['SAVE'])):?>
		<button
			id="sender-ui-button-panel-save"
			data-role="panel-button-save"
			name="<?=htmlspecialcharsbx($arParams['SAVE']['NAME'])?>"
			class="ui-btn ui-btn-success"
		>
			<?=htmlspecialcharsbx($arParams['SAVE']['CAPTION'])?>
		</button>
		<?endif;?>

		<?if(!empty($arParams['CANCEL'])):?>
			<a
				id="sender-ui-button-panel-cancel"
				data-role="panel-button-cancel"
				href="<?=htmlspecialcharsbx($arParams['CANCEL']['URL'])?>"
				class="ui-btn ui-btn-link"
			>
				<?=htmlspecialcharsbx($arParams['CANCEL']['CAPTION'])?>
			</a>
		<?endif;?>

		<?if(!empty($arParams['CLOSE'])):?>
			<a
				id="sender-ui-button-panel-cancel"
				data-role="panel-button-cancel"
				href="<?=htmlspecialcharsbx($arParams['CLOSE']['URL'])?>"
				class="ui-btn ui-btn-light-border"
			>
				<?=htmlspecialcharsbx($arParams['CLOSE']['CAPTION'])?>
			</a>
		<?endif;?>
	</div>
</div>