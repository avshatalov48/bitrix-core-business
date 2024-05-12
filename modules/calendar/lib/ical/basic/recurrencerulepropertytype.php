<?php


namespace Bitrix\Calendar\ICal\Basic;


class RecurrenceRulePropertyType extends PropertyType
{
	private $rrule;

	public static function createInstance($names, RecurrenceRuleProperty $rrule): RecurrenceRulePropertyType
	{
		return new self($names, $rrule);
	}

	public function __construct($names, RecurrenceRuleProperty $rrule)
	{
		parent::__construct($names);

		$this->rrule = $rrule;

		if ($this->rrule->freq)
		{
			$this->addParameter(Parameter::getInstance('FREQ', $this->rrule->freq));
		}

		if ($this->rrule->interval)
		{
			$this->addParameter(Parameter::getInstance('INTERVAL', $this->rrule->interval));
		}

		if ($this->rrule->day && is_array($this->rrule->day))
		{
			$this->addParameter(Parameter::getInstance('BYDAY', implode(',', $this->rrule->day), true));
		}

		if ($this->rrule->count)
		{
			$this->addParameter(Parameter::getInstance('COUNT', $this->rrule->count));
		}

		if ($this->rrule->until)
		{
			$this->addParameter(Parameter::getInstance('UNTIL', $this->rrule->until));
		}
	}

	public function getValue(): string
	{
		return "";
	}

	public function getOriginalValue(): RecurrenceRuleProperty
	{
		return $this->rrule;
	}
}