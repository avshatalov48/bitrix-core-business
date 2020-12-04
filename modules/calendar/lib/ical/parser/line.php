<?php


namespace Bitrix\Calendar\ICal\Parser;


class Line
{
	private $name;
	private $value;
	private $params = [];
	private $line;

	public static function getInstance(string $line)
	{
		return new self($line);
	}

	public function __construct(string $line)
	{
		$this->line = $line;
	}

	public function prepareData()
	{
		$line = $this->line;
		$valuePos = (int) strpos($line, ':');
		$parts = explode(';', substr($line, 0, $valuePos));
		$name = strtolower(array_shift($parts));
		$value = $this->getValueFromString($valuePos);

		$params = [];
		foreach($parts as $v)
		{
			list($k, $v) = explode('=', $v);
			$params[strtolower($k)] = trim($v, '"');
		}

		$this->value = $value;
		$this->params = $params;
		$this->name = $name;
	}

	public function isBegin(): bool
	{
		return $this->name === 'begin';
	}

	public function isEnd(): bool
	{
		return $this->name == 'end';
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getParams(): array
	{
		return $this->params;
	}

	public function getValueAsArray(): array
	{
		if (strpos($this->value,",") !== false)
		{
			return explode(",",$this->value);
		}

		return [$this->value];
	}

	public function __toString()
	{
		return $this->getValue();
	}

	public function count()
	{
		return count($this->params);
	}

	private function getValueFromString(int $valuePos): string
	{
		$replacements = array('from'=>['\\,', '\\n', '\\;', '\\:', '\\"'], 'to'=>[',', "\n", ';', ':', '"']);
		$tmp = trim(substr($this->line, $valuePos+1));
		$tmp = str_replace($replacements['from'], $replacements['to'], $tmp);

		return  $tmp;
	}
}