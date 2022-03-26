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

if(!isset($_POST['data']) || !is_array($_POST['data']) || !Main\Loader::includeModule("messageservice"))
{
	Main\Application::getInstance()->terminate();
}

$smsStatuses = array();
foreach ($_POST["data"] as $entry)
{
	$lines = explode("\n", $entry);
	if (count($lines) < 3 || $lines[0] !== 'sms_status')
	{
		continue;
	}

	$messageId = (string)$lines[1];
	$externalStatus = (string)$lines[2];

	$message = \Bitrix\MessageService\Message::loadByExternalId(Sender\Sms\SmsRu::ID, $messageId);
	if ($message)
	{
		$message->updateStatusByExternalStatus($externalStatus);
	}
}

echo '100'; // SMS.RU required success answer code

Main\Application::getInstance()->terminate();