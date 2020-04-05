<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2012 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

global $MESS;
include(GetLangFileName($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/ldap/lang/", "/options.php"));
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

$module_id = "ldap";
CModule::IncludeModule($module_id);

$MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($MOD_RIGHT>="R"):

$arAllLdapServers = array(0 => GetMessage('LDAP_NOT_USE_DEFAULT_NTLM_SERVER'));
$rsLdapServers = CLdapServer::GetList();

while($arLdapServer = $rsLdapServers->Fetch())
{
	$arAllLdapServers[$arLdapServer['ID']] = $arLdapServer['NAME'];
}

// get current NTLM user login for displaying later
$ntlmVarname = COption::GetOptionString($module_id, 'ntlm_varname', 'REMOTE_USER');

if (array_key_exists($ntlmVarname,$_SERVER) && trim($_SERVER[$ntlmVarname])!='')
{
	$currentUserNTLMMsg = htmlspecialcharsbx($_SERVER[$ntlmVarname]);
}
else
{
	$currentUserNTLMMsg = GetMessage("LDAP_CURRENT_USER_ABS");
}


// set up form
$arAllOptions =	Array(
		//Array("group_limit", GetMessage('LDAP_OPTIONS_GROUP_LIMIT'), 0, Array("text", 5)),
		Array("default_email", GetMessage('LDAP_OPTIONS_DEFAULT_EMAIL'), "no@email", Array("text")),
		Array("use_ntlm", GetMessage('LDAP_OPTIONS_USE_NTLM'), "N", Array("checkbox")),
		Array("use_ntlm_login", GetMessage('LDAP_CURRENT_USER'), $currentUserNTLMMsg, Array("statictext")),
		Array("ntlm_varname", GetMessage('LDAP_OPTIONS_NTLM_VARNAME'), "REMOTE_USER", Array("text", 20)),
		Array("ntlm_default_server", GetMessage('LDAP_DEFAULT_NTLM_SERVER'), "0", Array("selectbox", $arAllLdapServers)),
		Array("add_user_when_auth", GetMessage("LDAP_OPTIONS_NEW_USERS"), "Y", Array("checkbox")),
		Array("ntlm_auth_without_prefix", GetMessage("LDAP_WITHOUT_PREFIX"), "Y", Array("checkbox")),
		Array("ldap_create_duplicate_login_user", GetMessage("LDAP_DUPLICATE_LOGIN_USER"), "Y", Array("checkbox")),
		GetMessage("LDAP_BITRIXVM_BLOCK"),
		Array("bitrixvm_auth_support", GetMessage("LDAP_BITRIXVM_SUPPORT"), "N", Array("checkbox")),
		Array("bitrixvm_auth_net", GetMessage('LDAP_BITRIXVM_NET'), "", Array("text", 40)),
	);

if($MOD_RIGHT>="W"):

	if ($REQUEST_METHOD=="GET" && strlen($RestoreDefaults)>0 && check_bitrix_sessid())
	{
		COption::RemoveOption($module_id);
		$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
		while($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
	}

	if($REQUEST_METHOD=="POST" && strlen($Update)>0 && check_bitrix_sessid())
	{
		if($_POST['bitrixvm_auth_net'] && !preg_match("#(\d{1,3}\.){3,3}(\d{1,3})/(\d{1,3}\.){3,3}(\d{1,3})#",$_POST['bitrixvm_auth_net']) && !preg_match("#(\d{1,3}\.){3,3}(\d{1,3})/(\d{1,3})#",$_POST['bitrixvm_auth_net']))
			CAdminMessage::ShowMessage(GetMessage('LDAP_WRONG_NET_MASK'));

		foreach($arAllOptions as $option)
		{
			if(!is_array($option))
				continue;

			$name = $option[0];
			$val = ${$name};
			if($option[3][0] == "checkbox" && $val != "Y")
				$val = "N";
			if($option[3][0] == "multiselectbox")
				$val = @implode(",", $val);

			COption::SetOptionString($module_id, $name, $val, $option[1]);
		}
		if ($_POST['use_ntlm'] == 'Y')
		{
			RegisterModuleDependences('main', 'OnBeforeProlog', 'ldap', 'CLDAP', 'NTLMAuth', 40);
		}
		else
		{
			UnRegisterModuleDependences('main', 'OnBeforeProlog', 'ldap', 'CLDAP', 'NTLMAuth');
		}

		if ($_POST['bitrixvm_auth_support'] == 'Y')
			CLdapUtil::SetBitrixVMAuthSupport();
		else
			CLdapUtil::UnSetBitrixVMAuthSupport();
	}

endif; //if($MOD_RIGHT>="W"):

$arAllOptions[] = Array("bitrixvm_auth_hint", "", BeginNote().GetMessage("LDAP_BITRIXVM_HINT").EndNote(), Array("statichtml", ""));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "ldap_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "ldap_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

?>
<?
$tabControl->Begin();
?><form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>" name="ldap_settings">
<?$tabControl->BeginNextTab();?>
<?__AdmSettingsDrawList("ldap", $arAllOptions);?>
<?
$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
<script language="JavaScript">
function RestoreDefaults()
{
	if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid)."&".bitrix_sessid_get();?>";
}
</script>
<input type="submit" name="Update" <?if ($MOD_RIGHT<"W") echo "disabled" ?> value="<?echo GetMessage("LDAP_OPTIONS_SAVE")?>">
<input type="reset" name="reset" value="<?echo GetMessage("LDAP_OPTIONS_RESET")?>">
<input type="hidden" name="Update" value="Y">
<?=bitrix_sessid_post();?>
<input type="button" <?if ($MOD_RIGHT<"W") echo "disabled" ?> title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
<?$tabControl->End();?>
</form>
<?endif;

echo BeginNote();
echo GetMessage("LDAP_OPTIONS_USE_NTLM_MSG");
echo EndNote();

?>
