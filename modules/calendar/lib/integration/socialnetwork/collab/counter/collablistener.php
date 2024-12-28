<?php

namespace Bitrix\Calendar\Integration\SocialNetwork\Collab\counter;

use Bitrix\Calendar\Internals\Counter;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Entity\Event\EventDispatcher;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;

class CollabListener
{
	public function notify(array $groupsToNotify): void
	{
		if (!$this->isAvailable())
		{
			return;
		}

		foreach ($groupsToNotify as $groupId => $userIds)
		{
			$group = CollabRegistry::getInstance()->get($groupId);

			if ($group === null)
			{
				continue;
			}

			$this->notifyDispatcher($groupId, $userIds);
		}
	}

	private function notifyDispatcher(int $groupId, array $userIds): void
	{
		$counters = [];
		foreach ($userIds as $userId)
		{
			$counter = Counter::getInstance($userId)->get(Counter\CounterDictionary::COUNTER_GROUP_INVITES, $groupId);
			$counters[$userId] = $counter;
		}

		EventDispatcher::onCountersRecount($groupId, $counters, 'calendar');
	}

	private function isAvailable(): bool
	{
		return Loader::includeModule('socialnetwork');
	}
}
