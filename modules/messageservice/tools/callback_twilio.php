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

if(
	!isset($_POST['SmsSid'])
	|| !isset($_POST['SmsStatus'])
	|| !preg_match('|[A-Z0-9]{34}|i', $_POST['SmsSid'])
	|| !Main\Loader::includeModule("messageservice")
)
{
	Main\Application::getInstance()->terminate();
}

$messageId = (string)$_POST['SmsSid'];
$externalStatus = (string)$_POST['SmsStatus'];

$message = \Bitrix\MessageService\Message::loadByExternalId(Sender\Sms\Twilio::ID, $messageId);
if ($message && $externalStatus != '')
{
	$message->updateStatusByExternalStatus($externalStatus);
}

Main\Application::getInstance()->terminate();