<?php

use Bitrix\Main;
use Bitrix\MessageService\Sender;

define('NOT_CHECK_PERMISSIONS', true);
define('EXTRANET_NO_REDIRECT', true);
define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

if (!Main\Loader::includeModule('messageservice'))
{
	Main\Application::getInstance()->terminate();
}

$request = Main\Web\Json::decode(Main\HttpRequest::getInput());

$messageId = $request['smsOutMessageId'];
$externalStatus = (string)$request['dlvStatus'];

$message = \Bitrix\MessageService\Message::loadByExternalId(Sender\Sms\SmsEdnaru::ID, $messageId);
if ($message && $externalStatus != '')
{
	$message->updateStatusByExternalStatus($externalStatus);
}

Main\Application::getInstance()->terminate();
