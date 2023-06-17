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

	if ($arResult["ShowForm"] == "Input")
	{
		?>
		<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
			<table class="sonet-message-form data-table" cellspacing="0" cellpadding="0">
				<tr>
					<th colspan="2"><?= GetMessage("SONET_C35_T_PROMT") ?></th>
				</tr>
				<tr>
					<td valign="top" width="10%" nowrap><?= GetMessage("SONET_C35_T_USER") ?>:</td>
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
			</table>
			<input type="hidden" name="SONET_USER_ID" value="<?= $arResult["User"]["ID"] ?>">
			<?=bitrix_sessid_post()?>
			<br />
			<input type="submit" name="save" value="<?= GetMessage("SONET_C35_T_SAVE") ?>">
			<input type="reset" name="cancel" value="<?= GetMessage("SONET_C35_T_CANCEL") ?>" OnClick="window.location='<?= $arResult["Urls"]["User"] ?>'">
		</form>
		<?
	}
	else
	{
		?>
		<?= GetMessage("SONET_C35_T_SUCESS") ?><br><br>
		<?if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"]):?>
			<a href="<?= $arResult["Urls"]["User"] ?>"><?= $arResult["User"]["NAME_FORMATTED"]; ?></a>
		<?endif;?>
		<?
	}
}
?>