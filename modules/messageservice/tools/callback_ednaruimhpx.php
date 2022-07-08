<?php

use Bitrix\Main\Loader;
use Bitrix\MessageService\Sender\Sms\EdnaruImHpx;

define("NOT_CHECK_PERMISSIONS", true);
define("EXTRANET_NO_REDIRECT", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!Loader::includeModule('messageservice'))
{
	\Bitrix\Main\Application::getInstance()->terminate();
}

$provider = new EdnaruImHpx();
$response = $provider->processIncomingRequest(\Bitrix\Main\HttpRequest::getInput());

if ($response->statusCode && $response->statusCode !== 200)
{
	CHTTP::SetStatus($response->statusCode);
}
echo $response->body;

\Bitrix\Main\Application::getInstance()->terminate();
