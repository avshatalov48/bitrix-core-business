<?php

use Bitrix\ImConnector\Library;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\MessageService\Internal\Entity\MessageTable;
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

if (
	!$messageFields
	|| !Loader::includeModule('messageservice')
	|| !Loader::includeModule('imconnector')
)
{
	\Bitrix\Main\Application::getInstance()->terminate();
}

if (isset($messageFields['dlvStatus']))
{
	$messageId = $messageFields['imOutMessageId'];
	$messageStatus = Ednaru::resolveStatus($messageFields['dlvStatus']);

	if (is_null($messageStatus))
	{
		\Bitrix\Main\Application::getInstance()->terminate();
	}

	$message = MessageTable::getList([
		'select' => ['ID'],
		'filter' => [
			'=SENDER_ID' => 'ednaru',
			'=EXTERNAL_ID' => $messageId,
		]
	])->fetch();

	if ($message)
	{
		MessageTable::update($message['ID'], ['STATUS_ID' => $messageStatus]);
		$message['STATUS_ID'] = $messageStatus;

		if (\Bitrix\MessageService\Integration\Pull::canUse())
		{
			\Bitrix\MessageService\Integration\Pull::onMessagesUpdate([$message]);
		}
	}
}
else if (isset($messageFields['imSubject']))
{
	$messageFields['CONNECTOR'] = Library::ID_EDNA_WHATSAPP_CONNECTOR;
	$portal = new \Bitrix\ImConnector\Input($messageFields);
	$portal->reception();
}
\Bitrix\Main\Application::getInstance()->terminate();
