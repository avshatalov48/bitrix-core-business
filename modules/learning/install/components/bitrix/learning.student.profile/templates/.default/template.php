<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?ShowError($arResult["ERROR_MESSAGE"]);?>

<?if (!empty($arResult["USER"])):?>


<form method="post" name="learn_studen_profile" action="<?=$arResult["CURRENT_PAGE"]?>" enctype="multipart/form-data">

<table class="learn-student-profile data-table">

<tr>
	<th colspan="2"><?=GetMessage("LEARNING_PERSONAL_DATA")?></th>
</tr>

	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_USER_NAME");?>:</td>
		<td><input type="text" name="NAME" size="35" maxlength="50" value="<?=$arResult["USER"]["NAME"]?>"></td>
	</tr>


	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_USER_LAST_NAME");?>:</td>
		<td><input type="text" name="LAST_NAME" size="35" maxlength="50" value="<?=$arResult["USER"]["LAST_NAME"]?>"></td>
	</tr>

	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_USER_EMAIL");?>:</td>
		<td><input type="text" name="EMAIL" size="35" maxlength="50" value="<?=$arResult["USER"]["EMAIL"]?>"></td>
	</tr>

	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_USER_PERSONAL_WWW");?>:</td>
		<td><input type="text" name="PERSONAL_WWW" size="35" maxlength="50" value="<?=$arResult["USER"]["PERSONAL_WWW"]?>"></td>
	</tr>

	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_USER_PERSONAL_ICQ");?>:</td>
		<td><input type="text" name="PERSONAL_ICQ" size="35" maxlength="50" value="<?=$arResult["USER"]["PERSONAL_ICQ"]?>"></td>
	</tr>

<tr>
	<th colspan="2"><?=GetMessage("LEARNING_EDIT_PROFILE")?></th>
</tr>
	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_PUBLIC_PROFILE");?>:</td>
		<td><input type="checkbox" name="PUBLIC_PROFILE" value="Y" <?if (isset($arResult["STUDENT"]["PUBLIC_PROFILE"]) && $arResult["STUDENT"]["PUBLIC_PROFILE"]=="Y") echo "checked";?>></td>
	</tr>

	<?if (!empty($arResult["STUDENT"]["TRANSCRIPT"])):?>
	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_TRANSCRIPT");?>:</td>
		<td><a href="<?=$arResult["TRANSCRIPT_DETAIL_URL"]?>"><?=$arResult["STUDENT"]["TRANSCRIPT"]?>-<?=$arResult["STUDENT"]["USER_ID"]?></a></td>
	</tr>
	<?endif?>
	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_RESUME");?>:</td>
		<td><textarea class="typearea" name="RESUME"><?=($arResult["STUDENT"]["RESUME"] ?? '')?></textarea></td>
	</tr>

	<tr>
		<td class="field-name"><?echo GetMessage("LEARNING_USER_PHOTO")?></td>
		<td>
		<input name="PERSONAL_PHOTO" size="30" type="file"><br />
		<label><input name="PERSONAL_PHOTO_del" value="Y" type="checkbox"><?=GetMessage("LEARNING_DELETE_FILE");?></label>

		<?if ($arResult["USER"]["PERSONAL_PHOTO_ARRAY"]!==false):?>
			<br /><?=CFile::ShowImage($arResult["USER"]["PERSONAL_PHOTO_ARRAY"], 200, 200, "border=0", "", true)?>
		<?endif?>
		</td>
	</tr>

<tr>
	<th colspan="2"><?=GetMessage("LEARNING_USER_ADDRESS")?></th>
</tr>


	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_USER_PERSONAL_COUNTRY");?>:</td>
		<td>
			<select name="PERSONAL_COUNTRY">
				<option value="">&nbsp;</option>
			<?for ($i = 0, $countryCount = count($arResult["USER"]["PERSONAL_COUNTRY_ARRAY"]["reference_id"]); $i < $countryCount; $i++ ):?>
				<option value="<?=$arResult["USER"]["PERSONAL_COUNTRY_ARRAY"]["reference_id"][$i]?>"<?if ($arResult["USER"]["PERSONAL_COUNTRY_ARRAY"]["reference_id"][$i]==$arResult["USER"]["PERSONAL_COUNTRY"]):?> selected="selected"<?endif?>><?=$arResult["USER"]["PERSONAL_COUNTRY_ARRAY"]["reference"][$i]?></option>
			<?endfor?>
			</select>
		</td>
	</tr>


	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_USER_PERSONAL_STATE");?>:</td>
		<td><input type="text" name="PERSONAL_STATE" size="35" maxlength="50" value="<?=$arResult["USER"]["PERSONAL_STATE"]?>"></td>
	</tr>

	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_USER_PERSONAL_CITY");?>:</td>
		<td><input type="text" name="PERSONAL_CITY" size="35" maxlength="50" value="<?=$arResult["USER"]["PERSONAL_CITY"]?>"></td>
	</tr>

	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_USER_PERSONAL_ZIP");?>:</td>
		<td><input type="text" name="PERSONAL_ZIP" size="35" maxlength="50" value="<?=$arResult["USER"]["PERSONAL_ZIP"]?>"></td>
	</tr>

	<tr>
		<td class="field-name"><?=GetMessage("LEARNING_USER_PERSONAL_STREET");?>:</td>
		<td><input type="text" name="PERSONAL_STREET" size="35" maxlength="50" value="<?=$arResult["USER"]["PERSONAL_STREET"]?>"></td>
	</tr>

</table>

<p>
<?=bitrix_sessid_post()?>
<input type="hidden" name="ACTION" value="EDIT">
<input type="submit" name="save" value="<?=GetMessage("LEARNING_SAVE")?>">
</p>
</form>



<?endif?>
