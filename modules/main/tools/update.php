<?
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if ($request->getQuery("action") == "stepper")
{
	\Bitrix\Main\Update\Stepper::checkRequest();
}