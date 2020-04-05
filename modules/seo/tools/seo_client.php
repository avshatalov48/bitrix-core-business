<?php
/*
This is callback page for Dropbox OAuth 2.0 authentication.
Dropbox redirects only to specific back url set in the OAuth application.
The page opens in popup window after user authorized on Dropbox.
*/
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define("NO_AGENT_CHECK", true);

define('SKIP_TEMPLATE_AUTH_ERROR', true);
define('NOT_CHECK_PERMISSIONS', true);

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
		$clientId = (int)$_REQUEST['proxy_client_id'];
		$engine = (string)$_REQUEST['engine'];
		\Bitrix\Seo\Service::clearClientsCache($engine, $clientId);

		$jsEventData = [
			'reload' => true,
			'engine' => $engine,
			'clientId' => $clientId > 0 ? $clientId : '',
		];
?>
<script type="text/javascript">
	var eventData = <?=CUtil::PhpToJSObject($jsEventData)?>;
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