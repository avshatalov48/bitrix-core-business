<?
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * */

require_once(__DIR__."/../include/prolog_admin_before.php");
define("HELP_FILE", "settings/user_settings.php");

$editable = ($USER->CanDoOperation('edit_own_profile') || $USER->CanDoOperation('edit_other_settings'));
if (!$USER->CanDoOperation('view_other_settings') && !$editable)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("user_sett_tab"), "ICON"=>"", "TITLE"=>GetMessage("user_sett_tab_title")),
	array("DIV" => "edit2", "TAB" => GetMessage("user_sett_del"), "ICON"=>"", "TITLE"=>GetMessage("user_sett_del_title")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$bFormValues = false;
$sSuccessMsg = "";

if(!empty($_REQUEST["action"]) && $editable && check_bitrix_sessid())
{
	if ($_REQUEST["action"] == "clear")
	{
		CUserOptions::DeleteUsersOptions($USER->GetID());
		$sSuccessMsg .= GetMessage("user_sett_mess_del")."<br>";
	}
	if ($_REQUEST["action"] == "clear_links")
	{
		CUserOptions::DeleteOption("start_menu", "recent");
		$sSuccessMsg .= GetMessage("user_sett_mess_links")."<br>";
	}
	if($_REQUEST["action"] == "clear_all" && $USER->CanDoOperation('edit_other_settings'))
	{
		CUserOptions::DeleteCommonOptions();
		$sSuccessMsg .= GetMessage("user_sett_mess_del_common")."<br>";
	}
	if($_REQUEST["action"] == "clear_all_user" && $USER->CanDoOperation('edit_other_settings'))
	{
		CUserOptions::DeleteUsersOptions();
		$sSuccessMsg .= GetMessage("user_sett_mess_del_user")."<br>";
	}
	if($sSuccessMsg <> "")
	{
		\Bitrix\Main\Application::getInstance()->getSession()["ADMIN"]["USER_SETTINGS_MSG"] = $sSuccessMsg;
		LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
	}
}

if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_REQUEST["Update"]) && $_REQUEST["Update"]=="Y" && $editable && check_bitrix_sessid())
{
	$aMsg = array();

	if(empty($aMsg))
	{
		$aFields = [
			"context_menu" => isset($_REQUEST["context_menu"]) && $_REQUEST["context_menu"] == "Y"? "Y":"N",
			"context_ctrl" => isset($_REQUEST["context_ctrl"]) && $_REQUEST["context_ctrl"] == "Y"? "Y":"N",
			"autosave" => isset($_REQUEST["autosave"]) && $_REQUEST["autosave"] == "Y"? "Y":"N",
			"start_menu_links" => intval($_REQUEST["start_menu_links"] ?? 0),
			"start_menu_preload" => isset($_REQUEST["start_menu_preload"]) && $_REQUEST["start_menu_preload"] == "Y"? "Y":"N",
			"start_menu_title" => isset($_REQUEST["start_menu_title"]) && $_REQUEST["start_menu_title"] == "Y"? "Y":"N",
			"panel_dynamic_mode" => isset($_REQUEST["panel_dynamic_mode"]) && $_REQUEST["panel_dynamic_mode"] == "Y"? "Y":"N",
			"page_edit_control_enable" => isset($_REQUEST["page_edit_control_enable"]) && $_REQUEST["page_edit_control_enable"] == "Y"? "Y":"N",
			"messages" => [
				"support"=> isset($_REQUEST["messages_support"]) && $_REQUEST["messages_support"] == "Y"? "Y":"N",
				"security"=> isset($_REQUEST["messages_security"]) && $_REQUEST["messages_security"] == "Y"? "Y":"N",
				"perfmon"=> isset($_REQUEST["messages_perfmon"]) && $_REQUEST["messages_perfmon"] == "Y"? "Y":"N",
			],
			"sound" => isset($_REQUEST["sound"]) && $_REQUEST["sound"] == "Y"? "Y":"N",
			"sound_login" => $_REQUEST["sound_login"] ?? null,
		];

		//common default
		if($USER->CanDoOperation('edit_other_settings') && isset($_REQUEST["default"]) && $_REQUEST["default"] == "Y")
		{
			CUserOptions::SetOption("global", "settings", $aFields, true);
			$sSuccessMsg .= GetMessage("user_sett_mess_save")."<br>";
		}

		//personal
		CUserOptions::SetOption("global", "settings", $aFields);
		$sSuccessMsg .= GetMessage("user_sett_mess_save1")."<br>";

		\Bitrix\Main\Application::getInstance()->getSession()["ADMIN"]["USER_SETTINGS_MSG"] = $sSuccessMsg;
		LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID);
	}
	else
	{
		$bFormValues = true;
		$APPLICATION->ThrowException(new CAdminException($aMsg));
	}
}

$APPLICATION->SetTitle(GetMessage("user_sett_title"));
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if($bFormValues)
{
	$aUserOpt = array(
		"context_menu"=>$_REQUEST["context_menu"] ?? null,
		"context_ctrl"=>$_REQUEST["context_ctrl"] ?? null,
		"autosave"=>$_REQUEST["autosave"] ?? null,
		"start_menu_links"=>$_REQUEST["start_menu_links"] ?? null,
		"start_menu_preload"=>$_REQUEST["start_menu_preload"] ?? null,
		"start_menu_title"=>$_REQUEST["start_menu_title"] ?? null,
		"panel_dynamic_mode"=>$_REQUEST["panel_dynamic_mode"] ?? null,
		"page_edit_control_enable" => $_REQUEST['page_edit_control_enable'] ?? null,
		"messages" => array(
			"support"=>$_REQUEST["messages_support"] ?? null,
			"security"=>$_REQUEST["messages_security"] ?? null,
			"perfmon"=>$_REQUEST["messages_perfmon"] ?? null,
		),
		"sound" => $_REQUEST["sound"] ?? null,
		"sound_login" => $_REQUEST["sound_login"] ?? null,
	);
}
else
{
	$aUserOpt = CUserOptions::GetOption("global", "settings");
	if(!isset($aUserOpt["context_menu"]) || $aUserOpt["context_menu"] == "") $aUserOpt["context_menu"] = "Y";
	if(!isset($aUserOpt["context_ctrl"]) || $aUserOpt["context_ctrl"] == "") $aUserOpt["context_ctrl"] = "N";
	if(!isset($aUserOpt["autosave"]) || $aUserOpt["autosave"] == "") $aUserOpt["autosave"] = "Y";
	if(!isset($aUserOpt["start_menu_links"]) || $aUserOpt["start_menu_links"] == "") $aUserOpt["start_menu_links"] = "5";
	if(!isset($aUserOpt["start_menu_preload"]) || $aUserOpt["start_menu_preload"] == "") $aUserOpt["start_menu_preload"] = "N";
	if(!isset($aUserOpt["start_menu_title"]) || $aUserOpt["start_menu_title"] == "") $aUserOpt["start_menu_title"] = "Y";
	if(!isset($aUserOpt["panel_dynamic_mode"]) || $aUserOpt["panel_dynamic_mode"] == "") $aUserOpt["panel_dynamic_mode"] = "N";
	if(!isset($aUserOpt["page_edit_control_enable"]) || $aUserOpt["page_edit_control_enable"] == "") $aUserOpt["page_edit_control_enable"] = "Y";
	if(!isset($aUserOpt["messages"]["support"]) || $aUserOpt["messages"]["support"] == "") $aUserOpt["messages"]["support"] = "Y";
	if(!isset($aUserOpt["messages"]["security"]) || $aUserOpt["messages"]["security"] == "") $aUserOpt["messages"]["security"] = "Y";
	if(!isset($aUserOpt["messages"]["perfmon"]) || $aUserOpt["messages"]["perfmon"] == "") $aUserOpt["messages"]["perfmon"] = "Y";
	if(!isset($aUserOpt["sound"]) || $aUserOpt["sound"] == "") $aUserOpt["sound"] = "N";
	if(!isset($aUserOpt["sound_login"]) || $aUserOpt["sound_login"] == "") $aUserOpt["sound_login"] = "/bitrix/sounds/main/bitrix_tune.mp3";
}

$message = null;
if($e = $APPLICATION->GetException())
{
	$message = new CAdminMessage(GetMessage("user_sett_err_title"), $e);
	echo $message->Show();
}

if(!empty(\Bitrix\Main\Application::getInstance()->getSession()["ADMIN"]["USER_SETTINGS_MSG"]))
{
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("user_sett_mess_title"), "TYPE"=>"OK", "DETAILS"=>\Bitrix\Main\Application::getInstance()->getSession()["ADMIN"]["USER_SETTINGS_MSG"], "HTML"=>true));
	unset(\Bitrix\Main\Application::getInstance()->getSession()["ADMIN"]["USER_SETTINGS_MSG"]);
}
?>
<form method="POST" name="form1" action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("user_sett_personal")?></td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("user_sett_context")?></td>
		<td width="60%"><input type="checkbox" name="context_menu" value="Y"<?if($aUserOpt["context_menu"] == "Y") echo " checked"?> onclick="this.form.context_ctrl[0].disabled = this.form.context_ctrl[1].disabled = !this.checked"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("user_sett_context_ctrl")?></td>
		<td>
			<input type="radio" name="context_ctrl" id="context_ctrl_N" value="N"<?if($aUserOpt["context_ctrl"] <> "Y") echo " checked"?><?if($aUserOpt["context_menu"] <> "Y") echo " disabled"?>><label for="context_ctrl_N"><?echo GetMessage("user_sett_context_ctrl_val1")?></label><br>
			<input type="radio" name="context_ctrl" id="context_ctrl_Y" value="Y"<?if($aUserOpt["context_ctrl"] == "Y") echo " checked"?><?if($aUserOpt["context_menu"] <> "Y") echo " disabled"?>><label for="context_ctrl_Y"><?echo GetMessage("user_sett_context_ctrl_val2")?></label><br>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("user_sett_autosave")?></td>
		<td><input type="checkbox" name="autosave" value="Y"<?if($aUserOpt["autosave"] == "Y") echo " checked=\"checked\""?> /></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("user_sett_panel")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_OPTION_DYN_EDIT")?></td>
		<td><input type="checkbox" name="panel_dynamic_mode" value="Y"<?if($aUserOpt["panel_dynamic_mode"] == "Y") echo " checked"?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_OPTION_PAGE_EDIT_ENABLE")?></td>
		<td><input type="checkbox" name="page_edit_control_enable" value="Y"<?if($aUserOpt["page_edit_control_enable"] != "N") echo " checked"?>></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("user_sett_start")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("user_sett_start_preload")?></td>
		<td><input type="checkbox" name="start_menu_preload" value="Y"<?if($aUserOpt["start_menu_preload"] == "Y") echo " checked"?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("user_sett_titles")?></td>
		<td><input type="checkbox" name="start_menu_title" value="Y"<?if($aUserOpt["start_menu_title"] == "Y") echo " checked"?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("user_sett_start_links")?></td>
		<td>
			<input type="text" name="start_menu_links" value="<?echo htmlspecialcharsbx($aUserOpt["start_menu_links"])?>" size="10">
			<a href="javascript:if(confirm('<?echo CUtil::addslashes(GetMessage("user_sett_del_links_conf"))?>'))window.location='user_settings.php?action=clear_links&lang=<?echo LANG?>&<?echo bitrix_sessid_get()?>';"><?echo GetMessage("user_sett_del_links")?></a>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("user_sett_sounds")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("user_sett_sounds_play")?></td>
		<td><input type="checkbox" name="sound" value="Y"<?if($aUserOpt["sound"] == "Y") echo " checked"?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("user_sett_sounds_login")?></td>
		<td>
<?
CAdminFileDialog::ShowScript(
	Array
	(
		"event" => "OpenFileBrowserWindFile",
		"arResultDest" => Array("FORM_NAME" => "form1", "FORM_ELEMENT_NAME" => "sound_login"),
		"arPath" => Array('PATH' => '/bitrix/sounds/main/'),
		"select" => 'F',// F - file only, D - folder only
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"fileFilter" => 'wma,mp3,aac',
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
?>
			<input type="text" name="sound_login" value="<?echo htmlspecialcharsbx($aUserOpt["sound_login"])?>" size="40">
			<input type="button" value="..." title="<?echo GetMessage("user_sett_sounds_button_title")?>" onclick="OpenFileBrowserWindFile()">
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("user_sett_mess_head")?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("user_sett_mess")?></td>
		<td>
			<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" name="messages_support" value="Y" id="messages_support"<?if($aUserOpt['messages']['support'] == 'Y') echo " checked"?>></div>
					<div class="adm-list-label"><label for="messages_support"><?echo GetMessage("user_sett_mess_support")?></label></div>
				</div>
				<?if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/index.php")):?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" name="messages_security" value="Y" id="messages_security"<?if($aUserOpt['messages']['security'] == 'Y') echo " checked"?>></div>
					<div class="adm-list-label"><label for="messages_security"><?echo GetMessage("user_sett_mess_security")?></label></div>
				</div>
				<?endif;?>
				<?if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/index.php")):?>
				<div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" name="messages_perfmon" value="Y" id="messages_perfmon"<?if($aUserOpt['messages']['perfmon'] == 'Y') echo " checked"?>></div>
					<div class="adm-list-label"><label for="messages_perfmon"><?echo GetMessage("user_sett_mess_perfmon")?></label></div>
				</div>
				<?endif;?>
			</div>
		</td>
	</tr>
<?if($USER->CanDoOperation('edit_other_settings')):?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("user_sett_common")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("user_sett_common_set")?></td>
		<td><input type="checkbox" name="default" value="Y"></td>
	</tr>
<?endif;?>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2"><a href="javascript:if(confirm('<?echo CUtil::addslashes(GetMessage("user_sett_del_pers_conf"))?>'))window.location='user_settings.php?action=clear&lang=<?echo LANG?>&<?echo bitrix_sessid_get()?>&tabControl_active_tab=edit2';"><?echo GetMessage("user_sett_del_pers1")?></a></td>
	</tr>
<?if($USER->CanDoOperation('edit_other_settings')):?>
	<tr>
		<td colspan="2"><a href="javascript:if(confirm('<?echo CUtil::addslashes(GetMessage("user_sett_del_common_conf"))?>'))window.location='user_settings.php?action=clear_all&lang=<?echo LANG?>&<?echo bitrix_sessid_get()?>&tabControl_active_tab=edit2';"><?echo GetMessage("user_sett_del_common1")?></a></td>
	</tr>
	<tr>
		<td colspan="2"><a href="javascript:if(confirm('<?echo CUtil::addslashes(GetMessage("user_sett_del_user_conf"))?>'))window.location='user_settings.php?action=clear_all_user&lang=<?echo LANG?>&<?echo bitrix_sessid_get()?>&tabControl_active_tab=edit2';"><?echo GetMessage("user_sett_del_user1")?></a></td>
	</tr>
<?endif;?>

<?
$tabControl->Buttons();
?>
<input<?if(!$editable) echo " disabled"?> type="submit" name="apply" value="<?echo GetMessage("admin_lib_edit_apply")?>" title="<?echo GetMessage("admin_lib_edit_apply_title")?>" class="adm-btn-save">
<?
$tabControl->End();
$tabControl->ShowWarnings("form1", $message);
?>
</form>

<?
require_once ($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>
