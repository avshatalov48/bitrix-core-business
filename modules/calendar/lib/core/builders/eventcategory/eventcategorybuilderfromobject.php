<?php

namespace Bitrix\Calendar\Core\Builders\EventCategory;

use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory;

final class EventCategoryBuilderFromObject extends EventCategoryBuilder
{
	public function __construct(private readonly OpenEventCategory $eventCategory)
	{
	}

	protected function getId(): ?int
	{
		return $this->eventCategory->getId();
	}

	protected function getName(): ?string
	{
		return $this->eventCategory->getName();
	}

	protected function getCreatorId(): ?int
	{
		return $this->eventCategory->getCreatorId();
	}

	protected function getClosed(): ?bool
	{
		return $this->eventCategory->getClosed();
	}

	protected function getDescription(): ?string
	{
		return $this->eventCategory->getDescription();
	}

	protected function getAccessCodes(): ?array
	{
		return $this->eventCategory->getAccessCodes()
			? explode(',', $this->eventCategory->getAccessCodes())
			: null;
	}

	protected function getDeleted(): ?bool
	{
		return $this->eventCategory->getDeleted();
	}

	protected function getChannelId(): ?int
	{
		return $this->eventCategory->getChannelId();
	}

	protected function getEventsCount(): ?int
	{
		return $this->eventCategory->getEventsCount();
	}
}
