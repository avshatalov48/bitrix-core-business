<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Push;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Integration\Pull\PushService;

class PushSender
{
	/**
	 * @param array $userIds
	 * @param array $eventData
	 */
	public static function sendPersonalEvent(array $userIds, string $command = '', array $eventData = []): void
	{
		if (
			$command === ''
			|| !ModuleManager::isModuleInstalled('pull')
			|| !Loader::includeModule('pull')
		)
		{
			return;
		}

		foreach ($userIds as $userId)
		{
			$pushData = $eventData;
			$pushData['userId'] = $userId;

			self::createPush([ $userId ], $command, $pushData);
		}
	}

	/**
	 * @param array $userIds
	 * @param string $command
	 * @param array $params
	 */
	protected static function createPush(array $userIds, string $command, array $params): void
	{
		if (
			!ModuleManager::isModuleInstalled('pull')
			|| !Loader::includeModule('pull')
		)
		{
			return;
		}

		PushService::addEvent($userIds, [
			'module_id' => PushService::MODULE_NAME,
			'command' => $command,
			'params' => $params
		]);
	}
}
