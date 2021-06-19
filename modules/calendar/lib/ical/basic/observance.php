<?php


namespace Bitrix\Calendar\ICal\Basic;


use Bitrix\Calendar\ICal\MailInvitation\Helper;
use Bitrix\Calendar\Util;
use DateTimeZone;

abstract class Observance extends BasicComponent
{
	protected $start;
	protected $offsetFrom;
	protected $offsetTo;
	protected $timezone;

	public static function createInstance(): Observance
	{
		return new static();
	}

	public function getProperties(): array
	{
		return [
			'DTSTART',
			'TZOFFSETFROM',
			'TZOFFSETTO',
		];
	}

	/**
	 * @param null $start
	 * @return $this
	 */
	public function setDTStart($start = null): Observance
	{
		$this->start = $start ? $start : Helper::getIcalDateTimeShort('19700101T000000');

		return $this;
	}

	/**
	 * @param DateTimeZone $tz
	 * @return $this
	 */
	public function setOffsetFrom(DateTimeZone $tz): Observance
	{
		$time = Util::getDateObject(null, false, $tz->getName());
		$this->offsetFrom = $time->format('O');

		return $this;
	}

	public function setOffsetFromValue(string $value): Observance
	{
		$this->offsetFrom = $value;
		return $this;
	}

	/**
	 * @param DateTimeZone $tz
	 * @return $this
	 */
	public function setOffsetTo(DateTimeZone $tz): Observance
	{
		$time = Util::getDateObject(null, false, $tz->getName());
		$this->offsetTo = $time->format('O');
		return $this;
	}

	public function setOffsetToValue(string $value): Observance
	{
		$this->offsetTo = $value;
		return $this;
	}

	public function setAbbrTimezone($tz): Observance
	{
		$exp = (new \DateTime($tz))->format('T');
		$this->timezone = (new \DateTime())->setTimeZone(new DateTimeZone($tz))->format('T');
		return $this;
	}

	public function setTimezoneFromAbbr(?string $abbr): Observance
	{
		$this->timezone = $abbr;
		return $this;
	}

	public function setContent(): Content
	{
		return Content::getInstance($this->getType())
			->dateTimeProperty('DTSTART', $this->start, true, false)
			->textProperty('TZOFFSETFROM', $this->offsetFrom)
			->textProperty('TZOFFSETTO', $this->offsetTo)
			->textProperty('TZNAME', $this->timezone);
	}
}
