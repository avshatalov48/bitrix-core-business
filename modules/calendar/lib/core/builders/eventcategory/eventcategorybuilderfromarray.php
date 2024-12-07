<?php

namespace Bitrix\Calendar\Core\Builders\EventCategory;

final class EventCategoryBuilderFromArray extends EventCategoryBuilder
{
	public function __construct(private readonly array $eventCategory)
	{
	}

	protected function getId(): ?int
	{
		return $this->eventCategory['ID'] ?? null;
	}

	protected function getName(): ?string
	{
		return $this->eventCategory['NAME'] ?? null;
	}

	protected function getCreatorId(): ?int
	{
		return $this->eventCategory['CREATOR_ID'] ?? null;
	}

	protected function getClosed(): bool
	{
		return $this->eventCategory['CLOSED'] ?? false;
	}

	protected function getDescription(): string
	{
		return $this->eventCategory['DESCRIPTION'] ?? '';
	}

	protected function getAccessCodes(): array
	{
		return $this->eventCategory['ACCESS_CODES'] ?? [];
	}

	protected function getDeleted(): bool
	{
		return $this->eventCategory['DELETED'] ?? false;
	}

	protected function getChannelId(): int
	{
		return $this->eventCategory['CHANNEL_ID'] ?? 0;
	}

	protected function getEventsCount(): int
	{
		return $this->eventCategory['EVENT_COUNT'] ?? 0;
	}
}
