<?php


namespace Bitrix\Calendar\ICal\Basic;


class Parameter
{
	private $name;
	private $value;
	private $disableEscaping;

	public static function getInstance(string $name, string $value, $disableEscaping = false): Parameter
	{
		return new self($name, $value, $disableEscaping);
	}

	public function __construct(string $name, string $value, $disableEscaping = false)
	{
		$this->name = $name;

		$this->value = $value;
		$this->disableEscaping = $disableEscaping;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getValue(): string
	{
		if ($this->disableEscaping) {
			return $this->value;
		}

		$replacements = [
			'\\' => '\\\\',
			'"' => '\\"',
			',' => '\\,',
			';' => '\\;',
			"\n" => '\\n',
		];

		return str_replace(array_keys($replacements), $replacements, $this->value);
	}
}