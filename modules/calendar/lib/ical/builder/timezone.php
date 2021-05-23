<?php


namespace Bitrix\Calendar\ICal\Builder;


use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\Content;
use Bitrix\Calendar\ICal\Basic\Observance;
use Bitrix\Calendar\Util;
use DateTimeZone;

class Timezone extends BasicComponent implements BuilderComponent
{
	private $id;
	private $observances = [];

	public static function createInstance(): Timezone
	{
		return new self();
	}

	public function __construct()
	{
	}

	public function getType(): string
	{
		return 'VTIMEZONE';
	}

	public function getProperties(): array
	{
		return [
			'TZID',
		];
	}

	/**
	 * @param DateTimeZone $tz
	 * @return $this
	 */
	public function setTimezoneId(DateTimeZone $tz): Timezone
	{
		$this->id = $tz->getName();

		return $this;
	}

	public function setObservance(Observance $observance)
	{
		$this->observances[] = $observance;

		return $this;
	}

	/**
	 * @return Content
	 */
	public function setContent(): Content
	{
		return Content::getInstance($this->getType())
			->textProperty('TZID', $this->id)
			->subComponent(...$this->observances);
	}
}