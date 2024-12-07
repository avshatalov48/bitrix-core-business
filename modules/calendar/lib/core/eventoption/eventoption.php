<?php

namespace Bitrix\Calendar\Core\EventOption;

use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Core\EventCategory\EventCategory;

final class EventOption implements EntityInterface
{
	private ?int $id = null;
	private ?int $eventId = null;
	private ?int $categoryId = null;
	private ?EventCategory $category = null;
	private ?int $threadId = null;
	private ?OptionsDto $options = null;
	private ?int $attendeesCount = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(?int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getEventId(): ?int
	{
		return $this->eventId;
	}

	public function setEventId(?int $eventId): self
	{
		$this->eventId = $eventId;

		return $this;
	}

	public function getCategoryId(): ?int
	{
		return $this->categoryId;
	}

	public function setCategoryId(?int $categoryId): self
	{
		$this->categoryId = $categoryId;

		return $this;
	}

	public function getCategory(): ?EventCategory
	{
		return $this->category;
	}

	public function setCategory(?EventCategory $category): self
	{
		$this->category = $category;

		return $this;
	}

	public function getThreadId(): ?int
	{
		return $this->threadId;
	}

	public function setThreadId(?int $threadId): self
	{
		$this->threadId = $threadId;

		return $this;
	}

	public function getOptions(): ?OptionsDto
	{
		return $this->options;
	}

	public function setOptions(?OptionsDto $options): self
	{
		$this->options = $options;

		return $this;
	}

	public function getAttendeesCount(): ?int
	{
		return $this->attendeesCount;
	}

	public function setAttendeesCount(?int $attendeesCount): self
	{
		$this->attendeesCount = $attendeesCount;

		return $this;
	}
}