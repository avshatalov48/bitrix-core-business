<?
IncludeModuleLangFile(__FILE__);

$strError = "";
ClearVars("str_student_");
if (CModule::IncludeModule("learning")):
	$ID = intval($ID);
	$db_res = CStudent::GetList(array(), array("USER_ID" => $ID));
	if (!$db_res->ExtractFields("str_student_", true))
	{
		if (!isset($str_student_PUBLIC_PROFILE) || ($str_student_PUBLIC_PROFILE!="Y" && $str_student_PUBLIC_PROFILE!="N"))
			$str_student_PUBLIC_PROFILE = "N";
	}

	if ($strError <> '')
	{
		$DB->InitTableVarsForEdit("b_learn_student", "student_", "str_student_");
	}
	?>
	<input type="hidden" name="profile_module_id[]" value="learning">
	<tr valign="top">
			<td align="right" width="40%"><?=GetMessage("learning_PUBLIC_PROFILE");?>:</td>
			<td width="60%"><input type="checkbox" name="student_PUBLIC_PROFILE" value="Y" <?if ($str_student_PUBLIC_PROFILE=="Y") echo "checked";?>></td>
	</tr>

	<tr valign="top">
		<td align="right"><?=GetMessage("learning_RESUME");?>:</td>
		<td><textarea class="typearea" name="student_RESUME" style="width:50%; height:200px;"><?echo $str_student_RESUME; ?></textarea></td>
	</tr>

<?if ($str_student_TRANSCRIPT <> ''):?>
	<tr valign="top">
		<td align="right"><?=GetMessage("learning_TRANSCRIPT");?>:</td>
		<td>
			<?=$str_student_TRANSCRIPT?>-<?=$ID?>
		</td>
	</tr>
<?endif?>

	<tr valign="top">
		<td align="right"></td>
		<td>
			<a href="/bitrix/admin/learn_certification_admin.php?lang=<?=LANG?>&amp;filter_user=<?=$ID?>&amp;set_filter=Y"><?=GetMessage("learning_CERTIFICATION")?></a><br />
			<a href="/bitrix/admin/learn_gradebook_admin.php?lang=<?=LANG?>&amp;filter_user=<?=$ID?>&amp;set_filter=Y"><?=GetMessage("learning_GRADEBOOK")?></a>
		</td>
	</tr>
<?endif;?>