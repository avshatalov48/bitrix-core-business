<?php

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Session\SessionConfigurationResolver;

define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');
IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/
if(!$USER->CanDoOperation('security_panel_view'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$strError = "";

$data = array(
	"scanner" => array(
		"NAME" => GetMessage("SEC_PANEL_SCANNER_NAME"),
		"TITLE" => GetMessage("SEC_PANEL_SCANNER_TITLE"),
		"HEADERS" => array(
			array(
				"id" => "KPI_NAME",
				"content" => GetMessage("SEC_PANEL_HEADERS_NAME"),
				"default" => true,
			),
			array(
				"id" => "KPI_VALUE",
				"content" => GetMessage("SEC_PANEL_HEADERS_VALUE"),
				"align" => "left",
				"default" => true,
			),
			array(
				"id" => "KPI_RECOMMENDATION",
				"content" => GetMessage("SEC_PANEL_HEADERS_RECOMMENDATION"),
				"default" => true,
			),
		),
		"ITEMS" => array(
		),
	),
	"std" => array(
		"NAME" => GetMessage("SEC_PANEL_STD_NAME"),
		"TITLE" => GetMessage("SEC_PANEL_STD_TITLE"),
		"HEADERS" => array(
			array(
				"id" => "KPI_NAME",
				"content" => GetMessage("SEC_PANEL_HEADERS_NAME"),
				"default" => true,
			),
			array(
				"id" => "KPI_VALUE",
				"content" => GetMessage("SEC_PANEL_HEADERS_VALUE"),
				"align" => "left",
				"default" => true,
			),
			array(
				"id" => "KPI_RECOMMENDATION",
				"content" => GetMessage("SEC_PANEL_HEADERS_RECOMMENDATION"),
				"default" => true,
			),
		),
		"ITEMS" => array(
		),
	),
	"high" => array(
		"NAME" => GetMessage("SEC_PANEL_HIGH_NAME"),
		"TITLE" => GetMessage("SEC_PANEL_HIGH_TITLE"),
		"HEADERS" => array(
			array(
				"id" => "KPI_NAME",
				"content" => GetMessage("SEC_PANEL_HEADERS_NAME"),
				"default" => true,
			),
			array(
				"id" => "KPI_VALUE",
				"content" => GetMessage("SEC_PANEL_HEADERS_VALUE"),
				"align" => "left",
				"default" => true,
			),
			array(
				"id" => "KPI_RECOMMENDATION",
				"content" => GetMessage("SEC_PANEL_HEADERS_RECOMMENDATION"),
				"default" => true,
			),
		),
		"ITEMS" => array(
		),
	),
	"very_high" => array(
		"NAME" => GetMessage("SEC_PANEL_VERY_HIGH_NAME"),
		"TITLE" => GetMessage("SEC_PANEL_VERY_HIGH_TITLE"),
		"HEADERS" => array(
			array(
				"id" => "KPI_NAME",
				"content" => GetMessage("SEC_PANEL_HEADERS_NAME"),
				"default" => true,
			),
			array(
				"id" => "KPI_VALUE",
				"content" => GetMessage("SEC_PANEL_HEADERS_VALUE"),
				"align" => "left",
				"default" => true,
			),
			array(
				"id" => "KPI_RECOMMENDATION",
				"content" => GetMessage("SEC_PANEL_HEADERS_RECOMMENDATION"),
				"default" => true,
			),
		),
		"ITEMS" => array(
		),
	),
);

$lastTestingInfo = CSecuritySiteChecker::getLastTestingInfo();
if(isset($lastTestingInfo["results"]))
{
	$lastResults = $lastTestingInfo["results"];
} else
{
	$lastResults = array();
}

if(!empty($lastResults))
{
	$criticalResultsCount = CSecuritySiteChecker::calculateCriticalResults($lastResults);
}
else
{
	$criticalResultsCount = 0;
}

if(isset($lastTestingInfo["test_date"]))
{
	$lastDate = $lastTestingInfo["test_date"];
}
else
{
	$lastDate = GetMessage("SEC_PANEL_SCANNER_NEVER_START");
}

$data['scanner']['ITEMS'][] = array(
	"KPI_NAME" => GetMessage("SEC_PANEL_SCANNER_LAST_SCAN"),
	"KPI_VALUE" => $lastDate,
	"KPI_RECOMMENDATION" => (
	!CSecuritySiteChecker::isNewTestNeeded()?
		'&nbsp;':
		(
		$USER->isAdmin()?
			'<a href="security_scanner.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_SCANNER_RUN").'</a>'
			:GetMessage("SEC_PANEL_SCANNER_RUN")
		)
	),
);

$data['scanner']['ITEMS'][] = array(
	"KPI_NAME" => GetMessage("SEC_PANEL_SCANNER_PROBLEM_COUNT"),
	"KPI_VALUE" => count($lastResults),
	"KPI_RECOMMENDATION" => (
	count($lastResults) <= 0 ?
		'&nbsp;':
		(
		$USER->isAdmin()?
			'<a href="security_scanner.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_SCANNER_FIX_IT").'</a>'
			:GetMessage("SEC_PANEL_SCANNER_FIX_IT")
		)
	),
);

$data['scanner']['ITEMS'][] = array(
	"KPI_NAME" => GetMessage("SEC_PANEL_SCANNER_CRITICAL_PROBLEM_COUNT"),
	"KPI_VALUE" => $criticalResultsCount,
	"KPI_RECOMMENDATION" => (
	$criticalResultsCount <= 0 ?
		'&nbsp;':
		(
		$USER->isAdmin()?
			'<a href="security_scanner.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_SCANNER_FIX_IT").'</a>'
			:GetMessage("SEC_PANEL_SCANNER_FIX_IT")
		)
	),
);
unset($lastTestingInfo);
unset($lastResults);
unset($criticalResultsCount);



$bSecurityFilter = CSecurityFilter::IsActive();

$data['std']['ITEMS'][] = array(
	"IS_OK" => $bSecurityFilter,
	"KPI_NAME" => GetMessage("SEC_PANEL_FILTER_NAME"),
	"KPI_VALUE" => ($bSecurityFilter? GetMessage("SEC_PANEL_FILTER_VALUE_ON"): GetMessage("SEC_PANEL_FILTER_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		$bSecurityFilter?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_filter_settings_write')?
			'<a href="security_filter.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_FILTER_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_FILTER_RECOMMENDATION")
		)
	),
);

$rsSecurityFilterExclMask = CSecurityFilterMask::GetList();
if($rsSecurityFilterExclMask->Fetch())
	$bSecurityFilterExcl = true;
else
	$bSecurityFilterExcl = false;

$data['std']['ITEMS'][] = array(
	"IS_OK" => !$bSecurityFilterExcl,
	"KPI_NAME" => GetMessage("SEC_PANEL_FILTER_EXCL_NAME"),
	"KPI_VALUE" => ($bSecurityFilterExcl? GetMessage("SEC_PANEL_FILTER_EXCL_VALUE_ON"): GetMessage("SEC_PANEL_FILTER_EXCL_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		!$bSecurityFilterExcl?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_filter_settings_write')?
			'<a href="security_filter.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'&amp;tabControl_active_tab=exceptions">'.GetMessage("SEC_PANEL_FILTER_EXCL_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_FILTER_EXCL_RECOMMENDATION")
		)
	),
);

$days = COption::GetOptionInt("main", "event_log_cleanup_days", 7);
if($days > 7)
	$days = 7;
$cntLog = 0;
$rsLog = CEventLog::GetList(array(), array(
	"TIMESTAMP_X_1" => ConvertTimeStamp(time()-$days*24*3600+CTimeZone::GetOffset(), "FULL"),
	"AUDIT_TYPE_ID" => "SECURITY_FILTER_SQL|SECURITY_FILTER_XSS|SECURITY_FILTER_XSS2|SECURITY_FILTER_PHP|SECURITY_REDIRECT",
	),
	array("nPageSize" => 1)
);
$cntLog = $rsLog->NavRecordCount;

$data['std']['ITEMS'][] = array(
	"IS_OK" => true,
	"KPI_NAME" => GetMessage("SEC_PANEL_FILTER_LOG_NAME", array("#DAYS#" => $days)),
	"KPI_VALUE" => $cntLog,
	"KPI_RECOMMENDATION" => (
		$cntLog?
		(
			$USER->CanDoOperation('view_event_log')?
			'<a href="event_log.php?lang='.LANGUAGE_ID.'&amp;set_filter=Y&amp;find_type=audit_type_id&amp;find_audit_type[]=SECURITY_FILTER_SQL&amp;find_audit_type[]=SECURITY_FILTER_XSS&amp;find_audit_type[]=SECURITY_FILTER_XSS2&amp;find_audit_type[]=SECURITY_FILTER_PHP&amp;find_audit_type[]=SECURITY_REDIRECT&amp;mod=security">'.GetMessage("SEC_PANEL_FILTER_LOG_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_FILTER_LOG_RECOMMENDATION")
		):
		'&nbsp;'
	),
);

$bStatistic = CModule::IncludeModule('statistic');
if($bStatistic)
{
	$bActivity = COption::GetOptionString("statistic", "DEFENCE_ON") == "Y";
	$data['std']['ITEMS'][] = array(
		"IS_OK" => $bActivity,
		"KPI_NAME" => GetMessage("SEC_PANEL_ACTIVITY_NAME"),
		"KPI_VALUE" => ($bActivity? GetMessage("SEC_PANEL_ACTIVITY_VALUE_ON"): GetMessage("SEC_PANEL_ACTIVITY_VALUE_OFF")),
		"KPI_RECOMMENDATION" => (
			$bActivity?
			'&nbsp;':
			(
				$USER->CanDoOperation('security_stat_activity_settings_write')?
				'<a href="security_stat_activity.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_ACTIVITY_RECOMMENDATION").'</a>'
				:GetMessage("SEC_PANEL_ACTIVITY_RECOMMENDATION")
			)
		),
	);
}

$level = \CCheckListTools::AdminPolicyLevel();

$data['std']['ITEMS'][] = array(
	"IS_OK" => $level == "high",
	"KPI_NAME" => GetMessage("SEC_PANEL_ADM_GROUP_NAME"),
	"KPI_VALUE" => ($level == "high"? GetMessage("SEC_PANEL_ADM_GROUP_VALUE_HIGH"): ($level == "middle"? GetMessage("SEC_PANEL_ADM_GROUP_VALUE_MIDDLE"): GetMessage("SEC_PANEL_ADM_GROUP_VALUE_LOW"))),
	"KPI_RECOMMENDATION" => (
		$level == "high"?
		'&nbsp;':
		(
			$USER->CanDoOperation('edit_groups')?
			'<a href="group_edit.php?lang='.LANGUAGE_ID.'&amp;ID=1&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'&amp;tabControl_active_tab=edit2">'.GetMessage("SEC_PANEL_ADM_GROUP_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_ADM_GROUP_RECOMMENDATION")
		)
	),
);

$bCAPTCHA = COption::GetOptionString("main", "captcha_registration", "N") == "Y";

$data['std']['ITEMS'][] = array(
	"IS_OK" => $bCAPTCHA,
	"KPI_NAME" => GetMessage("SEC_PANEL_CAPTCHA_NAME"),
	"KPI_VALUE" => ($bCAPTCHA? GetMessage("SEC_PANEL_CAPTCHA_VALUE_ON"): GetMessage("SEC_PANEL_CAPTCHA_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		$bCAPTCHA?
		'&nbsp;':
		(
			$USER->CanDoOperation('edit_other_settings')?
			'<a href="settings.php?lang='.LANGUAGE_ID.'&amp;mid=main&amp;back_url_settings='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'&amp;tabControl_active_tab=edit6">'.GetMessage("SEC_PANEL_CAPTCHA_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_CAPTCHA_RECOMMENDATION")
		)
	),
);

$reporting_level = COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);
if($reporting_level == (E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE))
	$error_level = GetMessage("SEC_PANEL_ERROR1");
elseif($reporting_level == (E_ALL^E_NOTICE))
	$error_level = GetMessage("SEC_PANEL_ERROR2");
elseif($reporting_level == 0)
	$error_level = GetMessage("SEC_PANEL_ERROR3");
else
	$error_level = GetMessage("SEC_PANEL_ERROR4");

$data['std']['ITEMS'][] = array(
	"IS_OK" => $error_level == GetMessage("SEC_PANEL_ERROR1") || $error_level == GetMessage("SEC_PANEL_ERROR3"),
	"KPI_NAME" => GetMessage("SEC_PANEL_ERROR_NAME"),
	"KPI_VALUE" => $error_level,
	"KPI_RECOMMENDATION" => (
		$error_level == GetMessage("SEC_PANEL_ERROR1") || $error_level == GetMessage("SEC_PANEL_ERROR3")?
		'&nbsp;':
		(
			$USER->CanDoOperation('edit_other_settings')?
			'<a href="settings.php?lang='.LANGUAGE_ID.'&amp;mid=main&amp;back_url_settings='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_ERROR1").'</a>'
			:GetMessage("SEC_PANEL_ERROR1")
		)
	),
);

global $DB;
$data['std']['ITEMS'][] = array(
	"IS_OK" => !$DB->debug,
	"KPI_NAME" => GetMessage("SEC_PANEL_QUERY_DEBUG"),
	"KPI_VALUE" => ($DB->debug? GetMessage("SEC_PANEL_QUERY_DEBUG_VALUE_ON"): GetMessage("SEC_PANEL_QUERY_DEBUG_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		!$DB->debug?
		'&nbsp;':
		(IsModuleInstalled('fileman') && ($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files'))?
			GetMessage("SEC_PANEL_QUERY_DEBUG_RECOMMENDATION_WITH_HREF", array(
				"#HREF#" => '/bitrix/admin/fileman_file_edit.php?lang='.LANGUAGE_ID.'&amp;full_src=Y&amp;path='.urlencode(BX_PERSONAL_ROOT.'/php_interface/dbconn.php').'&amp;back_url='.urlencode('/bitrix/admin/security_panel.php?lang='.LANGUAGE_ID),
			)):
			GetMessage("SEC_PANEL_QUERY_DEBUG_RECOMMENDATION_WO_HREF")
		)
	),
);

$bEventLog = COption::GetOptionString("main", "event_log_logout", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_login_success", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_login_fail", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_register", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_register_fail", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_password_request", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_password_change", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_user_delete", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_user_groups", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_group_policy", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_module_access", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_file_access", "N") === "Y"
	&& COption::GetOptionString("main", "event_log_task", "N") === "Y"
;

$data['high']['ITEMS'][] = array(
	"IS_OK" => $bEventLog,
	"KPI_NAME" => GetMessage("SEC_PANEL_EVENT_LOG_NAME"),
	"KPI_VALUE" => ($bEventLog? GetMessage("SEC_PANEL_EVENT_LOG_VALUE_ON"): GetMessage("SEC_PANEL_EVENT_LOG_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		$bEventLog?
		'&nbsp;':
		(
			$USER->CanDoOperation('edit_other_settings')?
			'<a href="settings.php?lang='.LANGUAGE_ID.'&amp;mid=main&amp;back_url_settings='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'&amp;tabControl_active_tab=edit8">'.GetMessage("SEC_PANEL_EVENT_LOG_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_EVENT_LOG_RECOMMENDATION")
		)
	),
);

$bSecurityFrame = CSecurityFrame::IsActive();

$data['high']['ITEMS'][] = array(
	"IS_OK" => $bSecurityFrame,
	"KPI_NAME" => GetMessage("SEC_PANEL_FRAME_NAME"),
	"KPI_VALUE" => ($bSecurityFrame? GetMessage("SEC_PANEL_FRAME_VALUE_ON"): GetMessage("SEC_PANEL_FRAME_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		$bSecurityFrame?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_frame_settings_write')?
			'<a href="security_frame.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_FRAME_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_FRAME_RECOMMENDATION")
		)
	),
);

$rsIPRule = CSecurityIPRule::GetList(array(), array(
	"=RULE_TYPE" => "A",
	"=ADMIN_SECTION" => "Y",
	"=SITE_ID" => false,
	"=SORT" => 10,
	"=ACTIVE_FROM" => false,
	"=ACTIVE_TO" => false,
), array("ID" => "ASC"));
$arIPRule = $rsIPRule->Fetch();
if($arIPRule)
	$bIPProtection = $arIPRule["ACTIVE"] == "Y";
else
	$bIPProtection = false;

$msgStopListDisabled = CSecurityIPRule::CheckAntiFile(true);

$data['high']['ITEMS'][] = array(
	"IS_OK" => $bIPProtection && $msgStopListDisabled===false,
	"KPI_NAME" => GetMessage("SEC_PANEL_IPBLOCK_NAME"),
	"KPI_VALUE" => ($bIPProtection && $msgStopListDisabled===false? GetMessage("SEC_PANEL_IPBLOCK_VALUE_ON"): GetMessage("SEC_PANEL_IPBLOCK_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		$bIPProtection?
		($msgStopListDisabled===false? '&nbsp;': $msgStopListDisabled->Show()):
		(
			$USER->CanDoOperation('security_iprule_admin_settings_write')?
			'<a href="security_iprule_admin.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_IPBLOCK_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_IPBLOCK_RECOMMENDATION")
		)
	),
);

$resolver = new SessionConfigurationResolver(Configuration::getInstance());
$sessionConfig = $resolver->getSessionConfig();
$generalHandlerType = $sessionConfig['handlers']['general']['type'] ?? null;
$sessionInFiles = $generalHandlerType === SessionConfigurationResolver::TYPE_FILE;

$bSessionsDB = COption::GetOptionString("security", "session") == "Y";

$data['high']['ITEMS'][] = array(
	"IS_OK" => !$sessionInFiles,
	"KPI_NAME" => GetMessage("SEC_PANEL_SESS_STORAGE_NAME"),
	"KPI_VALUE" => (!$sessionInFiles? GetMessage("SEC_PANEL_SESSDB_VALUE_ON"): GetMessage("SEC_PANEL_SESSDB_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		!$sessionInFiles?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_session_settings_write')?
			'<a href="security_session.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'&amp;tabControl_active_tab=savedb">'.GetMessage("SEC_PANEL_SESSDB_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_SESSDB_RECOMMENDATION")
		)
	),
);

$bSessionTTL = (COption::GetOptionString("main", "use_session_id_ttl", "N") == "Y")
	&& (COption::GetOptionInt("main", "session_id_ttl", 0) > 0)
;

$data['high']['ITEMS'][] = array(
	"IS_OK" => $bSessionTTL,
	"KPI_NAME" => GetMessage("SEC_PANEL_SESSID_NAME"),
	"KPI_VALUE" => ($bSessionTTL? GetMessage("SEC_PANEL_SESSID_VALUE_ON"): GetMessage("SEC_PANEL_SESSID_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		$bSessionTTL?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_session_settings_write')?
			'<a href="security_session.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'&amp;tabControl_active_tab=sessid">'.GetMessage("SEC_PANEL_SESSID_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_SESSID_RECOMMENDATION")
		)
	),
);

$bRedirect = CSecurityRedirect::IsActive();

$data['high']['ITEMS'][] = array(
	"IS_OK" => $bRedirect,
	"KPI_NAME" => GetMessage("SEC_PANEL_ANTIFISHING_NAME"),
	"KPI_VALUE" => ($bRedirect? GetMessage("SEC_PANEL_ANTIFISHING_VALUE_ON"): GetMessage("SEC_PANEL_ANTIFISHING_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		$bRedirect?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_redirect_settings_write')?
			'<a href="security_redirect.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_ANTIFISHING_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_ANTIFISHING_RECOMMENDATION")
		)
	),
);

$bOTP = CSecurityUser::isActive();

$data['very_high']['ITEMS'][] = array(
	"IS_OK" => $bOTP,
	"KPI_NAME" => GetMessage("SEC_PANEL_OTP_NAME"),
	"KPI_VALUE" => ($bOTP? GetMessage("SEC_PANEL_OTP_VALUE_ON"): GetMessage("SEC_PANEL_OTP_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		$bOTP?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_otp_settings_write')?
			'<a href="security_otp.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_OTP_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_OTP_RECOMMENDATION")
		)
	),
);

$timeFC = COption::GetOptionInt("security", "last_files_check", -1);

$data['very_high']['ITEMS'][] = array(
	"IS_OK" => ($timeFC > 1) && ((time()-$timeFC) < 7*24*3600),
	"KPI_NAME" => GetMessage("SEC_PANEL_FILES_NAME"),
	"KPI_VALUE" => ($timeFC < 0? GetMessage("SEC_PANEL_FILES_VALUE_NEVER"): ((time()-$timeFC) > 24*3600? GetMessage("SEC_PANEL_FILES_VALUE_LONGTIMEAGO"): GetMessage("SEC_PANEL_FILES_VALUE_ACTUAL"))),
	"KPI_RECOMMENDATION" => (
		($timeFC > 1) && ((time()-$timeFC) < 7*24*3600)?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_file_verifier_verify')?
			'<a href="security_file_verifier.php?lang='.LANGUAGE_ID.'">'.GetMessage("SEC_PANEL_FILES_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_FILES_RECOMMENDATION")
		)
	),
);

$bSecurityAV = CSecurityAntiVirus::IsActive();

$data['very_high']['ITEMS'][] = array(
	"IS_OK" => $bSecurityAV,
	"KPI_NAME" => GetMessage("SEC_PANEL_ANTIVIRUS_NAME"),
	"KPI_VALUE" => ($bSecurityAV? GetMessage("SEC_PANEL_ANTIVIRUS_VALUE_ON"): GetMessage("SEC_PANEL_ANTIVIRUS_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		$bSecurityAV?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_antivirus_settings_write')?
			'<a href="security_antivirus.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'">'.GetMessage("SEC_PANEL_ANTIVIRUS_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_ANTIVIRUS_RECOMMENDATION")
		)
	),
);

$strSecurityAVAction = COption::GetOptionString("security", "antivirus_action");

$data['very_high']['ITEMS'][] = array(
	"IS_OK" => $strSecurityAVAction !== "notify_only",
	"KPI_NAME" => GetMessage("SEC_PANEL_AV_ACTION_NAME"),
	"KPI_VALUE" => ($strSecurityAVAction === "notify_only"? GetMessage("SEC_PANEL_AV_ACTION_VALUE_NOTIFY"): GetMessage("SEC_PANEL_AV_ACTION_VALUE_ACT")),
	"KPI_RECOMMENDATION" => (
		$strSecurityAVAction !== "notify_only"?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_antivirus_settings_write')?
			'<a href="security_antivirus.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'&amp;tabControl_active_tab=params">'.GetMessage("SEC_PANEL_AV_ACTION_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_AV_ACTION_RECOMMENDATION")
		)
	),
);

$rsSecurityWhiteList = CSecurityAntiVirus::GetWhiteList();
if($rsSecurityWhiteList->Fetch())
	$bSecurityWhiteList = true;
else
	$bSecurityWhiteList = false;

$data['very_high']['ITEMS'][] = array(
	"IS_OK" => !$bSecurityWhiteList,
	"KPI_NAME" => GetMessage("SEC_PANEL_AV_WHITE_LIST_NAME"),
	"KPI_VALUE" => ($bSecurityWhiteList? GetMessage("SEC_PANEL_AV_WHITE_LIST_VALUE_ON"): GetMessage("SEC_PANEL_AV_WHITE_LIST_VALUE_OFF")),
	"KPI_RECOMMENDATION" => (
		!$bSecurityWhiteList?
		'&nbsp;':
		(
			$USER->CanDoOperation('security_antivirus_settings_write')?
			'<a href="security_antivirus.php?lang='.LANGUAGE_ID.'&amp;return_url='.urlencode('security_panel.php?lang='.LANGUAGE_ID).'&amp;tabControl_active_tab=exceptions">'.GetMessage("SEC_PANEL_AV_WHITE_LIST_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_AV_WHITE_LIST_RECOMMENDATION")
		)
	),
);

$days = COption::GetOptionInt("main", "event_log_cleanup_days", 7);
if($days > 7)
	$days = 7;
$cntLog = 0;
$rsLog = CEventLog::GetList(array(), array(
	"TIMESTAMP_X_1" => ConvertTimeStamp(time()-$days*24*3600+CTimeZone::GetOffset(), "FULL"),
	"AUDIT_TYPE_ID" => "SECURITY_VIRUS",
));
while($rsLog->Fetch())
	$cntLog++;

$data['very_high']['ITEMS'][] = array(
	"IS_OK" => true,
	"KPI_NAME" => GetMessage("SEC_PANEL_VIRUS_LOG_NAME", array("#DAYS#" => $days)),
	"KPI_VALUE" => $cntLog,
	"KPI_RECOMMENDATION" => (
		$cntLog?
		(
			$USER->CanDoOperation('view_event_log')?
			'<a href="event_log.php?lang='.LANGUAGE_ID.'&amp;set_filter=Y&amp;find_type=audit_type_id&amp;find_audit_type[]=SECURITY_VIRUS&amp;mod=security">'.GetMessage("SEC_PANEL_VIRUS_LOG_RECOMMENDATION").'</a>'
			:GetMessage("SEC_PANEL_VIRUS_LOG_RECOMMENDATION")
		):
		'&nbsp;'
	),
);

function CheckLevel($arItems)
{
	$result = true;
	foreach($arItems as $item)
	{
		if(!$item["IS_OK"])
		{
			$result = false;
			break;
		}
	}
	return $result;
}


$messageType = "OK";
if(CheckLevel($data["std"]["ITEMS"]))
{
	if(CheckLevel($data["high"]["ITEMS"]))
	{
		if(CheckLevel($data["very_high"]["ITEMS"]))
		{
			$SECURITY_LEVEL = $data["very_high"]["NAME"];
		}
		else
		{
			$SECURITY_LEVEL = $data["high"]["NAME"];
		}
	}
	else
	{
		$SECURITY_LEVEL = $data["std"]["NAME"];
	}
}
else
{
	$SECURITY_LEVEL = GetMessage("SEC_PANEL_NORMAL_NAME");
	$messageType = "ERROR";
}

$sTableID = "tbl_security_panel";

$APPLICATION->SetTitle(GetMessage("SEC_PANEL_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CAdminMessage::ShowMessage($strError);

CAdminMessage::ShowMessage(array(
			"MESSAGE"=>GetMessage("SEC_PANEL_CURRENT_LEVEL", array("#LEVEL_NAME#" => $SECURITY_LEVEL)),
			"TYPE"=>$messageType,
		));

if(count($data))
{
	foreach($data as $i => $arTable)
	{
		$lAdmin = new CAdminList($sTableID.$i);

		$lAdmin->BeginPrologContent();
		if(array_key_exists("TITLE", $arTable))
			echo "<h4>".$arTable["TITLE"]."</h4>\n";
		$lAdmin->EndPrologContent();

		$lAdmin->AddHeaders($arTable["HEADERS"]);

		$rsData = new CDBResult;
		$rsData->InitFromArray($arTable["ITEMS"]);
		$rsData = new CAdminResult($rsData, $sTableID.$i);

		$j = 0;
		while($arRes = $rsData->NavNext(true, "f_"))
		{
			$row =& $lAdmin->AddRow($j++, $arRes);
			foreach($arRes as $key => $value)
				$row->AddViewField($key, $value);
			//$row->AddViewField("KPI_RECOMMENDATION", $arRes["KPI_RECOMMENDATION"]);
		}
		$lAdmin->CheckListMode();
		$lAdmin->DisplayList();
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
