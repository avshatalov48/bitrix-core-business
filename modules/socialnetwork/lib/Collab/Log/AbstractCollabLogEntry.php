<?php

namespace Bitrix\Socialnetwork\Collab\Log;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntity;

abstract class AbstractCollabLogEntry implements Arrayable
{
	protected ?int $id;
	protected int $userId;
	protected int $collabId;
	protected DateTime $dateTime;
	protected ?CollabEntity $collabEntity = null;
	private array $data = [];

	abstract static public function getEventType(): string;

	final public function __construct(
		int $userId,
		int $collabId,
		?CollabEntity $collabEntity = null,
		?int $id = null,
		?DateTime $dateTime = null,
	)
	{
		$this->id = $id;
		$this->userId = $userId;
		$this->collabId = $collabId;
		$this->collabEntity = $collabEntity;
		$this->dateTime = $dateTime ?? new DateTime();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setUserId(int $userId): static
	{
		$this->userId = $userId;

		return $this;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setDateTime(DateTime $dateTime): static
	{
		$this->dateTime = $dateTime;

		return $this;
	}

	public function getDateTime(): DateTime
	{
		return $this->dateTime;
	}

	public function setEntity(?CollabEntity $entity): static
	{
		$this->collabEntity = $entity;

		return $this;
	}

	public function getEntity(): ?CollabEntity
	{
		return $this->collabEntity;
	}

	public function setData(array $data): static
	{
		$this->data = $data;

		return $this;
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function getCollabId(): int
	{
		return $this->collabId;
	}

	public function setCollabId(int $collabId): static
	{
		$this->collabId = $collabId;

		return $this;
	}

	protected function setDataValue(string $key, mixed $value): static
	{
		$this->data[$key] = $value;

		return $this;
	}

	protected function getDataValue(string $key): mixed
	{
		return $this->data[$key] ?? null;
	}

	public function toArray(): array
	{
		$result = [
			'USER_ID' => $this->getUserId(),
			'COLLAB_ID' => $this->getCollabId(),
			'TYPE' => $this->getEventType(),
			'ENTITY_TYPE' => $this->getEntity()?->getType()->value,
			'ENTITY_ID' => $this->getEntity()?->getId(),
			'DATETIME' => $this->getDateTime(),
			'DATA' => $this->getData(),
		];

		if ($this->getId() > 0)
		{
			$result['ID'] = $this->getId();
		}

		return $result;
	}
}
