<?php

use Bitrix\Calendar\Sync\Managers\PushManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Sender\PushSender;
use Bitrix\Main\Application;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;

define('NOT_CHECK_PERMISSIONS', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$allowedFields = ['HTTP_X_GOOG_CHANNEL_ID' => true, 'HTTP_X_GOOG_RESOURCE_ID' => true];

$fields = array_intersect_key($_SERVER, $allowedFields);

if (empty($fields))
{
	exit;
}

foreach ($fields as $field)
{
	if (!preg_match('/^([A-z\d\-=])+$/', $field))
	{
		exit;
	}
}

$channelId = $fields['HTTP_X_GOOG_CHANNEL_ID'];
$resourceId = $fields['HTTP_X_GOOG_RESOURCE_ID'];

Loader::includeModule('calendar');

try
{
	if (\Bitrix\Calendar\Synchronization\Public\Service\SynchronizationFeature::isOn())
	{
		$sender = ServiceLocator::getInstance()->get(PushSender::class);

		$sender->sendGooglePushMessage($channelId, $resourceId);
	}
	else
	{
		Loader::includeModule('dav');

		(new PushManager())->handlePush($channelId, $resourceId);
	}
}
catch (\Throwable)
{}

Application::getInstance()->end();
