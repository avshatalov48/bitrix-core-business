<?php
namespace Bitrix\Calendar\ICal;

use Bitrix\Calendar\Core\Base\DateTimeZone;
use Bitrix\Calendar\Core\Event\Properties\RecurringEventRules;

class IcsBuilder {
	//const DT_FORMAT = 'Ymd\THis\Z';
	const
		PRODID = '-//Bitrix//Bitrix Calendar//EN',
		DATE_FORMAT = 'Ymd',
		DATETIME_FORMAT = 'Ymd\THis';

	protected const DAY_LENGTH = 86400;

	protected
		$fullDayMode = false,
		$organizer,
		$timezoneFrom,
		$timezoneTo,
		$attendees = [],
		$properties = [];

	protected ?RecurringEventRules $rrule = null;

	private
		$availableProperties = [
		'summary',
		'description',
		'dtstart',
		'dtend',
		'dtstamp',
		'location',
		'url',
		'alarm',
		'transp',
		'status',
		'uid',
		'attendee',
		'created',
		'last-modified',
		'sequence',
		'transp',
		'rrule',
		'location',
	];
	private static
		$METHOD = 'REQUEST';


	/**
	 * Constructor.
	 *
	 * @param array $properties list of properties.
	 */
	public function __construct($properties = [], $config = [])
	{
		$this->setProperties($properties);
		$this->setConfig($config);
	}

	public function setProperties($properties)
	{
		if (is_array($properties))
		{
			foreach ($properties as $key => $value)
			{
				if (in_array($key, $this->availableProperties) && !empty($value))
				{
					$this->properties[$key] = $this->prepareValue($value, $key);
				}
			}
		}
	}

	public function setConfig($config)
	{
		if (is_array($config))
		{
			if (isset($config['timezoneFrom']))
			{
				$this->timezoneFrom = $config['timezoneFrom'];
			}
			if (isset($config['timezoneTo']))
			{
				$this->timezoneTo = $config['timezoneTo'];
			}
		}
	}

	public function setFullDayMode($value)
	{
		$this->fullDayMode = $value;
	}

	public function setOrganizer($name, $email, $phone)
	{
		$this->organizer = ['name' => $name, 'email' => $email, 'phone' => $phone];
	}

	public function setAttendees($attendeeDataList = [])
	{
		if (is_array($attendeeDataList))
		{
			foreach($attendeeDataList as $attendeeData)
			{
				$this->attendees[] = $attendeeData;
			}
		}
	}

	public function setRrule(RecurringEventRules $rrule): void
	{
		$this->rrule = $rrule;
	}

	public function render()
	{
		return implode("\r\n", $this->buildBody());
	}

	private function buildBody()
	{
		// Build ICS properties - add header
		$ics_props = [
			'BEGIN:VCALENDAR',
			'PRODID:'.self::PRODID,
			'VERSION:2.0',
			'CALSCALE:GREGORIAN',
			'METHOD:'.self::$METHOD,
			'BEGIN:VEVENT'
		];

		$props = [];

		// Add organizer field
		if (isset($this->organizer['email']))
		{
			$props[self::formatOrganizerKey($this->organizer)] = self::formatEmailValue($this->organizer['email']);
		}
		else if (isset($this->organizer['phone']))
		{
			$props[self::formatOrganizerKey($this->organizer)] = self::formatPhoneValue($this->organizer['phone']);
		}

		// Add attendees
		if (is_array($this->attendees))
		{
			foreach($this->attendees as $k => $attendee)
			{
				$props[self::formatAttendeeKey($attendee)] = self::formatEmailValue($attendee['email']);
			}
		}

		// Build ICS properties - add header
		foreach($this->properties as $k => $v)
		{
			if ($k === 'dtstamp')
			{
				$props['DTSTAMP'] = self::formatDateTimeValue($v);
				continue;
			}
			if ($k === 'url' )
			{
				$props['URL;VALUE=URI'] = $v;
			}
			else if ($k === 'dtstart' || $k === 'dtend')
			{
				if ($this->fullDayMode)
				{
					$props[mb_strtoupper($k).';VALUE=DATE'] = self::formatDateValue($v);
				}
				else
				{
					$tzid = ($k === 'dtstart') ? $this->timezoneFrom : $this->timezoneTo;
					$props[mb_strtoupper($k).';TZID='.$tzid] = self::formatDateTimeValue($v);
				}
			}
			else
			{
				$props[mb_strtoupper($k)] = $v;
			}
		}

		if ($this->rrule !== null)
		{
			$props['RRULE'] = self::prepareRecurrenceRule($this->rrule, $this->timezoneTo);
		}

		// Append properties
		foreach ($props as $k => $v)
		{
			if ($k !== 'ALARM')
			{
				$ics_props[] = "$k:$v";
			}
			else
			{
				$ics_props[] = 'BEGIN:VALARM';
				$ics_props[] = 'TRIGGER:-PT' . $props['ALARM'];
				$ics_props[] = 'ACTION:DISPLAY';
				$ics_props[] = 'END:VALARM';
			}
		}

		// Build ICS properties - add footer
		$ics_props[] = 'END:VEVENT';
		$ics_props[] = 'END:VCALENDAR';

		return $ics_props;
	}

	private function prepareValue($val, $key = false)
	{
		switch($key)
		{
//			case 'dtstamp':
//			case 'dtstart':
//			case 'dtend':
//				$val = $this->formatDateValue($val);
//				break;
			default:
				$val = $this->escapeString($val);
		}
		return $val;
	}

	private static function formatDateValue($timestamp)
	{
		$dt = new \DateTime();
		$dt->setTimestamp($timestamp);
		return $dt->format(self::DATE_FORMAT);
	}

	private static function formatDateTimeValue($timestamp)
	{
		$dt = new \DateTime();
		if ($timestamp)
		{
			$dt->setTimestamp($timestamp);
		}
		return $dt->format(self::DATETIME_FORMAT);
	}

	private static function formatEmailValue($email)
	{
		return 'mailto:'.$email;
	}

	private static function formatPhoneValue($phone): string
	{
		return 'tel:'.$phone;
	}


	private static function formatAttendeeKey($attendee)
	{
		$key = 'ATTENDEE';
		$key .= ';CUTYPE=INDIVIDUAL';
		//$key .= ';PARTSTAT=ACCEPTED'; // NEEDS-ACTION
		$key .= ';RSVP=TRUE';
		$key .= ';CN='.$attendee['email'];
		return $key;
	}

	private static function formatOrganizerKey($organizer)
	{
		$key = 'ORGANIZER';
		if ($organizer['name'])
		{
			$key .= ';CN='.$organizer['name'];
		}
		return $key;
	}

	public static function prepareRecurrenceRule(RecurringEventRules $rrule, ?DateTimeZone $timeZone): string
	{
		$result = 'FREQ=' . $rrule->getFrequency();
		if ($rrule->getInterval())
		{
			$result .= ';INTERVAL=' . $rrule->getInterval();
		}
		if ($rrule->getByday())
		{
			$result .= ';BYDAY=' . implode(',', $rrule->getByday());
		}
		if ($rrule->getCount())
		{
			$result .= ';COUNT=' . $rrule->getCount();
		}
		else if (
			$rrule->getUntil()
			&& $rrule->getUntil()->getDate()->getTimestamp()
			&& $rrule->getUntil()->getDate()->getTimestamp() < 2145830400
		)
		{
			$offset = 0;
			if ($timeZone)
			{
				$offset = $timeZone->getTimeZone()->getOffset(new \DateTime('now', $timeZone->getTimeZone()));
			}

			$untilTimestamp = $rrule->getUntil()->getDate()->getTimestamp() + (self::DAY_LENGTH - 1) - $offset;
			$result .= ';UNTIL=' . date('Ymd\\THis\\Z', $untilTimestamp);
		}

		return $result;
	}

	private static function escapeString($str)
	{
		return preg_replace('/([\,;])/','\\\$1', $str);
	}
}