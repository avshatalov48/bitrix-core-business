<?
/*
This is callback page for Twitter OAuth 1.0 authentication.
Dropbox redirects only to specific back url set in the OAuth application.
The page opens in popup window after user authorized on Yandex.
*/
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

if(CModule::IncludeModule("socialservices"))
{
	$oAuthManager = new CSocServAuthManager();
	$oAuthManager->Authorize("Twitter");
}

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_after.php");
?>