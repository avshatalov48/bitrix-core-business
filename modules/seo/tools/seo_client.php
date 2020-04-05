<?php
/*
This is callback page for Dropbox OAuth 2.0 authentication.
Dropbox redirects only to specific back url set in the OAuth application.
The page opens in popup window after user authorized on Dropbox.
*/
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

if(isset($_REQUEST["action"]) && $_REQUEST["action"] == 'web_hook')
{
	if (CModule::IncludeModule("seo") && CModule::IncludeModule("socialservices"))
	{
		\Bitrix\Seo\WebHook\Service::listen();
		exit;
	}
}

if(CModule::IncludeModule("socialservices") && CSocServAuthManager::CheckUniqueKey())
{
	if(isset($_REQUEST["authresult"]))
	{
		\Bitrix\Seo\Service::clearLocalAuth();
?>
<script type="text/javascript">
	var eventData = {'reload': true};
	window.opener.BX.onCustomEvent(
		window,
		'seo-client-auth-result',
		[eventData]
	);
	if (eventData.reload)
	{
		opener.location.reload();
	}
	window.close();
</script>
<?
	}
}

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_after.php");