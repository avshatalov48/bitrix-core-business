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

foreach ($arResult['MESSAGE'] as $itemValue)
{
	echo ShowMessage(['MESSAGE' => $itemValue, 'TYPE' => 'OK']);
}
foreach ($arResult['ERROR'] as $itemValue)
{
	echo ShowMessage(['MESSAGE' => $itemValue, 'TYPE' => 'ERROR']);
}

if ($arResult['ALLOW_ANONYMOUS'] == 'N' && !$USER->IsAuthorized()):
	echo ShowMessage(['MESSAGE' => GetMessage('CT_BSE_AUTH_ERR'), 'TYPE' => 'ERROR']);
else:
?>
<div class="subscription">
	<form action="<?=$arResult['FORM_ACTION']?>" method="post">
	<?php echo bitrix_sessid_post();?>
	<input type="hidden" name="PostAction" value="<?php echo ($arResult['ID'] > 0 ? 'Update' : 'Add')?>" />
	<input type="hidden" name="ID" value="<?php echo $arResult['SUBSCRIPTION']['ID'];?>" />
	<input type="hidden" name="RUB_ID[]" value="0" />

	<div class="subscription-title">
		<b class="r2"></b><b class="r1"></b><b class="r0"></b>
		<div class="subscription-title-inner"><?php echo GetMessage('CT_BSE_SUBSCRIPTION_FORM_TITLE')?></div>
	</div>

	<div class="subscription-form">

		<table cellspacing="0" class="subscription-layout">
			<tr>
				<td class="field-name"><?php echo GetMessage('CT_BSE_EMAIL_LABEL')?></td>
				<td class="field-form">
					<div class="subscription-format"><span><?php echo GetMessage('CT_BSE_FORMAT_LABEL')?></span>&nbsp;<input type="radio" name="FORMAT" id="MAIL_TYPE_TEXT" value="text" <?php echo ($arResult['SUBSCRIPTION']['FORMAT'] != 'html') ? 'checked' : '';?> /><label for="MAIL_TYPE_TEXT"><?php echo GetMessage('CT_BSE_FORMAT_TEXT')?></label>&nbsp;<input type="radio" name="FORMAT" id="MAIL_TYPE_HTML" value="html" <?php echo ($arResult['SUBSCRIPTION']['FORMAT'] == 'html') ? 'checked' : '';?> /><label for="MAIL_TYPE_HTML"><?php echo GetMessage('CT_BSE_FORMAT_HTML')?></label></div>
					<input type="text" name="EMAIL" value="<?php echo $arResult['SUBSCRIPTION']['EMAIL'] != '' ? $arResult['SUBSCRIPTION']['EMAIL'] : $arResult['REQUEST']['EMAIL'];?>" class="subscription-email" />
					</td>
			</tr>
			<tr>
				<td class="field-name"><?php echo GetMessage('CT_BSE_RUBRIC_LABEL')?></td>
				<td class="field-form">
					<?php foreach ($arResult['RUBRICS'] as $itemID => $itemValue):?>
						<div class="subscription-rubric">
							<input type="checkbox" id="RUBRIC_<?php echo $itemID?>" name="RUB_ID[]" value="<?=$itemValue['ID']?>" <?php echo ($itemValue['CHECKED']) ? 'checked' : '';?> /><label for="RUBRIC_<?php echo $itemID?>"><b><?php echo $itemValue['NAME']?></b><span><?php echo $itemValue['DESCRIPTION']?></span></label>
						</div>
					<?php endforeach;?>

					<?php if ($arResult['ID'] == 0):?>
						<div class="subscription-notes"><?php echo GetMessage('CT_BSE_NEW_NOTE')?></div>
					<?php else:?>
						<div class="subscription-notes"><?php echo GetMessage('CT_BSE_EXIST_NOTE')?></div>
					<?php endif?>

					<div class="subscription-buttons"><input type="submit" name="Save" value="<?php echo ($arResult['ID'] > 0 ? GetMessage('CT_BSE_BTN_EDIT_SUBSCRIPTION') : GetMessage('CT_BSE_BTN_ADD_SUBSCRIPTION'))?>" /></div>
				</td>
			</tr>
		</table>
	</div>

	<?php if ($arResult['ID'] > 0 && $arResult['SUBSCRIPTION']['CONFIRMED'] <> 'Y'):?>
	<div class="subscription-utility">
		<p><?php echo GetMessage('CT_BSE_CONF_NOTE')?></p>
		<input name="CONFIRM_CODE" type="text" class="subscription-textbox" value="<?php echo GetMessage('CT_BSE_CONFIRMATION')?>" onblur="if (this.value=='')this.value='<?php echo GetMessage('CT_BSE_CONFIRMATION')?>'" onclick="if (this.value=='<?php echo GetMessage('CT_BSE_CONFIRMATION')?>')this.value=''" /> <input type="submit" name="confirm" value="<?php echo GetMessage('CT_BSE_BTN_CONF')?>" />
	</div>
	<?php endif?>

	</form>

	<?php if (!CSubscription::IsAuthorized($arResult['ID'])):?>
	<form action="<?=$arResult['FORM_ACTION']?>" method="post">
	<?php echo bitrix_sessid_post();?>
	<input type="hidden" name="action" value="sendcode" />

	<div class="subscription-utility">
		<p><?php echo GetMessage('CT_BSE_SEND_NOTE')?></p>
		<input name="sf_EMAIL" type="text" class="subscription-textbox" value="<?php echo GetMessage('CT_BSE_EMAIL')?>" onblur="if (this.value=='')this.value='<?php echo GetMessage('CT_BSE_EMAIL')?>'" onclick="if (this.value=='<?php echo GetMessage('CT_BSE_EMAIL')?>')this.value=''" /> <input type="submit" value="<?php echo GetMessage('CT_BSE_BTN_SEND')?>" />
	</div>
	</form>
	<?php endif?>

</div>
<?php endif;
