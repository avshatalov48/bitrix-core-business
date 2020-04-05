<?
/*
This is callback page for LiveID OAuth 2.0 authentication.
LiveID redirects only to specific back url set in the OAuth application.
The page opens in popup window after user authorized on LiveID.
*/
define("NOT_CHECK_PERMISSIONS", true);
if(isset($_REQUEST["state"]) && is_string($_REQUEST["state"]))
{
	$arState = array();
	parse_str($_REQUEST["state"], $arState);

	if(isset($arState['site_id']) && is_string($arState['site_id']))
	{
		$site = substr(preg_replace("/[^a-z0-9_]/i", "", $arState['site_id']), 0, 2);
		define("SITE_ID", $site);
	}
}

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

if(CModule::IncludeModule("socialservices"))
{
	$oAuthManager = new CSocServAuthManager();
	$oAuthManager->Authorize("LiveIDOAuth");
}

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_after.php");
?>