<?php

define("NOT_CHECK_PERMISSIONS", true);
define("EXTRANET_NO_REDIRECT", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$jsonText = \Bitrix\Main\HttpRequest::getInput();
$messageFields = $jsonText ? \Bitrix\Main\Web\Json::decode($jsonText) : null;

if (
	!$messageFields
	|| !isset($messageFields['message_id'])
	|| !isset($messageFields['status'])
	|| !CModule::IncludeModule("messageservice")
)
{
	\Bitrix\Main\Application::getInstance()->terminate();
}

$messageId = (string)$messageFields['message_id'];
$externalStatus = (string)$messageFields['status'];

$message = \Bitrix\MessageService\Message::loadByExternalId(\Bitrix\MessageService\Sender\Sms\ISmsCenter::ID, $messageId);
if ($message && $externalStatus != '')
{
	$message->updateStatusByExternalStatus($externalStatus);
}

\Bitrix\Main\Application::getInstance()->terminate();
