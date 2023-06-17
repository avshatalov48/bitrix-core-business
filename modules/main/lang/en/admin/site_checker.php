<?php
$MESS["ERR_MAX_INPUT_VARS"] = "The value of max_input_vars must be #MIN# or greater. The current value is: #CURRENT#";
$MESS["ERR_NO_MODS"] = "The required extensions are not installed:";
$MESS["ERR_NO_MODS_DOC_GENERATOR"] = "The Document Generator module requires php-xml and php-zip extensions.";
$MESS["ERR_NO_SSL"] = "SSL support is not enabled for PHP";
$MESS["ERR_NO_VM"] = "Bitrix24 is guaranteed to operate smoothly only on Bitrix Environment. You are using custom server environment.";
$MESS["ERR_OLD_VM"] = "You are running an outdated version of Bitrix Environment (#CURRENT#). Please install the most recent version to prevent configuration issues (#LAST_VERSION#).";
$MESS["MAIN_AGENTS_HITS"] = "The system agents are run on hits. Migrate the agents to cron.";
$MESS["MAIN_BX_CRONTAB_DEFINED"] = "Defined the BX_CRONTAB constant which can only be done in scripts running on cron.";
$MESS["MAIN_CATDOC_WARN"] = "Bad catdoc version: #VERSION#<br>
Details: https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=679877<br>
Install an earlier catdoc version or a newer version with fixes. ";
$MESS["MAIN_CRON_NO_START"] = "cron_events.php is not configured to run on cron; the most recent agent has been executed more than 24 hours ago.";
$MESS["MAIN_FAST_DOWNLOAD_ERROR"] = "nginx powered fast file downloads are unavailable however enabled in the Kernel settings.";
$MESS["MAIN_FAST_DOWNLOAD_SUPPORT"] = "nginx powered fast file downloads are available but disabled in the Kernel settings.";
$MESS["MAIN_IS_CORRECT"] = "Correct";
$MESS["MAIN_NO_OPTION_PULL"] = "The option that enables PUSH notification is unchecked in the Push and Pull module settings. Mobile devices will not receive PUSH notifications.";
$MESS["MAIN_NO_PULL"] = "The Push and Pull module is not installed.";
$MESS["MAIN_NO_PULL_MODULE"] = "The Push and Pull module is not installed. Mobile devices will not receive PUSH notifications.";
$MESS["MAIN_PAGES_PER_SECOND"] = "pages per second";
$MESS["MAIN_PERF_HIGH"] = "High";
$MESS["MAIN_PERF_LOW"] = "Low";
$MESS["MAIN_PERF_MID"] = "Average";
$MESS["MAIN_PERF_VERY_LOW"] = "Unacceptably low";
$MESS["MAIN_SC_ABS"] = "None";
$MESS["MAIN_SC_ABSENT_ALL"] = "None";
$MESS["MAIN_SC_AGENTS_CRON"] = "Use cron to run agents";
$MESS["MAIN_SC_ALL_FUNCS_TESTED"] = "All Intranet features have been checked and found to be in order.";
$MESS["MAIN_SC_ALL_MODULES"] = "All required modules are installed.";
$MESS["MAIN_SC_AVAIL"] = "Available";
$MESS["MAIN_SC_BUSINESS"] = "Intranet business features";
$MESS["MAIN_SC_CANT_CHANGE"] = "Unable to modify the value of pcre.backtrack_limit using ini_set.";
$MESS["MAIN_SC_CLOUD_TEST"] = "Access to Bitrix cloud services";
$MESS["MAIN_SC_COMPRESSION_TEST"] = "Page compression and acceleration";
$MESS["MAIN_SC_COMP_DISABLED"] = "The server doesn't support compression, using the Bitrix Compression module instead (PHP)";
$MESS["MAIN_SC_COMP_DISABLED_MOD"] = "The server doesn't support compression, the compression module disabled";
$MESS["MAIN_SC_CORRECT"] = "Correct";
$MESS["MAIN_SC_CORRECT_DESC"] = "Intranet requires special configuration of the server environment. <a href=\"http://www.bitrixsoft.com/products/virtual_appliance/\" target=\"_blank\">Bitrix Virtual Appliance</a> is configured properly out-of-the-box. Some features may be unavailable if you fail to adjust the required parameters.";
$MESS["MAIN_SC_CORRECT_SETTINGS"] = "Settings are correct";
$MESS["MAIN_SC_DEFAULT_CHARSET"] = "The default_charset parameter must not be empty.";
$MESS["MAIN_SC_DOCS_EDIT_MS_OFFICE"] = "Editing documents in Microsoft Office";
$MESS["MAIN_SC_ENABLED"] = "The server supports compression, the Bitrix Compression module needs to be uninstalled";
$MESS["MAIN_SC_ENABLED_MOD"] = "Using the web server module for compression";
$MESS["MAIN_SC_ENC_EQUAL"] = "The mbstring.internal_encoding and default_charset values do not match. It is recommended to clear mbstring.internal_encoding and set default_charset values.";
$MESS["MAIN_SC_ENC_NON_UTF"] = "The value of default_charset must be set to an encoding other than UTF-8.";
$MESS["MAIN_SC_ENC_UTF"] = "The value of default_charset must be set to UTF-8.";
$MESS["MAIN_SC_ERROR_PRECISION"] = "The \"precision\" parameter value is invalid.";
$MESS["MAIN_SC_EXTERNAL_ANSWER_INCORRECT"] = "External connection to the Intranet was a success, but the server returned an incorrect status.";
$MESS["MAIN_SC_EXTERNAL_APPS_TEST"] = "Applications (MS Office, Outlook, Exchange) via secure connection";
$MESS["MAIN_SC_EXTERNAL_CALLS"] = "External video calls";
$MESS["MAIN_SC_EXTRANET_ACCESS"] = "External access to Extranet";
$MESS["MAIN_SC_FAST_FILES_TEST"] = "Fast file and document access";
$MESS["MAIN_SC_FULL_TEST_DESC"] = "Run full system check to find weak spots and fix website issues or to avoid problems in the future. Short but comprehensive descriptions provided for each of the tests will help you get to the root of the problem and fix it.";
$MESS["MAIN_SC_FUNC_OVERLOAD"] = "Legacy parameter mbstring.func_overload delected. Please delete it.";
$MESS["MAIN_SC_FUNC_WORKS_FINE"] = "The feature is OK";
$MESS["MAIN_SC_FUNC_WORKS_PARTIAL"] = "The feature may have issues; check for and fix them.";
$MESS["MAIN_SC_FUNC_WORKS_WRONG"] = "The feature is out of order. Fix errors.";
$MESS["MAIN_SC_GENERAL"] = "General intranet features";
$MESS["MAIN_SC_GENERAL_SITE"] = "General website features";
$MESS["MAIN_SC_GOT_ERRORS"] = "Intranet has errors. <a href=\"#LINK#\">Check and repair</a>";
$MESS["MAIN_SC_MAIL_INTEGRATION"] = "External e-mail account integration is OK, but none of the users configured integration settings.";
$MESS["MAIN_SC_MAIL_IS_NOT_INSTALLED"] = "The Mail module is not installed.";
$MESS["MAIN_SC_MAIL_TEST"] = "E-mail notifications";
$MESS["MAIN_SC_MBSTRING_SETTIGNS_DIFFER"] = "mbstring preferences in <i>/bitrix/.settings.php</i> (utf_mode) and <i>/bitrix/php_interface/dbconn.php</i> (BX_UTF) are different.";
$MESS["MAIN_SC_MCRYPT"] = "Encryption features";
$MESS["MAIN_SC_METHOD_NOT_SUP"] = "The server does not support the method #METHOD#.";
$MESS["MAIN_SC_NOT_AVAIL"] = "Unavailable";
$MESS["MAIN_SC_NOT_SUPPORTED"] = "Server does not support this feature.";
$MESS["MAIN_SC_NO_ACCESS"] = "Cannot access Bitrix24 server. Updates and Bitrix Cloud Service are unavailable.";
$MESS["MAIN_SC_NO_CONFLICT"] = "No conflicts.";
$MESS["MAIN_SC_NO_CONNECTTO"] = "Cannot connect to #HOST#";
$MESS["MAIN_SC_NO_EXTERNAL_ACCESS_"] = "This feature is unavailable because the Intranet is externally inaccessible.";
$MESS["MAIN_SC_NO_EXTERNAL_ACCESS_MOB"] = "This feature is unavailable because the Intranet is externally inaccessible from the mobile application.";
$MESS["MAIN_SC_NO_EXTERNAL_CONNECT_WARN"] = "Cannot connect to the Intranet externally. The mobile application will not function.";
$MESS["MAIN_SC_NO_EXTRANET_CONNECT"] = "The Extranet does not function properly because the Intranet is externally inaccessible via the Internet.";
$MESS["MAIN_SC_NO_IM"] = "The Web Messenger module is not installed.";
$MESS["MAIN_SC_NO_LDAP_INTEGRATION"] = "AD server integration is not configured.";
$MESS["MAIN_SC_NO_LDAP_MODULE"] = "The LDAP module is not installed.";
$MESS["MAIN_SC_NO_NTLM"] = "Current connection does not use NTLM authentication";
$MESS["MAIN_SC_NO_PULL_EXTERNAL_2"] = "External connection to your Bitrix24 was established. However, the Push server read port is unavailable. Instant messaging will not be available in mobile app.";
$MESS["MAIN_SC_NO_PUSH_STREAM_2"] = "Push server is not configured in the Push and Pull module settings. This server is required to show the Feed comments in real time.";
$MESS["MAIN_SC_NO_PUSH_STREAM_CONNECTION"] = "Cannot connect to nginx-push-stream module for sending instant messages";
$MESS["MAIN_SC_NO_PUSH_STREAM_CONNECTION_2"] = "Cannot connect to the Push server to send instant messages";
$MESS["MAIN_SC_NO_PUSH_STREAM_VIDEO_2"] = "Push server is not configured in the Push and Pull module settings. This server is required to make video calls.";
$MESS["MAIN_SC_NO_REST_MODULE"] = "The Rest module is not installed.";
$MESS["MAIN_SC_NO_SOCIAL_MODULE"] = "The social networking module is not installed.";
$MESS["MAIN_SC_NO_SOCIAL_SERVICES"] = "No social networking service configured in the social networking module settings.";
$MESS["MAIN_SC_NO_SOCIAL_SERVICES_24NET"] = "bitrix24.net integration is not configured in the Social Service module settings.";
$MESS["MAIN_SC_NO_SUB_CONNECTION_2"] = "Cannot connect to the Push server to read instant messages";
$MESS["MAIN_SC_NO_WEBDAV_MODULE"] = "The Document Library module is not installed.";
$MESS["MAIN_SC_NTLM_SUCCESS"] = "NTLM authentication is OK, current user: ";
$MESS["MAIN_SC_OPTION_SWITCHED_OFF"] = "NTLM authentication is enabled in the LDAP module settings.";
$MESS["MAIN_SC_PATH_PUB"] = "Incorrect publish path specified in the Push and Pull module settings";
$MESS["MAIN_SC_PATH_SUB"] = "The message read URL is incorrect in the Push and Pull module settings.";
$MESS["MAIN_SC_PERFORM"] = "Performance";
$MESS["MAIN_SC_PERF_TEST"] = "Server performance test";
$MESS["MAIN_SC_PULL_NOT_REGISTERED"] = "Error registering on Bitrix provided push server";
$MESS["MAIN_SC_PULL_UNSUPPORTED_VERSION"] = "The Push and Pull module settings specify a deprecated version of Push server. You have to update your Push server. <a href=\"https://training.bitrix24.com/support/training/course/index.php?COURSE_ID=26&LESSON_ID=21596\">Learn more.</a> ";
$MESS["MAIN_SC_PUSH_INCORRECT"] = "nginx-push-stream module functions incorrectly.";
$MESS["MAIN_SC_REAL_TIME"] = "Real time communications and video calls";
$MESS["MAIN_SC_REQUIRED_MODS_DESC"] = "Checks that all the required modules are installed and the most essential settings are correct. Otherwise, intranet may not function properly.";
$MESS["MAIN_SC_SEARCH_INCORRECT"] = "Document contents indexing does not function properly.";
$MESS["MAIN_SC_SITE_GOT_ERRORS"] = "The site has errors. <a href=\"#LINK#\">Check and repair</a>";
$MESS["MAIN_SC_SOME_WARNING"] = "Warning";
$MESS["MAIN_SC_SSL_NOT_VALID"] = "The server's SSL certificate is invalid";
$MESS["MAIN_SC_STREAM_DISABLED_2"] = "Push server is not configured in the Push and Pull module settings.";
$MESS["MAIN_SC_SYSTEST_LOG"] = "System check log";
$MESS["MAIN_SC_TEST_COMMENTS"] = "Live comments";
$MESS["MAIN_SC_TEST_DOCS"] = "Editing documents in Google Docs and Microsoft Office Online";
$MESS["MAIN_SC_TEST_FAST_FILES"] = "Bitrix24.Drive. Fast file operations";
$MESS["MAIN_SC_TEST_IS_INCORRECT"] = "The test has failed to produce correct results.";
$MESS["MAIN_SC_TEST_LDAP"] = "Active Directory integration";
$MESS["MAIN_SC_TEST_MAIL_INTEGRATION"] = "Internal corporate mail integration";
$MESS["MAIN_SC_TEST_MAIL_PUSH"] = "Relay e-mail messages to Activity Stream";
$MESS["MAIN_SC_TEST_MOBILE"] = "Bitrix24 mobile app";
$MESS["MAIN_SC_TEST_NTLM"] = "Windows NTLM authentication";
$MESS["MAIN_SC_TEST_PUSH"] = "Notifications to mobile devices (push notifications)";
$MESS["MAIN_SC_TEST_PUSH_SERVER"] = "Push and Pull server";
$MESS["MAIN_SC_TEST_REST"] = "REST API Usage";
$MESS["MAIN_SC_TEST_RESULT"] = "Test result:";
$MESS["MAIN_SC_TEST_SEARCH_CONTENTS"] = "Search document contents";
$MESS["MAIN_SC_TEST_SOCNET_INTEGRATION"] = "Social service integration";
$MESS["MAIN_SC_TEST_SSL1"] = "Secure HTTPS connection was established but the SSL certificate could not be verified because the list of certification authorities was not downloaded from Bitrix server";
$MESS["MAIN_SC_TEST_SSL_WARN"] = "Could not connect securely. You may experience problems communicating with external applications.";
$MESS["MAIN_SC_TEST_VIDEO"] = "Video calls";
$MESS["MAIN_SC_UNKNOWN_ANSWER"] = "Unknown response from #HOST#";
$MESS["MAIN_SC_WARNINGS"] = "mobile notifications";
$MESS["MAIN_SC_WARN_EXPAND_SESSION"] = "If the Instant Messenger module is installed, disable the session keep alive feature in the <a href='/bitrix/admin/settings.php?mid=main' target=_blank>kernel settings</a> to reduce server load.";
$MESS["MAIN_SC_WINDOWS_ENV"] = "Windows environment integration";
$MESS["MAIN_TMP_FILE_ERROR"] = "Could not create a temporary test file";
$MESS["MAIN_WRONG_ANSWER_PULL"] = "PUSH server replied with an unknown response.";
$MESS["PHP_VER_NOTIFY"] = "Attention! You are not receiving system or security updates because your PHP version #CUR# is outdated. Please update your PHP to version #REQ#. Make sure you've read this <a href=\"https://helpdesk.bitrix24.com/open/17347208/\">Helpdesk page</a> before updating PHP.";
$MESS["SC_BX_UTF"] = "Use the following code in <i>/bitrix/php_interface/dbconn.php</i>:
<code>define('BX_UTF', true);</code> ";
$MESS["SC_BX_UTF_DISABLE"] = "The BX_UTF constant must not be defined";
$MESS["SC_CACHED_EVENT_WARN"] = "Found cached e-mail sending data which might be due to an error. Try to clear cache.";
$MESS["SC_CHARSET_CONN_VS_RES"] = "The connection charset (#CONN#) is different than the result charset (#RES#).";
$MESS["SC_CHECK_B"] = "Check";
$MESS["SC_CHECK_FILES"] = "Check file permissions";
$MESS["SC_CHECK_FILES_ATTENTION"] = "Attention!";
$MESS["SC_CHECK_FILES_WARNING"] = "File permissions check script can generate a large load on the server.";
$MESS["SC_CHECK_FOLDER"] = "Folder Check";
$MESS["SC_CHECK_FULL"] = "Full Check";
$MESS["SC_CHECK_KERNEL"] = "Kernel Check";
$MESS["SC_CHECK_TABLES_ERRORS"] = "Database tables have #VAL# encoding error(s), #VAL1# of which can be fixed automatically.";
$MESS["SC_CHECK_TABLES_STRUCT_ERRORS"] = "There are errors in database structure. Total issues: #VAL#. #VAL1# can be fixed right away.";
$MESS["SC_CHECK_TABLES_STRUCT_ERRORS_FIX"] = "The issues have been fixed, but some fields (#VAL#) have different types. You will have to fix them manually by reviewing the website check log.";
$MESS["SC_CHECK_UPLOAD"] = "Upload Folder Check";
$MESS["SC_COLLATE_WARN"] = "The collation value for &quot;#TABLE#&quot; (#VAL0#) differs from the database value (#VAL1#).";
$MESS["SC_CONNECTION_CHARSET"] = "Connection charset";
$MESS["SC_CONNECTION_CHARSET_NA"] = "Verification failed due to connection encoding error.";
$MESS["SC_CONNECTION_CHARSET_WRONG"] = "The database connection charset must be #VAL#, the current value is #VAL1#.";
$MESS["SC_CONNECTION_CHARSET_WRONG_NOT_UTF"] = "The database connection charset must not be UTF-8, the current value is: #VAL#.";
$MESS["SC_CONNECTION_COLLATION_WRONG_UTF"] = "The database connection collation must be utf8_unicode_ci, the current value is #VAL#.";
$MESS["SC_CRON_WARN"] = "The constant BX_CRONTAB_SUPPORT is defined in /bitrix/php_interface/dbconn.php, this requires running agents using cron.";
$MESS["SC_DATABASE_CHARSET_DIFF"] = "The database charset (#VAL1#) does not match the connection charset (#VAL0#).";
$MESS["SC_DATABASE_COLLATION_DIFF"] = "The database collation (#VAL1#) does not match the connection collation (#VAL0#).";
$MESS["SC_DB_CHARSET"] = "Database charset";
$MESS["SC_DB_ERR"] = "Problem database version:";
$MESS["SC_DB_ERR_INNODB_STRICT"] = "innodb_strict_mode=#VALUE#, OFF is required";
$MESS["SC_DB_ERR_MODE"] = "The sql_mode variable in MySQL must be empty. Current value:";
$MESS["SC_DB_MISC_CHARSET"] = "The table #TBL# charset (#T_CHAR#) does not match the database charset (#CHARSET#).";
$MESS["SC_DELIMITER_ERR"] = "Current delimiter: &quot;#VAL#&quot;, &quot;.&quot; is required.";
$MESS["SC_ERROR0"] = "Error!";
$MESS["SC_ERROR1"] = "The test has failed to complete.";
$MESS["SC_ERRORS_FOUND"] = "There&nbsp;were&nbsp;errors";
$MESS["SC_ERRORS_NOT_FOUND"] = "No&nbsp;errors&nbsp;detected";
$MESS["SC_ERR_CONNECT_MAIL001"] = "Cannot connect mail server mail-001.bitrix24.com";
$MESS["SC_ERR_CONN_DIFFER"] = "in .settings.php and dbconn.php are different.";
$MESS["SC_ERR_DNS"] = "Cannot get MX record for domain #DOMAIN#";
$MESS["SC_ERR_DNS_WRONG"] = "DNS configuration is incorrect. Only one MX record has to be there: mail-001.bitrix24.com (current: #DOMAIN#).";
$MESS["SC_ERR_FIELD_DIFFERS"] = "Table #TABLE#: the field #FIELD# \"#CUR#\" does not match the description \"#NEW#\"";
$MESS["SC_ERR_NO_FIELD"] = "The field #FIELD# is missing from the table #TABLE#";
$MESS["SC_ERR_NO_INDEX"] = "Index #INDEX# is missing from the table #TABLE#";
$MESS["SC_ERR_NO_INDEX_ENABLED"] = "Full-text search index #INDEX# is not enabled for table #TABLE#";
$MESS["SC_ERR_NO_SETTINGS"] = "Configuration file /bitrix/.settings.php was not found";
$MESS["SC_ERR_NO_TABLE"] = "The table #TABLE# does not exist.";
$MESS["SC_ERR_NO_VALUE"] = "There is no system record #SQL# for the table #TABLE#";
$MESS["SC_ERR_PHP_PARAM"] = "The parameter #PARAM# is #CUR#, but #REQ# is required.";
$MESS["SC_ERR_TEST_MAIL_PUSH"] = "Cannot connect to #DOMAIN# from e-mail server";
$MESS["SC_FIELDS_COLLATE_WARN"] = "The field &quot;#FIELD#&quot; result in the table &quot;#TABLE#&quot;  (#VAL1#) does not match that in the database (#VAL1#).";
$MESS["SC_FILES_CHECKED"] = "Files checked: <b>#NUM#</b><br>Current path: <i>#PATH#</i>";
$MESS["SC_FILES_FAIL"] = "Unavailable for reading or writing (first 10):";
$MESS["SC_FILES_OK"] = "All the files checked are available for reading and writing.";
$MESS["SC_FILE_EXISTS"] = "File exists:";
$MESS["SC_FIX"] = "Fix";
$MESS["SC_FIX_DATABASE"] = "Fix Database Errors";
$MESS["SC_FIX_DATABASE_CONFIRM"] = "The system will now attempt to fix database errors. This action is potentially dangerous. Create the database backup copy before you proceed.

Continue?";
$MESS["SC_FIX_MBSTRING"] = "Repair configuration";
$MESS["SC_FIX_MBSTRING_CONFIRM"] = "Attention!

This will change the configuration files. If the operation fails, your site will be recoverable only from the web hosting control panel.

Continue?";
$MESS["SC_FULL_CP_TEST"] = "Full system check";
$MESS["SC_GR_EXTENDED"] = "Advanced features";
$MESS["SC_GR_FIX"] = "Fix errors";
$MESS["SC_GR_MYSQL"] = "Database test";
$MESS["SC_HELP"] = "Help.";
$MESS["SC_HELP_CHECK_ACCESS_DOCS"] = "To view or edit documents using Google Docs or MS Office Online, a special externally accessible URL is created and passed over to these services which they use to get a document. The URL is unique and becomes invalid as soon as the document is closed.

This feature requires that your Intranet is remotely accessible via the Internet.";
$MESS["SC_HELP_CHECK_ACCESS_MOBILE"] = "The mobile application requires that your Intranet is remotely accessible via the Internet.

The test employs a special server at checker.internal.bitrix24.com that attempts a connection to your Intranet using the current Bitrix24 URL as provided by the web browser. No user data is transmitted while the connection with the remote server is active.

Instant messaging requires that the read port of Nginx's push-stream-module can be connected to. The port number comes from the <a href=\"http://www.bitrixsoft.com/support/training/course/index.php?COURSE_ID=26&LESSON_ID=5144\">Push and Pull</a> module settings.";
$MESS["SC_HELP_CHECK_AD"] = "If a Windows AD or LDAP server is set up on your local network, it is advised to check that AD is <a href=\"http://www.bitrixsoft.com/support/training/course/index.php?COURSE_ID=20&CHAPTER_ID=04264\">properly configured</a>.

This feature requires that the PHP ldap module is installed.";
$MESS["SC_HELP_CHECK_BX_CRONTAB"] = "To migrate the non-periodic agents and e-mail to cron, add the following constant to <i>/bitrix/php_interface/dbconn.php</i>:
<code>define('BX_CRONTAB_SUPPORT', true);</code>

With this constant set to \"true\", the system will run only the periodic agents when a hit occurs. Now add a task to cron to execute <i>/var/www/bitrix/modules/main/tools/cron_events.php</i> every minute (replace <i>/var/www</i> with the website root path).

The script defines the constant <b>BX_CRONTAB</b> which indicates that the script is activated by cron and runs only non-periodic agents. If you define this constant in <i>dbconn.php</i> by mistake, periodic agents will never run.";
$MESS["SC_HELP_CHECK_CACHE"] = "This will check if a PHP process can create a <b>.tmp</b> file in the cache directory and then rename it to <b>.php</b>. Some Windows web server may fail to rename the file if the user permissions are configured incorrectly.";
$MESS["SC_HELP_CHECK_CA_FILE"] = "The test attempts to connect to www.bitrixsoft.com. 

This connection is required by many cloud related routine tasks (CDN, cloud backup, security scanner etc.) to update the current free space quota and the service status. No user information are sent while performing these operations.

Then, the test downloads a list of certification centers from the Bitrix server which is required by the SSL certificate test.
";
$MESS["SC_HELP_CHECK_COMPRESSION"] = "HTML compression reduces file size and decreases file transmission time.

To reduce server load, make sure a special web server module is used to compress HTML files.

If the server does not support HTML page compression, the Bitrix Compression module is used instead. Remember that this module <a href=\"/bitrix/admin/module_admin.php\">should not be installed</a> otherwise.";
$MESS["SC_HELP_CHECK_CONNECT_MAIL"] = "To get notified by Intranet about new e-mail messages, a user has to specify the mailbox connection parameters on the Intranet user profile page.";
$MESS["SC_HELP_CHECK_DBCONN"] = "This will check the text output in the configuration files <i>dbconn.php</i> and <i>init.php</i>.

Even an excess space or newline may cause a compressed page to be unpackable and unreadable by a client browser.

Besides, authorizations and CAPTCHA problems may occur.";
$MESS["SC_HELP_CHECK_DBCONN_SETTINGS"] = "This test will compare database connection parameters specified in <i>/bitrix/php_interface/dbconn.php</i> with those in <i>/bitrix/.settings.php</i>. 
These settings must be the same in both files. Otherwise, some SQL queries may divert to another database which will entail unpredictable consequences.

The new D7 kernel uses parameters in <i>.settings.php</i>. Due to backward compatibility issues, the use of <i>dbconn.php</i> cannot be avoided.

However, if <i>.settings.php</i> does not specify connection parameters at all, the new kernel uses those in <i>dbconn.php</i>.";
$MESS["SC_HELP_CHECK_EXEC"] = "If PHP runs in CGI/FastCGI mode on a Unix system, the scripts require execution permissions, otherwise they will not run.
If this test fails, contact your hosting techsupport for necessary file permissions and set the contants <b>BX_FILE_PERMISSIONS</b> and <b>BX_DIR_PERMISSIONS</b> in <i>dbconn.php</i> accordingly.

Configure PHP to run as an Apache module if possible.";
$MESS["SC_HELP_CHECK_EXTRANET"] = "The <a href=\"http://www.bitrixsoft.com/products/intranet/features/collaboration/extranet.php\">Extranet</a> module requires that your Intranet is externally accessible via the Internet.

If you don't need features provided by this module, simply <a href=\"/bitrix/admin/module_admin.php\">uninstall it</a>.";
$MESS["SC_HELP_CHECK_FAST_DOWNLOAD"] = "Fast file download is implemented using <a href=\"http://wiki.nginx.org/X-accel\">nginx's internal redirection</a>. The file access permissions are checked using PHP calls, while the actual download is handled by nginx. 

Once a request has been served, PHP resources are freed to process a subsequent request in the queue. This significantly improves Intranet performance and boosts file download speed when accessed via Bitrix.Drive, Document Library or when downloading attachments from Activity Stream posts.

Enable this option in the <a href=\"/bitrix/admin/settings.php?mid=main\">Kernel settings</a>. <a href=\"http://www.bitrixsoft.com/products/virtual_appliance/\">Bitrix Virtual Appliance</a> supports fast file downloads by default.

";
$MESS["SC_HELP_CHECK_GETIMAGESIZE"] = "When you add a Flash object, the visual editor needs to get the object size and calls the standard PHP function <b>getimagesize</b> which requires the <b>Zlib</b> extension. This function may fail when called for a compressed Flash object if the <b>Zlib</b> extension is installed as a module. It needs to be built statically.

To resolve this problem, contact your hosting techsupport.";
$MESS["SC_HELP_CHECK_HTTP_AUTH"] = "This test will send the authorization data using the HTTP headers and then attempt to resolve the data using the REMOTE_USER server variable (or REDIRECT_REMOTE_USER). HTTP authorization is required for integration with third-party software.

If PHP runs in CGI/FastCGI mode (contact your hosting for details), the Apache server will require the mod_rewrite module and the following rule in .htaccess:
<b>RewriteRule .* - [E=REMOTE_USER:%{HTTP:Authorization}]</b>

Configure PHP to run as an Apache module if possible.";
$MESS["SC_HELP_CHECK_INSTALL_SCRIPTS"] = "Users may occasionally forget to delete the installation scripts (restore.php, bitrixsetup.php) after system recovery or installation. This may become a serious security threat and result in website hijacking. If you have ignored the autodelete warning, remember to remove these files manually.";
$MESS["SC_HELP_CHECK_LOCALREDIRECT"] = "After a Control Panel's form is saved (that is, a user clicked Save or Apply), the client is redirected to an initial page. This is done to prevent repeated form posts which may occur if a user refreshes the page. A redirect will only succeed if some crucial variables are defined correctly on a web server, and HTTP header rewrite is allowed.

If some of the server variables are redefined in <i>dbconn.php</i>, the test will use that redefinitions. In other words, redirect fully simulate the real life situations.";
$MESS["SC_HELP_CHECK_MAIL"] = "This will send an e-mail message to hosting_test@bitrixsoft.com using the standard PHP function \"mail\". A special mailbox exists to make the test conditions as real-life as possible.

This test sends the site check script as a test message and <b>never sends any user data</b>.

Note that the test does not verify the message delivery. Delivery to other mailboxes cannot be verified as well.

If the e-mail sending time exceeds one second, the server performance may experience severe degradation. Contact your hosting techsupport so that they configure delayed e-mail sending using spooler.

Alternatively, you can use cron to send the e-mails. To do so, add <code>define('BX_CRONTAB_SUPPORT', true);</code> to dbconn.php. Then, set cron to execute <i>php /var/www/bitrix/modules/main/tools/cron_events.php</I> every minute (replace <i>/var/www</i> with your website root).

If the call to mail() has failed, you cannot send e-mail from your server using conventional methods.

If your hosting provider offers alternative e-mail sending services, you can use them by calling the function \"custom_mail\". Define this function in <i>/bitrix/php_interface/init.php</I>. If the system find this function definition, it will use the latter instead of PHP's \"mail\" with the same input parameters.";
$MESS["SC_HELP_CHECK_MAIL_BIG"] = "This will test bulk e-mails by sending the same message as in the previous text (the site check script) 10 times. Additionally, a newline character is inserted into the message subject, and the message is BCC'ed to noreply@bitrixsoft.com.

Such messages may not send if the server is configured incorrectly.

Should any problem appear, contact your hosting provider. If you are running the system at a local machine, you will have to configure the server manually.";
$MESS["SC_HELP_CHECK_MAIL_B_EVENT"] = "The database table B_EVENT stores the website's e-mail queue and logs the e-mail sending events. If some of the messages failed to be sent, possible reasons are invalid recipient address, incorrect e-mail template parameters or the server's e-mail subsystem.";
$MESS["SC_HELP_CHECK_MAIL_PUSH"] = "The <a href=\"https://helpdesk.bitrix24.com/open/1612393/\" target=_blank>Message Relay</a> feature will post messages from e-mail to Activity Stream making it possible to involve in discussion any user who does not have an account on your Bitrix24.

You will have to configure DNS properly and make your Bitrix24 externally accessible to use this feature.";
$MESS["SC_HELP_CHECK_MBSTRING"] = "mbstring module is required for multilanguage support. 

Website encoding needs to be specified as a value of the default_charset parameter. For example:

<b>default_charset=utf-8</b>

Misconfiguration will lead to numerous issues: texts will be haphazardly truncated, XML import and the Update System will be broken etc.

Add this code to <i>/bitrix/php_interface/dbconn.php</I> to enable UTF-8 on your site:
<code>define('BX_UTF', true);</code>
and add this code to <i>/bitrix/.settings.php</i>:
<code>'utf_mode' => 
  array (
    'value' => true,
    'readonly' => true,
  ),</code>";
$MESS["SC_HELP_CHECK_MEMORY_LIMIT"] = "This test creates an isolated PHP process to generate a variable whose size is incremented gradually. In the end, this will produce the amount of memory available to the PHP process.

PHP defines the memory limit in php.ini by setting the <b>memory_limit</b> parameter. However, this may be overridden on shared hostings. You should not trust this parameter.

The test attempts to increase the value of <b>memory_limit</b> using the code:
<code>ini_set(&quot;memory_limit&quot;, &quot;512M&quot;)</code>

If the current value is less than that, add this line of code to <i>/bitrix/php_interface/dbconn.php</i>.
";
$MESS["SC_HELP_CHECK_METHOD_EXISTS"] = "The script fails when calling <i>method_exists</I> on some PHP versions. Please refer to this discussion for more information: <a href='http://bugs.php.net/bug.php?id=51425' target=_blank>http://bugs.php.net/bug.php?id=51425</a>
Install a different PHP version to resolve the issue.";
$MESS["SC_HELP_CHECK_MYSQL_BUG_VERSION"] = "There are known MySQL versions containing errors which may cause website malfunction.
<b>4.1.21</b> - sort functions work incorrectly in certain conditions;
<b>5.0.41</b> - the EXISTS function works incorrectly; the search functions return incorrect results;
<b>5.1.34</b> - the auto_increment step is 2 by default while 1 is required.
<b>5.1.66</b> - returns incorrect forum topic post count which may break breadcrumb navigation.

You need to update your MySQL if you have one of these versions installed.";
$MESS["SC_HELP_CHECK_MYSQL_CONNECTION_CHARSET"] = "This test will check the charset and collation the system uses when sending data to the MySQL server.

If your website uses <i>UTF-8</I>, the charset must be set to <i>utf8</I> and the collation - <i>utf8_unicode_ci</i>. If the website uses <i>iso-8859-1</i>, the connection must use the same charset.

To change the connection charset (for example, set it to UTF-8), add the following code to <i>/bitrix/php_interface/after_connect_d7.php</i>:
<code>\$connection = Bitrix\\Main\\Application::getConnection();
\$connection->queryExecute('SET NAMES &quot;utf8&quot;');</code>

To change the collation, add the code <b>after the charset declaration</b>:
<code>\$connection->queryExecute('SET collation_connection = &quot;utf8_unicode_ci&quot;');</code>

<b>Attention!</b> Once you have changed the new values, make sure your website functions properly.
";
$MESS["SC_HELP_CHECK_MYSQL_DB_CHARSET"] = "This test check if the database charset and collation match those of the connection. MySQL uses these preferences to create new tables.

Such errors, if any occur can be fixed automatically if a current user has database write permission (ALTER DATABASE).
";
$MESS["SC_HELP_CHECK_MYSQL_MODE"] = "The parameter <i>sql_mode</i> specifies the MySQL operation mode. Note that it may accept values incompatible with Bitrix solutions. Add the following code to <i>/bitrix/php_interface/after_connect_d7.php</I> to set the default mode:
<code>\$connection = Bitrix\\Main\\Application::getConnection();
\$connection-&gt;queryExecute(&quot;SET sql_mode=''&quot;);
\$connection-&gt;queryExecute(&quot;SET innodb_strict_mode=0&quot;);</code>

Note that you may need to have database user privilege SESSION_VARIABLES_ADMIN on MySQL 8.0.26 and newer. If your current privilege is insufficient, you have to contact your database administrator or edit the MySQL configuration file.";
$MESS["SC_HELP_CHECK_MYSQL_TABLE_CHARSET"] = "The charset of all the tables and fields must match the database charset. If the charset of any of the tables is defferent, you have to fix it manually using the SQL commands.

The table collation should match the database collations as well. If the charsets are configured correctly, mismatching collation will be fixed automatically.

<b>Attention!</b> Always create full backup copy of the database before changing the charset.";
$MESS["SC_HELP_CHECK_MYSQL_TABLE_STATUS"] = "This test uses the conventional MySQL table check mechanism. If the test finds one or more damaged table, you will be prompted to fix them.";
$MESS["SC_HELP_CHECK_MYSQL_TABLE_STRUCTURE"] = "The module installation packages always include information on the structure of database tables they use. When updating, the module installers may change the table structure and the module files (scripts).

If the module scripts do not match the current table structure, it will definitely bring about runtime errors.

There may be new database indexes that were added to the new distribution packages but not included in updates. It is because updating a system to include new indexes would take too long and fail in the end.

Website check will diagnose the <b>installed</b> modules and create and/or update the missing indexes and fields to ensure data integrity. However, you will have to review the log manually if the field type has changed.";
$MESS["SC_HELP_CHECK_MYSQL_TIME"] = "This test compares the database system time with the web server time. These two may become mistimed if they are installed on individual machines, but the most frequent reason is incorrect time zone configuration.

Set the PHP time zone in <i>/bitrix/php_interface/dbconn.php</i>:
<code>date_default_timezone_set(&quot;Europe/London&quot;);</code> (use your region and city).

Set the database time zone by adding the following code to <i>/bitrix/php_interface/after_connect_d7.php</i>:
<code>\$connection = Bitrix\\Main\\Application::getConnection(); 
\$connection->queryExecute(&quot;SET LOCAL time_zone='&quot;.date('P').&quot;'&quot;);</code>

Please refer to http://en.wikipedia.org/wiki/List_of_tz_database_time_zones to find a correct standard value for your region and city.";
$MESS["SC_HELP_CHECK_NTLM"] = "<a href=\"http://en.wikipedia.org/wiki/Single_sign-on\">Single sign-on</a> authentication technology requires that a web server is configured in a special way and NTLM authentication is enabled and configured on the Intranet.

Setting up NTLM on Linux is definitely not a trivial task; <a href=\"http://www.bitrixsoft.com/products/virtual_appliance/\">Bitrix Virtual Appliance</a> includes NTLM authentication fully configured since version 4.2.";
$MESS["SC_HELP_CHECK_PCRE_RECURSION"] = "The parameter <i>pcre.recursion_limit</i> is set to 100000 by default. If recursion eats more memory than the system stack size can provide (commonly 8 MB), PHP will error out on complex regular expressions showing a <i>Segmentation fault</i> error message.

To disable stack size limit, edit the Apache startup script: <code>ulimit -s unlimited</code>
On FreeBSD, you will have to rebuild PCRE using the option --disable-stack-for-recursion

Alternatively, you can decrease the value of <i>pcre.recursion_limit</i> to 1000 or less. This solution also applies to Windows based installations.

This will prevent PHP catastrophic failures but may lead to inconsistencies in the behavior of string functions: for example, the forums may begin to show empty posts.";
$MESS["SC_HELP_CHECK_PERF"] = "Server performance evaluation as provided by <a href=\"http://www.bitrixsoft.com/support/training/course/index.php?COURSE_ID=20&CHAPTER_ID=04955\">Performance Monitor</a>.

Shows the number of empty pages the server can serve per second. This value is the inverse of the time required to generate an empty page that contains only the mandatory kernel inclusion call.

The reference <a href=\"http://www.bitrixsoft.com/products/virtual_appliance/\">Bitrix Virtual Appliance</a> usually scores 30 points.

Getting a bad value on a non-loaded machine is indicative of poor configuration. Decrease of an otherwise good score during high load periods may be due to insufficient hardware resources.";
$MESS["SC_HELP_CHECK_PHP_MODULES"] = "This will check for the PHP extensions required by the system. If there are missing extensions, shows the modules that cannot run without these extensions.

To add missing PHP extensions, contact your hosting techsupport. If you run the system at a local machine, you will have to install them manually; refer to documentation available at php.net.";
$MESS["SC_HELP_CHECK_PHP_SETTINGS"] = "This will check for the critical parameters defined in php.ini. Shows the parameters whose values will cause system malfunction. You will find the detailed parameter description at php.net.";
$MESS["SC_HELP_CHECK_POST"] = "This will send a POST request with a large number of parameters. Some server protector software like \"suhosin\" may block verbose requests. This may prevent information block elements from being saved which is definitely a problem.";
$MESS["SC_HELP_CHECK_PULL_COMMENTS"] = "To make comments in Avtivity Stream available to all readers right away, the Push and Pull module may require additional configuration. Namely, your Nginx instance needs to have push-stream-module installed, and then activated in the <a href=\"http://www.bitrixsoft.com/support/training/course/index.php?COURSE_ID=26&LESSON_ID=5144\">Push and Pull</a> module settings.

<a href=\"http://www.bitrixsoft.com/products/virtual_appliance/index.php\">Bitrix Virtual Appliance</a> comes fully preconfigured to support this feature since version 4.2.
";
$MESS["SC_HELP_CHECK_PULL_STREAM"] = "The <a href=\"http://www.bitrixsoft.com/support/training/course/index.php?COURSE_ID=26&LESSON_ID=5144\">Push and Pull</a> module requires that your server supports this feature.

This module handles the delivery of instant messages to Web Messenger and the mobile application. It is also used to update Activity Stream.

<a href=\"http://www.bitrixsoft.com/products/virtual_appliance/\">Bitrix Virtual Appliance</a> supports this module since version 4.2.
";
$MESS["SC_HELP_CHECK_PUSH_BITRIX"] = "The <a href=\"http://www.bitrixsoft.com/support/training/course/index.php?COURSE_ID=26&LESSON_ID=5144\">Push and Pull</a> module handles the delivery of instant messages (Pull), and sends Push notifications to mobile devices (<a href=\"http://www.bitrixsoft.com/products/intranet/features/bitrixmobile.php\">Bitrix mobile application</a>).

Sending notification to Apple and Android devices is performed using the secure (HTTPS) Bitrix messaging center https://cloud-messaging.bitrix24.com.

Your Intranet needs access to this server for push notifications to work as designed.
";
$MESS["SC_HELP_CHECK_REST"] = "The REST module is required to integrate external applications and run a number of Bitrix24.Market applications. To integrate your own applications into Bitrix24, please follow <a href=\"https://training.bitrix24.com/rest_help/\" target=\"_blank\">the guidelines</a>.";
$MESS["SC_HELP_CHECK_SEARCH"] = "The system can search text in documents in Open XML format (introduced in Microsoft Office 2007) out of the box. To support other file formats, specify paths to parsing applications <a href=\"/bitrix/admin/settings.php?mid=intranet\">in the Intranet module settings</a>. Otherwise, the system will be able to search filenames only.

<a href=\"http://www.1c-bitrix.ru/products/vmbitrix/index.php\">Bitrix Virtual Appliance</a> supports it by default.";
$MESS["SC_HELP_CHECK_SECURITY"] = "The Apache's mod_security module, like the PHP's suhosin, is intended to protect the website against hackers, but eventually it just prevents normal user actions. It is recommended that you use the standard \"Proactive Protection\" module instead of mod_security.";
$MESS["SC_HELP_CHECK_SERVER_VARS"] = "This will check the server variables.

The value of HTTP_HOST is derived from the current virtual host (domain). Some browsers cannot save cookies for invalid domain names, which will cause cookie authorization failure.";
$MESS["SC_HELP_CHECK_SESSION"] = "This will check if the server is capable of storing data using sessions. This is required to preserve authorization between hits.

This test will fail if no session support is installed on the server, an invalid session directory is specified in php.ini or if this directory is read-only.";
$MESS["SC_HELP_CHECK_SESSION_UA"] = "This will also test the session storage capability, but without setting the <i>User-Agent</i> HTTP header.

Many external applications and add-ons don't set this header: file and photo uploaders, WebDav clients etc.

If the test fails, the most likely problem is incorrect configuration of the <b>suhosin</b> PHP module.";
$MESS["SC_HELP_CHECK_SITES"] = "Verifies general multisite parameters. If a website specifies the root directory path (which is required only for websites existing on different domains), that directory must contain a symbolic link to writable \"bitrix\" folder.

All the websites that share the same Bitrix system instance must use the same encoding: either UTF-8 or single byte.";
$MESS["SC_HELP_CHECK_SOCKET"] = "This will set the web server to establish a connection to itself which is required to verify networking functions and for other subsequent tests.

If this test fails, the subsequent tests requiring a child PHP process cannot be performed. This problem is usually caused by a firewall, restricted  IP access or HTTP/NTLM authorization. Disable these functions while performing the test.";
$MESS["SC_HELP_CHECK_SOCKET_SSL"] = "An encrypted connection is always established using <a href=\"http://en.wikipedia.org/wiki/HTTPS\">HTTPS</a> protocol. A valid SSL certificate is required to ensure the connection is really secure.

A certificate is valid if it was verified by the issuing authority and is owned by a website on which it is to be used. You can normally buy a certificate from your hosting company.

If you use a self-issued certificate on a HTTPS connection, your visitors may experience problems using external software when connecting a WebDav drive or communicating with Microsoft Outlook.
";
$MESS["SC_HELP_CHECK_SOCNET"] = "To receive updates from social resources, the Social Website Integration module has to be configured providing authentication keys for each service that are going to be used.";
$MESS["SC_HELP_CHECK_TURN"] = "Video calling requires that the involved users' browsers can connect to each other. If the callers sit on different networks - for example, in offices  in different locations - and no direct connection is possible, you will need a special TURN server to establish connection.

Bitrix24 provides the preconfigured TURN server free of charge at turn.calls.bitrix24.com. 

Alternatively, you can set up your own server and specify the server URL in the Web Messenger module settings.";
$MESS["SC_HELP_CHECK_UPDATE"] = "This will try to establish a test connection to the update server using the Kernel module current settings. If the connection cannot be established, you will not be able to install updates or activate the trial version.

The most common reasons are incorrect proxy settings, firewall restrictions or invalid server networking parameters.";
$MESS["SC_HELP_CHECK_UPLOAD"] = "This will attempt to connect to the web server and send a chunk of binary data as a file. The server will then compare the received data with the original sample. If a problem arises, it may be caused by some parameter in <i>php.ini</I> prohibiting binary data transfer, or by inaccessible temporary folder (or <i>/bitrix/tmp</i>).

Should the problem appear, contact your hosting provider. If you are running the system at a local machine, you will have to configure the server manually.";
$MESS["SC_HELP_CHECK_UPLOAD_BIG"] = "This will upload a large binary file (over 4MB). If this test fails while the previous one succeeds, the problem may be the limit in php.ini (<b>post_max_size</b> or <b>upload_max_filesize</b>). Use phpinfo to get the current values (Settings - Tools - System Administration - PHP Settings).

Insufficient disk space may cause this problem as well.";
$MESS["SC_HELP_CHECK_UPLOAD_RAW"] = "Sends binary data in the body of a POST request. However, the data sometimes may become damaged on the server side in which case the Flash based image uploader won't work.";
$MESS["SC_HELP_CHECK_WEBDAV"] = "<a href=\"http://en.wikipedia.org/wiki/WebDAV\">WebDAV</a> is the protocol that enables a user to open, edit and save documents in Microsoft Office directly from or to the Intranet without having to download or upload them from/to a server. A mandatory requirement is that the server on which the Intranet is installed passes WebDAV requests to PHP scripts exactly as received, unmodified. If the server blocks these requests, direct editing will not be possible.

Notice that some extra configuration might be required <a href=\"http://www.bitrixsoft.com/support/training/course/index.php?COURSE_ID=27&LESSON_ID=1466#office\">on the client side</a> to support direct editing, and there's no way to verify it remotely.
";
$MESS["SC_HELP_NOTOPIC"] = "Sorry, no help on this topic.";
$MESS["SC_MBSTRING_NA"] = "Verification failed due to UTF configuration errors";
$MESS["SC_MB_NOT_UTF"] = "The website runs in single byte encoding";
$MESS["SC_MB_UTF"] = "The website runs in UTF encoding";
$MESS["SC_MEMORY_CHANGED"] = "The value of memory_limit was increased from #VAL0# to #VAL1# using ini_set while testing.";
$MESS["SC_MOD_GD"] = "GD Library";
$MESS["SC_MOD_GD_JPEG"] = "GD JPEG support";
$MESS["SC_MOD_JSON"] = "Support JSON";
$MESS["SC_MOD_MBSTRING"] = "mbstring support";
$MESS["SC_MOD_PERL_REG"] = "Regular Expression support (Perl-Compatible)";
$MESS["SC_MOD_XML"] = "XML support";
$MESS["SC_MYSQL_ERR_VER"] = "MySQL #CUR# is currently installed, but #REQ# is required.";
$MESS["SC_NOT_FILLED"] = "The problem description is required.";
$MESS["SC_NOT_LESS"] = "Not less than #VAL# M.";
$MESS["SC_NO_PROXY"] = "Cannot connect to the proxy server.";
$MESS["SC_NO_ROOT_ACCESS"] = "Cannot access the folder ";
$MESS["SC_NO_TMP_FOLDER"] = "Temporary folder does not exist.";
$MESS["SC_PATH_FAIL_SET"] = "The website root path must be empty, the current path is:";
$MESS["SC_PCRE_CLEAN"] = "Long text strings may be handled incorrectly due to system restrictions.";
$MESS["SC_PORTAL_WORK"] = "Intranet operability";
$MESS["SC_PORTAL_WORK_DESC"] = "Intranet operability check";
$MESS["SC_PROXY_ERR_RESP"] = "Invalid proxy assisted update server response.";
$MESS["SC_READ_MORE_ANC"] = "See the details in <a href=\"#LINK#\" target=_blank>system check log</a>.";
$MESS["SC_RUS_L1"] = "Site ticket";
$MESS["SC_SEC"] = "sec.";
$MESS["SC_SENT"] = "Sent on:";
$MESS["SC_SITE_CHARSET_FAIL"] = "Mixed encodings: UTF-8 and non UTF-8";
$MESS["SC_SOCKET_F"] = "Socket Support";
$MESS["SC_SOCK_NA"] = "Verification failed due to socket error.";
$MESS["SC_START_TEST_B"] = "Start Test";
$MESS["SC_STOP_B"] = "Stop";
$MESS["SC_STOP_TEST_B"] = "Stop";
$MESS["SC_STRLEN_FAIL_PHP56"] = "String functions work incorrectly.  ";
$MESS["SC_STRTOUPPER_FAIL"] = "The string functions strtoupper and strtolower produce incorrect results";
$MESS["SC_SUBTITLE_DISK"] = "Checking the disk access";
$MESS["SC_SUBTITLE_DISK_DESC"] = "The site scripts must have write access to site files. This is required for proper functioning of the file manager, file upload and the update system that is used to keep the site kernel up-to-date.";
$MESS["SC_SUPPORT_COMMENT"] = "If you have problems sending the message, please use the contact form at our site:";
$MESS["SC_SWF_WARN"] = "SWF objects may not run.";
$MESS["SC_SYSTEM_TEST"] = "System check";
$MESS["SC_TABLES_NEED_REPAIR"] = "Table integrity damaged, they need to be fixed.";
$MESS["SC_TABLE_BROKEN"] = "The table &quot;#TABLE#&quot; has been destroyed due to internal MySQL error. Autorecovery will recreate the table.";
$MESS["SC_TABLE_CHARSET_WARN"] = "The &quot;#TABLE#&quot; table contains fields in encoding not matching the database encoding.";
$MESS["SC_TABLE_CHECK_NA"] = "Verification failed due to database charset error.";
$MESS["SC_TABLE_COLLATION_NA"] = "Not checked due to table charset errors";
$MESS["SC_TABLE_ERR"] = "Error in table #VAL#:";
$MESS["SC_TABLE_SIZE_WARN"] = "The size of the &quot;#TABLE#&quot; table is possibly too large (#SIZE# M).";
$MESS["SC_TAB_2"] = "Access check";
$MESS["SC_TAB_5"] = "Technical support";
$MESS["SC_TESTING"] = "Now checking...";
$MESS["SC_TESTING1"] = "Testing...";
$MESS["SC_TEST_CONFIG"] = "Configuration Check";
$MESS["SC_TEST_DOMAIN_VALID"] = "The current domain is invalid (#VAL#). The domain name can only contain numbers, Latin letters and hyphens. The first domain level must be separated with a period (e.g. .com).";
$MESS["SC_TEST_FAIL"] = "Invalid server response. Test cannot be completed.";
$MESS["SC_TEST_START"] = "Start test";
$MESS["SC_TEST_SUCCESS"] = "Success";
$MESS["SC_TEST_WARN"] = "The server configuration report is about to be collected.
If an error occurs, please uncheck the \"Send Test Log\" option and try again.";
$MESS["SC_TIK_ADD_TEST"] = "Send Test Log";
$MESS["SC_TIK_DESCR"] = "Problem description";
$MESS["SC_TIK_DESCR_DESCR"] = "sequence of operations that caused the error, error description,...";
$MESS["SC_TIK_LAST_ERROR"] = "Last error text";
$MESS["SC_TIK_LAST_ERROR_ADD"] = "attached";
$MESS["SC_TIK_SEND_MESS"] = "Send message";
$MESS["SC_TIK_SEND_SUCCESS"] = "The message has been sent successfully. Please check your inbox #EMAIL# after some time for confirmation of the message receipt from the technical support system.";
$MESS["SC_TIK_TITLE"] = "Send message to the technical support system";
$MESS["SC_TIME_DIFF"] = "The time is off by #VAL# seconds.";
$MESS["SC_TMP_FOLDER_PERMS"] = "Insufficient permission to write to temporary folder.";
$MESS["SC_T_APACHE"] = "Web server modules";
$MESS["SC_T_AUTH"] = "HTTP authorization";
$MESS["SC_T_CACHE"] = "Using cache files";
$MESS["SC_T_CHARSET"] = "Database table charset";
$MESS["SC_T_CHECK"] = "Table Check";
$MESS["SC_T_CLONE"] = "Passing objects by reference";
$MESS["SC_T_DBCONN"] = "Redundant output in configuration files";
$MESS["SC_T_DBCONN_SETTINGS"] = "Database connection parameters";
$MESS["SC_T_EXEC"] = "File creation and execution";
$MESS["SC_T_GETIMAGESIZE"] = "getimagesize support for SWF";
$MESS["SC_T_INSTALL_SCRIPTS"] = "Service scripts in the site root";
$MESS["SC_T_MAIL"] = "E-mail sending";
$MESS["SC_T_MAIL_BIG"] = "Large e-mail sending (over 64 KB)";
$MESS["SC_T_MAIL_B_EVENT"] = "Check for unsent messages";
$MESS["SC_T_MAIL_B_EVENT_ERR"] = "Errors occurred while sending system e-mail messages. Messages not sent:";
$MESS["SC_T_MBSTRING"] = "UTF configuration parameters (mbstring and BX_UTF)";
$MESS["SC_T_MEMORY"] = "Memory limit";
$MESS["SC_T_METHOD_EXISTS"] = "method_exists called on line";
$MESS["SC_T_MODULES"] = "Required PHP Modules";
$MESS["SC_T_MYSQL_VER"] = "MySQL version";
$MESS["SC_T_PHP"] = "PHP Required Parameters";
$MESS["SC_T_POST"] = "POST requests with many parameters";
$MESS["SC_T_RECURSION"] = "Stack size; pcre.recursion_limit";
$MESS["SC_T_REDIRECT"] = "Local redirects (LocalRedirect function)";
$MESS["SC_T_SERVER"] = "Server Variables";
$MESS["SC_T_SESS"] = "Session retention";
$MESS["SC_T_SESS_UA"] = "Session retention without UserAgent";
$MESS["SC_T_SITES"] = "Website Parameters";
$MESS["SC_T_SOCK"] = "Using sockets";
$MESS["SC_T_SQL_MODE"] = "MySQL Mode";
$MESS["SC_T_STRUCTURE"] = "Database structure";
$MESS["SC_T_TIME"] = "Database and web server times";
$MESS["SC_T_UPLOAD"] = "File upload";
$MESS["SC_T_UPLOAD_BIG"] = "Upload files over 4MB";
$MESS["SC_T_UPLOAD_RAW"] = "Upload file using php://input";
$MESS["SC_UPDATE_ACCESS"] = "Access to update server";
$MESS["SC_UPDATE_ERROR"] = "Not connected to update server";
$MESS["SC_UPDATE_ERR_RESP"] = "Invalid update server response.";
$MESS["SC_VER_ERR"] = "The PHP version is #CUR#, but #REQ# or higher is required.";
$MESS["SC_WARN"] = "not configured";
$MESS["SC_WARNINGS_FOUND"] = "There were warnings but no errors.";
$MESS["SC_WARN_DAV"] = "WebDav is disabled because the module mod_dav/mod_dav_fs is loaded.";
$MESS["SC_WARN_SECURITY"] = "The mod_security module loaded, some Control Panel problems may arise.";
$MESS["SC_WARN_SUHOSIN"] = "The suhosin module loaded, some Control Panel problems may arise (suhosin.simulation=#VAL#).";
