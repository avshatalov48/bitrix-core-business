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

$messageId = $messageFields['message_id'];
$messageStatus = \Bitrix\MessageService\Sender\Sms\ISmsCenter::resolveStatus($messageFields['status']);

if ($messageStatus === null)
{
	\Bitrix\Main\Application::getInstance()->terminate();
}

$message = \Bitrix\MessageService\Internal\Entity\MessageTable::getList([
	'select' => ['ID'],
	'filter' => [
		'=SENDER_ID' => 'ismscenter',
		'=EXTERNAL_ID' => $messageId
	]
])->fetch();

if ($message)
{
	\Bitrix\MessageService\Internal\Entity\MessageTable::update($message['ID'], ['STATUS_ID' => $messageStatus]);
	$message['STATUS_ID'] = $messageStatus;
	\Bitrix\MessageService\Integration\Pull::onMessagesUpdate([$message]);
}
\Bitrix\Main\Application::getInstance()->terminate();
