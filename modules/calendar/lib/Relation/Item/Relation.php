<?php

namespace Bitrix\Calendar\Relation\Item;

use Bitrix\Main\Type\Contract\Arrayable;

class Relation implements Arrayable
{
	private ?Entity $entity = null;
	private ?Owner $owner = null;

	public function __construct(private int $eventId)
	{}

	public function getEventId(): int
	{
		return $this->eventId;
	}

	public function getEntity(): ?Entity
	{
		return $this->entity;
	}

	public function setEntity(Entity $entity): self
	{
		$this->entity = $entity;

		return $this;
	}

	public function getOwner(): ?Owner
	{
		return $this->owner;
	}

	public function setOwner(Owner $owner): self
	{
		$this->owner = $owner;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'eventId' => $this->eventId,
			'owner' => $this->owner?->toArray(),
			'entity' => $this->entity?->toArray(),
		];
	}
}
