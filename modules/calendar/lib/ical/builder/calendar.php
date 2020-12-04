<?php

namespace Bitrix\Calendar\ICal\Builder;

use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\Content;
use Bitrix\Calendar\ICal\Basic\ICalUtil;
use Bitrix\Calendar\ICal\Basic\LengthPropertyType;
use Bitrix\Calendar\ICal\Basic\Parameter;

class Calendar extends BasicComponent implements BuilderComponent
{
	private $events = [];
	private $timezones = [];
	private $name;
	private $description;
	private $withTimezone = false;
	private $refreshInterval;
	private $productIdentifier;
	private $method;

	public static function getInstance(string $name = null): Calendar
	{
		return new self($name);
	}

	public function __construct(string $name = null)
	{
		$this->name = $name;
	}

	public function getType(): string
	{
		return 'VCALENDAR';
	}

	public function getProperties(): array
	{
		return [
			'VERSION',
			'PRODID',
		];
	}

	public function setMethod($method): Calendar
	{
		$this->method = $method;

		return $this;
	}

	public function setName(string $name): Calendar
	{
		$this->name = $name;

		return $this;
	}

	public function setDescription(string $description): Calendar
	{
		$this->description = $description;

		return $this;
	}

	public function setIdentifier(string $identifier): Calendar
	{
		$this->productIdentifier = $identifier;

		return $this;
	}

	public function setEvent($event): Calendar
	{
		if (is_null($event)) {
			return $this;
		}

		$events = array_map(function ($eventToResolve) {
			if (! is_callable($eventToResolve)) {
				return $eventToResolve;
			}
			$newEvent = new Event(ICalUtil::getUniqId());

			$eventToResolve($newEvent);

			return $newEvent;
		}, is_array($event) ? $event : [$event]);

		$this->events = array_merge($this->events, $events);

		return $this;
	}

	public function setTimezones($timezone): Calendar
	{
		if (is_null($timezone)) {
			return $this;
		}

		$timezones = array_map(function ($eventToResolve) {
			if (! is_callable($eventToResolve)) {
				return $eventToResolve;
			}

			$newTimezone = new Timezone();

			$eventToResolve($newTimezone);

			return $newTimezone;
		}, is_array($timezone) ? $timezone : [$timezone]);

		$this->timezones = array_merge($this->timezones, $timezones);

		return $this;
	}

	public function setWithTimezone(): Calendar
	{
		$this->withTimezone = true;

		return $this;
	}

	public function refreshInterval(int $min): Calendar
	{
		$this->refreshInterval = new \DateInterval("PT{$min}M");

		return $this;
	}

	public function get(): string
	{
		return $this->toString();
	}

	public function setContent(): Content
	{
		$events = $this->events;
		$timezones = $this->timezones;

		if ($this->withTimezone) {
			array_walk($events, function (Event $event)
			{
				$event->setWithTimezone();
			});
		}

		$content = Content::getInstance($this->getType())
			->textProperty('VERSION', '2.0')
			->textProperty('METHOD', $this->method)
			->textProperty('CALSCALE', 'GREGORIAN')
			->textProperty('PRODID', $this->productIdentifier ?? '-//Bitrix//Bitrix Calendar//EN')
			->textProperty(['NAME', 'X-WR-CALNAME'], $this->name)
			->textProperty(['DESCRIPTION', 'X-WR-CALDESC'], $this->description)
			->subComponent(...$timezones)
			->subComponent(...$events);

		if ($this->refreshInterval)
		{
			$content->property(
					LengthPropertyType::getInstance('REFRESH-INTERVAL', $this->refreshInterval)
						->addParameter(new Parameter('VALUE', 'DURATION'))
				)
				->property(LengthPropertyType::getInstance('X-PUBLISHED-TTL', $this->refreshInterval));
		}

		return $content;
	}
}
