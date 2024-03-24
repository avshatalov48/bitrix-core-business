<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CUser $USER */
/** @var CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
?>
<div class="subscribe-form"  id="subscribe-form">
<?php
$frame = $this->createFrame('subscribe-form', false)->begin();
?>
	<form action="<?=$arResult['FORM_ACTION']?>">

	<?php foreach ($arResult['RUBRICS'] as $itemValue):?>
		<label for="sf_RUB_ID_<?=$itemValue['ID']?>">
			<input type="checkbox" name="sf_RUB_ID[]" id="sf_RUB_ID_<?=$itemValue['ID']?>" value="<?=$itemValue['ID']?>" <?php echo ($itemValue['CHECKED']) ? 'checked' : '';?> /> <?=$itemValue['NAME']?>
		</label><br />
	<?php endforeach;?>

		<table border="0" cellspacing="0" cellpadding="2" align="center">
			<tr>
				<td><input type="text" name="sf_EMAIL" size="20" value="<?=$arResult['EMAIL']?>" title="<?=GetMessage('subscr_form_email_title')?>" /></td>
			</tr>
			<tr>
				<td align="right"><input type="submit" name="OK" value="<?=GetMessage('subscr_form_button')?>" /></td>
			</tr>
		</table>
	</form>
<?php
$frame->beginStub();
?>
	<form action="<?=$arResult['FORM_ACTION']?>">

		<?php foreach ($arResult['RUBRICS'] as $itemValue):?>
			<label for="sf_RUB_ID_<?=$itemValue['ID']?>">
				<input type="checkbox" name="sf_RUB_ID[]" id="sf_RUB_ID_<?=$itemValue['ID']?>" value="<?=$itemValue['ID']?>" /> <?=$itemValue['NAME']?>
			</label><br />
		<?php endforeach;?>

		<table border="0" cellspacing="0" cellpadding="2" align="center">
			<tr>
				<td><input type="text" name="sf_EMAIL" size="20" value="" title="<?=GetMessage('subscr_form_email_title')?>" /></td>
			</tr>
			<tr>
				<td align="right"><input type="submit" name="OK" value="<?=GetMessage('subscr_form_button')?>" /></td>
			</tr>
		</table>
	</form>
<?php
$frame->end();
?>
</div>
