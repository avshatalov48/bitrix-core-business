<?php

use Bitrix\Main;
use Bitrix\MessageService\Sender;

define("NOT_CHECK_PERMISSIONS", true);
define("EXTRANET_NO_REDIRECT", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$jsonText = Main\HttpRequest::getInput();
$messageFields = $jsonText ? Main\Web\Json::decode($jsonText) : null;

if (
	!$messageFields
	|| !isset($messageFields['id_message'])
	|| !isset($messageFields['state'])
	|| !CModule::IncludeModule("messageservice")
)
{
	Main\Application::getInstance()->terminate();
}

$messageId = $messageFields['id_message'];
$externalStatus = (string)$messageFields['state']['state'];

$message = \Bitrix\MessageService\Message::loadByExternalId(Sender\Sms\SmsLineBy::ID, $messageId);
if ($message && $externalStatus != '')
{
	$message->updateStatusByExternalStatus($externalStatus);
}

Main\Application::getInstance()->terminate();