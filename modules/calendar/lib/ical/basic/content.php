<?php

namespace Bitrix\Calendar\ICal\Basic;

use Bitrix\Main\Type\Date;

class Content
{
	private $type;
	private $properties = [];
	private $subComponents = [];

	public static function getInstance(string $type): Content
	{
		return new self($type);
	}

	public function __construct(string $type)
	{
		$this->type = $type;
	}

	public function property(PropertyType $property, array $parameters = null): Content
	{
		$property->addParameters($parameters ?? []);

		$this->properties[] = $property;

		return $this;
	}

	public function dateTimeProperty(
		$names,
		Date $value,
		bool $withTime = false,
		bool $withTimeZone = false,
		bool $isUTC = false
	): Content
	{
		if ($value === null)
		{
			return $this;
		}

		return $this->property(new DateTimePropertyType($names, $value, $withTime, $withTimeZone, $isUTC));
	}

	public function textProperty($names, ?string $value, bool $disableEscaping = false) : Content
	{
		if ($value === null) {
			return $this;
		}

		return $this->property(new TextPropertyType($names, $value, $disableEscaping));
	}

	public function timezoneOffsetProperty(
		$names,
		\DateTimeZone $value
	): Content
	{
		if ($value === null)
		{
			return $this;
		}

		return $this->property(new DateTimePropertyType($names, $value));
	}

	public function subComponent(BasicComponent ...$components) : Content
	{
		foreach ($components as $component)
		{
			$this->subComponents[] = $component;
		}

		return $this;
	}

	public function getType() : string
	{
		return $this->type;
	}

	public function getProperties() : array
	{
		return $this->properties;
	}

	public function getSubComponents() : array
	{
		return $this->subComponents;
	}
}
