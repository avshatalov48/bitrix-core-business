<?php

namespace Bitrix\Calendar\Integration\Tasks;

class TaskQueryParameter
{
	public const TYPE_USER = 'user';
	public const TYPE_GROUP = 'group';

	private int $ownerId;
	private int $userId;
	private string $type = '';

	public function __construct(int $userId = 0)
	{
		$this->userId = $userId;
	}

	public function getOwnerId(): int
	{
		return $this->ownerId;
	}

	public function setOwnerId(int $ownerId): static
	{
		$this->ownerId = $ownerId;
		return $this;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): static
	{
		$this->type = $type;
		return $this;
	}

	public function isUserType(): bool
	{
		return $this->type === static::TYPE_USER;
	}

	public function isGroupType(): bool
	{
		return $this->type === static::TYPE_GROUP;
	}
}