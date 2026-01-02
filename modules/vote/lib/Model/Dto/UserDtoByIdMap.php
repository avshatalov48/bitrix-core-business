<?php

namespace Bitrix\Vote\Model\Dto;

use JsonSerializable;

class UserDtoByIdMap
{
	/**
	 * @var array<int, JsonSerializable>
	 */
	private array $map = [];

	public function add(int $userId, JsonSerializable $dto): self
	{
		$this->map[$userId] = $dto;

		return $this;
	}

	public function get(int $userId): ?JsonSerializable
	{
		return $this->map[$userId] ?? null;
	}

	/**
	 * @return JsonSerializable[]
	 */
	public function toArray(): array
	{
		return $this->map;
	}
}