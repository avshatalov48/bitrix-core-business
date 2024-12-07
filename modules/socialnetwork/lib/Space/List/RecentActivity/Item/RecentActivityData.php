<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Item;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\Type\DateTime;

final class RecentActivityData implements Arrayable
{
	private ?int $id = null;
	private int $spaceId;
	private int $userId;
	private ?string $typeId = null;
	private ?int $entityId = null;
	private ?DateTime $dateTime = null;
	private ?string $description = '';
	private ?int $secondaryEntityId = null;

	public function setId(?int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setSpaceId(int $spaceId): self
	{
		$this->spaceId = $spaceId;

		return $this;
	}

	public function getSpaceId(): int
	{
		return $this->spaceId;
	}

	public function setUserId(int $userId): self
	{
		$this->userId = $userId;

		return $this;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setTypeId(?string $typeId): self
	{
		$this->typeId = $typeId;

		return $this;
	}

	public function getTypeId(): ?string
	{
		return $this->typeId;
	}

	public function setEntityId(?int $entityId): self
	{
		$this->entityId = $entityId;

		return $this;
	}

	public function getEntityId(): ?int
	{
		return $this->entityId;
	}

	public function setDateTime(?DateTime $dateTime): self
	{
		$this->dateTime = $dateTime;

		return $this;
	}

	public function getDateTime(): ?DateTime
	{
		return $this->dateTime;
	}

	public function setDescription(?string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setSecondaryEntityId(?int $secondaryEntityId): self
	{
		$this->secondaryEntityId = $secondaryEntityId;

		return $this;
	}

	public function getSecondaryEntityId(): ?int
	{
		return $this->secondaryEntityId;
	}

	public function toArray(): array
	{
		return [
			'spaceId' => $this->spaceId,
			'userId' => $this->userId,
			'typeId' => $this->typeId,
			'entityId' => $this->entityId,
			'secondaryEntityId' => $this->secondaryEntityId,
			'timestamp' => $this->dateTime?->getTimestamp(),
			'description' => $this->description,
		];
	}
}