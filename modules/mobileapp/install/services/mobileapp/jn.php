<?
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$componentName = $_GET["componentName"];
$namespace = $_GET["namespace"];

$APPLICATION->IncludeComponent("bitrix:mobileapp.jnrouter", "", [
	"componentName" => $componentName,
	"namespace" => $namespace,
	"needAuth" => true,
], null, ["HIDE_ICONS" => "Y"]);


