<?
IncludeModuleLangFile(__FILE__);
ClearVars("str_forum_");
if (CModule::IncludeModule("forum")):
	$ID = intval($ID);
	$db_res = CForumUser::GetList(array(), array("USER_ID" => $ID));
	$db_res->ExtractFields("str_forum_", True);
	if (!isset($str_forum_ALLOW_POST) || ($str_forum_ALLOW_POST!="Y" && $str_forum_ALLOW_POST!="N"))
		$str_forum_ALLOW_POST = "Y";
	if (!isset($str_forum_SHOW_NAME) || ($str_forum_SHOW_NAME!="Y" && $str_forum_SHOW_NAME!="N"))
		$str_forum_SHOW_NAME = "Y";
	$str_forum_SUBSC_GET_MY_MESSAGE = ($str_forum_SUBSC_GET_MY_MESSAGE == "Y" ? "Y" : "N");

	if($COPY_ID > 0)
		$str_forum_AVATAR = "";

	if ($strError <> '')
	{
		$str_forum_ALLOW_POST = htmlspecialcharsbx($_POST["forum_ALLOW_POST"]);
		$str_forum_HIDE_FROM_ONLINE = htmlspecialcharsbx($_POST["forum_HIDE_FROM_ONLINE"]);
		$str_forum_SUBSC_GET_MY_MESSAGE = htmlspecialcharsbx($_POST["forum_SUBSC_GET_MY_MESSAGE"]);
		$str_forum_SHOW_NAME = htmlspecialcharsbx($_POST["forum_SHOW_NAME"]);
		$str_forum_DESCRIPTION = htmlspecialcharsbx($_POST["forum_DESCRIPTION"]);
		$str_forum_INTERESTS = htmlspecialcharsbx($_POST["forum_INTERESTS"]);
		$str_forum_SIGNATURE = htmlspecialcharsbx($_POST["forum_SIGNATURE"]);
	}
	?>
	<input type="hidden" name="profile_module_id[]" value="forum">
	<?if ($USER->IsAdmin() || $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W"):?>
		<tr>
			<td width="40%"><?=GetMessage("forum_ALLOW_POST")?></td>
			<td width="60%"><input type="checkbox" name="forum_ALLOW_POST" value="Y" <?if ($str_forum_ALLOW_POST=="Y") echo "checked";?>></td>
		</tr>
	<?endif;?>
	<tr>
		<td><?=GetMessage("forum_HIDE_FROM_ONLINE")?></td>
		<td><input type="checkbox" name="forum_HIDE_FROM_ONLINE" value="Y" <?if ($str_forum_HIDE_FROM_ONLINE=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td><?=GetMessage("forum_SUBSC_GET_MY_MESSAGE")?></td>
		<td><input type="checkbox" name="forum_SUBSC_GET_MY_MESSAGE" value="Y" <?if ($str_forum_SUBSC_GET_MY_MESSAGE=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td><?=GetMessage("forum_SHOW_NAME")?></td>
		<td><input type="checkbox" name="forum_SHOW_NAME" value="Y" <?if ($str_forum_SHOW_NAME=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td><?=GetMessage('forum_DESCRIPTION')?></td>
		<td><input class="typeinput" type="text" name="forum_DESCRIPTION" size="30" maxlength="255" value="<?=$str_forum_DESCRIPTION?>"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage('forum_INTERESTS')?></td>
		<td><textarea class="typearea" name="forum_INTERESTS" rows="3" cols="35"><?echo $str_forum_INTERESTS; ?></textarea></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("forum_SIGNATURE")?></td>
		<td><textarea class="typearea" name="forum_SIGNATURE" rows="3" cols="35"><?echo $str_forum_SIGNATURE; ?></textarea></td>
	</tr>
	<tr class="adm-detail-file-row">
		<td><?=GetMessage("forum_AVATAR")?></td>
		<td><?
			echo CFile::InputFile("forum_AVATAR", 20, $str_forum_AVATAR);
			if ((is_array($str_forum_AVATAR) && sizeof($str_forum_AVATAR)>0) || (!is_array($str_forum_AVATAR) && $str_forum_AVATAR <> '')):
				?><div class="adm-detail-file-image"><?
				echo CFile::ShowImage($str_forum_AVATAR, 150, 150, "border=0", "", true);?></div><?
			endif;
			?></td>
	</tr>
	<?
endif;
/*
GetMessage("forum_TAB");
GetMessage("forum_TAB_TITLE");
GetMessage("forum_INFO");
*/
?>
