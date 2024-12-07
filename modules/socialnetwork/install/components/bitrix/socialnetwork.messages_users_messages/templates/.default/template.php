<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (!empty($arResult["FatalError"]))
{
	?>
	<span class='errortext'><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	if(!empty($arResult["ErrorMessage"]))
	{
		?>
		<span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}
	?>

	<table width="100%" cellspacing="0" cellpadding="8" border="0">
	<tr>
		<td valign="top" width="35%">
			<?=$arResult["User"]["PERSONAL_PHOTO_IMG"]?>
		</td>
		<td valign="top" width="65%">
			<h4><?=$arResult["User"]["NAME_FORMATTED"]?></h4>
			<?if ($arResult["IS_ONLINE"]):?>
				<span class="sonet_online"><?= GetMessage("SONET_C31_T_ONLINE") ?></span><br /><br />
			<?endif;?>
			<?if ($arResult["CanViewProfile"]):?>
				<a href="<?= $arResult["Urls"]["User"] ?>"><?= GetMessage("SONET_C31_T_PROFILE") ?></a><br><br>
			<?endif;?>
			<?if ($arResult["CanMessage"]):?>
				<a href="<?= $arResult["Urls"]["Chat"] ?>" onclick="if (typeof(BX) != 'undefined' && BX.IM) { BXIM.openMessenger(<?=$arUser['ID']?>); return false; } else { window.open('<?= $arResult["Urls"]["Chat"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5)); return false; }"><?= GetMessage("SONET_C31_T_WRITE_MESSAGE") ?></a><br><br>
			<?endif;?>
			<?if ($arResult["ShowBanLink"]):?>
				<a href="<?= $arResult["Urls"]["BanLink"] ?>"><?= GetMessage("SONET_C31_T_BAN") ?></a>
			<?endif;?>
		</td>
	</tr>
	</table>

	<script>
	<!--
	function SelectAllRows(checkbox)
	{
		var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
		var bChecked = checkbox.checked;
		var i;
		var n = tbl.rows.length;
		for (i = 1; i < n; i++)
		{
			var j;
			var m = tbl.rows[i].cells[0].childNodes.length;
			for (j = 0; j < m; j++)
			{
				var box = tbl.rows[i].cells[0].childNodes[j];
				if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
				{
					if (box.checked != bChecked && !box.disabled)
						box.checked = bChecked;
					break;
				}
			}
		}
	}
	//-->
	</script>

	<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
	<input type="hidden" name="do_delete_all_flag" value="">
	<?if ($arResult["NAV_STRING"] <> ''):?>
		<?=$arResult["NAV_STRING"]?><br /><br />
	<?endif;?>
	<div class="sonet-cntnr-messages-users-messages">
	<table width="100%" class="sonet-user-profile-friends data-table">
		<tr>
			<th width="0%"><input type="checkbox" id="check_all" value="" title="<?= GetMessage("SONET_C31_T_SELECT_ALL") ?>" onclick="SelectAllRows(this);"></th>
			<th width="100%"><?= GetMessage("SONET_C31_T_MESSAGE") ?></th>
			<th width="0%"><?= GetMessage("SONET_C31_T_ACTION") ?></th>
		</tr>
		<?$ind = 0;?>
		<?if ($arResult["Events"]):?>
			<?foreach ($arResult["Events"] as $event):?>
				<tr>
					<td valign="top" align="center"<?= (!$event["IS_READ"] ? " class=\"selected\"" : "") ?> width="0%">
						<input type="checkbox" name="checked_<?= $ind ?>" value="Y">
						<input type="hidden" name="id_<?= $ind ?>" value="<?= $event["ID"] ?>">
					</td>
					<td valign="top"<?= (!$event["IS_READ"] ? " class=\"selected\"" : "") ?> width="100%">
						<b><?= (($event["WHO"] == "OUT") ? GetMessage("SONET_C31_T_ME_LABEL") : $arResult["User"]["NAME_FORMATTED"]); ?></b>:
						<?= $event["MESSAGE"]; ?><br><br>
						<i><?= $event["DATE_CREATE_FORMAT"]; ?></i><br>
					</td>
					<td valign="top"<?= (!$event["IS_READ"] ? " class=\"selected\"" : "") ?> width="0%" nowrap>
						<?if (!$event["IS_READ"]):?>
							<a href="<?= $event["READ_LINK"] ?>"><?= GetMessage("SONET_C31_T_ACT_READ") ?></a><br><br>
						<?endif;?>
						<a href="<?= $event["DELETE_LINK"] ?>"><?= GetMessage("SONET_C31_T_ACT_DEL") ?></a><br><br>
					</td>
				</tr>
				<?$ind++;?>
			<?endforeach;?>
		<?else:?>
			<tr>
				<td colspan="4"><?= GetMessage("SONET_C31_T_EMPTY") ?></td>
			</tr>
		<?endif;?>
	</table>
	</div>	
	<?if ($arResult["NAV_STRING"] <> ''):?>
		<?=$arResult["NAV_STRING"]?>
		<br /><br />
	<?endif;?>
		<input type="hidden" name="max_count" value="<?= $ind ?>">
		<?=bitrix_sessid_post()?>
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td width="100%" align="left">
			<input type="submit" name="do_read" value="<?= GetMessage("SONET_C31_T_DO_READ") ?>">
			<input type="submit" name="do_delete" value="<?= GetMessage("SONET_C31_T_DO_DEL") ?>">
			</td>
			<td width="0%" align="right">
			<input type="button" name="do_delete_all" value="<?= GetMessage("SONET_C31_T_DO_DEL_ALL") ?>" onClick="javascript:if(confirm('<?= GetMessage("SONET_C31_T_DO_DEL_ALL_CONFIRM") ?>')) { document.form1.do_delete_all_flag.value = 'Y';  document.form1.submit(); }">
			</td>
		</tr>
		</table>		
	</form>
	<?
}
?>