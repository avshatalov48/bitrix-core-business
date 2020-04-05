<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define("DisableEventsCheck", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$successPath = COption::GetOptionString("sale", "sale_ps_success_path", "/");
LocalRedirect($successPath);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>