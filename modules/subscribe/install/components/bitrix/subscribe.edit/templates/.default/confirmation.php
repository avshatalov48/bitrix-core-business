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

//*************************************
//show confirmation form
//*************************************
?>
<form action="<?=$arResult['FORM_ACTION']?>" method="get">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?php echo GetMessage('subscr_title_confirm')?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p><?php echo GetMessage('subscr_conf_code')?><span class="starrequired">*</span><br />
		<input type="text" name="CONFIRM_CODE" value="<?php echo $arResult['REQUEST']['CONFIRM_CODE'];?>" size="20" /></p>
		<p><?php echo GetMessage('subscr_conf_date')?></p>
		<p><?php echo $arResult['SUBSCRIPTION']['DATE_CONFIRM'];?></p>
	</td>
	<td width="60%">
		<?php echo GetMessage('subscr_conf_note1')?> <a title="<?php echo GetMessage('adm_send_code')?>" href="<?php echo $arResult['FORM_ACTION']?>?ID=<?php echo $arResult['ID']?>&amp;action=sendcode&amp;<?php echo bitrix_sessid_get()?>"><?php echo GetMessage('subscr_conf_note2')?></a>.
	</td>
</tr>
<tfoot><tr><td colspan="2"><input type="submit" name="confirm" value="<?php echo GetMessage('subscr_conf_button')?>" /></td></tr></tfoot>
</table>
<input type="hidden" name="ID" value="<?php echo $arResult['ID'];?>" />
<?php echo bitrix_sessid_post();?>
</form>
<br />
