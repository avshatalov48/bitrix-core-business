<?php


namespace Bitrix\Calendar\ICal\Parser;


class Calendar extends ParserComponent
{
	public const COMPONENT_TYPE = 'VCALENDAR';
	/**
	 * @var Event[]
	 */
	private $events = [];
	/**
	 * @var Timezone[]
	 */
	private $timezones = [];
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var ParserPropertyType|null
	 */
	private $productIdentifier;
	/**
	 * @var ParserPropertyType|null
	 */
	private $method;
	/**
	 * @var ParserPropertyType|null
	 */
	private $version;
	/**
	 * @var ParserPropertyType|null
	 */
	private $calScale;

	/**
	 * @param string $name
	 * @return Calendar
	 */
	public static function createInstance(string $name = ''): Calendar
	{
		return new self($name);
	}

	/**
	 * Calendar constructor.
	 * @param $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::COMPONENT_TYPE;
	}

	/**
	 * @return array
	 */
	public function getProperties(): array
	{
		return [];
	}

	/**
	 * @param ParserPropertyType|null $method
	 * @return $this
	 */
	public function setMethod(?ParserPropertyType $method): Calendar
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $prodId
	 * @return $this
	 */
	public function setProdId(?ParserPropertyType $prodId): Calendar
	{
		$this->productIdentifier = $prodId;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $calscale
	 * @return $this
	 */
	public function setCalScale(?ParserPropertyType $calscale): Calendar
	{
		$this->calScale = $calscale;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $version
	 * @return $this
	 */
	public function setVersion(?ParserPropertyType $version): Calendar
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * @param Event $event
	 * @return $this
	 */
	public function setEvent(Event $event): Calendar
	{
		$this->events[] = $event;

		return $this;
	}

	/**
	 * @param iterable $subComponents
	 * @return $this
	 */
	public function setSubComponents(iterable $subComponents): Calendar
	{
		foreach ($subComponents as $subComponent)
		{
			if ($subComponent instanceof ParserComponent)
			{
				if ($subComponent instanceof Event)
				{
					$this->events[] = $subComponent;
				}
				elseif($subComponent instanceof Timezone)
				{
					$this->timezones[] = $subComponent;
				}
			}
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function getContent(): Calendar
	{
		return $this;
	}

	/**
	 * @return Event[]
	 */
	public function getEvents(): array
	{
		return $this->events;
	}

	/**
	 * @return string|null
	 */
	public function getProdId(): ?string
	{
		if ($this->productIdentifier instanceof ParserPropertyType)
		{
			return $this->productIdentifier->getValue();
		}

		return null;
	}

	/**
	 * @return string|null
	 */
	public function getVersion(): ?string
	{
		if ($this->version instanceof ParserPropertyType)
		{
			return $this->version->getValue();
		}

		return null;
	}

	/**
	 * @return Timezone[]
	 */
	public function getTimezones(): array
	{
		return $this->timezones;
	}

	/**
	 * @return string|null
	 */
	public function getMethod(): ?string
	{
		if ($this->method instanceof ParserPropertyType)
		{
			return $this->method->getValue();
		}

		return null;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getCalScale(): ?ParserPropertyType
	{
		return $this->calScale;
	}

	/**
	 * @return int
	 */
	public function countEvents(): int
	{
		return count($this->events);
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return bool
	 */
	public function hasOneEvent(): bool
	{
		return $this->countEvents() === 1;
	}

	/**
	 * @return Event
	 */
	public function getEvent(): Event
	{
		return $this->events[0];
	}
}
