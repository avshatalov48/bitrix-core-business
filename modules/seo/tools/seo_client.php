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

$clientId = (int)($_REQUEST['proxy_client_id'] ?? 0);
$engine = (string)($_REQUEST['engine'] ?? '');

$clearCache = static function (int $clientId, string $engine)
{
	if (CModule::IncludeModule('seo'))
	{
		\Bitrix\Seo\Service::clearClientsCache($engine, $clientId);
		\Bitrix\Seo\BusinessSuite\Utils\QueueEventHandler::handleEvent($clientId, $engine);
	}
};

if (isset($_REQUEST['isMobileApp']))
{
	$clearCache($clientId, $engine);

	header('Location: bitrix24://');
	exit;
}

if (isset($_REQUEST["action"]) && $_REQUEST["action"] == 'web_hook')
{
	if (CModule::IncludeModule("seo") && CModule::IncludeModule("socialservices"))
	{
		\Bitrix\Seo\WebHook\Service::listen();
		exit;
	}
}

if (isset($_REQUEST["action"]) && $_REQUEST["action"] === 'catalog_callback')
{
	if (CModule::IncludeModule("seo") && CModule::IncludeModule("socialservices"))
	{
		$serviceLocator = \Bitrix\Main\DI\ServiceLocator::getInstance();
		if ($serviceLocator->has('seo.catalog.webhook.handler'))
		{
			$serviceLocator->get('seo.catalog.webhook.handler')->handle();
		}
	}
}

if(CModule::IncludeModule("socialservices") && CSocServAuthManager::CheckUniqueKey())
{
	if(isset($_REQUEST["authresult"]))
	{
		$clearCache($clientId, $engine);

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
