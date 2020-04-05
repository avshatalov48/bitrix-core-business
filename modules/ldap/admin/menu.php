<?
IncludeModuleLangFile(__FILE__);
$MOD_RIGHT = $APPLICATION->GetGroupRight("ldap");
if($MOD_RIGHT!="D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_settings",
		"section" => "ldap",
		"sort" => 400,
		"text" => "AD/LDAP",
		"title" => GetMessage("LDAP_MENU_SERVERS_ALT"),
		"icon" => "ldap_menu_icon",
		"page_icon" => "ldap_page_icon",
		"items_id" => "menu_ldap",
		"url" => "/bitrix/admin/ldap_server_admin.php?lang=".LANG,
		"more_url" => Array("/bitrix/admin/ldap_server_edit.php"),
	);
	return $aMenu;
}
return false;
?>
