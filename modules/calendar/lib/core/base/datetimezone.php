<?php

namespace Bitrix\Calendar\Core\Base;

use Bitrix\Calendar\Util;

class DateTimeZone extends BaseProperty
{
	protected $timeZone;

	/**
	 * @param ?string $timezone
	 * @return DateTimeZone
	 */
	public static function createByString(?string $timezone): DateTimeZone
	{
		return new self(Util::prepareTimezone($timezone));
	}

	public function __construct(\DateTimeZone $timeZone)
	{
		$this->timeZone = $timeZone;
	}

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return [
			'timeZone',
		];
	}

	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->timeZone->getName();
	}

	/**
	 * @return \DateTimeZone
	 */
	public function getTimeZone(): \DateTimeZone
	{
		return $this->timeZone;
	}
}