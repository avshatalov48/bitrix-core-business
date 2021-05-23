<?php


namespace Bitrix\Calendar\ICal\Basic;


class PropertyCreator
{
	private $property;

	public function __construct(PropertyType $property)
	{
		$this->property = $property;
	}

	public function build(): array
	{
		$parameters = $this->resolveParameters();

		$value = $this->property->getValue();

		return array_map(function (string $name) use ($value, $parameters)
		{
			if ($value === '' && $parameters === '')
			{
				return "{$name}:";
			}

			if ($value === '')
			{
				if ($name === 'RRULE')
				{
					$parameters = substr_replace($parameters, ':',0, 1);
				}

				return "{$name}{$parameters}";
			}

			return "{$name}{$parameters}:{$value}";
		}, $this->property->getNames());
	}

	private function resolveParameters(): string
	{
		$parameters = '';

		foreach ($this->property->getParameters() as $parameter)
		{
			$name = $parameter->getName();
			$value = $parameter->getValue();

			$parameters .= ";{$name}={$value}";
		}

		return $parameters;
	}
}