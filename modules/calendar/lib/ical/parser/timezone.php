<?php


namespace Bitrix\Calendar\ICal\Parser;


class Timezone extends ParserComponent
{
	/**
	 * @var ParserPropertyType|null
	 */
	private $tzid;
	/**
	 * @var ParserPropertyType|null
	 */
	private $tzurl;
	/**
	 * @var array
	 */
	private $standard;
	/**
	 * @var array
	 */
	private $daylight;

	/**
	 * @return Timezone
	 */
	public static function createInstance(): Timezone
	{
		return new self();
	}

	/**
	 * Timezone constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return 'VTIMEZONE';
	}

	/**
	 * @return string[]
	 */
	public function getProperties(): array
	{
		return [
			'TZID',
			'TZURL'
		];
	}

	/**
	 * @param ParserPropertyType|null $tzid
	 * @return $this
	 */
	public function setTimezoneId(?ParserPropertyType $tzid): Timezone
	{
		$this->tzid = $tzid;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $url
	 * @return $this
	 */
	public function setTimezoneUrl(?ParserPropertyType $url): Timezone
	{
		$this->tzurl = $url;

		return $this;
	}

	/**
	 * @param array $subComponents
	 * @return $this
	 */
	public function setSubComponents(iterable $subComponents): Timezone
	{
		foreach ($subComponents as $subComponent)
		{
			if ($subComponent instanceof Observance)
			{
				if ($subComponent instanceof StandardObservance)
				{
					$this->standard[] = $subComponent;
				}
				elseif($subComponent instanceof DaylightObservance)
				{
					$this->daylight[] = $subComponent;
				}
			}
		}

		return $this;
	}

	/**
	 *
	 */
	public function getContent(): void
	{
		// TODO: Implement getContent() method.
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getTzUrl(): ?ParserPropertyType
	{
		return $this->tzurl;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getTzId(): ?ParserPropertyType
	{
		return $this->tzid;
	}
}