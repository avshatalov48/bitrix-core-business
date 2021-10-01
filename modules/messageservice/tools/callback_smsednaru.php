<?php

use Bitrix\Main;

define('NOT_CHECK_PERMISSIONS', true);
define('EXTRANET_NO_REDIRECT', true);
define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

try
{
	if (!Main\Loader::includeModule('messageservice'))
	{
		Main\Application::getInstance()->terminate();
	}

	$request = Main\Web\Json::decode(Main\HttpRequest::getInput());

	$messageId = $request['smsOutMessageId'];
	$messageStatus = \Bitrix\MessageService\Sender\Sms\SmsEdnaru::resolveStatus($request['dlvStatus']);

	if (is_null($messageStatus))
	{
		Main\Application::getInstance()->terminate();
	}

	$message = \Bitrix\MessageService\Internal\Entity\MessageTable::getList([
		'select' => ['ID'],
		'filter' => [
			'=SENDER_ID' => 'smsednaru',
			'=EXTERNAL_ID' => $messageId,
		]
	])->fetch();

	if ($message)
	{
		\Bitrix\MessageService\Internal\Entity\MessageTable::update($message['ID'], ['STATUS_ID' => $messageStatus]);
		$message['STATUS_ID'] = $messageStatus;

		\Bitrix\MessageService\Integration\Pull::onMessagesUpdate([$message]);
	}

	CMain::FinalActions();
}
finally
{
	Main\Application::getInstance()->terminate();
}
