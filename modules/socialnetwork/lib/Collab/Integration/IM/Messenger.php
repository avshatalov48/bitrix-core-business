<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM;

use Bitrix\Im\V2\Chat\CollabChat;
use Bitrix\Im\V2\Chat\Update\UpdateFields;
use Bitrix\Im\V2\Chat\Update\UpdateService;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Integration\Im\Chat\Workgroup;

class Messenger
{
	public static function getInstance(): ?\Bitrix\Im\V2\Service\Messenger
	{
		if (!Loader::includeModule('im'))
		{
			return null;
		}

		return \Bitrix\Im\V2\Service\Messenger::getInstance();
	}

	public static function getUpdateService(int $chatId, array $fields = []): ?UpdateService
	{
		if (!Loader::includeModule('im'))
		{
			return null;
		}

		$chat = static::getInstance()?->getChat($chatId);
		if (!$chat instanceof CollabChat)
		{
			return null;
		}

		$updateFields = UpdateFields::create($fields);


		return new UpdateService($chat, $updateFields);
	}

	public static function setManagers(int $collabId, array $managers): void
	{
		if (!Loader::includeModule('im'))
		{
			return;
		}

		Workgroup::setChatManagers(
			[
				'group_id' => $collabId,
				'user_id' => $managers,
				'set' => true,
			]
		);
	}

	public static function unsetManagers(int $collabId, array $users): void
	{
		if (!Loader::includeModule('im'))
		{
			return;
		}

		Workgroup::setChatManagers(
			[
				'group_id' => $collabId,
				'user_id' => $users,
				'set' => false,
			]
		);
	}

	public static function synchronizeCollabChat(int $chatId, array $fields): void
	{
		if (!Loader::includeModule('im'))
		{
			return;
		}

		static::getUpdateService($chatId, $fields)?->updateChat();
	}
}