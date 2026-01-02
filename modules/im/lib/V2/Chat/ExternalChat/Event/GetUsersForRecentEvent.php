<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

class GetUsersForRecentEvent extends ChatEvent
{
	protected function getActionName(): string
	{
		return 'GetUsersForRecent';
	}

	public function getUsersForRecent(): array
	{
		$userIds = $this->getParameterFromResult('userIds');
		if (!is_array($userIds))
		{
			return [];
		}

		return $userIds;
	}
}