<?php


namespace Bitrix\Calendar\ICal\Parser;


abstract class Observance extends ParserComponent
{
	public const TYPE = StandardObservance::TYPE;
	/**
	 * @var ParserPropertyType
	 */
	protected $dtStart;
	/**
	 * @var ParserPropertyType
	 */
	protected $tzOffsetFrom;
	/**
	 * @var ParserPropertyType
	 */
	protected $tzOffsetTo;

	/**
	 * Observance constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return static::TYPE;
	}

	/**
	 * @return string[]
	 */
	public function getProperties(): array
	{
		return [
			"DTSTART",
			"TZOFFSETFROM",
			"TZOFFSETTO",
		];
	}

	/**
	 * @param ParserPropertyType|null $dtStart
	 * @return $this
	 */
	public function setDtStart(?ParserPropertyType $dtStart): Observance
	{
		$this->dtStart = $dtStart;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $tzOffsetFrom
	 * @return $this
	 */
	public function setTzOffsetFrom(?ParserPropertyType $tzOffsetFrom): Observance
	{
		$this->tzOffsetFrom = $tzOffsetFrom;

		return $this;
	}

	/**
	 * @param ParserPropertyType|null $tzOffsetTo
	 * @return $this
	 */
	public function setTzOffsetTo(?ParserPropertyType $tzOffsetTo): Observance
	{
		$this->tzOffsetTo = $tzOffsetTo;

		return $this;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getTzOffsetTo(): ?ParserPropertyType
	{
		return $this->tzOffsetTo;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getTzOffsetFrom(): ?ParserPropertyType
	{
		return $this->tzOffsetFrom;
	}

	/**
	 * @return ParserPropertyType|null
	 */
	public function getDtStart(): ?ParserPropertyType
	{
		return $this->dtStart;
	}
}