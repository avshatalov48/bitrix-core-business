<?php


namespace Bitrix\Calendar\ICal\Parser;


use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\Content;

class Timezone extends BasicComponent implements ParserComponent
{
	private $tzid;
	private $tzurl;
	/**
	 * @var array
	 */
	private $standard;
	/**
	 * @var array
	 */
	private $daylight;

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
			'TZURL'
		];
	}

	public function setTimezoneId($value): Timezone
	{
		$this->tzid = $value;
		return $this;
	}

	public function setTimezoneUrl($value)
	{
		$this->tzurl = $value;
		return $this;
	}

	public function setSubComponents(array $subComponents)
	{
		foreach ($subComponents as $subComponent)
		{
			if ($subComponent instanceof BasicComponent)
			{
				if ($subComponent->getType() === 'STANDARD')
				{
					$this->standard[] = $subComponent;
				}
				else
				{
					$this->daylight[] = $subComponent;
				}
			}
		}
		return $this;
	}


	protected function setContent(): Content
	{

	}

	public function getContent()
	{
		// TODO: Implement getContent() method.
	}
}