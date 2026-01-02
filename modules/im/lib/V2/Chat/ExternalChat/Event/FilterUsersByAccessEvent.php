<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

use Bitrix\Im\V2\Chat\ExternalChat;

class FilterUsersByAccessEvent extends ChatEvent
{
	public function __construct(ExternalChat $chat, array $userIds)
	{
		$parameters = ['userIds' => $userIds];

		parent::__construct($chat, $parameters);
	}

	protected function getActionName(): string
	{
		return 'FilterUsersByAccess';
	}

	public function getUserIds(): array
	{
		return $this->parameters['userIds'];
	}

	public function getUsersWithAccess(): array
	{
		$userIds = $this->getParameterFromResult('userIds');
		if (!is_array($userIds))
		{
			return [];
		}

		return $userIds;
	}
}