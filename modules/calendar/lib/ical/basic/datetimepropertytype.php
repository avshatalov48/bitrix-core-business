<?php


namespace Bitrix\Calendar\ICal\Basic;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class DatetimePropertyType extends PropertyType
{
	private $dateTime;
	private $withTime;
	private $withTimezone;
	private $isUTC;

	public static function getInstance(
		$names,
		Date $dateTime,
		bool $withTime = false,
		bool $withTimezone = false,
		bool $isUTC = false
	): DateTimePropertyType
	{
		return new self($names, $dateTime, $withTime, $withTimezone, $isUTC);
	}

	public function __construct(
		$names,
		Date $dateTime,
		bool $withTime = false,
		bool $withTimezone = false,
		bool $isUTC = false
	)
	{
		parent::__construct($names);

		$this->dateTime = $dateTime;
		$this->withTimezone = $withTimezone;
		$this->withTime = $withTime;
		$this->isUTC = $isUTC;

		if ($this->withTimezone && $this->dateTime instanceof DateTime)
		{
			$timezone = $this->dateTime->getTimezone()->getName();
			$this->addParameter(new Parameter('TZID', $timezone));
		}
		elseif (!$this->withTime)
		{
			$this->addParameter(new Parameter('VALUE', 'DATE'));
		}
	}

	public function getValue(): string
	{
		if ($this->isUTC)
		{
			$format = 'Ymd\THis\Z';
		}
		else
		{
			$format = $this->withTime ? 'Ymd\THis' : 'Ymd';
		}

		return $this->dateTime->format($format);
	}

	public function getOriginalValue(): Date
	{
		return $this->dateTime;
	}
}