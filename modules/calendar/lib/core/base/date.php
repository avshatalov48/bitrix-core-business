<?php

namespace Bitrix\Calendar\Core\Base;

use Bitrix\Calendar\Util;
use Bitrix\Main\Type;
use DateTimeZone;

class Date extends BaseProperty
{
	/**
	 * @var Type\Date
	 */
	private Type\Date $date;
	/**
	 * @var mixed|string
	 */
	private $format;

	/**
	 * @param string $date
	 * @param string $format
	 * @return Date
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function createDateTimeFromFormat(string $date, string $format): ?Date
	{
		$timeZone = null;
		$entity = \DateTime::createFromFormat($format, $date);

		if ($entity && $entity->getTimeZone()->getName() === 'Z')
		{
			$timeZone = new DateTimeZone(Util::DEFAULT_TIMEZONE);
		}

        if ($entity)
        {
            return new self(new Type\DateTime($entity->format($format), $format, $timeZone), $format);
        }

        return null;
	}

	/**
	 * @param string $date
	 * @param string $format
	 * @return Date
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function createDateFromFormat(string $date, string $format): Date
	{
		return new self(new Type\Date($date, $format), $format);
	}

	/**
	 * @param Type\Date|null $date
	 * @param string|null $dateFormat
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function __construct(Type\Date $date = null, string $dateFormat = null)
	{
		$this->date = $date ?? Util::getDateObject(null, false, (new \DateTime())->getTimezone()->getName());
		$this->format = $dateFormat
			?? $this->date instanceof Type\DateTime
				? Type\Date::convertFormatToPhp(FORMAT_DATETIME)
				: Type\Date::convertFormatToPhp(FORMAT_DATE)
		;
	}

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return [
			'date' => $this->date->format($this->format),
			'timezone' => ($this->date instanceof Type\DateTime) ? $this->date->getTimeZone()->getName() : null,
		];
	}

	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->date->format($this->format);
	}

	/**
	 * @param DateTimeZone $timeZone
	 * @return $this
	 */
	public function setTimezone(DateTimeZone $timeZone): Date
	{
		if ($this->date instanceof Type\DateTime)
		{
			$this->date->setTimeZone($timeZone);
		}

		return $this;
	}

	/**
	 * @param string|null $format
	 * @return string
	 */
	public function format(string $format = null): string
	{
		return $this->date->format($format ?? $this->format);
	}

	/**
	 * return clone object. original object not change
	 *
	 * @param string $addTime
	 * @return Date
	 */
	public function add(string $addTime): Date
	{
		$object = clone $this;
		$object->date->add($addTime);

		return $object;
	}

	/**
	 * @return int
	 */
	public function getTimestamp(): int
	{
		return $this->date->getTimestamp();
	}

	/**
	 * return clone object. original object not change
	 *
	 * @param string $subtractTime
	 * @return Date
	 */
	public function sub(string $subtractTime): Date
	{
		$object = clone $this;
		$object->date->add("-{$subtractTime}");

		return $object;
	}

	/**
	 * @return void
	 */
	public function __clone()
	{
		$this->date = clone $this->date;
	}

	/**
	 * @param int $hour
	 * @param int $minutes
	 * @param int $seconds
	 * @return $this
	 */
	public function setTime(int $hour, int $minutes, int $seconds): Date
	{
		if ($this->date instanceof Type\DateTime)
		{
			$this->date->setTime($hour, $minutes, $seconds);
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function resetTime(): Date
	{
		return $this->setTime(0,0,0);
	}

	/**
	 * @return bool
	 */
	public function isDateTime(): bool
	{
		return $this->date instanceof Type\DateTime;
	}

	/**
	 * @return Type\Date
	 */
	public function getDate(): Type\Date
	{
		return $this->date;
	}

	/**
	 * @return int
	 */
	public function getHour(): int
	{
		return (int)$this->date->format('H');
	}

	/**
	 * @return int
	 */
	public function getMinutes(): int
	{
		return (int)$this->date->format('i');
	}

	/**
	 * @return int
	 */
	public function getSeconds(): int
	{
		return (int)$this->date->format('s');
	}

	/**
	 * @return int
	 */
	public function getMonth(): int
	{
		return (int)$this->date->format('m');
	}

	/**
	 * @return int
	 */
	public function getDay(): int
	{
		return (int)$this->date->format('d');
	}

	/**
	 * @return int
	 */
	public function getYear(): int
	{
		return (int)$this->date->format('Y');
	}

	/**
	 * @return bool
	 */
	public function isStartDay(): bool
	{
		return $this->getHour() === 0 && $this->getMinutes() === 0 && $this->getSeconds() === 0;
	}

	/**
	 * @param string $format
	 * @return $this
	 */
	public function setDateTimeFormat(string $format): Date
	{
		$this->format = $format;

		return $this;
	}

	/**
	 * @return mixed|string
	 */
	public function getDateTimeFormat(): string
	{
		return $this->format;
	}
}
