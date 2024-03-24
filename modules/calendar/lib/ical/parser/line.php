<?php


namespace Bitrix\Calendar\ICal\Parser;


class Line
{
	private const COMPONENT_PROPERTY_NAME_BEGIN = 'begin';
	private const COMPONENT_PROPERTY_NAME_END = 'end';
	private const FIELDS_LIST_PARSE_WITH_END = ['attendee', 'dtstart', 'dtend'];
	private $name;
	private $value;
	private $params = [];
	private $line;

	/**
	 * @param string $line
	 * @return Line
	 */
	public static function createInstance(string $line): Line
	{
		return new self($line);
	}

	/**
	 * Line constructor.
	 * @param string $line
	 */
	public function __construct(string $line)
	{
		$this->line = $line;
	}


	/**
	 * @return $this
	 */
	public function parse(): Line
	{
		$line = $this->line;
		$valuePos = (int) mb_strpos($line, ':');
		$parts = explode(';', mb_substr($line, 0, $valuePos));
		$name = mb_strtolower(array_shift($parts));
		if (in_array($name, self::FIELDS_LIST_PARSE_WITH_END, true))
		{
			$valuePos = (int) mb_strrpos($this->line, ':');
		}
		$value = $this->getValueFromString($valuePos);

		$params = [];
		foreach($parts as $v)
		{
			if (!str_contains($v, '='))
			{
				continue;
			}

			[$k, $v] = explode('=', $v);
			$params[mb_strtolower($k)] = trim($v, '"');
		}

		$this->value = $value;
		$this->params = $params;
		$this->name = $name;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isBegin(): bool
	{
		return $this->name === self::COMPONENT_PROPERTY_NAME_BEGIN;
	}

	/**
	 * @return bool
	 */
	public function isEnd(): bool
	{
		return $this->name === self::COMPONENT_PROPERTY_NAME_END;
	}

	/**
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * @return array
	 */
	public function getValueAsArray(): array
	{
		if (mb_strpos($this->value,",") !== false)
		{
			return explode(",",$this->value);
		}

		return [$this->value];
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getValue();
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return count($this->params);
	}

	/**
	 * @param int $valuePos
	 * @return string
	 */
	private function getValueFromString(int $valuePos): string
	{
		$replacements = array('from'=>['\\,', '\\n', '\\;', '\\:', '\\"'], 'to'=>[',', "\n", ';', ':', '"']);
		$tmp = trim(mb_substr($this->line, $valuePos+1));

		return str_replace($replacements['from'], $replacements['to'], $tmp);
	}
}
