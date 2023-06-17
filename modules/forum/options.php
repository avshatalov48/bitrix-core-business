<?
IncludeModuleLangFile(__FILE__);
$module_id = "forum";
$FORUM_RIGHT = $APPLICATION->GetGroupRight($module_id);
$zr = "";
if (! ($FORUM_RIGHT >= "R"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

ClearVars();
CModule::IncludeModule("forum");
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if ($request->isPost() !== true && $FORUM_RIGHT > "R" && $request->getQuery('RestoreDefaults') !== null && check_bitrix_sessid())
{
	COption::RemoveOption("forum");
	$z = CGroup::GetList("id", "asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
	while($zr = $z->Fetch())
		$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
}

$arLangs = array();
$arNameStatusesDefault = array();
$arNameStatuses = @unserialize(COption::GetOptionString("forum", "statuses_name"), ["allowed_classes" => false]);

$db_res = CLanguage::GetList();
if ($db_res && $res = $db_res->Fetch())
{
	do 
	{
		$arLangs[$res["LID"]] = $res;
		$name = array(
			"guest" => "Guest",
			"user" => "User",
			"moderator" => "Moderator",
			"editor" => "Editor",
			"administrator" => "Administrator");
/*
GetMessage("FR_GUEST");
GetMessage("FR_USER");
GetMessage("FR_MODERATOR");
GetMessage("FR_EDITOR");
GetMessage("FR_ADMINISTRATOR");
*/
		$arMess = IncludeModuleLangFile(__FILE__, $res["LID"], true);
		foreach ($name as $k => $v):
			$name[$k] = $arMess["FR_".mb_strtoupper($k)] ?? $v;
		endforeach;
		$arNameStatusesDefault[$res["LID"]] = $name;

		if (empty($arNameStatuses[$res["LID"]]) || !is_array($arNameStatuses[$res["LID"]])):
			$arNameStatuses[$res["LID"]] = $name;
		else:
			foreach ($name as $k => $v)
			{
				$n = trim($arNameStatuses[$res["LID"]][$k]);
				$arNameStatuses[$res["LID"]][$k] = (empty($n) ? $v : $n);
			}
		endif;
	} while ($res = $db_res->Fetch());
	$tmp = array_diff(array_keys($arNameStatuses), array_keys($arNameStatusesDefault)); 
	foreach ($arNameStatuses as $k => $v):
		if (!is_set($arNameStatusesDefault, $k))
			unset($arNameStatuses[$k]); 
	endforeach;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && $FORUM_RIGHT == "W" && $_REQUEST["Update"] <> '' && check_bitrix_sessid())
{
	COption::SetOptionString("forum", "avatar_max_size", $_REQUEST["avatar_max_size"]);
	COption::SetOptionString("forum", "avatar_max_width", $_REQUEST["avatar_max_width"]);
	COption::SetOptionString("forum", "avatar_max_height", $_REQUEST["avatar_max_height"]);
	COption::SetOptionString("forum", "file_max_size", $_REQUEST["file_max_size"]);
	COption::SetOptionString("forum", "parser_nofollow", ($_REQUEST["parser_nofollow"] == "Y" ? "Y" : "N"));
	COption::SetOptionString("forum", "parser_link_target", ($_REQUEST["parser_link_target"] == "_blank" ? "_blank" : "_self"));
	COption::SetOptionInt("forum", "smile_gallery_id", $_REQUEST["smile_gallery_id"]);

	COption::SetOptionString("forum", "FORUM_FROM_EMAIL", $_REQUEST["FORUM_FROM_EMAIL"]);
	//COption::SetOptionString("forum", "FORUMS_PER_PAGE", $_REQUEST["FORUMS_PER_PAGE_MAIN"]);
	//COption::SetOptionString("forum", "TOPICS_PER_PAGE", $_REQUEST["TOPICS_PER_PAGE"]);
	//COption::SetOptionString("forum", "MESSAGES_PER_PAGE", $_REQUEST["MESSAGES_PER_PAGE"]);

	COption::SetOptionString("forum", "SHOW_VOTES", (($_REQUEST["SHOW_VOTES"]=="Y") ? "Y" : "N" ));
	//COption::SetOptionString("forum", "SHOW_ICQ_CONTACT", (($_REQUEST["SHOW_ICQ_CONTACT"]=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "MaxPrivateMessages", $_REQUEST["MaxPrivateMessages"]);
	COption::SetOptionString("forum", "UsePMVersion", $_REQUEST["UsePMVersion"]);
//	COption::SetOptionString("forum", "MESSAGE_HTML", ($_REQUEST["MESSAGE_HTML"]=="Y" ? "Y" : "N" ));
	COption::SetOptionString("forum", "FORUM_GETHOSTBYADDR", (($_REQUEST["FORUM_GETHOSTBYADDR"]=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "FILTER", (($_REQUEST["FILTER"]=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "FILTER_ACTION", $_REQUEST["FILTER_ACTION"]);
	COption::SetOptionString("forum", "FILTER_RPL", $_REQUEST["FILTER_RPL"]);
	COption::SetOptionString("forum", "FILTER_MARK", $_REQUEST["FILTER_MARK"]);
	COption::SetOptionString("forum", "search_message_count", $_REQUEST["search_message_count"]);

	COption::SetOptionString("forum", "show_avatar_photo", (($_REQUEST["show_avatar_photo"]=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "USE_AUTOSAVE", (($_REQUEST["USE_AUTOSAVE"]=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "USER_EDIT_OWN_POST", (($_REQUEST["USER_EDIT_OWN_POST"]=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "USER_SHOW_NAME", (($_REQUEST["USER_SHOW_NAME"]=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "USE_COOKIE", (($_REQUEST["USE_COOKIE"]=="Y") ? "Y" : "N" ));
	if ($_REQUEST["LOGS"] == "Y"):
		$_REQUEST["LOGS"] = ($_REQUEST["LOGS_ADDITIONAL"] == "Y" ? "U" : "Q");
	else:
		$_REQUEST["LOGS"] = "A";
	endif;
//	A - no logs, Q - log for moderate, U - log for all
	COption::SetOptionString("forum", "LOGS", $_REQUEST["LOGS"]);
//****************************************************************************************************************
	foreach ($_REQUEST["FILTER_DICT"] as $l => $val)
	{
		COption::SetOptionString("forum", "FILTER_DICT_W", $val["W"], false, $l);
		COption::SetOptionString("forum", "FILTER_DICT_T", $val["T"], false, $l);
	}
	foreach ($arNameStatuses as $lid => $names):
		foreach ($names as $key => $val):
			$n = trim($_REQUEST["STATUS_NAME"][$lid][$key]);
			$arNameStatuses[$lid][$key] = (!empty($n) ? $n : $arNameStatuses[$lid][$key]);
		endforeach;
	endforeach;
	
	COption::SetOptionString("forum", "statuses_name", serialize($arNameStatuses));
//*****************************************************************************************************************
}
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "vote_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "vote_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
	array("DIV" => "edit3", "TAB" => GetMessage("USE_FILTER"), "ICON" => "vote_settings", "TITLE" => GetMessage("USE_FILTER")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>
<?
CForumDBTools::GetDBUpdaters();
$tabControl->Begin();
?><form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($module_id)?>&lang=<?=LANGUAGE_ID?>" id="FORMACTION"><?
?><?=bitrix_sessid_post()?><?
$tabControl->BeginNextTab();
?>

	<tr>
		<td width="40%"><?echo GetMessage("FORUM_FROM_EMAIL")?>:</td>
		<td width="60%">
			<?$val = COption::GetOptionString("forum", "FORUM_FROM_EMAIL", "nomail@nomail.nomail");?>
			<input type="text" size="35" maxlength="255" value="<?=htmlspecialcharsbx($val)?>" name="FORUM_FROM_EMAIL" /></td>
	</tr>
	<tr>
		<td><label for="SHOW_VOTES"><?= GetMessage("FORUM_GG_SHOW_VOTE") ?></label></td>
		<td>
			<?$val = COption::GetOptionString("forum", "SHOW_VOTES", "Y");?>
			<input type="checkbox" value="Y" name="SHOW_VOTES" id="SHOW_VOTES" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
<?if (($val = COption::GetOptionString("forum", "SHOW_ICQ_CONTACT", "N")) == "Y"):?>
	<tr>
		<td><label for="SHOW_ICQ_CONTACT"><?= GetMessage("SHOW_ICQ_CONTACT")?></td>
		<td><input type="checkbox" value="Y" name="SHOW_ICQ_CONTACT" id="SHOW_ICQ_CONTACT" checked="checked"></td>
	</tr>
<?endif;?>
<?if (($val = COption::GetOptionString("forum", "FORUM_GETHOSTBYADDR", "N")) == "Y"):?>
	<tr>
		<td><label for="FORUM_GETHOSTBYADDR"><?=GetMessage("FORUM_GETHOSTBYADDR")?></label></td>
		<td><input type="checkbox" value="Y" name="FORUM_GETHOSTBYADDR" id="FORUM_GETHOSTBYADDR" checked="checked" /></td>
	</tr>
<?endif;?>
<?if (($val = COption::GetOptionString("forum", "USE_COOKIE", "N")) == "Y"):?>
	<tr>
		<td><label for="USE_COOKIE"><?= GetMessage("FORUM_USE_COOKIE") ?></label></td>
		<td><input type="checkbox" value="Y" name="USE_COOKIE" id="USE_COOKIE" checked="checked"></td>
	</tr>
<?endif;?>
	<tr>
		<td class="adm-detail-valign-top"><label for="LOGS"><?=GetMessage("FORUM_LOGS_TITLE")?>:</label></td>
		<td>
			<?$val = COption::GetOptionString("forum", "LOGS", "Q");?>
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" name="LOGS" id="LOGS" value="Y" <?=($val > "A" ? "checked='checked'" : "")?> <?
					?>onclick="BX('log-additional').style.display=(this.checked ? 'block' : 'none')"></div>
					<div class="adm-list-label"><label for="LOGS"><?=GetMessage("FORUM_LOGS")?></label></div>
				</div>
				<div id="log-additional" class="adm-list-item"<?=($val <= "A" ? " style='display:none;'" : "")?>>
					<div class="adm-list-control"><input type="checkbox" name="LOGS_ADDITIONAL" ID="LOGS_ADDITIONAL" value="Y" <?=($val > "Q" ? "checked='checked'" : "")?>></div>
					<div class="adm-list-label"><label for="LOGS_ADDITIONAL"><?
						?><?=GetMessage("FORUM_LOGS_ADDITIONAL")?></label></div>
				</div>
			</div>
		</td>
	</tr>
	<tr class="heading"><td colspan="2"><?=GetMessage("F_USER_SETTINGS")?></td></tr>
	<tr>
		<td><label for="USER_EDIT_OWN_POST"><?=GetMessage("FORUM_USER_EDIT_OWN_POST") ?></label></td>
		<td>
			<?$val = COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "Y");?>
			<select name="USER_EDIT_OWN_POST" id="USER_EDIT_OWN_POST">
				<option value="Y" <?if ($val=="Y") echo "selected";?>><?=GetMessage("FORUM_USER_EDIT_OWN_POST_Y") ?></option>
				<option value="N" <?if ($val!="Y") echo "selected";?>><?=GetMessage("FORUM_USER_EDIT_OWN_POST_N") ?></option>
			</select>
	</tr>
	<tr>
		<td><label for="USER_SHOW_NAME"><?=GetMessage("FORUM_USER_SHOW_NAME") ?></label></td>
		<td>
			<?$val = COption::GetOptionString("forum", "USER_SHOW_NAME", "Y");?>
			<input type="checkbox" value="Y" name="USER_SHOW_NAME" id="USER_SHOW_NAME" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td><label for="smile_gallery_id"><?=GetMessage("FORUM_OPTIONS_SMILE_GALLERY_ID") ?></label></td>
		<td>
			<?$val = COption::GetOptionInt("forum", "smile_gallery_id", 0);
			$arSmileGallery = CSmileGallery::getListForForm();
			?><select name="smile_gallery_id" id="smile_gallery_id"><?
				foreach($arSmileGallery as $key => $v):
					?><option value="<?=$key?>"<?if($val==$key)echo" selected"?>><?=$v?></option><?
				endforeach;
			?></select>
		</td>
	</tr>
	<?
	?>
	<tr>
		<td><label for="parser_nofollow"><?=GetMessage("F_PARSER_NOFOLLOW")?>:</label></td>
		<td>
			<?$val = COption::GetOptionString("forum", "parser_nofollow", "Y");?>
			<input type="checkbox" value="Y" name="parser_nofollow" id="parser_nofollow" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td><label for="parser_link_target"><?=GetMessage("F_PARSER_LINK_TARGET")?></label></td>
		<td>
			<?$val = COption::GetOptionString("forum", "parser_link_target", "_blank");?>
			<input type="checkbox" value="_blank" name="parser_link_target" id="parser_link_target" <?if ($val=="_blank") echo "checked";?>></td>
	</tr>
	<tr>
		<td><label for="USE_AUTOSAVE"><?=GetMessage("F_USE_AUTOSAVE")?></label></td>
		<td>
			<?$val = COption::GetOptionString("forum", "USE_AUTOSAVE", "Y");?>
			<input type="checkbox" value="Y" name="USE_AUTOSAVE" id="USE_AUTOSAVE" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORUM_GG_AVATAR_S")?>:</td>
		<td>
			<?$val = COption::GetOptionString("forum", "avatar_max_size", 1048576);?>
			<input type="text" size="35" maxlength="255" value="<?=htmlspecialcharsbx($val)?>" name="avatar_max_size" /></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORUM_GG_AVATAR_W")?>:</td>
		<td>
			<?$val = COption::GetOptionString("forum", "avatar_max_width", 100);?>
			<input type="text" size="14" maxlength="255" value="<?=htmlspecialcharsbx($val)?>" name="avatar_max_width" />&nbsp;/&nbsp;
			<?$val = COption::GetOptionString("forum", "avatar_max_height", 100);?>
			<input type="text" size="14" maxlength="255" value="<?=htmlspecialcharsbx($val)?>" name="avatar_max_height" />
			</td>
	</tr>
	<tr>
		<td><?=GetMessage("FORUM_GG_FILE_S")?>:</td>
		<td>
			<?$val = COption::GetOptionString("forum", "file_max_size", 5242880);?>
			<input type="text" size="35" maxlength="255" value="<?=htmlspecialcharsbx($val)?>" name="file_max_size"></td>
	</tr>
	<tr class="heading"><td colspan="2"><?=GetMessage("F_PM_SETTINGS")?></td></tr>
	<tr>
		<td><?=GetMessage("UsePMVersion")?>:</td>
		<td>
			<?$val = COption::GetOptionString("forum", "UsePMVersion", "2");?>
			<select name="UsePMVersion" id="UsePMVersion" onclick="OnClickUsePMVersion(this)">
				<option value="none" <?if ($val!="1" &&  $val!="2") echo "selected";?>><?=GetMessage("FO_USEPMVERSION")?></option>
				<option value="1" <?if ($val=="1") echo "selected";?>>1.0</option>
				<option value="2" <?if ($val=="2") echo "selected";?>>2.0</option>
			</select>
	</tr>
	<tr id="tr_maxprivatemessages">
		<td><?=GetMessage("FORUM_PRIVATE_MESSAGE")?>:</td>
		<td>
			<?$val = COption::GetOptionString("forum", "MaxPrivateMessages", 100);?>
			<input type="text" size="35" maxlength="255" value="<?=intval($val)?>" name="MaxPrivateMessages"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("F_SEARCH_HEADER")?></td>
	</tr>
	<tr>
		<td><?=GetMessage("F_SEARCH_COUNT")?>:</td>
		<td>
			<?$val = COption::GetOptionString("forum", "search_message_count", 50);?>
			<input type="text" size="35" maxlength="255" value="<?=intval($val)?>" name="search_message_count"></td>
	</tr>
	<tr class="heading"><td colspan="2"><?=GetMessage("F_FORUM_STATUSES")?></td></tr>
	<tr>
		<td colspan="2" align="center">
			<table border="0" class="internal" style="width:auto;">
				<tr class="heading">
					<td align="center"><?=GetMessage("LANG")?></td>
					<?
		foreach ($arNameStatusesDefault[LANGUAGE_ID] as $key => $val):
					?><td><?=$val?></td><?
		endforeach;
					?>
				</tr>
<?
		foreach ($arNameStatuses as $lid => $names):
?>
				<tr>
					<td><?=$arLangs[$lid]["NAME"]?> [ <?=$lid?> ]</td>
<?
			foreach ($names as $key => $val):
?>
					<td><input type="text" style="width:110px" name="STATUS_NAME[<?=$lid?>][<?=$key?>]" value="<?=htmlspecialcharsbx($val)?>" /></td>
<?				
			endforeach;
?>
				</tr>
<?
		endforeach;
?>
				</table>
		</td>
	</tr>

<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->BeginNextTab();?>
	<tr>
		<td width="40%"><label for="FILTER"><?=GetMessage("FILTER")?></label></td>
		<td width="60%">
			<?$val = COption::GetOptionString("forum", "FILTER", "Y");?>
			<input type="checkbox" value="Y" name="FILTER" id="FILTER" <?if ($val=="Y") echo "checked";?> onclick="DisableAction(this)"></td>
	</tr>
	<tr>
		<td><?=GetMessage("FILTER_ACTION")?>:</td>
		<td>
			<?echo SelectBoxFromArray("FILTER_ACTION", array("REFERENCE" => array(GetMessage("non"), GetMessage("del"), GetMessage("rpl")), "REFERENCE_ID" => array("non", "del", "rpl")), COption::GetOptionString("forum", "FILTER_ACTION", "rpl"))?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("FILTER_RPL")?>:</td>
		<?$val = COption::GetOptionString("forum", "FILTER_RPL", "*");?>
		<td><input type="text" value="<?=htmlspecialcharsbx($val)?>" name="FILTER_RPL" id="FILTER_RPL"></td>
	</tr>
	<script language="JavaScript">
	function DisableAction(CheckB)
	{
		var Form = document.getElementById('FORMACTION');
		if (CheckB.checked)
		{
			Form.FILTER_ACTION.disabled = false;
			Form.FILTER_ACTION.value = '<?=CUtil::JSEscape(COption::GetOptionString("forum", "FILTER_ACTION", "rpl"))?>';
			Form.FILTER_RPL.disabled = false;
			Form.FILTER_RPL.value = '<?=CUtil::JSEscape(COption::GetOptionString("forum", "FILTER_RPL", "*"))?>';
		}
		else
		{
			Form.FILTER_ACTION.disabled = true;
			Form.FILTER_RPL.disabled = true;
		}
		return false;
	}
	<?if ($val = COption::GetOptionString("forum", "FILTER", "Y")!="Y"):?>
	var Form = document.getElementById('FORMACTION');
	Form.FILTER_ACTION.disabled = true;
	Form.FILTER_RPL.disabled = true;
	<?endif;?>
	</script>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("ASSOC_LANG_PARAMS")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table border="0" cellspacing="6" class="internal" style="width:auto;">
				<tr class="heading">
					<td align="center"><?=GetMessage("LANG")?></td>
					<td align="center"><?=GetMessage("DICTINARY_AND_EREG")?></td>
					<td align="center"><span id="SECTION_NAME_TITLE"><?=GetMessage("TRANSCRIPTION_DICTIONARY")?></span></td>
				</tr><?
			$db_res = CFilterDictionary::GetList();
			$Dict = array();
			while ($res = $db_res->Fetch())
			{
				$Dict[$res["TYPE"]]["reference_id"][] = $res["ID"];
				$Dict[$res["TYPE"]]["reference"][] = $res["TITLE"];
			}
			$Dict['W']["reference_id"][] = "";
			$Dict['W']["reference"][] = GetMessage("DICTIONARY_NONE");
			$Dict['T']["reference_id"][] = "";
			$Dict['T']["reference"][] = GetMessage("DICTIONARY_NONE");
			$l = CLanguage::GetList();
			while($ar = $l->ExtractFields("l_"))
			{
				?><tr class="adm-detail-required-field">
					<td><span class="tablefieldtext"><?=$ar["NAME"]?> [ <?=$ar["LID"]?> ]:</span></td>
					<td><?=SelectBoxFromArray("FILTER_DICT[".$ar["LID"]."][W]", $Dict["W"], COption::GetOptionString("forum", "FILTER_DICT_W", '', $ar["LID"]))?></td>
					<td><?=SelectBoxFromArray("FILTER_DICT[".$ar["LID"]."][T]", $Dict["T"], COption::GetOptionString("forum", "FILTER_DICT_T", '', $ar["LID"]))?></td>
				</tr><?
			}
			?></table>
		</td>
	</tr>
<?$tabControl->Buttons();?>
<script language="JavaScript">
function RestoreDefaults()
{
	if(confirm('<?=CUtil::JSEscape(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
		window.location = "<?=$APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?=urlencode($module_id)?>&<?=bitrix_sessid_get()?>";
}
function OnClickUsePMVersion(control)
{
	var
		node = BX('tr_maxprivatemessages'),
		val = control.value + "";
	if (!node) {}
	else if (val == "1" || val == "2")
		BX.show(node);
	else
		BX.hide(node);
}
OnClickUsePMVersion(BX('UsePMVersion'));
</script>
	<input <?if ($FORUM_RIGHT<"W") echo "disabled" ?> type="submit" class="adm-btn-green" name="Update" value="<?echo GetMessage("PATH_SAVE")?>" />
	<input type="hidden" name="Update" value="Y" />
	<input type="reset" name="reset" value="<?echo GetMessage("PATH_RESET")?>" />
	<input <?if ($FORUM_RIGHT<"W") echo "disabled" ?> type="button" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>" />
<?$tabControl->End();?>
</form>