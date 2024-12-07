<?php

namespace Bitrix\Calendar\Core\EventCategory;

use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Core\Event\Properties\AttendeeCollection;

final class EventCategory implements EntityInterface
{
	private ?int $id = null;
	private ?string $name = null;
	private ?int $creatorId = null;
	private ?bool $closed = null;
	private ?string $description = null;
	private ?AttendeeCollection $attendees = null;
	private ?array $accessCodes = null;
	private ?bool $deleted = null;
	private ?int $channelId = null;
	private ?int $eventsCount = 0;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(?int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(?string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getCreatorId(): ?int
	{
		return $this->creatorId;
	}

	public function setCreatorId(?int $creatorId): self
	{
		$this->creatorId = $creatorId;

		return $this;
	}

	public function getClosed(): ?bool
	{
		return $this->closed;
	}

	public function setClosed(?bool $closed): self
	{
		$this->closed = $closed;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function getAttendees(): ?AttendeeCollection
	{
		if (is_null($this->attendees))
		{
			return new AttendeeCollection();
		}

		return $this->attendees;
	}

	public function setAttendees(?AttendeeCollection $collection): EventCategory
	{
		$this->attendees = $collection;

		return $this;
	}

	public function getAccessCodes(): ?array
	{
		return $this->accessCodes;
	}

	public function setAccessCodes(?array $accessCodes): self
	{
		$this->accessCodes = $accessCodes;
		$this->initAttendees();

		return $this;
	}

	public function getDeleted(): ?bool
	{
		return $this->deleted;
	}

	public function setDeleted(?bool $deleted): self
	{
		$this->deleted = $deleted;

		return $this;
	}

	public function getChannelId(): ?int
	{
		return $this->channelId;
	}

	public function setChannelId(?int $channelId): self
	{
		$this->channelId = $channelId;

		return $this;
	}

	public function getEventsCount(): ?int
	{
		return $this->eventsCount;
	}

	public function setEventsCount(?int $eventsCount): self
	{
		$this->eventsCount = $eventsCount;

		return $this;
	}

	protected function initAttendees(): void
	{
		if ($this->getAccessCodes())
		{
			$collection = new AttendeeCollection();
			$collection->setAttendeesCodes($this->getAccessCodes());

			$this->attendees = $collection;
		}
	}
}