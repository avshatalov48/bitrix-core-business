<?php


namespace Bitrix\Calendar\ICal\Basic;


class AttachPropertyType extends PropertyType
{
	private $attach;

	public static function getInstance($names, AttachProperty $attach): AttachPropertyType
	{
		return new self($names, $attach);
	}

	public function __construct($names, AttachProperty $attach)
	{
		parent::__construct($names);

		$this->attach = $attach;

		if ($this->attach->name)
		{
			$this->addParameter(Parameter::getInstance('FILENAME', $this->attach->name));
		}
	}

	public function getValue(): string
	{
		return "{$this->attach->url}";
	}

	public function getOriginalValue(): AttachProperty
	{
		return $this->attach;
	}
}