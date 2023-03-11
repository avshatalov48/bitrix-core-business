<?php

namespace Bitrix\Catalog\v2\IoC;

use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use ReflectionNamedType;

/**
 * Class Container
 *
 * @package Bitrix\Catalog\v2\IoC
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
final class Container implements ContainerContract
{
	private $dependencies = [];
	private $entities = [];

	private $lock = false;

	public function __construct()
	{
		$this->dependencies[static::class] = static::class;
		$this->dependencies[ContainerContract::class] = static::class;
	}

	private function __clone()
	{
	}

	public function has(string $id): bool
	{
		return isset($this->dependencies[$id]);
	}

	public function get(string $id, array $args = [])
	{
		if (!$this->has($id))
		{
			throw new ObjectNotFoundException("Dependency {{$id}} not found.");
		}

		$definition = $this->getDefinition($id, $args);

		if (!isset($this->entities[$definition]))
		{
			$this->entities[$definition] = $this->instantiate($id, $args);
		}

		return $this->entities[$definition];
	}

	// ToDo highlight container return values with phpstorm.meta.php
	public function make(string $id, array $args = [])
	{
		if (!$this->has($id))
		{
			throw new ObjectNotFoundException("Dependency {{$id}} not found.");
		}

		return $this->instantiate($id, $args);
	}

	public function inject(string $id, $dependency): ContainerContract
	{
		if ($this->isLocked())
		{
			throw new NotSupportedException(
				"Dependency {{$dependency}} cannot be injected after resolving container dependencies.
				 Try this before resolving."
			);
		}

		$this->dependencies[$id] = $dependency;

		if (!isset($this->dependencies[$dependency]) && class_exists($dependency))
		{
			$this->dependencies[$dependency] = $dependency;
		}

		unset($this->entities[$id]);

		return $this;
	}

	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->dependencies);
	}

	private function instantiate($id, array $args = [])
	{
		$this->lock();

		$dependency = $this->dependencies[$id];

		if ($dependency instanceof \Closure)
		{
			$entity = $this->instantiateClosure($dependency, $args);
		}
		elseif (class_exists($dependency))
		{
			$entity = $this->instantiateClass($dependency, $args);
		}
		else
		{
			$entity = $dependency;
		}

		return $entity;
	}

	private function instantiateClosure($dependency, array $args = [])
	{
		$function = new \ReflectionFunction($dependency);

		$invokeArguments = [];

		foreach ($function->getParameters() as $parameter)
		{
			$invokeArguments[] = $this->resolveParameter($parameter, $args);
		}

		return $function->invokeArgs($invokeArguments);
	}

	private function instantiateClass($dependency, array $args = [])
	{
		$class = new \ReflectionClass($dependency);
		$constructor = $class->getConstructor();

		if ($constructor)
		{
			$invokeArguments = [];

			foreach ($constructor->getParameters() as $parameter)
			{
				$invokeArguments[] = $this->resolveParameter($parameter, $args);
			}

			return $class->newInstance(...$invokeArguments);
		}

		return $class->newInstanceWithoutConstructor();
	}

	private function resolveParameter(\ReflectionParameter $parameter, array $args = [])
	{
		$type = $parameter->getType();
		if (($type instanceof ReflectionNamedType) && !$type->isBuiltin())
		{
			return $this->resolveClassParameter($parameter, $args);
		}

		return $this->resolveVariableParameter($parameter, $args);
	}

	private function resolveClassParameter(\ReflectionParameter $parameter, array $args = [])
	{
		try
		{
			$type = $parameter->getType();
			if (($type instanceof ReflectionNamedType) && !$type->isBuiltin())
			{
				$className = $type->getName();
			}
			else
			{
				throw new ObjectNotFoundException('Not class type');
			}

			if ($className === static::class || is_subclass_of(static::class, $className))
			{
				return $this;
			}

			$dependency = $args[$className] ?? $this->get($className, $args);
		}
		catch (ObjectNotFoundException $exception)
		{
			if ($parameter->isDefaultValueAvailable())
			{
				$dependency = $parameter->getDefaultValue();
			}
			else
			{
				$name = $parameter->getName();
				throw new ObjectNotFoundException("Dependency {{$name}} not found.");
			}
		}

		return $dependency;
	}

	private function resolveVariableParameter(\ReflectionParameter $parameter, array $args = [])
	{
		$parameterName = $parameter->getName();

		if (isset($args[$parameterName]))
		{
			return $args[$parameterName];
		}

		if ($parameter->isDefaultValueAvailable())
		{
			return $parameter->getDefaultValue();
		}

		return null;
	}

	private function lock(): self
	{
		$this->lock = true;

		return $this;
	}

	private function isLocked(): bool
	{
		return $this->lock;
	}

	private function getDefinition(string $id, array $args = []): string
	{
		if (empty($args))
		{
			return $id;
		}

		ksort($args);
		$argString = '';

		foreach ($args as $key => $argument)
		{
			if (is_object($argument))
			{
				$argument = spl_object_hash($argument);
			}

			$argString .= "|$key=$argument";
		}

		return $id . $argString;
	}
}
