<?
/*
This is callback page for Google OAuth 2.0 authentication.
Google redirects only to specific back url set in the OAuth application.
The page opens in popup window after user authorized on Google.
*/
define("NOT_CHECK_PERMISSIONS", true);

$provider = "GoogleOAuth";
if(isset($_REQUEST["state"]) && is_string($_REQUEST["state"]))
{
	$arState = array();
	parse_str($_REQUEST["state"], $arState);

	if(isset($arState['site_id']) && is_string($arState['site_id']))
	{
		$site = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $arState['site_id']), 0, 2);
		define("SITE_ID", $site);
	}

	if(isset($arState['provider']) && $arState['provider'] === 'GooglePlusOAuth')
	{
		$provider = 'GooglePlusOAuth';
	}
}

define('SOCSERV_CURRENT_PROVIDER', $provider);

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

if(Bitrix\Main\Loader::includeModule("socialservices"))
{
	$oAuthManager = CSocServGoogleProxyOAuth::isProxyAuth()
			? new CSocServGoogleProxyOAuth()
			: new CSocServAuthManager()
	;
	$oAuthManager->Authorize(SOCSERV_CURRENT_PROVIDER);
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_after.php");
?>