<?php

namespace Bitrix\Calendar\Access\Model;

use Bitrix\Main\Access\AccessibleItem;

final class SyncModel implements AccessibleItem
{
	private ?int $id = null;
	private ?string $entityType = null;
	private ?int $entityId = null;

	public static function createFromArray(array $connection): ?self
	{
		if (empty($connection['ID']) || empty($connection['ENTITY_TYPE']) || empty($connection['ENTITY_ID']))
		{
			return null;
		}

		return self::createFromId((int)$connection['ID'])
			->setEntityType($connection['ENTITY_TYPE'])
			->setEntityId((int)$connection['ENTITY_ID'])
		;
	}

	public static function createFromId(int $itemId): self
	{
		return self::createNew()->setId($itemId);
	}

	public static function createNew(): self
	{
		return new self();
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getEntityType(): string
	{
		return $this->entityType;
	}

	public function setEntityType(string $entityType): self
	{
		$this->entityType = $entityType;

		return $this;
	}

	public function getEntityId(): int
	{
		return $this->entityId;
	}

	public function setEntityId(int $entityId): self
	{
		$this->entityId = $entityId;

		return $this;
	}
}
