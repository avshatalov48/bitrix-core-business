<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//***********************************
//status and unsubscription/activation section
//***********************************
?>
<form action="<?=$arResult["FORM_ACTION"]?>" method="get">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
	<thead><tr><td colspan="3"><?echo GetMessage("subscr_title_status")?></td></tr></thead>
	<tr valign="top">
		<td nowrap><?echo GetMessage("subscr_conf")?></td>
		<td nowrap class="<?echo ($arResult["SUBSCRIPTION"]["CONFIRMED"] == "Y"? "notetext":"errortext")?>"><?echo ($arResult["SUBSCRIPTION"]["CONFIRMED"] == "Y"? GetMessage("subscr_yes"):GetMessage("subscr_no"));?></td>
		<td width="60%" rowspan="5">
			<?if($arResult["SUBSCRIPTION"]["CONFIRMED"] <> "Y"):?>
				<p><?echo GetMessage("subscr_title_status_note1")?></p>
			<?elseif($arResult["SUBSCRIPTION"]["ACTIVE"] == "Y"):?>
				<p><?echo GetMessage("subscr_title_status_note2")?></p>
				<p><?echo GetMessage("subscr_status_note3")?></p>
			<?else:?>
				<p><?echo GetMessage("subscr_status_note4")?></p>
				<p><?echo GetMessage("subscr_status_note5")?></p>
			<?endif;?>
		</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("subscr_act")?></td>
		<td nowrap class="<?echo ($arResult["SUBSCRIPTION"]["ACTIVE"] == "Y"? "notetext":"errortext")?>"><?echo ($arResult["SUBSCRIPTION"]["ACTIVE"] == "Y"? GetMessage("subscr_yes"):GetMessage("subscr_no"));?></td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("adm_id")?></td>
		<td nowrap><?echo $arResult["SUBSCRIPTION"]["ID"];?>&nbsp;</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("subscr_date_add")?></td>
		<td nowrap><?echo $arResult["SUBSCRIPTION"]["DATE_INSERT"];?>&nbsp;</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("subscr_date_upd")?></td>
		<td nowrap><?echo $arResult["SUBSCRIPTION"]["DATE_UPDATE"];?>&nbsp;</td>
	</tr>
	<?if($arResult["SUBSCRIPTION"]["CONFIRMED"] == "Y"):?>
		<tfoot><tr><td colspan="3">
		<?if($arResult["SUBSCRIPTION"]["ACTIVE"] == "Y"):?>
			<input type="submit" name="unsubscribe" value="<?echo GetMessage("subscr_unsubscr")?>" />
			<input type="hidden" name="action" value="unsubscribe" />
		<?else:?>
			<input type="submit" name="activate" value="<?echo GetMessage("subscr_activate")?>" />
			<input type="hidden" name="action" value="activate" />
		<?endif;?>
		</td></tr></tfoot>
	<?endif;?>
</table>
<input type="hidden" name="ID" value="<?echo $arResult["SUBSCRIPTION"]["ID"];?>" />
<?echo bitrix_sessid_post();?>
</form>
<br />