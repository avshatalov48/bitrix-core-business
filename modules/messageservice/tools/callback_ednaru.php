<?php

use Bitrix\ImConnector\Library;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\MessageService\Providers\Edna\WhatsApp\EdnaRuIncomingMessage;
use Bitrix\MessageService\Sender\Sms\Ednaru;

define("NOT_CHECK_PERMISSIONS", true);
define("EXTRANET_NO_REDIRECT", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$jsonText = \Bitrix\Main\HttpRequest::getInput();
$messageFields = $jsonText ? Json::decode($jsonText) : null;

if (!$messageFields || !Loader::includeModule('messageservice'))
{
	\Bitrix\Main\Application::getInstance()->terminate();
}

// region Old API
if (isset($messageFields['dlvStatus']) && isset($messageFields['imOutMessageId']))
{
	$messageId = $messageFields['imOutMessageId'];
	$externalStatus = (string)$messageFields['dlvStatus'];

	$message = \Bitrix\MessageService\Message::loadByExternalId(Ednaru::ID, $messageId);
	if ($message && $externalStatus != '')
	{
		$message->updateStatusByExternalStatus($externalStatus);
	}
}
else if (isset($messageFields['imSubject']) && Loader::includeModule('imconnector'))
{
	$messageFields['CONNECTOR'] = Library::ID_EDNA_WHATSAPP_CONNECTOR;
	$portal = new \Bitrix\ImConnector\Input($messageFields);
	$portal->reception();
}
//endregion
// region New API
else if (isset($messageFields['requestId'], $messageFields['status']))
{
	$messageId = $messageFields['requestId'];
	$externalStatus = (string)$messageFields['status'];

	$message = \Bitrix\MessageService\Message::loadByExternalId(Ednaru::ID, $messageId);
	if ($message && $externalStatus !== '')
	{
		$message->updateStatusByExternalStatus($externalStatus);
	}
}
else if (isset($messageFields['userInfo']) && Loader::includeModule('imconnector'))
{
	$addResult = EdnaRuIncomingMessage::addMessage(Ednaru::ID, $messageFields);

	if (!$addResult->isSuccess())
	{
		\Bitrix\Main\Application::getInstance()->terminate();
	}
	$messageFields['internalId'] = $addResult->getId();

	Application::getInstance()->addBackgroundJob(
		[EdnaRuIncomingMessage::class, 'sendMessageToChat'],
		[$messageFields],
		Application::JOB_PRIORITY_NORMAL
	);

}
// endregion
\Bitrix\Main\Application::getInstance()->terminate();
