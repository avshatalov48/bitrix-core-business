<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use ReflectionClass;

abstract class AbstractCommand
{
	public function __call(string $name, array $args)
	{
		$operation = substr($name, 0, 3);
		$property = lcfirst(substr($name, 3));

		if ($operation === 'set')
		{
			return $this->setProperty($property, $args);
		}

		return null;
	}

	protected function setProperty(string $property, array $args): static
	{
		$reflection = new ReflectionClass($this);
		if (!$reflection->hasProperty($property))
		{
			return $this;
		}

		$reflectionProperty = $reflection->getProperty($property);
		if ($reflectionProperty->isReadOnly())
		{
			return $this;
		}

		$this->{$property} = $args[0];

		return $this;
	}
}