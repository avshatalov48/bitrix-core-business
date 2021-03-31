<?php

namespace Bitrix\Calendar\ICal\Builder;

use Bitrix\Calendar\ICal\Basic\AttachProperty;
use Bitrix\Calendar\ICal\Basic\AttachPropertytype;
use Bitrix\Calendar\ICal\Basic\AttendeesProperty;
use Bitrix\Calendar\ICal\Basic\AttendeesPropertyType;
use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\Content;
use Bitrix\Calendar\ICal\Basic\ICalUtil;
use Bitrix\Calendar\ICal\Basic\RecurrenceRuleProperty;
use Bitrix\Calendar\ICal\Basic\RecurrenceRulePropertyType;
use Bitrix\Calendar\Util;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;

class Event extends BasicComponent implements BuilderComponent
{
	private $alerts = [];
	private $starts;
	private $ends;
	private $name;
	private $description = '';
	private $address;
	private $addressName;
	private $uid;
	private $created;
	private $withTimezone = false;
	private $classification = null;
	private $transparent = null;
	private $attendees = [];
	private $organizer = null;
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

	public static function getInstance($uid): EVent
	{
		return new self($uid);
	}

	public function __construct($uid)
	{
		$this->uid = $uid;
	}

	public function getType(): string
	{
		return 'VEVENT';
	}

	public function setRRule($rrule): Event
	{
		$this->rrule = is_array($rrule)
			? new RecurrenceRuleProperty($rrule)
			: null
		;

		return $this;
	}

	public function setWithTime(?bool $withTime = false): Event
	{
		$this->withTime = $withTime;

		return $this;
	}

	public function setLocation(?string $location = null)
	{
		$this->location = $location;

		return $this;
	}

	public function setModified($modified = null): Event
	{
		$this->modified = $modified;

		return $this;
	}

	public function setSequence($sequence = null): Event
	{
		$this->sequence = $sequence;

		return $this;
	}

	public function getProperties(): array
	{
		return [
			'UID',
			'DTSTAMP',
			'DTSTART',
		];
	}

	public function setStartsAt(?Date $starts = null): Event
	{
		$this->starts = $starts;

		return $this;
	}

	public function setEndsAt(?Date $ends = null): Event
	{
		$this->ends = $ends;

		return $this;
	}

	public function setPeriod(?Date $starts = null, Date $ends = null): Event
	{
		$this->starts = $starts;
		$this->ends = $ends;

		return $this;
	}

	public function setName(?string $name = null): Event
	{
		$this->name = $name;

		return $this;
	}

	public function setDescription(?string $description = null): Event
	{
		$this->description = str_replace("\r\n", " \n", $description);

		return $this;
	}

	public function setAddress(?string $address, ?string $name = null): Event
	{
		$this->address = $address;

		if ($name) {
			$this->addressName = $name;
		}

		return $this;
	}

	public function setAddressName(?string $name): Event
	{
		$this->addressName = $name;

		return $this;
	}

	public function setCreatedAt(?Date $created = null): Event
	{
		$this->created = $created;

		return $this;
	}

	public function setWithTimezone(?bool $param = null): Event
	{
		$this->withTimezone = $param === null ? true : $param;

		return $this;
	}

	public function setAlert(Alert $alert): Event
	{
		$this->alerts[] = $alert;

		return $this;
	}

	public function setAlertBefore(int $min, string $message = null): Event
	{
		$this->alerts[] = Alert::minutesBeforeStart($min, $message);

		return $this;
	}

	public function setClassification($classification = null): Event
	{
		$this->classification = $classification;

		return $this;
	}

	public function setTransparent($transp = null): Event
	{
		$this->transparent = $transp;

		return $this;
	}

	public function setAttendees(?array $attendees = null): Event
	{
		foreach ($attendees as $attendee)
		{
			$this->attendees[] = new AttendeesProperty(
				$attendee['EMAIL'],
				$attendee['NAME'].( $attendee['LAST_NAME'] ? ' '.$attendee['LAST_NAME'] : ''),
				$attendee['STATUS'],
				'REQ-PARTICIPANT',//$attendee['ROLE'],
				'INDIVIDUAL'//$attendee['CUTYPE']
			);
		}
		return $this;
	}

	public function setOrganizer($organizer, $mailto = null): Event
	{
		$this->organizer = new AttendeesProperty(
			$organizer['EMAIL'],
			$organizer['NAME'].( $organizer['LAST_NAME'] ? ' '.$organizer['LAST_NAME'] : ''),
			$organizer['STATUS'],
			$organizer['ROLE'],
			$organizer['CUTYPE'],
			$mailto
		);

		return $this;
	}

	public function setStatus($status): Event
	{
		$this->status = $status;

		return $this;
	}

	public function setAttaches(?array $attaches): Event
	{
		foreach ($attaches as $attach)
		{
			$this->attaches[] = new AttachProperty($attach);
		}

		return $this;
	}

	public function setExdates(?string $exdates = null, ?string $tz = null): Event
	{
		if ($exdates)
		{
			$arExdates = explode(';', $exdates);
			foreach ($arExdates as $exdate)
			{
				$this->exdates[] = Util::getDateObject($exdate, !$this->withTime, $tz);
			}
		}

		return $this;
	}

	public function setDtStamp(?Date $dtStamp): Event
	{
		$this->dtStamp = $dtStamp;

		return $this;
	}

	public function setUrl($url): Event
	{
		$this->url = $url;

		return $this;
	}

	public function setContent(): Content
	{
		$content = Content::getInstance($this->getType())
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

		if ($this->organizer)
		{
			$content->property(AttendeesPropertyType::getInstance('ORGANIZER', $this->organizer));
		}

		foreach ($this->attendees as $attendee)
		{
			$content->property(AttendeesPropertyType::getInstance('ATTENDEE', $attendee));
		}

		if ($this->rrule !== null && !empty($this->rrule->freq) && $this->rrule->freq !== 'NONE')
		{
			$content->property(RecurrenceRulePropertyType::getInstance('RRULE', $this->rrule));

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
}
