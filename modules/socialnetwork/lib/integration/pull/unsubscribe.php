<?php

namespace Bitrix\Socialnetwork\Integration\Pull;

use Bitrix\Main\Loader;

class Unsubscribe
{
	public function resetByTags(array $tags, array $userIds): void
	{
		foreach ($tags as $tag)
		{
			foreach ($userIds as $userId)
			{
				$this->resetByTag($tag, $userId);
			}
		}
	}

	public function resetAllButIgnored(array $tags, array $usersToIgnore): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		foreach ($tags as $tag)
		{
			$watching = \CPullWatch::GetUserList($tag);

			foreach ($watching as $watchingUserId => $user)
			{
				if (!in_array($watchingUserId, $usersToIgnore))
				{
					$this->resetByTag($tag, $watchingUserId);
				}
			}
		}
	}

	private function resetByTag(string $tag, int $userId): void
	{
		$params = [
			'eventName' => PushCommand::PULL_UNSUBSCRIBE,
			'userId' => $userId,
		];
		PushService::addEventByTag($tag, $params);
	}
}
