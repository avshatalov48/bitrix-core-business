<?php


namespace Bitrix\Calendar\ICal\Parser;


class FactoryComponents
{
	/**
	 * @var string
	 */
	private $componentName;
	/**
	 * @var ParserComponent
	 */
	private $component;

	/**
	 * @param string $componentName
	 * @return FactoryComponents
	 */
	public static function createInstance(string $componentName): FactoryComponents
	{
		return new self($componentName);
	}

	/**
	 * FactoryComponents constructor.
	 * @param string $componentName
	 */
	public function __construct(string $componentName)
	{
		$this->componentName = $componentName;
	}

	/**
	 * @param $properties
	 * @param $subComponents
	 *
	 * @return $this
	 * @throws IcalParserException
	 */
	public function createComponent($properties, $subComponents): FactoryComponents
	{
		switch ($this->componentName)
		{
			case 'standard':
				$this->component = $this->getStandardComponent($properties);
				break;
			case 'daylight':
				$this->component = $this->getDaylightComponent($properties);
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
			case 'valarm':
				break; //TODO: Add VALARM component support
			default:
				$this->addMessageLog();
		}

		return $this;
	}

	/**
	 * @return ParserComponent|null
	 */
	public function getComponent(): ?ParserComponent
	{
		return $this->component;
	}

	/**
	 * @param $properties
	 * @return StandardObservance
	 */
	private function getStandardComponent(?array $properties): StandardObservance
	{
		return StandardObservance::createInstance()
				->setTzOffsetTo($properties['tzoffsetto'] ?? null)
				->setTzOffsetFrom($properties['tzoffsetfrom'] ?? null)
				->setDtStart($properties['dtstart'] ?? null);
	}

	/**
	 * @param array|null $properties
	 * @return DaylightObservance
	 */
	private function getDaylightComponent(?array $properties): DaylightObservance
	{
		return DaylightObservance::createInstance()
			->setTzOffsetTo($properties['tzoffsetto'] ?? null)
			->setTzOffsetFrom($properties['tzoffsetfrom'] ?? null)
			->setDtStart($properties['dtstart'] ?? null);
	}

	/**
	 * @param $properties
	 * @param $subComponents
	 * @return Calendar
	 */
	private function getCalendarComponent($properties, $subComponents): Calendar
	{
		/** @var ParserPropertyType[] $properties */
		$name = isset($properties['name']) ? $properties['name']->getValue() : 'Outer Calendar';
		return  (Calendar::createInstance($name))
			->setMethod($properties['method'] ?? null)
			->setProdId($properties['prodid'] ?? null)
			->setCalScale($properties['calscale'] ?? null)
			->setVersion($properties['version'] ?? null)
			->setSubComponents($subComponents);
	}

	/**
	 * @param $properties
	 * @param $subComponents
	 *
	 * @return Event
	 * @throws IcalParserException
	 */
	private function getEventComponent($properties, $subComponents): Event
	{
		if (empty($properties['uid']))
		{
			throw new IcalParserException("event identifier is not passed");
		}
		return (Event::createInstance($properties['uid']->getValue()))
			->setStart($properties['dtstart'] ?? null)
			->setEnd($properties['dtend'] ?? null)
			->setDescription($properties['description'] ?? null)
			->setSummary($properties['summary'] ?? null)
			->setSequence($properties['sequence'] ?? null)
			->setCreated($properties['created'] ?? null)
			->setDTStamp($properties['dtstamp'] ?? null)
			->setLocation($properties['location'] ?? null)
			->setUrl($properties['url'] ?? null)
			->setRRule($properties['rrule'] ?? null)
			->setTransparent($properties['transp'] ?? null)
			->setCategories($properties['categories'] ?? null)
			->setOrganizer($properties['organizer'] ?? null)
			->setAttendees($properties['attendee'] ?? null)
			->setModified($properties['last-modified'] ?? null)
			->setStatus($properties['status'] ?? null)
			->setRecurrenceId($properties['recurrence-id'] ?? null)
			->setAttachment($properties['attach'] ?? null);
	}

	/**
	 * @param $properties
	 * @param $subComponents
	 * @return Timezone
	 */
	private function getTimezoneComponent($properties, $subComponents): Timezone
	{
		return Timezone::createInstance()
			->setTimezoneId($properties['tzid'] ?? null)
			->setTimezoneUrl($properties['tzurl'] ?? null)
			->setSubComponents($subComponents);
	}

	/**
	 * @return void
	 */
	private function addMessageLog(): void
	{
		AddMessage2Log("Component not found: {$this->componentName}", "calendar", 2);
	}
}
