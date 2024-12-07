<?php

namespace Bitrix\Calendar\OpenEvents\Item;

use Bitrix\Calendar\EventCategory\Dto\EventCategoryPermissions;
use Bitrix\Main\Type\Contract\Arrayable;

final class Category implements Arrayable
{
	public function __construct(
		public readonly int $id,
		public readonly bool $closed,
		public readonly string $name,
		public readonly string $description,
		public readonly int $creatorId,
		public readonly int $eventsCount,
		public readonly EventCategoryPermissions $permissions,
		public readonly int $channelId,
		public readonly ?bool $isMuted = false,
		public readonly ?bool $isBanned = false,
		public readonly ?int $newCount = null,
		public readonly ?int $updatedAt = null,
	)
	{
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'closed' => $this->closed,
			'name' => $this->name,
			'description' => $this->description,
			'creatorId' => $this->creatorId,
			'eventsCount' => $this->eventsCount,
			'permissions' => $this->permissions->toArray(),
			'channelId' => $this->channelId,
			'isMuted' => $this->isMuted,
			'isBanned' => $this->isBanned,
			'newCount' => $this->newCount,
			'updatedAt' => $this->updatedAt,
		];
	}
}
