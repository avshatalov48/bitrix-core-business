<?php

namespace Bitrix\Calendar\Core\Builders\EventOption;

use Bitrix\Calendar\Core\Builders\BuilderException;
use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\Core\EventOption\OptionsDto;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Main\DI\ServiceLocator;

final class EventOptionBuilderFromArray extends EventOptionBuilder
{
	public function __construct(private readonly array $eventOption)
	{
	}

	protected function getId(): ?int
	{
		return $this->eventOption['ID'] ?? null;
	}

	protected function getEventId(): ?int
	{
		return $this->eventOption['EVENT_ID'] ?? null;
	}

	protected function getCategoryId(): ?int
	{
		return $this->eventOption['CATEGORY_ID'] ?? null;
	}

	protected function getCategory(): EventCategory
	{
		if ($this->getCategoryId() !== null)
		{
			/** @var Factory $mapperFactory */
			$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
			$eventCategoryFactory = $mapperFactory->getEventCategory();

			return $eventCategoryFactory->getById($this->getCategoryId());
		}

		throw new BuilderException('it is impossible to find the event category');
	}

	protected function getThreadId(): ?int
	{
		return $this->eventOption['THREAD_ID'] ?? null;
	}

	protected function getOptions(): ?OptionsDto
	{
		return $this->eventOption['OPTIONS']
			? OptionsDto::fromArray($this->eventOption['OPTIONS'])
			: null;
	}

	protected function getAttendeesCount(): int
	{
		return $this->eventOption['ATTENDEES_COUNT'] ?? 0;
	}
}