<?php

namespace Bitrix\Calendar\ICal\Builder;

use Bitrix\Calendar\ICal\Basic\AttachPropertytype;
use Bitrix\Calendar\ICal\Basic\AttendeesProperty;
use Bitrix\Calendar\ICal\Basic\AttendeesPropertyType;
use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\Content;
use Bitrix\Calendar\ICal\Basic\Dictionary;
use Bitrix\Calendar\ICal\Basic\RecurrenceRuleProperty;
use Bitrix\Calendar\ICal\Basic\RecurrenceRulePropertyType;
use Bitrix\Calendar\ICal\MailInvitation\Helper;
use Bitrix\Calendar\Util;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Type\Date;

class Event extends BasicComponent implements BuilderComponent, EventFactoryInterface
{
	public const TYPE = 'VEVENT';

	private $alerts = [];
	private $starts;
	private $ends;
	private $name;
	private $description = '';
	private $address;
	private $uid;
	private $created;
	private $withTimezone = false;
	private $classification = null;
	private $transparent = null;
	private $attendees = [];
	private ?AttendeesProperty $organizer = null;
	private $status = null;
	private $rrule;
	private $withTime;
	private $location = null;
	private $modified;
	private $sequence = 0;
	private $attaches = [];
	private $exdates = [];
	private $dtStamp;
	private $url;

	/**
	 * @param array $event
	 * @param string $type
	 * @return static
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function create(array $event, string $type): static
	{
		return match ($type)
		{
			EventFactoryInterface::REPLY => static::createFromReply($event),
			EventFactoryInterface::CANCEL => static::createFromCancel($event),
			EventFactoryInterface::REQUEST => static::createFromRequest($event),
		};
	}

	/**
	 * @param $uid
	 * @return Event
	 */
	public static function createInstance($uid): EVent
	{
		return new self($uid);
	}

	public function __construct($uid)
	{
		$this->uid = $uid;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::TYPE;
	}

	/**
	 * @param RecurrenceRuleProperty|null $rrule
	 * @return $this
	 */
	public function setRRule(RecurrenceRuleProperty $rrule = null): Event
	{
		$this->rrule = $rrule;

		return $this;
	}

	/**
	 * @param bool $withTime
	 * @return $this
	 */
	public function setWithTime(bool $withTime = false): Event
	{
		$this->withTime = $withTime;

		return $this;
	}

	/**
	 * @param string|null $location
	 * @return $this
	 */
	public function setLocation(string $location = null): Event
	{
		$this->location = $location;

		return $this;
	}

	/**
	 * @param Date|null $modified
	 * @return $this
	 */
	public function setModified(Date $modified): Event
	{
		$this->modified = $modified;

		return $this;
	}

	/**
	 * is version
	 * @param int $sequence
	 * @return $this
	 */
	public function setSequence(int $sequence): Event
	{
		$this->sequence = $sequence;

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getProperties(): array
	{
		//TODO: write all properties
		return [
			'UID',
			'DTSTAMP',
			'DTSTART',
		];
	}

	/**
	 * @param Date $starts
	 * @return $this
	 */
	public function setStartsAt(Date $starts): Event
	{
		$this->starts = $starts;

		return $this;
	}

	/**
	 * @param Date $ends
	 * @return $this
	 */
	public function setEndsAt(Date $ends): Event
	{
		$this->ends = $ends;

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name = null): Event
	{
		$this->name = $name ? Emoji::decode($name) : Loc::getMessage('CAL_ICAL_NEW_EVENT');

		return $this;
	}

	/**
	 * @param string $description
	 * @return $this
	 */
	public function setDescription(string $description = null): Event
	{
		$this->description = $description ? Emoji::decode($description) : $description;

		return $this;
	}

	/**
	 * @param string|null $address
	 * @return $this
	 */
	public function setAddress(string $address = null): Event
	{
		$this->address = $address;

		return $this;
	}

	/**
	 * @param Date $created
	 * @return $this
	 */
	public function setCreatedAt(Date $created): Event
	{
		$this->created = $created;

		return $this;
	}

	/**
	 * @param bool $withTimeZone
	 * @return $this
	 */
	public function setWithTimezone(bool $withTimeZone): Event
	{
		$this->withTimezone = $withTimeZone;

		return $this;
	}

	/**
	 * @param string|null $classification
	 * @return $this
	 */
	public function setClassification(string $classification = null): Event
	{
		$this->classification = $classification;

		return $this;
	}

	/**
	 * @param string $transparent
	 * @return $this
	 */
	public function setTransparent(string $transparent): Event
	{
		$this->transparent = $transparent;

		return $this;
	}

	/**
	 * @param iterable $attendees
	 * @return $this
	 */
	public function setAttendees(iterable $attendees): Event
	{
		foreach ($attendees as $attendee)
		{
			if ($attendee instanceof Attendee)
			{
				$this->attendees[] = new AttendeesProperty(
					$attendee->getEmail(),
					$attendee->getFullName(),
					$attendee->getStatus(),
					$attendee->getRole(),
					$attendee->getCuType(),
					$attendee->getMailTo(),
					$attendee->isRsvp()
				);
			}
		}

		return $this;
	}

	/**
	 * @param Attendee $organizer
	 * @param string $mailTo
	 * @return $this
	 */
	public function setOrganizer(Attendee $organizer, string $mailTo): Event
	{
		$this->organizer = new AttendeesProperty(
			$organizer->getEmail(),
			$organizer->getFullName(),
			$organizer->getStatus(),
			$organizer->getRole(),
			$organizer->getCuType(),
			$mailTo,
			$organizer->isRsvp()
		);

		return $this;
	}

	/**
	 * @param string $status
	 * @return $this
	 */
	public function setStatus(string $status): Event
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * @param array|null $attaches
	 * @return $this
	 */
	public function setAttaches(array $attaches = null): Event
	{
		$this->attaches[] = $attaches;

		return $this;
	}

	/**
	 * @param array|null $exdates
	 * @return $this
	 */
	public function setExdates(array $exdates = null): Event
	{
		$this->exdates = $exdates;

		return $this;
	}

	/**
	 * @param Date $dtStamp
	 * @return $this
	 */
	public function setDtStamp(Date $dtStamp): Event
	{
		$this->dtStamp = $dtStamp;

		return $this;
	}

	/**
	 * @param $url
	 * @return $this
	 */
	public function setUrl($url): Event
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * @return Content
	 */
	public function setContent(): Content
	{
		$content = Content::getInstance(self::TYPE)
			->textProperty('UID', $this->uid)
			->textProperty('SUMMARY', $this->name)
			->textProperty('DESCRIPTION', $this->description)
			->textProperty('LOCATION', $this->address)
			->textProperty('CLASS', $this->classification)
			->textProperty('TRANSP', $this->transparent)
			->textProperty('STATUS', $this->status)
			->textProperty('LOCATION', $this->location)
			->textProperty('SEQUENCE', $this->sequence)
			->dateTimeProperty('DTSTART', $this->starts, $this->withTime, $this->withTimezone)
			->dateTimeProperty('DTEND', $this->ends, $this->withTime, $this->withTimezone)
			->dateTimeProperty('DTSTAMP', $this->dtStamp, true, false, true)
			->dateTimeProperty('CREATED', $this->created, true, false, true)
			->dateTimeProperty('LAST-MODIFIED', $this->modified, true, false, true)
			->subComponent(...$this->alerts);

		if ($this->organizer !== null)
		{
			$content->property(AttendeesPropertyType::createInstance('ORGANIZER', $this->organizer));
		}

		foreach ($this->attendees as $attendee)
		{
			$content->property(AttendeesPropertyType::createInstance('ATTENDEE', $attendee));
		}

		if ($this->isRecurringEvent())
		{
			$content->property(RecurrenceRulePropertyType::createInstance('RRULE', $this->rrule));

			if (!empty($this->exdates))
			{
				foreach ($this->exdates as $exdate)
				{
					$content->dateTimeProperty('EXDATE', $exdate, $this->withTime, $this->withTimezone);
				}
			}
		}

		if (!empty($this->attaches))
		{
			foreach ($this->attaches as $attach)
			{
				$content->property(AttachPropertyType::getInstance('ATTACH', $attach));
			}
		}

		if (!empty($this->url))
		{
			$content->textProperty('URL', $this->url);
		}

		return $content;
	}

	/**
	 * @return bool
	 */
	private function isRecurringEvent(): bool
	{
		return !empty($this->rrule) && !empty($this->rrule->freq) && $this->rrule->freq !== 'NONE';
	}

	/**
	 * @param array $event
	 * @return Date
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function getEndDateByEvent(array $event)
	{
		return $event['SKIP_TIME']
			? Util::getDateObject($event['DATE_TO'])->add('1 days')
			: Util::getDateObject($event['DATE_TO'], false, $event['TZ_TO'])
		;
	}

	/**
	 * @param array $event
	 * @return static
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function createFromReply(array $event): static
	{
		$instance = new static($event['DAV_XML_ID']);

		return $instance
			->setName($event['NAME'])
			->setStartsAt(
				Util::getDateObject($event['DATE_FROM'], $event['SKIP_TIME'], $event['TZ_FROM'])
			)
			->setEndsAt($instance->getEndDateByEvent($event))
			->setDtStamp(Helper::getIcalDateTime())
			->setCreatedAt(Util::getDateObject($event['DATE_CREATE'], false, $event['TZ_FROM']))
			->setModified(Util::getDateObject($event['TIMESTAMP_X'], false, $event['TZ_FROM']))
			->setWithTimezone(!$event['SKIP_TIME'])
			->setWithTime(!$event['SKIP_TIME'])
			->setOrganizer(
				$event['ICAL_ORGANIZER'],
				$event['ORGANIZER_MAIL']['MAILTO'] ?? $event['ORGANIZER_MAIL']['EMAIL']
			)
			->setTransparent(Dictionary::TRANSPARENT[$event['ACCESSIBILITY']] ?? Dictionary::TRANSPARENT['busy'])
			->setSequence(((int)$event['VERSION']))
			->setStatus(Dictionary::INVITATION_STATUS['confirmed'])
			->setUrl($event['URL'] ?? null)
		;
	}

	/**
	 * @param array $event
	 * @return static
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function createFromCancel(array $event): static
	{
		$fullDay = $event['DT_SKIP_TIME'] === 'Y';

		return (new static($event['DAV_XML_ID']))
			->setName($event['NAME'])
			->setStartsAt(Util::getDateObject($event['DATE_FROM'], $fullDay, $event['TZ_FROM']))
			->setEndsAt(Util::getDateObject($event['DATE_TO'], $fullDay, $event['TZ_TO']))
			->setCreatedAt(Util::getDateObject($event['DATE_CREATE'], false, $event['TZ_FROM']))
			->setDtStamp(Helper::getIcalDateTime())
			->setModified(Util::getDateObject($event['TIMESTAMP_X'], false, $event['TZ_FROM']))
			->setWithTimezone(!$fullDay)
			->setWithTime(!$fullDay)
			->setDescription($event['DESCRIPTION'])
			->setTransparent(Dictionary::TRANSPARENT[$event['ACCESSIBILITY']] ?? Dictionary::TRANSPARENT['busy'])
			->setLocation($event['TEXT_LOCATION'])
			->setSequence((int)$event['VERSION'] + 1)
			->setStatus(Dictionary::INVITATION_STATUS['cancelled'])
		;
	}

	/**
	 * @param array $event
	 * @return static
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function createFromRequest(array $event): static
	{
		return (new static($event['DAV_XML_ID']))
			->setName($event['NAME'])
			->setStartsAt(Util::getDateObject($event['DATE_FROM'], $event['SKIP_TIME'], $event['TZ_FROM']))
			->setEndsAt(Util::getDateObject($event['DATE_TO'], $event['SKIP_TIME'], $event['TZ_TO']))
			->setCreatedAt(Util::getDateObject($event['CREATED'], false, $event['TZ_FROM']))
			->setDtStamp(Util::getDateObject($event['CREATED'], false, $event['TZ_FROM']))
			->setModified(Util::getDateObject($event['MODIFIED'], false, $event['TZ_FROM']))
			->setWithTimezone(!$event['SKIP_TIME'])
			->setWithTime(!$event['SKIP_TIME'])
			->setDescription($event['DESCRIPTION'])
			->setTransparent(Dictionary::TRANSPARENT[$event['ACCESSIBILITY']] ?? Dictionary::TRANSPARENT['busy'])
			->setRRule($event['RRULE'])
			->setExdates($event['EXDATE'])
			->setLocation($event['TEXT_LOCATION'])
			->setSequence((int)$event['VERSION'])
			->setStatus(Dictionary::INVITATION_STATUS['confirmed'])
		;
	}
}
