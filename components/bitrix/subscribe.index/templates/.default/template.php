<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<div class="subscribe-index">

<h4><?echo GetMessage("SUBSCR_NEW_TITLE")?></h4>
<p><?echo GetMessage("SUBSCR_NEW_NOTE")?></p>
<form action="<?=$arResult["FORM_ACTION"]?>" method="get">
	<table class="data-table" border="0" cellpadding="0" cellspacing="0">
		<thead>
		<tr>
			<td>&nbsp;</td>
			<td><?echo GetMessage("SUBSCR_NAME")?></td>
			<td><?echo GetMessage("SUBSCR_DESC")?></td>
			<?if($arResult["SHOW_COUNT"]):?>
				<td><?echo GetMessage("SUBSCR_CNT")?></td>
			<?endif;?>
		</tr>
		</thead>
		<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
		<tr>
			<td><input type="checkbox" name="sf_RUB_ID[]" id="sf_RUB_ID_<?=$itemID?>" value="<?=$itemValue["ID"]?>" checked /></td>
			<td><label for="sf_RUB_ID_<?=$itemID?>"><?=$itemValue["NAME"]?></label></td>
			<td><?=$itemValue["DESCRIPTION"]?></td>
			<?if($arResult["SHOW_COUNT"]):?>
				<td align="right"><?=$itemValue["SUBSCRIBER_COUNT"]?></td>
			<?endif?>
		</tr>
		<?endforeach;?>
	</table>
	<p><?echo GetMessage("SUBSCR_ADDR")?>&nbsp;<input type="text" name="sf_EMAIL" size="20" value="<?=$arResult["EMAIL"]?>" title="<?echo GetMessage("SUBSCR_EMAIL_TITLE")?>" /><input type="submit" value="<?echo GetMessage("SUBSCR_BUTTON")?>" /></p>
</form>
<br />

<form action="<?=$arResult["FORM_ACTION"]?>" method="get">
<?echo bitrix_sessid_post();?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?echo GetMessage("SUBSCR_EDIT_TITLE")?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p>e-mail<br />
		<input type="text" name="sf_EMAIL" size="20" value="<?=$arResult["EMAIL"]?>" title="<?echo GetMessage("SUBSCR_EMAIL_TITLE")?>" /></p>
		<?if($arResult["SHOW_PASS"]=="Y"):?>
			<p><?echo GetMessage("SUBSCR_EDIT_PASS")?><span class="starrequired">*</span><br />
			<input type="password" name="AUTH_PASS" size="20" value="" title="<?echo GetMessage("SUBSCR_EDIT_PASS_TITLE")?>" /></p>
		<?else:?>
			<p><span class="green"><?echo GetMessage("SUBSCR_EDIT_PASS_ENTERED")?></span><span class="starrequired">*</span></p>
		<?endif;?>
	<td width="60%">
		<p><?echo GetMessage("SUBSCR_EDIT_NOTE")?></p>
	</td>
</tr>
<tfoot><tr><td colspan="2">
	<input type="submit" value="<?echo GetMessage("SUBSCR_EDIT_BUTTON")?>" />
</td></tr></tfoot>
</table>
<input type="hidden" name="action" value="authorize" />
</form>
<br />

<form action="<?=$arResult["FORM_ACTION"]?>" method="get">
<?echo bitrix_sessid_post();?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?echo GetMessage("SUBSCR_PASS_TITLE")?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p>e-mail<br />
		<input type="text" name="sf_EMAIL" size="20" value="<?=$arResult["EMAIL"]?>" title="<?echo GetMessage("SUBSCR_EMAIL_TITLE")?>" /></p>
	<td width="60%">
		<p><?echo GetMessage("SUBSCR_PASS_NOTE")?></p>
	</td>
</tr>
<tfoot><tr><td colspan="2">
	<input type="submit" value="<?echo GetMessage("SUBSCR_PASS_BUTTON")?>" />
</td></tr></tfoot>
</table>
<input type="hidden" name="action" value="sendpassword" />
</form>
<br />

<form action="<?=$arResult["FORM_ACTION"]?>" method="get">
<?echo bitrix_sessid_post();?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?echo GetMessage("SUBSCR_UNSUBSCRIBE_TITLE")?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p>e-mail<br />
		<input type="text" name="sf_EMAIL" size="20" value="<?=$arResult["EMAIL"]?>" title="<?echo GetMessage("SUBSCR_EMAIL_TITLE")?>" /></p>
		<?if($arResult["SHOW_PASS"]=="Y"):?>
			<p><?echo GetMessage("SUBSCR_EDIT_PASS")?><span class="starrequired">*</span><br />
			<input type="password" name="AUTH_PASS" size="20" value="" title="<?echo GetMessage("SUBSCR_EDIT_PASS_TITLE")?>" /></p>
		<?else:?>
			<p><span class="green"><?echo GetMessage("SUBSCR_EDIT_PASS_ENTERED")?></span><span class="starrequired">*</span></p>
		<?endif;?>
	<td width="60%">
		<p><?echo GetMessage("SUBSCR_UNSUBSCRIBE_NOTE")?></p>
	</td>
</tr>
<tfoot><tr><td colspan="2">
	<input type="submit" value="<?echo GetMessage("SUBSCR_EDIT_BUTTON")?>" />
</td></tr></tfoot>
</table>
<input type="hidden" name="action" value="authorize" />
</form>
<br />

<p><span class="starrequired">*&nbsp;</span><?echo GetMessage("SUBSCR_NOTE")?></p>

</div>