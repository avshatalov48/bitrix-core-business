<?php

namespace Bitrix\Im\V2\Integration\Socialnetwork;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Main\Loader;
use Bitrix\Pull\Event;
use Bitrix\Socialnetwork\Collab\CollabFeature;
use Bitrix\Socialnetwork\Collab\Requirement;

class Collab
{
	public static function isAvailable(): bool
	{
		return
			Loader::includeModule('socialnetwork')
			&& CollabFeature::isOn()
			&& Requirement::check()->isSuccess()
		;
	}

	public static function isCreationAvailable(): bool
	{
		$userId = User::getCurrent()->getId() ?? 0;

		return self::isAvailable() && Requirement::checkWithAccess($userId)->isSuccess();
	}

	public static function onEntityCountersUpdate(int $collabId, array $counters, string $entityType): void
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return;
		}

		$collab = \Bitrix\Socialnetwork\Collab\Provider\CollabProvider::getInstance()->getCollab($collabId);
		if ($collab === null)
		{
			return;
		}

		$chatId = $collab->getChatId();
		$chat = Chat::getInstance($chatId);
		if (!$chat instanceof Chat\CollabChat)
		{
			return;
		}

		$dialogId = $chat->getDialogId();

		if (!Loader::includeModule('pull'))
		{
			return;
		}

		if (empty($counters))
		{
			return;
		}

		foreach ($counters as $userId => $counter)
		{
			Event::add($userId, [
				'module_id' => 'im',
				'command' => 'updateCollabEntityCounter',
				'params' => [
					'dialogId' => $dialogId,
					'chatId' => $chatId,
					'entity' => $entityType,
					'counter' => $counter,
				],
				'extra' => \Bitrix\Im\Common::getPullExtra(),
			]);
		}
	}
}