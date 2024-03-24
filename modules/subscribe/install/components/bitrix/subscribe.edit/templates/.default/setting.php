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

//***********************************
//setting section
//***********************************
?>
<form action="<?=$arResult['FORM_ACTION']?>" method="post">
<?php echo bitrix_sessid_post();?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?php echo GetMessage('subscr_title_settings')?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p><?php echo GetMessage('subscr_email')?><span class="starrequired">*</span><br />
		<input type="text" name="EMAIL" value="<?=$arResult['SUBSCRIPTION']['EMAIL'] != '' ? $arResult['SUBSCRIPTION']['EMAIL'] : $arResult['REQUEST']['EMAIL'];?>" size="30" maxlength="255" /></p>
		<p><?php echo GetMessage('subscr_rub')?><span class="starrequired">*</span><br />
		<?php foreach ($arResult['RUBRICS'] as $itemValue):?>
			<label><input type="checkbox" name="RUB_ID[]" value="<?=$itemValue['ID']?>" <?php echo ($itemValue['CHECKED']) ? 'checked' : '';?> /><?=$itemValue['NAME']?></label><br />
		<?php endforeach;?></p>
		<p><?php echo GetMessage('subscr_fmt')?><br />
		<label><input type="radio" name="FORMAT" value="text" <?php echo ($arResult['SUBSCRIPTION']['FORMAT'] == 'text') ? 'checked' : '';?> /><?php echo GetMessage('subscr_text')?></label>&nbsp;/&nbsp;<label><input type="radio" name="FORMAT" value="html" <?php echo ($arResult['SUBSCRIPTION']['FORMAT'] == 'html') ? 'checked' : '';?> />HTML</label></p>
	</td>
	<td width="60%">
		<p><?php echo GetMessage('subscr_settings_note1')?></p>
		<p><?php echo GetMessage('subscr_settings_note2')?></p>
	</td>
</tr>
<tfoot><tr><td colspan="2">
	<input type="submit" name="Save" value="<?php echo ($arResult['ID'] > 0 ? GetMessage('subscr_upd') : GetMessage('subscr_add'))?>" />
	<input type="reset" value="<?php echo GetMessage('subscr_reset')?>" name="reset" />
</td></tr></tfoot>
</table>
<input type="hidden" name="PostAction" value="<?php echo ($arResult['ID'] > 0 ? 'Update' : 'Add')?>" />
<input type="hidden" name="ID" value="<?php echo $arResult['SUBSCRIPTION']['ID'];?>" />
<?php if ($_REQUEST['register'] == 'YES'):?>
	<input type="hidden" name="register" value="YES" />
<?php endif;?>
<?php if ($_REQUEST['authorize'] == 'YES'):?>
	<input type="hidden" name="authorize" value="YES" />
<?php endif;?>
</form>
<br />
