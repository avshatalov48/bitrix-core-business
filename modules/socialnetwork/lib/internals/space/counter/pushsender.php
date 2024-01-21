<?php

namespace Bitrix\Socialnetwork\Internals\Space\Counter;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Integration\Pull\PushService;

class PushSender
{
	public const COMMAND_USER_SPACES = 'user_spaces_counter';

	public function createPush(array $userIds, string $command, array $params)
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