<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Activity;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Activity\Command\DeleteLastActivityCommand;
use Bitrix\Socialnetwork\Collab\Control\Activity\Command\SetLastActivityCommand;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;

class LastActivityTrigger
{
	public static function execute(int $userId, int $collabId): void
	{
		if ($userId <= 0)
		{
			return;
		}

		$collab = CollabRegistry::getInstance()->get($collabId);
		if ($collab === null)
		{
			return;
		}

		$command = (new SetLastActivityCommand())
			->setUserId($userId)
			->setCollabId($collabId);

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.activity.service');

		$service->set($command);
	}

	public static function remove(int $collabId = 0, array $userIds = []): void
	{
		$command = new DeleteLastActivityCommand();
		if ($collabId > 0)
		{
			$command->setCollabId($collabId);
		}

		if (!empty($userIds))
		{
			$command->setUserIds($userIds);
		}

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.activity.service');

		$service->delete($command);
	}
}