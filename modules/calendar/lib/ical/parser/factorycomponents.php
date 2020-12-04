<?php


namespace Bitrix\Calendar\ICal\Parser;



use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\Observance;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;


class FactoryComponents
{
	/**
	 * @var string
	 */
	private $componentName;
	/**
	 * @var BasicComponent
	 */
	private $component;

	public static function createInstance(string $componentName): FactoryComponents
	{
		return new self($componentName);
	}

	public function __construct(string $componentName)
	{
		$this->componentName = $componentName;
	}

	public function createComponent($properties, $subComponents): FactoryComponents
	{
		switch ($this->componentName)
		{
			case 'standard':
				$this->component = $this->getStandardComponent($properties);
				break;
			case 'vcalendar':
				$this->component = $this->getCalendarComponent($properties, $subComponents);
				break;
			case 'vevent':
				$this->component = $this->getEventComponent($properties, $subComponents);
				break;
			case 'vtimezone':
				$this->component = $this->getTimezoneComponent($properties, $subComponents);
				break;
			default:
				// @TODO: write to log with unknown components
		}

		return $this;
	}

	public function getComponent()
	{
		return $this->component;
	}

	private function getStandardComponent($properties): Observance
	{
		$standard = StandardObservances::getInstance();
		try
		{
			$standard->setOffsetToValue($properties['tzoffsetto']['value'])
				->setOffsetFromValue($properties['tzoffsetfrom']['value'])
				->setTimezoneFromAbbr($properties['tzname']['value'])
				->setDTStart(new DateTime($properties['dtstart']['value'], 'Ymd\THis'));
		}
		catch (ObjectException $e)
		{
		}

		return $standard;
	}

	private function getCalendarComponent($properties, $subComponents): Calendar
	{
		$name = $properties['name']['value'] ?? 'Outer Calendar';
		return  (Calendar::getInstance($name))
			->setMethod($properties['method'])
			->setProdId($properties['prodid'])
			->setCalScale($properties['calscale'])
			->setVersion($properties['version'])
			->setSubComponents($subComponents);
	}

	private function getEventComponent($properties, $subComponents): Event
	{
		return (Event::getInstance($properties['uid']['value']))
			->setStart($properties['dtstart'])
			->setEnd($properties['dtend'])
			->setDescription($properties['description'])
			->setSummary($properties['summary'])
			->setSequence($properties['sequence'])
			->setCreated($properties['created'])
			->setDTStamp($properties['dtstamp'])
			->setLocation($properties['location'])
			->setUrl($properties['url'])
			->setRRule($properties['rrule'])
			->setTransparent($properties['transp'])
			->setCategories($properties['categories'])
			->setOrganizer($properties['organizer'])
			->setAttendees($properties['attendee'])
			->setModified($properties['last-modified'])
			->setStatus($properties['status'])
			->setAttachment($properties['attach']);
	}

	private function getTimezoneComponent($properties, $subComponents): Timezone
	{
		return (Timezone::getInstance())
			->setTimezoneId($properties['tzid']['value'])
			->setTimezoneUrl($properties['tzurl']['value'])
			->setSubComponents($subComponents);
	}
}