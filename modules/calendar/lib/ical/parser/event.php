<?php


namespace Bitrix\Calendar\ICal\Parser;


use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\ICalUtil;
use Bitrix\Calendar\Util;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Localization\Loc;

class Event extends BasicComponent implements ParserComponent
{
	private $alerts = [];
	private $start;
	private $end;
	private $tzStart;
	private $tzEnd;
	private $name;
	private $description = '';
	private $uid;
	private $created;
	private $withTimezone = false;
	private $transparent = null;
	private $attendees = [];
	private $organizer = null;
	private $status = null;
	private $rrule;
	private $withTime;
	private $location = null;
	private $modified;
	private $sequence = 0;
	private $dtstamp;
	private $url;
	private $categories;
	private $exDate;
	private $attachments;

	public static function getInstance(string $uid): Event
	{
		return new self($uid);
	}

	public function __construct(string $uid)
	{
		$this->uid = $uid;
	}

	public function getType(): string
	{
		return 'VEVENT';
	}

	public function getProperties(): array
	{
		return [
			'STARTS',
			'ENDS',
		];
	}

	public function setStart($start = []): Event
	{
		$this->start = $start['value'];

		if (!$this->isFullDayEvent($start))
		{
			$this->withTimezone = true;
			$this->withTime = true;
			$this->tzStart = $start['parameter']['tzid'];
		}
		else
		{
			$this->withTime = false;
			$this->withTimezone = false;
		}

		return $this;
	}

	public function setEnd($end = []): Event
	{
		$this->end = $end['value'];

		if (!$this->isFullDayEvent($end))
		{
			$this->withTimezone = true;
			$this->withTime = true;
			$this->tzEnd = $end['parameter']['tzid'];
		}
		else
		{
			$this->withTime = false;
			$this->withTimezone = false;
		}
		return $this;
	}

	public function setDescription($description): Event
	{
		$this->description = $description['value'];
		return $this;
	}

	public function setSummary($summary): Event
	{
		$this->name = $summary['value'];
		return $this;
	}

	public function setSequence($sequence): Event
	{
		$this->sequence = $sequence['value'];
		return $this;
	}

	public function setCreated($created): Event
	{
		$this->created = $created['value'];
		return $this;
	}

	public function setDTStamp($dtstamp): Event
	{
		$this->dtstamp = $dtstamp['value'];
		return $this;
	}

	public function setLocation($location): Event
	{
		$this->location = $location['value'];
		return $this;
	}

	public function setUrl($url = []): Event
	{
		$this->url = $url['value'];
		return $this;
	}

	public function setRRule($rrule = []): Event
	{
		$this->rrule = $rrule['value'];
		return $this;
	}

	public function setTransparent($transp): Event
	{
		$this->transparent = $transp['value'];
		return $this;
	}

	public function setCategories($categories): Event
	{
		$this->categories = $categories['value'];
		return $this;
	}

	public function setOrganizer($organizer): Event
	{
		$this->organizer = $organizer;
		return $this;
	}

	public function setAttendees($attendees): Event
	{
		$this->attendees = $attendees;
		return $this;
	}

	public function setModified($modified): Event
	{
		$this->modified = $modified['value'];
		return $this;
	}

	public function setExDate($exDate): Event
	{
		$this->exDate = $exDate['value'];
		return $this;
	}

	public function setStatus($status): Event
	{
		$this->status = is_array($status) ? $status['value'] : $status;

		return $this;
	}

	public function setAttachment($attachments): Event
	{
		$this->attachments = $attachments;
		return $this;
	}

	public function setSubComponents($subComponents): Event
	{
		if (!empty($subComponents))
		{
			foreach($subComponents as $subComponent)
			{
				if ($subComponent->getType() === 'alert')
				{
					$this->alerts[] = $subComponent;
				}
			}
		}

		return $this;
	}

	public function getContent(): array
	{
		$event = [];
		$event['DATE_FROM'] = $this->getStart();
		$event['DATE_TO'] = $this->getEnd();
		$event['TZ_FROM'] = $this->getTzFrom();
		$event['TZ_TO'] = $this->getTzTo();
		$event['DESCRIPTION'] = $this->getDescription();
		$event['NAME'] = $this->getName();
		$event['SKIP_TIME'] = $this->hasSkipDay();
		$event['DATE_CREATE'] = $this->getCreated();
		$event['TIMESTAMP_X'] = $this->getModified();
		$event['STATUS'] = $this->getStatus();
		$event['IMPORTANCE'] = $this->getImportance();
		$event['LOCATION'] = $this->getLocation();
		$event['TEXT_LOCATION'] = $this->getLocation();
		$event['REMIND'] = $this->getAlert();
		$event['RRULE'] = $this->getRRule();
		$event['EXDATE'] = $this->getExDate();
		$event['DAV_XML_ID'] = $this->getUid();
		$event['ATTENDEES_MAIL'] = $this->getAttendees();
		$event['ORGANIZER_MAIL'] = $this->getOrganizer();
		$event['DAV_XML_ID'] = $this->getUid();
		$event['VERSION'] = $this->getVersion();
		$event['DT_STAMP'] = $this->getDtStamp();
		$event['URL'] = $this->getUrl();
		$event['ACCESSIBILITY'] = $this->getTransparent();
		$event['ATTACHMENT_LINK'] = $this->getAttachmentLink();

		return $event;
	}

	public function getStart(): Date
	{
		if (($this->withTimezone && Util::isTimezoneValid($this->tzStart)) || $this->withTime)
		{
			return ICalUtil::getIcalDateTime($this->start, $this->tzStart);
		}

		return ICalUtil::getIcalDate($this->start);
	}

	public function getEnd(): Date
	{
		if (($this->withTimezone && Util::isTimezoneValid($this->tzEnd)) || $this->withTime)
		{
			return ICalUtil::getIcalDateTime($this->end, $this->tzEnd);
		}

		return ICalUtil::getIcalDate($this->end)->add('-1 day');
	}

	public function getTzFrom(): \DateTimeZone
	{
		return Util::isTimezoneValid($this->tzStart) ? Util::prepareTimezone($this->tzStart) : Util::prepareTimezone();
	}

	public function getTzTo(): \DateTimeZone
	{
		return Util::isTimezoneValid($this->tzEnd) ? Util::prepareTimezone($this->tzEnd) : Util::prepareTimezone();
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function hasSkipDay(): ?bool
	{
		return !$this->withTime;
	}

	public function getUid(): ?string
	{
		return $this->uid;
	}

	public function getCreated(): DateTime
	{
		return ICalUtil::getIcalDateTime($this->created);
	}

	public function getModified(): DateTime
	{
		return ICalUtil::getIcalDateTime($this->modified);
	}

	public function getStatus(): ?string
	{
		return $this->status;
	}

	public function getImportance()
	{
		return;
	}

	public function getLocation(): ?string
	{
		return is_string($this->location) ? $this->location : null;
	}

	public function getAlert()
	{
	}

	public function getRRule(): array
	{
		$params = [];
		$parts = explode(';', $this->rrule);

		if (!empty($parts))
		{
			foreach ($parts as $part)
			{
				list($k, $v) = explode('=', $part);

				if ($k === 'UNTIL')
				{
					$v = ICalUtil::getIcalDateTime($v);
				}

				$params[$k] = $v;
			}
		}

		return $params;
	}

	public function getExDate()
	{
		return $this->exDate;
	}

	public function getTransparent(): ?string
	{
		return $this->transparent;
	}

	public function getOrganizer(): ?array
	{
		$organizer['MAILTO'] = (explode(':', $this->organizer['value']))[1];
		$organizer['EMAIL'] = $this->organizer['parameter']['email'] ?? $organizer['MAILTO'];
		$name = explode(" ", trim($this->organizer['parameter']['cn'], '"'), 2);
		$organizer['NAME'] = $name[0];
		$organizer['LAST_NAME'] = $name[1] ?? '';
		return $organizer;
	}

	public function getAttendees(): ?array
	{
		$participants = [];
		$attendees = $this->attendees;

		if (!empty($attendees))
		{
			foreach($attendees as $attendee)
			{
				$participant['MAILTO'] = (explode(':', $attendee['value']))[1];
				$participant['EMAIL'] = $attendee['parameter']['email'] ?? $participant['MAILTO'];
				$name = explode(" ", trim($attendee['parameter']['cn'], '"'), 2);
				if (empty($name[0]))
				{
					$participant['NAME'] = $participant['EMAIL'];
				}
				else
				{
					$participant['NAME'] = $name[0];
					$participant['LAST_NAME'] = $name[1] ?? '';
				}
				$participant['STATUS'] = $attendee['parameter']['partstat'];
				$participant['ROLE'] = $attendee['parameter']['role'];
				$participant['CUTYPE'] = $attendee['parameter']['cutype'];

				$participants[] = $participant;
				unset($participant);
			}
		}

		return $participants;
	}

	public function getDtStamp(): DateTime
	{
		return ICalUtil::getIcalDateTime($this->dtstamp);
	}

	public function getUrl(): ?string
	{
		return $this->url;
	}

	public function getCategories(): ?string
	{
		return $this->categories;
	}

	public function getVersion()
	{
		return $this->sequence;
	}

	public function getAttachmentLink(): array
	{
		$attachmentsLinks = [];
		if (!empty($this->attachments))
		{
			foreach($this->attachments as $attachment)
			{
				$attachmentsLinks[] = [
					'filename' => $attachment['parameter']['filename'],
					'link' => $attachment['value'],
				];
			}
		}

		return $attachmentsLinks;
	}

	private function isFullDayEvent(array $dateTime = null): bool
	{
		if (!empty($dateTime)
			&& (!empty($dateTime['parameter']['tzid'])
			|| $dateTime['parameter']['value'] !== 'DATE'))
		{
			return false;
		}

		return true;
	}
}