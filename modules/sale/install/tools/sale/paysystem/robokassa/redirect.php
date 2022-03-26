<?php

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Landing;

define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define('DisableEventsCheck', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$request = Main\Application::getInstance()->getContext()->getRequest();

if ($request->get('SignatureValue'))
{
	$redirectUrl = Main\Config\Option::get('sale', 'sale_ps_success_path', '/');
}
else
{
	$redirectUrl = Main\Config\Option::get('sale', 'sale_ps_fail_path', '/');
}

$shpRedirectUrl = $request->get('SHP_BX_REDIRECT_URL');
if ($shpRedirectUrl)
{
	$domain = (new Main\Web\Uri($shpRedirectUrl))->getHost();
	if ($domain)
	{
		$isSiteExists = (bool)Main\SiteTable::getList([
			'select' => ['LID'],
			'filter' => [
				'%SERVER_NAME' => $domain,
			],
			'limit' => 1,
		])->fetch();

		if (!$isSiteExists)
		{
			$isSiteExists = (bool)Main\SiteDomainTable::getList([
				'select' => ['LID'],
				'filter' => [
					'%DOMAIN' => $domain,
				],
				'limit' => 1,
			])->fetch();
		}

		if (!$isSiteExists && Main\Loader::includeModule('landing'))
		{
			$isSiteExists = (bool)Landing\Site::getList([
				'select' => ['ID'],
				'filter' => [
					'CHECK_PERMISSIONS' => 'N',
					'=DOMAIN.DOMAIN' => $domain,
				],
				'limit' => 1,
			])->fetch();
		}

		if ($isSiteExists)
		{
			$redirectUrl = $shpRedirectUrl;
		}
	}
}
else
{
	Main\Loader::includeModule('sale');

	$debugInfo = http_build_query($request->toArray(), '', "\n");
	if (empty($debugInfo))
	{
		$debugInfo = file_get_contents('php://input');
	}

	Sale\PaySystem\Logger::addDebugInfo('Robokassa redirect request: '.($debugInfo ?: 'empty'));
}

LocalRedirect($redirectUrl, true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
