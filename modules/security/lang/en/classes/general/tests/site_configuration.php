<?
$MESS["SECURITY_SITE_CHECKER_SiteConfigurationTest_NAME"] = "Website configuration test";
$MESS["SECURITY_SITE_CHECKER_WAF_OFF"] = "Proactive Filter is disabled.";
$MESS["SECURITY_SITE_CHECKER_WAF_OFF_DETAIL"] = "Disabled Proactive Filter will definitely not help your website.";
$MESS["SECURITY_SITE_CHECKER_WAF_OFF_RECOMMENDATION"] = "Enable Proactive Filter: <a href=\"/bitrix/admin/security_filter.php\" target=\"_blank\">Enable</a>";
$MESS["SECURITY_SITE_CHECKER_REDIRECT_OFF"] = "Redirect protection disabled";
$MESS["SECURITY_SITE_CHECKER_REDIRECT_OFF_DETAIL"] = "A redirect to an arbitrary third-party website may cause attacks of various sorts. Enable redirect protection to make your website safe (when using the standard API).";
$MESS["SECURITY_SITE_CHECKER_REDIRECT_OFF_RECOMMENDATION"] = "Enable redirect protection <a href=\"/bitrix/admin/security_redirect.php\" target=\"_blank\">here</a>.";
$MESS["SECURITY_SITE_CHECKER_ADMIN_SECURITY_LEVEL"] = "The Administrator's user group security level is not elevated.";
$MESS["SECURITY_SITE_CHECKER_ADMIN_SECURITY_LEVEL_DETAIL"] = "Decreased security level may be possibly used by an attacker.";
$MESS["SECURITY_SITE_CHECKER_ADMIN_SECURITY_LEVEL_RECOMMENDATION"] = "Elevate admin user group security level";
$MESS["SECURITY_SITE_CHECKER_ERROR_REPORTING"] = "Warning level should be set to \"errors only\" or \"none\"";
$MESS["SECURITY_SITE_CHECKER_ERROR_REPORTING_DETAIL"] = "PHP warnings may display the full physical path to your web project.";
$MESS["SECURITY_SITE_CHECKER_ERROR_REPORTING_RECOMMENDATION"] = "Change warning level to \"none\" in the <a href=\"/bitrix/admin/settings.php?mid=main\" target=\"_blank\">Kernel settings</a>";
$MESS["SECURITY_SITE_CHECKER_DB_DEBUG"] = "SQL query debugging is on (\$DBDebug is true)";
$MESS["SECURITY_SITE_CHECKER_DB_DEBUG_DETAIL"] = "SQL debug info may disclose sensitive information.";
$MESS["SECURITY_SITE_CHECKER_DB_DEBUG_RECOMMENDATION"] = "Disable by setting \$DBDebug to false.";
$MESS["SECURITY_SITE_CHECKER_DB_EMPTY_PASS"] = "The database password is empty";
$MESS["SECURITY_SITE_CHECKER_DB_EMPTY_PASS_DETAIL"] = "An empty database password is one of the the ways to hack a database user account.";
$MESS["SECURITY_SITE_CHECKER_DB_EMPTY_PASS_RECOMMENDATION"] = "Set password";
$MESS["SECURITY_SITE_CHECKER_DB_SAME_REGISTER_PASS"] = "The database password includes characters in only lower or upper case.";
$MESS["SECURITY_SITE_CHECKER_DB_SAME_REGISTER_PASS_DETAIL"] = "The password is too weak. Your database user account is vulnerable.";
$MESS["SECURITY_SITE_CHECKER_DB_SAME_REGISTER_PASS_RECOMMENDATION"] = "Use lower and upper characters in the password.";
$MESS["SECURITY_SITE_CHECKER_DB_NO_DIT_PASS"] = "The database password does not include numbers";
$MESS["SECURITY_SITE_CHECKER_DB_NO_DIT_PASS_DETAIL"] = "The password is too weak. Your database user account is vulnerable.";
$MESS["SECURITY_SITE_CHECKER_DB_NO_DIT_PASS_RECOMMENDATION"] = "Add numbers to the password.";
$MESS["SECURITY_SITE_CHECKER_DB_NO_SIGN_PASS"] = "The database password does not include punctuation characters.";
$MESS["SECURITY_SITE_CHECKER_DB_NO_SIGN_PASS_DETAIL"] = "The password is too weak. Your database user account is vulnerable.";
$MESS["SECURITY_SITE_CHECKER_DB_NO_SIGN_PASS_RECOMMENDATION"] = "Add punctuation marks to the password.";
$MESS["SECURITY_SITE_CHECKER_DB_MIN_LEN_PASS"] = "The database password is shorter than 8 characters.";
$MESS["SECURITY_SITE_CHECKER_DB_MIN_LEN_PASS_DETAIL"] = "The password is too weak. Your database user account is vulnerable.";
$MESS["SECURITY_SITE_CHECKER_DB_MIN_LEN_PASS_RECOMMENDATION"] = "Make the password longer.";
$MESS["SECURITY_SITE_CHECKER_DANGER_EXTENSIONS"] = "The list of potentially dangerous file extensions is incomplete";
$MESS["SECURITY_SITE_CHECKER_DANGER_EXTENSIONS_DETAIL"] = "The current list of potentially dangerous file extensions does not include all of the recommended values. Keep this list up-to-date at all times.";
$MESS["SECURITY_SITE_CHECKER_DANGER_EXTENSIONS_RECOMMENDATION"] = "Edit the file extension list on the website settings page: <a href=\"/bitrix/admin/settings.php?mid=fileman\" target=\"_blank\">Site Explorer</a>.";
$MESS["SECURITY_SITE_CHECKER_DANGER_EXTENSIONS_ADDITIONAL"] = "Current: #ACTUAL#<br>
Recommended (excl. server side preferences): #EXPECTED#<br>
Missing: #MISSING#";
$MESS["SECURITY_SITE_CHECKER_EXCEPTION_DEBUG"] = "Extended error reporting is enabled";
$MESS["SECURITY_SITE_CHECKER_EXCEPTION_DEBUG_DETAIL"] = "Extended error reporting may disclose private information about your project.";
$MESS["SECURITY_SITE_CHECKER_EXCEPTION_DEBUG_RECOMMENDATION"] = "Disable extended reporting mode in .settings.php.";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION"] = "Outdated modules are still in use";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_DETAIL"] = "There are new versions available";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_RECOMMENDATION"] = "It is recommended that you update the modules once the new version is available: <a href=\"/bitrix/admin/update_system.php\" target=\"_blank\">Platform Update</a>";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_ERROR"] = "Cannot check for platform updates";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_ERROR_DETAIL"] = "An update for SiteUpdate may be available, or your update period has expired.";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_ERROR_RECOMMENDATION"] = "See details at the <a href=\"/bitrix/admin/update_system.php\" target=\"_blank\">platform update page</a>.";
$MESS["SECURITY_SITE_CHECKER_MODULES_VERSION_ARRITIONAL"] = "Updates are available for:<br>#MODULES#";
?>