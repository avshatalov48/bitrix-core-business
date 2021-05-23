<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//******************************************
//subscription authorization form
//******************************************
?>
<form action="<?echo $arResult["FORM_ACTION"].($_SERVER["QUERY_STRING"]<>""? "?".htmlspecialcharsbx($_SERVER["QUERY_STRING"]):"")?>" method="post">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?echo GetMessage("subscr_auth_sect_title")?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p>e-mail<br /><input type="text" name="sf_EMAIL" size="20" value="<?echo $arResult["REQUEST"]["EMAIL"];?>" title="<?echo GetMessage("subscr_auth_email")?>" /></p>
		<p><?echo GetMessage("subscr_auth_pass")?><br /><input type="password" name="AUTH_PASS" size="20" value="" title="<?echo GetMessage("subscr_auth_pass_title")?>" /></p>
	</td>
	<td width="60%">
		<?echo GetMessage("adm_auth_note")?>
	</td>
</tr>
<tfoot><tr><td colspan="2"><input type="submit" name="autorize" value="<?echo GetMessage("adm_auth_butt")?>" /></td></tr></tfoot>
</table>
<input type="hidden" name="action" value="authorize" />
<?echo bitrix_sessid_post();?>
</form>
<br />

<form action="<?=$arResult["FORM_ACTION"]?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?echo GetMessage("subscr_pass_title")?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p>e-mail<br /><input type="text" name="sf_EMAIL" size="20" value="<?echo $arResult["REQUEST"]["EMAIL"];?>" title="<?echo GetMessage("subscr_auth_email")?>" /></p>
	</td>
	<td width="60%">
		<?echo GetMessage("subscr_pass_note")?>
	</td>
</tr>
<tfoot><tr><td colspan="2"><input type="submit" name="sendpassword" value="<?echo GetMessage("subscr_pass_button")?>" /></td></tr></tfoot>
</table>
<input type="hidden" name="action" value="sendpassword" />
<?echo bitrix_sessid_post();?>
</form>
<br />
