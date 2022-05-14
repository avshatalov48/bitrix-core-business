<?php

namespace Bitrix\Socialnetwork\Internals\Counter\Push;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Integration\Pull\PushService;

class PushSender
{
	public const COMMAND_USER = 'user_counter';

	/**
	 * @param array $users
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function sendUserCounters(array $userIds, array $countersData = []): void
	{
		if (
			!ModuleManager::isModuleInstalled('pull')
			|| !Loader::includeModule('pull')
		)
		{
			return;
		}

		foreach ($userIds as $userId)
		{
			$pushData = $countersData;
			$pushData['userId'] = $userId;

			$this->createPush([$userId], self::COMMAND_USER, $pushData);
		}
	}

	/**
	 * @param array $userIds
	 * @param string $command
	 * @param array $params
	 */
	public function createPush(array $userIds, string $command, array $params): void
	{
		if (!ModuleManager::isModuleInstalled('pull') || !Loader::includeModule('pull'))
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