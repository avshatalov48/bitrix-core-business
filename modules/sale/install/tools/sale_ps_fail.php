<?
define("STOP_STATISTICS", true);
define("NO_AGENT_CHECK", true);
define('NOT_CHECK_PERMISSIONS', true);
define("DisableEventsCheck", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$failPath = COption::GetOptionString("sale", "sale_ps_fail_path", "/");
LocalRedirect($failPath);

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>