<?php

define("NOT_CHECK_PERMISSIONS", true);
define("EXTRANET_NO_REDIRECT", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$jsonText = file_get_contents('php://input');
$messageFields = $jsonText ? \Bitrix\Main\Web\Json::decode($jsonText) : null;

if (
	!$messageFields
	|| !isset($messageFields['id_message'])
	|| !isset($messageFields['state'])
	|| !CModule::IncludeModule("messageservice")
)
{
	\Bitrix\Main\Application::terminate();
}

$messageId = $messageFields['id_message'];
$messageStatus = \Bitrix\MessageService\Sender\Sms\SmsLineBy::resolveStatus($messageFields['state']['state']);

if ($messageStatus === null)
{
	\Bitrix\Main\Application::terminate();
}

$message = \Bitrix\MessageService\Internal\Entity\MessageTable::getList(array(
	'select' => array('ID'),
	'filter' => array(
		'=SENDER_ID' => 'smslineby',
		'=EXTERNAL_ID' => $messageId
	)
))->fetch();

if ($message)
{
	\Bitrix\MessageService\Internal\Entity\MessageTable::update($message['ID'], array('STATUS_ID' => $messageStatus));
	$message['STATUS_ID'] = $messageStatus;
	\Bitrix\MessageService\Integration\Pull::onMessagesUpdate(array($message));
}
\Bitrix\Main\Application::terminate();