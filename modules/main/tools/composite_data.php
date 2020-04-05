<?
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_FILE_PERMISSIONS", true);
define("STOP_STATISTICS", true);
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define("BX_SECURITY_SESSION_READONLY", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header("Content-Type: application/x-javascript; charset=".LANG_CHARSET);
echo CUtil::PhpToJSObject(CJSCore::GetCoreMessages());

\CMain::FinalActions();
die();