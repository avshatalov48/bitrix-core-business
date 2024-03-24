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

//******************************************
//subscription authorization form
//******************************************
?>
<form action="<?php echo $arResult['FORM_ACTION'] . ($_SERVER['QUERY_STRING'] <> '' ? '?' . htmlspecialcharsbx($_SERVER['QUERY_STRING']) : '')?>" method="post">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?php echo GetMessage('subscr_auth_sect_title')?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p>e-mail<br /><input type="text" name="sf_EMAIL" size="20" value="<?php echo $arResult['REQUEST']['EMAIL'];?>" title="<?php echo GetMessage('subscr_auth_email')?>" /></p>
		<p><?php echo GetMessage('subscr_auth_pass')?><br /><input type="password" name="AUTH_PASS" size="20" value="" title="<?php echo GetMessage('subscr_auth_pass_title')?>" /></p>
	</td>
	<td width="60%">
		<?php echo GetMessage('adm_auth_note')?>
	</td>
</tr>
<tfoot><tr><td colspan="2"><input type="submit" name="autorize" value="<?php echo GetMessage('adm_auth_butt')?>" /></td></tr></tfoot>
</table>
<input type="hidden" name="action" value="authorize" />
<?php echo bitrix_sessid_post();?>
</form>
<br />

<form action="<?=$arResult['FORM_ACTION']?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?php echo GetMessage('subscr_pass_title')?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p>e-mail<br /><input type="text" name="sf_EMAIL" size="20" value="<?php echo $arResult['REQUEST']['EMAIL'];?>" title="<?php echo GetMessage('subscr_auth_email')?>" /></p>
	</td>
	<td width="60%">
		<?php echo GetMessage('subscr_pass_note')?>
	</td>
</tr>
<tfoot><tr><td colspan="2"><input type="submit" name="sendpassword" value="<?php echo GetMessage('subscr_pass_button')?>" /></td></tr></tfoot>
</table>
<input type="hidden" name="action" value="sendpassword" />
<?php echo bitrix_sessid_post();?>
</form>
<br />
