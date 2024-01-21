<?php


namespace Bitrix\Calendar\ICal\Parser;


class Event extends ParserComponent
{
	public const COMPONENT_TYPE = 'VEVENT';
	private $alerts = [];
	private $start;
	private $end;
	private $name;
	private $description;
	private $uid;
	private $created;
	private $transparent;
	private $attendees = [];
	private $organizer;
	private $status;
	private $rrule;
	private $location;
	private $modified;
	private $sequence;
	private $dtstamp;
	private $url;
	private $categories;
	private $exDate;
	private ?ParserPropertyType $recurrenceId;
	private $attachments = [];

	/**
	 * @param string $uid
	 * @return Event
	 */
	public static function createInstance(string $uid): Event
	{
		return new self($uid);
	}

	/**
	 * Event constructor.
	 * @param string $uid
	 */
	public function __construct(string $uid)
	{
		$this->uid = $uid;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::COMPONENT_TYPE;
	}

	/**
	 * @return string[]
	 */
	public function getProperties(): array
	{
		return [
			'STARTS',
			'ENDS',
		];
	}

	/**
	 * @param ParserPropertyType|null $start
	 * @return $this
	 */
	public function setStart(?ParserPropertyType $start): Event
	{
		$this->start = $start;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $end
	 * @return $this
	 */
	public function setEnd(?ParserPropertyType $end): Event
	{
		$this->end = $end;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $description
	 * @return $this
	 */
	public function setDescription(?ParserPropertyType $description): Event
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $summary
	 * @return $this
	 */
	public function setSummary(?ParserPropertyType $summary): Event
	{
		$this->name = $summary;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $sequence
	 * @return $this
	 */
	public function setSequence(?ParserPropertyType $sequence): Event
	{
		$this->sequence = $sequence;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $created
	 * @return $this
	 */
	public function setCreated(?ParserPropertyType $created): Event
	{
		$this->created = $created;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $dtstamp
	 * @return $this
	 */
	public function setDTStamp(?ParserPropertyType $dtstamp): Event
	{
		$this->dtstamp = $dtstamp;
		return $this;
	}

	/**
	 * @param ParserPropertyType|null $location
	 * @return $this
	 */
	public function setLocation(?ParserPropertyType $location): Event
	{
		$this->location = $location;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $url
	 * @return $this
	 */
	public function setUrl(?ParserPropertyType $url): Event
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $rrule
	 * @return $this
	 */
	public function setRRule(?ParserPropertyType $rrule): Event
	{
		$this->rrule = $rrule;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $transparent
	 * @return $this
	 */
	public function setTransparent(?ParserPropertyType $transparent): Event
	{
		$this->transparent = $transparent;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $categories
	 * @return $this
	 */
	public function setCategories(?ParserPropertyType $categories): Event
	{
		$this->categories = $categories;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $organizer
	 * @return $this
	 */
	public function setOrganizer(?ParserPropertyType $organizer): Event
	{
		$this->organizer = $organizer;

		return $this;
	}

	/**
	 * @param ParserPropertyType[]|null $attendees
	 * @return $this
	 */
	public function setAttendees(?array $attendees): Event
	{
		$this->attendees = $attendees;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $modified
	 * @return $this
	 */
	public function setModified(?ParserPropertyType $modified): Event
	{
		$this->modified = $modified;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $exDate
	 * @return $this
	 */
	public function setExDate(?ParserPropertyType $exDate): Event
	{
		$this->exDate = $exDate;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $status
	 * @return $this
	 */
	public function setStatus(?ParserPropertyType $status): Event
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * @param ParserPropertyType[]|null $attachments
	 * @return $this
	 */
	public function setAttachment(?array $attachments): Event
	{
		$this->attachments = $attachments;

		return $this;
	}

	/**
	 * @param iterable|null $subComponents
	 * @return $this
	 */
	public function setSubComponents(?iterable $subComponents): Event
	{
		if (!is_null($subComponents))
		{
			foreach ($subComponents as $subComponent)
			{
				if ($subComponent->getType() === 'alert')
				{
					$this->alerts[] = $subComponent;
				}
			}
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function getContent(): Event
	{
		return $this;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getStart(): ?ParserPropertyType
	{
		return $this->start;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getEnd(): ?ParserPropertyType
	{
		return $this->end;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getDescription(): ?ParserPropertyType
	{
		return $this->description;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getName(): ?ParserPropertyType
	{
		return $this->name;
	}

	/**
	 * @return string|null
	 */
	public function getUid(): ?string
	{
		return $this->uid;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getCreated(): ?ParserPropertyType
	{
		return $this->created;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getModified(): ?ParserPropertyType
	{
		return $this->modified;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getStatus(): ?ParserPropertyType
	{
		return $this->status;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getLocation(): ?ParserPropertyType
	{
		return $this->location;
	}

	/**
	 * @return ParserComponent[]
	 */
	public function getAlert(): array
	{
		return $this->alerts;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getRRule(): ?ParserPropertyType
	{
		return $this->rrule;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getExDate(): ?ParserPropertyType
	{
		return $this->exDate;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getTransparent(): ?ParserPropertyType
	{
		return $this->transparent;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getOrganizer(): ?ParserPropertyType
	{
		return $this->organizer;
	}

	/**
	 * @return ParserPropertyType[]|null
	 */
	public function getAttendees(): ?array
	{
		return $this->attendees;
	}

	public function getDtStamp(): ?ParserPropertyType
	{
		return $this->dtstamp;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getUrl(): ?ParserPropertyType
	{
		return $this->url;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getCategories(): ?ParserPropertyType
	{
		return $this->categories;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getSequence(): ?ParserPropertyType
	{
		return $this->sequence;
	}

	/**
	 * @return array
	 */
	public function getAttachments(): ?array
	{
		return $this->attachments;
	}

	public function setRecurrenceId(?ParserPropertyType $recurrenceId): static
	{
		$this->recurrenceId = $recurrenceId;

		return $this;
	}

	public function getRecurrenceId(): ?ParserPropertyType
	{
		return $this->recurrenceId;
	}
}
