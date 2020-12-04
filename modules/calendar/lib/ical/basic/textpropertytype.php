<?php


namespace Bitrix\Calendar\ICal\Basic;


class TextPropertyType extends PropertyType
{
	private $text;
	private $disableEscaping;

	public static function getInstance($names, string $text, $disableEscaping = false): TextPropertyType
	{
		return new self($names, $text, $disableEscaping);
	}

	public function __construct($names, string $text, $disableEscaping = false)
	{
		parent::__construct($names);

		$this->text = $text;
		$this->disableEscaping = $disableEscaping;
	}

	public function getValue(): string
	{
		if ($this->disableEscaping) {
			return $this->text;
		}

		$replacements = [
			'\\' => '\\\\',
			'"' => '\\"',
			',' => '\\,',
			';' => '\\;',
			"\n" => '\\n',
		];

		return str_replace(array_keys($replacements), $replacements, $this->text);
	}

	public function getOriginalValue(): string
	{
		return $this->text;
	}
}