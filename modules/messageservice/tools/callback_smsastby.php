<?php
define("NOT_CHECK_PERMISSIONS", true);
define("EXTRANET_NO_REDIRECT", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!\Bitrix\Main\Loader::includeModule("messageservice"))
{
	\Bitrix\Main\Application::getInstance()->terminate();
}

foreach ($_POST as $smsId => $externalStatus)
{
	$message = \Bitrix\MessageService\Message::loadByExternalId(
		\Bitrix\MessageService\Sender\Sms\SmsAssistentBy::ID,
		$smsId
	);
	if ($message)
	{
		$message->updateStatusByExternalStatus($externalStatus);
	}
}

\Bitrix\Main\Application::getInstance()->terminate();