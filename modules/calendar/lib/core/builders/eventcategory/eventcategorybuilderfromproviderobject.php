<?php

namespace Bitrix\Calendar\Core\Builders\EventCategory;

use Bitrix\Calendar\OpenEvents\Item\Category;

final class EventCategoryBuilderFromProviderObject extends EventCategoryBuilder
{
	public function __construct(private readonly Category $eventCategory)
	{
	}

	protected function getId(): ?int
	{
		return $this->eventCategory->id;
	}

	protected function getName(): ?string
	{
		return $this->eventCategory->name;
	}

	protected function getCreatorId(): ?int
	{
		return $this->eventCategory->creatorId;
	}

	protected function getClosed(): ?bool
	{
		return $this->eventCategory->closed;
	}

	protected function getDescription(): ?string
	{
		return $this->eventCategory->description;
	}

	protected function getAccessCodes(): ?array
	{
		return null;
	}

	protected function getDeleted(): ?bool
	{
		return false;
	}

	protected function getChannelId(): ?int
	{
		return $this->eventCategory->channelId;
	}

	protected function getEventsCount(): ?int
	{
		return $this->eventCategory->eventsCount;
	}
}
