<?php


namespace Bitrix\Calendar\ICal\Basic;


class AttendeesPropertyType extends PropertyType
{
	private $calendarAddress;

	public static function createInstance($names, AttendeesProperty $calendarAddress): AttendeesPropertyType
	{
		return new self($names, $calendarAddress);
	}

	public function __construct($names, AttendeesProperty $calendarAddress)
	{
		parent::__construct($names);

		$this->calendarAddress = $calendarAddress;

		if ($this->calendarAddress->participationStatus)
		{
			$this->addParameter(Parameter::getInstance('PARTSTAT', $this->calendarAddress->participationStatus));
		}

		if ($this->calendarAddress->role)
		{
			$this->addParameter(Parameter::getInstance('ROLE', $this->calendarAddress->role));
		}

		if ($this->calendarAddress->cutype)
		{
			$this->addParameter(Parameter::getInstance('CUTYPE', $this->calendarAddress->cutype));
		}

		if ($this->calendarAddress->rsvp)
		{
			$this->addParameter(Parameter::getInstance('RSVP', 'TRUE'));
		}


		if ($this->calendarAddress->name)
		{
			$this->addParameter(Parameter::getInstance('CN', trim($this->calendarAddress->name)));
		}

		if ($this->calendarAddress->email)
		{
			$this->addParameter(Parameter::getInstance('EMAIL', trim($this->calendarAddress->email)));
		}
	}

	public function getValue(): string
	{
		if (!empty($this->calendarAddress->mailto))
		{
			return "mailto:{$this->calendarAddress->mailto}";
		}
		return "mailto:{$this->calendarAddress->email}";
	}

	public function getOriginalValue(): AttendeesProperty
	{
		return $this->calendarAddress;
	}
}
