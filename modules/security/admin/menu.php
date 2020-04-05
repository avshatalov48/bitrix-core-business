<?
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/prolog.php");

$aMenu = array(
	"parent_menu" => "global_menu_settings",
	"section" => "security",
	"sort" => 210,
	"text" => GetMessage("SEC_MENU_ITEM"),
	"title" => GetMessage("SEC_MENU_TITLE"),
	"icon" => "security_menu_icon",
	"page_icon" => "security_page_icon",
	"items_id" => "menu_security",
	"items" => array(),
);

/** @global CUser $USER */
if($USER->isAdmin())
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_SCANNER_ITEM"),
		"url" => "security_scanner.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_scanner.php"),
		"title" => GetMessage("SEC_MENU_SCANNER_TITLE"),
	);
}

if($USER->CanDoOperation('security_panel_view'))
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_PANEL_ITEM"),
		"url" => "security_panel.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_panel.php"),
		"title" => GetMessage("SEC_MENU_PANEL_TITLE"),
	);
}

if(
	$USER->CanDoOperation('security_filter_settings_read')
	|| $USER->CanDoOperation('security_filter_settings_write')
)
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_FILTER_ITEM"),
		"url" => "security_filter.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_filter.php"),
		"title" => GetMessage("SEC_MENU_FILTER_TITLE"),
	);
}

if(
	$USER->CanDoOperation('security_antivirus_settings_read')
	|| $USER->CanDoOperation('security_antivirus_settings_write')
)
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_ANTIVIRUS_ITEM"),
		"url" => "security_antivirus.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_antivirus.php"),
		"title" => GetMessage("SEC_MENU_ANTIVIRUS_TITLE"),
	);
}

if($USER->CanDoOperation('view_event_log'))
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_FILTER_LOG_ITEM"),
		"url" => "/bitrix/admin/event_log.php?lang=".LANGUAGE_ID."&set_filter=Y&find_type=audit_type_id&find_audit_type[]=SECURITY_VIRUS&find_audit_type[]=SECURITY_FILTER_SQL&find_audit_type[]=SECURITY_FILTER_XSS&find_audit_type[]=SECURITY_FILTER_XSS2&find_audit_type[]=SECURITY_FILTER_PHP&find_audit_type[]=SECURITY_REDIRECT&find_audit_type[]=SECURITY_HOST_RESTRICTION&mod=security",
		"more_url" => Array("event_log.php?find_type=audit_type_id&mod=security"),
		"title" => GetMessage("SEC_MENU_FILTER_LOG_TITLE"),
	);
}

if(
	$USER->CanDoOperation('security_otp_settings_read')
	|| $USER->CanDoOperation('security_otp_settings_write')
)
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_OTP_NEW_ITEM"),
		"url" => "/bitrix/admin/security_otp.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_otp.php"),
		"title" => GetMessage("SEC_MENU_OTP_NEW_ITEM_TITLE"),
	);
}

if(
	$USER->CanDoOperation('security_file_verifier_sign')
	|| $USER->CanDoOperation('security_file_verifier_collect')
	|| $USER->CanDoOperation('security_file_verifier_verify')
)
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_FILE_ITEM"),
		"url" => "security_file_verifier.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_file_verifier.php"),
		"title" => GetMessage("SEC_MENU_FILE_TITLE"),
	);
}

if(
	$USER->CanDoOperation('security_iprule_admin_settings_read')
	|| $USER->CanDoOperation('security_iprule_admin_settings_write')
)
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_ADMINIP_ITEM"),
		"url" => "security_iprule_admin.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_iprule_admin.php"),
		"title" => GetMessage("SEC_MENU_ADMINIP_TITLE"),
	);
}

if(
	$USER->CanDoOperation('security_session_settings_read')
	|| $USER->CanDoOperation('security_session_settings_write')
)
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_SESSION_ITEM"),
		"url" => "security_session.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_session.php"),
		"title" => GetMessage("SEC_MENU_SESSION_TITLE"),
	);
}

if(
	$USER->CanDoOperation('security_redirect_settings_read')
	|| $USER->CanDoOperation('security_redirect_settings_write')
)
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_REDIRECT_ITEM"),
		"url" => "security_redirect.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_redirect.php"),
		"title" => GetMessage("SEC_MENU_REDIRECT_TITLE"),
	);
}

if(
	$USER->CanDoOperation('security_frame_settings_read')
	|| $USER->CanDoOperation('security_frame_settings_write')
)
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_FRAME_ITEM"),
		"url" => "security_frame.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_frame.php"),
		"title" => GetMessage("SEC_MENU_FRAME_TITLE"),
	);
}

if(CModule::IncludeModule('statistic') && (
	$USER->CanDoOperation('security_stat_activity_settings_read')
	|| $USER->CanDoOperation('security_stat_activity_settings_write')
))
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_ACTIVITY_ITEM"),
		"url" => "security_stat_activity.php?lang=".LANGUAGE_ID,
		"more_url" => Array("security_stat_activity.php"),
		"title" => GetMessage("SEC_MENU_ACTIVITY_TITLE"),
	);
}

if(
	$USER->CanDoOperation('security_iprule_settings_read')
	|| $USER->CanDoOperation('security_iprule_settings_write')
)
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_IP_ITEM"),
		"url" => "security_iprule_list.php?lang=".LANGUAGE_ID."&find_rule_type=M",
		"more_url" => Array("security_iprule_list.php", "security_iprule_edit.php"),
		"title" => GetMessage("SEC_MENU_IP_TITLE"),
	);
}

if($USER->isAdmin())
{
	$aMenu["items"][] = array(
		"text" => GetMessage("SEC_MENU_HOSTS_ITEM"),
		"url" => "security_hosts.php?lang=".LANGUAGE_ID."&find_rule_type=M",
		"more_url" => Array("security_hosts.php"),
		"title" => GetMessage("SEC_MENU_HOSTS_TITLE"),
	);
}

if(LANGUAGE_ID == "ru" && $USER->IsAdmin() && !IsModuleInstalled("intranet"))
{
	$aMenu1 = array($aMenu);
	$aMenu1[] = array(
		"parent_menu" => "global_menu_settings",
		"section" => "security_ddos",
		"sort" => 211,
		"text" => GetMessage("SEC_MENU_DDOS_ITEM"),
		"title" => GetMessage("SEC_MENU_DDOS_TITLE"),
		"icon" => "security_menu_ddos_icon",
		"page_icon" => "",
		"items_id" => "menu_security_ddos",
		"items" => array(),
		"url" => "security_ddos.php?lang=".LANGUAGE_ID,
		"more_url" => "security_ddos.php",
	);
	$aMenu = $aMenu1;
}

if((isset($aMenu["items"]) && count($aMenu["items"]) > 0) || (isset($aMenu[0]["items"]) && count($aMenu[0]["items"]) > 0))
	return $aMenu;
else
	return false;
?>