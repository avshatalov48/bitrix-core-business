<?php


namespace Bitrix\Calendar\ICal\Builder;

use Bitrix\Main\Type\Date;

class Alert
{
	private const TRIGGER_START = 'trigger_start';
	private const TRIGGER_END = 'trigger_end';
	private const TRIGGER_DATE = 'trigger_date';
	private $triggerDate;
	private $triggerInterval;
	private $triggerMode = self::TRIGGER_DATE;
	private $message;

	public static function date(Date $date, string $description = null): Alert
	{
		return static::getInstance($description)->triggerDate($date);
	}

	public static function minutesBeforeStart(int $minutes, string $description = null): Alert
	{
		$interval = new \DateInterval("PT{$minutes}M");
		$interval->invert = 1;

		return static::getInstance($description)->triggerAtStart($interval);
	}

	public static function minutesAfterStart(int $min, string $description = null): Alert
	{
		return static::getInstance($description)->triggerAtStart(new \DateInterval("PT{$min}M"));
	}

	public static function minutesBeforeEnd(int $min, string $description = null): Alert
	{
		$interval = new \DateInterval("PT{$min}M");
		$interval->invert = 1;

		return static::getInstance($description)->triggerAtEnd($interval);
	}

	public static function minutesAfterEnd(int $min, string $description = null): Alert
	{
		return static::getInstance($description)->triggerAtEnd(new \DateInterval("PT{$min}M"));
	}

	private static function getInstance(array $reminds = [], $description = ''): Alert
	{
		return new self($reminds, $description);
	}

	public function __construct(array $reminds = [], $description = '')
	{
		$this->message = $reminds;
		$this->message = $description;
	}

	public function getType(): string
	{
		return 'VALARM';
	}

	public function getRequiredProperties(): array
	{
		return [
			'ACTION',
			'TRIGGER',
			'DESCRIPTION',
		];
	}

	public function message(string $message): Alert
	{
		$this->message = $message;

		return $this;
	}

	public function triggerDate(Date $triggerAt): Alert
	{
		$this->triggerMode = self::TRIGGER_DATE;
		$this->triggerDate = $triggerAt;

		return $this;
	}

	public function triggerAtStart(\DateInterval $interval): Alert
	{
		$this->triggerMode = self::TRIGGER_START;
		$this->triggerInterval = $interval;

		return $this;
	}

	public function triggerAtEnd(\DateInterval $interval): Alert
	{
		$this->triggerMode = self::TRIGGER_END;
		$this->triggerInterval = $interval;

		return $this;
	}

	protected function setContent(): Content
	{
		return Content::getInstance($this->getType())
			->textProperty('ACTION', 'DISPLAY')
			->textProperty('DESCRIPTION', $this->message)
			->property($this->resolveTriggerProperty());
	}

	private function resolveTriggerProperty()
	{
		if ($this->triggerMode === self::TRIGGER_DATE) {
			return DateTimePropertyType::getInstance(
				'TRIGGER',
				$this->triggerDate,
				true
			)->addParameter(new Parameter('VALUE', 'DATE-TIME'));
		}

		$property = LengthPropertyType::getInstance('TRIGGER', $this->triggerInterval);

		if ($this->triggerMode === self::TRIGGER_END) {
			return $property->addParameter(new Parameter('RELATED', 'END'));
		}

		return $property;
	}
}