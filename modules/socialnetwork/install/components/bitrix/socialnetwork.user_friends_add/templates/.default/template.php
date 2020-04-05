<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (strlen($arResult["FatalError"])>0)
{
	?>
	<span class='errortext'><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?>
		<span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}

	if ($arResult["ShowForm"] == "Input")
	{
		?>
		<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
			<table class="sonet-message-form data-table" cellspacing="0" cellpadding="0">
				<tr>
					<th colspan="2"><?= GetMessage("SONET_C34_T_PROMT") ?></th>
				</tr>
				<tr>
					<td valign="top" width="10%" nowrap><?= GetMessage("SONET_C34_T_USER") ?>:</td>
					<td valign="top">
						<b><?
						if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"])
							echo "<a href=\"".$arResult["Urls"]["User"]."\">";
						echo $arResult["User"]["NAME_FORMATTED"];
						if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"])
							echo "</a>";
						?></b>
					</td>
				</tr>
				<tr>
					<td valign="top" nowrap><span class="required-field">*</span><?= GetMessage("SONET_C34_T_MESSAGE") ?>:</td>
					<td valign="top"><textarea name="MESSAGE" style="width:98%" rows="5"><?= htmlspecialcharsex($_POST["MESSAGE"]); ?></textarea></td>
				</tr>
			</table>
			<input type="hidden" name="SONET_USER_ID" value="<?= $arResult["User"]["ID"] ?>">
			<?=bitrix_sessid_post()?>
			<br />
			<input type="submit" name="save" value="<?= GetMessage("SONET_C34_T_SAVE") ?>">
		</form>
		<?
	}
	else
	{
		?>
		<?= GetMessage("SONET_C34_T_SUCCESS") ?><br><br>
		<?if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"]):?>
			<a href="<?= $arResult["Urls"]["User"] ?>"><?= $arResult["User"]["NAME_FORMATTED"]; ?></a>
		<?endif;?>
		<?
	}
}
?>