<?php


namespace Bitrix\Calendar\ICal\Builder;


use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\Content;
use Bitrix\Calendar\ICal\Basic\Observance;
use Bitrix\Calendar\Util;

class Timezone extends BasicComponent implements BuilderComponent
{
	private $id;
	private $observances = [];

	public static function getInstance(): Timezone
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

	public function setTimezoneId(string $id = null): Timezone
	{
		if ($id)
		{
			$this->id = $id;
		}
		else
		{
			$this->id = Util::prepareTimezone()->getName();
		}

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