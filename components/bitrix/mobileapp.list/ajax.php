<?
define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_CHECK', true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('DisableEventsCheck', true);
define('NO_LANG_FILES', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!isset($_REQUEST["items"]) || !is_array($_REQUEST["items"]))
	die(false);

$arParams = array(
	"ITEMS" => $_REQUEST["items"]
	);

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.list',
	'.default',
	$arParams,
	false
);
?>