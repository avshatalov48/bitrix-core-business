<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Recepients;

use Bitrix\Socialnetwork\Permission\User\UserModel;
use Bitrix\Socialnetwork\Internals\Space\LiveWatch\LiveWatchService;

class Recepient
{
	private int $id;
	private bool $isOnline;

	public function __construct(int $id, bool $isOnline = true)
	{
		$this->id = $id;
		$this->isOnline = $isOnline;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function isOnline(): bool
	{
		return $this->isOnline;
	}

	public function isWatchingSpaces(): bool
	{
		return $this->isOnline && LiveWatchService::getInstance()->isUserWatchingSpaces($this->id);
	}

	public function getAccessCodes(): array
	{
		// merge for calendar attendees codes support
		return array_merge(UserModel::createFromId($this->id)->getAccessCodes(), ['UA']);
	}
}