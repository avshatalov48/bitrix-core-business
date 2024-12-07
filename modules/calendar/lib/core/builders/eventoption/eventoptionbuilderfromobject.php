<?php

namespace Bitrix\Calendar\Core\Builders\EventOption;

use Bitrix\Calendar\Core\Builders\BuilderException;
use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\Core\EventOption\EventOption;
use Bitrix\Calendar\Core\EventOption\OptionsDto;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption;
use Bitrix\Main\DI\ServiceLocator;

final class EventOptionBuilderFromObject extends EventOptionBuilder
{
	public function __construct(private readonly OpenEventOption $eventOption)
	{
	}

	public function build(): EventOption
	{
		return (new EventOption())
			->setId($this->getId())
			->setEventId($this->getEventId())
			->setCategoryId($this->getCategoryId())
			->setCategory($this->getCategory())
			->setThreadId($this->getThreadId())
			->setOptions($this->getOptions())
			->setAttendeesCount($this->getAttendeesCount())
		;
	}

	protected function getId(): ?int
	{
		return $this->eventOption->getId();
	}

	protected function getEventId(): ?int
	{
		return $this->eventOption->getEventId();
	}

	protected function getCategoryId(): ?int
	{
		return $this->eventOption->getCategoryId();
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
		return $this->eventOption->getThreadId();
	}

	protected function getOptions(): ?OptionsDto
	{
		$options = $this->eventOption->getOptions();
		$optionsArray = json_decode($options, true) ?? [];

		return OptionsDto::fromArray($optionsArray);
	}

	protected function getAttendeesCount(): int
	{
		return $this->eventOption->getAttendeesCount() ?? 0;
	}
}
