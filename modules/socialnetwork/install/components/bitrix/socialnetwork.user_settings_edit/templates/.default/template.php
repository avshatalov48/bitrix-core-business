<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (!empty($arResult["FatalError"]))
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	if(!empty($arResult["ErrorMessage"]))
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	if ($arResult["ShowForm"] == "Input")
	{
		?><form method="post" name="form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
			<table class="sonet-message-form data-table" cellspacing="0" cellpadding="0">
				<tr>
					<th colspan="2"><?= GetMessage("SONET_C40_T_SETTINGS") ?></th>
				</tr><?
				foreach ($arResult["Features"] as $feature => $perm):
					?><tr>
						<td valign="top" width="50%" align="right"><?= GetMessage("SONET_USER_OPERATIONS_".$feature) ?>:</td>
						<td valign="top" width="50%">
							<select name="<?= $feature ?>_perm">
								<?foreach ($arResult["PermsVar"] as $key => $value):?>
									<option value="<?= $key ?>"<?= ($key == $perm) ? " selected" : "" ?>><?= $value ?></option>
								<?endforeach;?>
							</select>
						</td>
					</tr><?
				endforeach;
			?></table>
			<input type="hidden" name="SONET_USER_ID" value="<?= $arParams["USER_ID"] ?>">
			<?=bitrix_sessid_post()?>
			<br />
			<input type="submit" name="save" value="<?= GetMessage("SONET_C40_T_SAVE") ?>">
			<input type="reset" name="cancel" value="<?= GetMessage("SONET_C40_T_CANCEL") ?>" OnClick="window.location='<?= $arResult["Urls"]["User"] ?>'">
		</form><?
	}
}
?>