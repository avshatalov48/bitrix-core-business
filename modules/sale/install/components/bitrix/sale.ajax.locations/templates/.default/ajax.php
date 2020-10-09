<?
define("STOP_STATISTICS", true);

$SITE_ID = '';
if(
	isset($_REQUEST["SITE_ID"])
	&& is_string($_REQUEST["SITE_ID"])
	&& $_REQUEST["SITE_ID"] <> ''
)
{
	$SITE_ID = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $_REQUEST["SITE_ID"]), 0, 2);
	define("SITE_ID", $SITE_ID);
}

if(
	isset($_REQUEST["ADMIN_SECTION"])
	&& is_string($_REQUEST["ADMIN_SECTION"])
	&& trim($_REQUEST["ADMIN_SECTION"]) == "Y"
)
{
	define("ADMIN_SECTION", true);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent(
	'bitrix:sale.ajax.locations',
	'',
	array(
		"AJAX_CALL" => "Y",
		"COUNTRY" => intval($_REQUEST["COUNTRY"]),
		"COUNTRY_INPUT_NAME" => $_REQUEST["COUNTRY_INPUT_NAME"],
		"REGION" => intval($_REQUEST["REGION"]),
		"REGION_INPUT_NAME" => $_REQUEST["REGION_INPUT_NAME"],
		"CITY_INPUT_NAME" => $_REQUEST["CITY_INPUT_NAME"],
		"CITY_OUT_LOCATION" => $_REQUEST["CITY_OUT_LOCATION"],
		"ALLOW_EMPTY_CITY" => $_REQUEST["ALLOW_EMPTY_CITY"],
		"ZIPCODE" => $_REQUEST["ZIPCODE"],
		//"LOCATION_VALUE" => $_REQUEST["LOCATION_VALUE"],
		"ONCITYCHANGE" => $_REQUEST["ONCITYCHANGE"]
	),
	null,
	array('HIDE_ICONS' => 'Y'));

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>