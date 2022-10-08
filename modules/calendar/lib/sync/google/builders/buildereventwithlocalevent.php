<?php

namespace Bitrix\Calendar\Sync\Google\Builders;

use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\Location;

class BuilderEventWithLocalEvent implements Builder
{
	private array $externalEvent;
	private Event $event;

	public function __construct(array $externalEvent, Event $event)
	{
		$this->externalEvent = $externalEvent;
		$this->event = $event;
	}

	/**
	 * @return Event
	 */
	public function build(): Event
	{
		$this->event
			->setDescription($this->externalEvent['description'])
			->setLocation($this->getLocation())
		;

		return $this->event;
	}

	/**
	 * @return Location|null
	 */
	private function getLocation(): ?Location
	{
		if ($this->externalEvent['location'])
		{
			return  new Location($this->externalEvent['location']);
		}

		return null;
	}
}
