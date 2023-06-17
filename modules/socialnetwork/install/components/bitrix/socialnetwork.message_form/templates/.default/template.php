<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$GLOBALS["APPLICATION"]->AddHeadScript("/bitrix/js/main/utils.js");
// *****************************************************************************************
if (LANGUAGE_ID == 'ru')
{
	$path = str_replace(array("\\", "//"), "/", __DIR__."/ru/script.php");
	@include_once($path);
}
// *****************************************************************************************
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (!empty($arResult["FatalError"]))
{
	?>
	<span class="errortext"><?=$arResult["FatalError"]?></span><br /><br />
	<?
}
else
{
	if(!empty($arResult["ErrorMessage"]))
	{
		?>
		<span class="errortext"><?=$arResult["ErrorMessage"]?></span><br /><br />
		<?
	}

	if ($arResult["ShowForm"] == "Input")
	{
		?>
		<form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data" onmouseover="if(null != init_form){init_form(this)}" onkeydown="if(null != init_form){init_form(this)}" onsubmit="return BX.Forum.ValidateForm(this);">
			<table class="sonet-message-form data-table" cellspacing="0" cellpadding="0">
				<tr>
					<th align="right"><?=GetMessage("SONET_MF_USER")?></th>
					<th><b>
						<?
						if ($arResult["CurrentUserPerms"]["ViewProfile"])
							echo "<a href=\"".$arResult["Urls"]["User"]."\">";
						echo $arResult["User"]["NAME_FORMATTED"];
						if ($arResult["CurrentUserPerms"]["ViewProfile"])
							echo "</a>";
						?></b>
					</th>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<th class="sonet-message-form-small">
						<input type="button" name='B' class='bold' accesskey='b' value="<?=GetMessage("PM_B")?>" title="<?=GetMessage("PM_BOLD")?>" />
						<input type="button" name='I' class='italic' accesskey='i' value="<?=GetMessage("PM_I")?>" title="<?=GetMessage("PM_ITAL")?>" />
						<input type="button" name='U' class='underline' accesskey='u' value="<?=GetMessage("PM_U")?>" title="<?=GetMessage("PM_UNDER")?>" />
						<select name='FONT' class='font'>
							<option value="0"><?=GetMessage("PM_FONT")?></option>
							<option value="Arial" style='font-family:Arial'>Arial</option>
							<option value="Times" style='font-family:Times'>Times</option>
							<option value="Courier" style='font-family:Courier'>Courier</option>
							<option value="Impact" style='font-family:Impact'>Impact</option>
							<option value="Geneva" style='font-family:Geneva'>Geneva</option>
							<option value="Optima" style='font-family:Optima'>Optima</option>
						</select>
						<select name='COLOR' class="color">
							<option value="0"><?=GetMessage("PM_COLOR")?></option>
							<option value="blue" style='color:blue'><?=GetMessage("PM_BLUE")?></option>
							<option value="red" style='color:red'><?=GetMessage("PM_RED")?></option>
							<option value="gray" style='color:gray'><?=GetMessage("PM_GRAY")?></option>
							<option value="green" style='color:green'><?=GetMessage("PM_GREEN")?></option>
						</select>
						<br /><br />
						<input type="button" name='URL' class="url" accesskey='h' value="<?=GetMessage("PM_HYPERLINK")?>" title="<?=GetMessage("PM_HYPERLINK_TITLE")?>" />
						<input type="button" name='IMG' class='img' accesskey='g' value="<?=GetMessage("PM_IMAGE")?>" title="<?=GetMessage("PM_IMAGE_TITLE")?>" />
						<input type="button" name='QUOTE' class='quote' accesskey='q' value="<?=GetMessage("PM_QUOTE")?>" title="<?=GetMessage("PM_QUOTE_TITLE")?>" />
						<input type="button" name='CODE' class='code' accesskey='p' value="<?=GetMessage("PM_CODE")?>" title="<?=GetMessage("PM_CODE_TITLE")?>" />
						<input type="button" name="LIST" class="list" accesskey='l' value="<?=GetMessage("PM_LIST");?>" title="<?=GetMessage("PM_LIST_TITLE")?>" />
						<?if (LANGUAGE_ID=="ru"):?>
							<input type="button" name="TRANSLIT" class="translit" accesskey='t' value="<?=GetMessage("PM_TRANSLIT")?>" title="<?=GetMessage("PM_TRANSLIT_TITLE")?>" />
						<?endif;?>
						<input type="button" name="CLOSE" class="close" title="<?=GetMessage("PM_CLOSE_OPENED_TAGS")?>" value="<?=GetMessage("PM_CLOSE_ALL_TAGS")?>">
						<br /><br />
						<?=GetMessage("PM_OPENED_TAGS")?>
						<input type="text" name="tagcount" class="tagcount" value="0" size="5" />&nbsp;
						<input type="text" name="helpbox" class="helpbox" value="" size="50" />
					</th>
				</tr>

				<tr>
					<td valign="top" align="center">
						<table class="sonet-message-form-smile">
							<tr>
								<td align="center" colspan="3"><?=GetMessage("SONET_MF_SMILES")?></td>
							</tr>
							<?=$arResult["PrintSmilesList"]?>
						</table>
					</td>
					<td valign="top">
						<?=GetMessage("SONET_MF_MESSAGE")?><br /><br />
						<textarea name="POST_MESSAGE" style="width:95%" rows="5"><?= htmlspecialcharsex($_POST["POST_MESSAGE"]); ?></textarea>
						<input type="hidden" name="SONET_USER_ID" value="<?= $arResult["User"]["ID"] ?>">
						<?=bitrix_sessid_post()?>
						<br />
						<br />
						<input type="submit" name="save" value="<?= GetMessage("SONET_C26_T_SEND_MESSAGE") ?>">
					</td>
				</tr>
			</table>
		</form>
		<?
	}
	else
	{
		?>
		<?= GetMessage("SONET_C26_T_SUCCESS") ?><br /><br />
		<?=GetMessage("SONET_MF_USER")?>
		<?if ($arResult["CurrentUserPerms"]["ViewProfile"]):?>
			<a href="<?= $arResult["Urls"]["User"] ?>">
		<?endif;?>
		<?= $arResult["User"]["NAME_FORMATTED"]; ?>
		<?if ($arResult["CurrentUserPerms"]["ViewProfile"]):?>
			</a>
		<?endif;?>
		<br /><br />
		<a href="<?= $arResult["Urls"]["MessagesInput"] ?>"><?= GetMessage("SONET_C26_T_MY_INBOX") ?></a><br>
		<a href="<?= $arResult["Urls"]["MessagesOutput"] ?>"><?= GetMessage("SONET_C26_T_MY_OUTBOX") ?></a><br>
		<?
	}
}
?>