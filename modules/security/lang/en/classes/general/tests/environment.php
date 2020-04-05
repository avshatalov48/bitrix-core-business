<?
$MESS["SECURITY_SITE_CHECKER_EnvironmentTest_NAME"] = "Environment check";
$MESS["SECURITY_SITE_CHECKER_SESSION_DIR"] = "Session file storage directory is accessible by all system users";
$MESS["SECURITY_SITE_CHECKER_SESSION_DIR_DETAIL"] = "This vulnerability may be used to read or change session data running scripts on other virtual servers.";
$MESS["SECURITY_SITE_CHECKER_SESSION_DIR_RECOMMENDATION"] = "Configure access permissions correctly, or change the directory. Another option is to store sessions in database: <a href=\"/bitrix/admin/security_session.php\">Session protection</a>.";
$MESS["SECURITY_SITE_CHECKER_SESSION_DIR_ADDITIONAL"] = "Session storage directory: #DIR#<br>
Permission: #PERMS#";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION"] = "The session storage directory may contain sessions of different projects.";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION_DETAIL"] = "This may help an attacker read and write session data using scripts on other virtual servers.";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION_RECOMMENDATION"] = "Change the directory or store sessions in database: <a href=\"/bitrix/admin/security_session.php\">Session protection</a>.";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION_ADDITIONAL_OWNER"] = "Reason: file owner is not the current user<br>
File: #FILE#<br>
File owner UID: #FILE_ONWER#<br>
Current user UID: #CURRENT_OWNER#<br>";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION_ADDITIONAL_SIGN"] = "Reason: session file is not signed with current site's signature<br>
File: #FILE#<br>
Current site's signature: #SIGN#<br>
File contents: <pre>#FILE_CONTENT#</pre>";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP"] = "PHP scripts are executed in the uploaded files directory.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DETAIL"] = "Sometimes developers don't pay enough attention to proper file name filters. An attacker may exploit this vulnerability to take full control of your  project.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_RECOMMENDATION"] = "Configure your web server correctly.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DOUBLE"] = "PHP scripts with the double extension (e.g. php.lala) are executed in the uploaded files directory.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DOUBLE_DETAIL"] = "Sometimes developers don't pay enough attention to proper file name filters. An attacker may exploit this vulnerability to take full control of your  project.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DOUBLE_RECOMMENDATION"] = "Configure your web server correctly.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PY"] = "Python scripts are executed in the uploaded files directory.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PY_DETAIL"] = "Sometimes developers don't pay enough attention to proper file name filters. An attacker may exploit this vulnerability to take full control of your  project.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PY_RECOMMENDATION"] = "Configure your web server correctly.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_HTACCESS"] = "Apache must not process the .htaccess files in the uploaded files directory";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_HTACCESS_DETAIL"] = "Sometimes developers don't pay enough attention to proper file name filters. An attacker may exploit this vulnerability to take full control of your  project.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_HTACCESS_RECOMMENDATION"] = "Configure your web server correctly.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_NEGOTIATION"] = "Apache Content Negotiation is enabled in file upload directory.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_NEGOTIATION_DETAIL"] = "Apache Content Negotiation is not recommended because it may incur XSS attacks.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_NEGOTIATION_RECOMMENDATION"] = "Configure your web server correctly.";
$MESS["SECURITY_SITE_CHECKER_PHP_PRIVILEGED_USER"] = "PHP is running as a privileged user";
$MESS["SECURITY_SITE_CHECKER_PHP_PRIVILEGED_USER_DETAIL"] = "Running PHP as a privileged user (e.g. root) may compromise security of your project";
$MESS["SECURITY_SITE_CHECKER_PHP_PRIVILEGED_USER_RECOMMENDATION"] = "Configure your server in such a way that PHP runs as an unprivileged user";
$MESS["SECURITY_SITE_CHECKER_PHP_PRIVILEGED_USER_ADDITIONAL"] = "#UID#/#GID#";
$MESS["SECURITY_SITE_CHECKER_BITRIX_TMP_DIR"] = "Temporary files are kept in the project root directory";
$MESS["SECURITY_SITE_CHECKER_BITRIX_TMP_DIR_DETAIL"] = "Saving temporary files created by CTempFile to the root folder is not recommended.";
$MESS["SECURITY_SITE_CHECKER_BITRIX_TMP_DIR_RECOMMENDATION"] = "Define a constant \"BX_TEMPORARY_FILES_DIRECTORY\" in \"bitrix/php_interface/dbconn.php\" and specify a required path.<br>
Follow these steps:<br>
1. Pick a name for your temp directory and create it. For example, \"/home/bitrix/tmp/www\":
<pre>
mkdir -p -m 700 /home/bitrix/tmp/www
</pre>
2. Define the constant to let the system know you want to save temporary files to that folder:
<pre>
define(\"BX_TEMPORARY_FILES_DIRECTORY\", \"/home/bitrix/tmp/www\");
</pre>";
$MESS["SECURITY_SITE_CHECKER_BITRIX_TMP_DIR_ADDITIONAL"] = "Current directory: #DIR#";
?>