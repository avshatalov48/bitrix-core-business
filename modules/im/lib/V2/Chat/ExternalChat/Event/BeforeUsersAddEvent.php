<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

use Bitrix\Im\V2\Chat\ExternalChat;
use Bitrix\Im\V2\Relation\AddUsersConfig;

class BeforeUsersAddEvent extends ChatEvent
{
	public function __construct(ExternalChat $chat, array $userIds, AddUsersConfig $config)
	{
		$parameters = ['userIds' => $userIds, 'config' => $config];

		parent::__construct($chat, $parameters);
	}

	protected function getActionName(): string
	{
		return 'BeforeUsersAdd';
	}

	public function getUserIds(): array
	{
		return $this->parameters['userIds'];
	}

	public function getAddUsersConfig(): AddUsersConfig
	{
		return $this->parameters['config'];
	}

	public function getNewUserIds(): ?array
	{
		return $this->getParameterFromResult('userIds');
	}

	public function getNewAddUsersConfig(): ?AddUsersConfig
	{
		return $this->getParameterFromResult('config');
	}
}
