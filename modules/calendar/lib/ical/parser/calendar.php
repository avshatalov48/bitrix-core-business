<?php


namespace Bitrix\Calendar\ICal\Parser;


use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\Content;
use Bitrix\Main\Type\Date;

class Calendar extends BasicComponent implements ParserComponent
{
	private $events = [];
	private $timezones = [];
	private $name;
	private $description;
	private $withTimezone = false;
	private $refreshInterval;
	private $productIdentifier;
	private $method;
	private $version;
	private $calScale;

	public static function getInstance(string $name = ''): Calendar
	{
		return new self($name);
	}

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function getType(): string
	{
		return 'VCALENDAR';
	}

	public function getProperties(): array
	{
		// TODO: Implement getProperties() method.
	}

	public function setMethod($method): Calendar
	{
		$this->method = $method['value'];
		return $this;
	}

	public function setProdId($prodId): Calendar
	{
		$this->productIdentifier = $prodId['value'];
		return $this;
	}

	public function setCalScale($calscale): Calendar
	{
		$this->calScale = $calscale['value'];
		return $this;
	}

	public function setVersion($version): Calendar
	{
		$this->version = $version['value'];
		return $this;
	}

	public function setSubComponents(array $subComponents): Calendar
	{
		foreach ($subComponents as $subComponent)
		{
			if ($subComponent instanceof BasicComponent)
			{
				if ($subComponent instanceof Event)
				{
					$this->events[] = $subComponent;
				}
				else
				{
					$this->timezones[] = $subComponent;
				}
			}
		}
		return $this;
	}

	public function handleEvents()
	{
		foreach ($this->events as &$event)
		{
			$event = $event->getContent();
		}
		unset($event);

		return $this;
	}

	public function handleTimezones()
	{
		foreach ($this->timezones as &$timezone)
		{
			$timezone->getContent();
		}
		unset($timezone);

		return $this;
	}

	public function getContent(): array
	{
		$this->handleEvents();
		$this->handleTimezones();
		$calendar = [];
		$calendar['PROD_ID'] = $this->getProdId();
		$calendar['VERSION'] = $this->getVersion();
		$calendar['TIMEZONES'] = $this->getTimezones();
		$calendar['EVENTS'] = $this->getEvents();
		$calendar['METHOD'] = $this->getMethod();
		$calendar['CALSCALE'] = $this->getCalScale();

		return $calendar;
	}

	public function getEvents(): array
	{
		return $this->events;
	}

	public function getProdId(): string
	{
		return $this->productIdentifier;
	}

	public function getVersion(): string
	{
		return $this->version;
	}

	public function getTimezones(): array
	{
		return $this->timezones;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	private function getCalScale()
	{
		return $this->calScale;
	}
}