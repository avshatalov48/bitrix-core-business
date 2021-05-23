<?php

namespace Bitrix\Calendar\ICal\Builder;

use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\Content;
use Bitrix\Calendar\ICal\Basic\LengthPropertyType;
use Bitrix\Calendar\ICal\Basic\Parameter;
use Bitrix\Calendar\ICal\MailInvitation\Helper;
use DateInterval;

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

	/**
	 * @param string|null $name
	 * @return Calendar
	 */
	public static function createInstance(string $name = null): Calendar
	{
		return new self($name);
	}

	/**
	 * Calendar constructor.
	 * @param string|null $name
	 */
	public function __construct(string $name = null)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return 'VCALENDAR';
	}

	/**
	 * @return string[]
	 */
	public function getProperties(): array
	{
		return [
			'VERSION',
			'PRODID',
		];
	}

	/**
	 * @param string $method
	 * @return $this
	 */
	public function setMethod(string $method): Calendar
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): Calendar
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @param string $description
	 * @return $this
	 */
	public function setDescription(string $description): Calendar
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @param string $identifier
	 * @return $this
	 */
	public function setIdentifier(string $identifier): Calendar
	{
		$this->productIdentifier = $identifier;

		return $this;
	}

	/**
	 * @param $event
	 * @return $this
	 */
	public function addEvent($event): Calendar
	{
		if (is_null($event))
		{
			return $this;
		}

		$events = array_map(function ($eventToResolve) {
			if (! is_callable($eventToResolve)) {
				return $eventToResolve;
			}
			$newEvent = new Event(Helper::getUniqId());

			$eventToResolve($newEvent);

			return $newEvent;
		}, is_array($event) ? $event : [$event]);

		$this->events = array_merge($this->events, $events);

		return $this;
	}

	/**
	 * @param $timezone
	 * @return $this
	 */
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

	/**
	 * @return $this
	 */
	public function setWithTimezone(): Calendar
	{
		$this->withTimezone = true;

		return $this;
	}

	/**
	 * @param int $min
	 * @return $this
	 */
	public function refreshInterval(int $min): Calendar
	{
		$this->refreshInterval = new DateInterval("PT{$min}M");

		return $this;
	}

	/**
	 * @return string
	 */
	public function get(): string
	{
		return $this->toString();
	}

	/**
	 * @return Content
	 */
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
