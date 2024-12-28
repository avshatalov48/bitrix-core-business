<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use BackedEnum;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessController;
use Bitrix\Socialnetwork\Control\Command\Attribute\Override;
use Bitrix\Socialnetwork\Control\Command\ValueObject\CreateObjectInterface;
use Bitrix\Socialnetwork\Control\Command\ValueObject\CreateWithDefaultValueInterface;
use Bitrix\Socialnetwork\ValueObjectInterface;
use ReflectionClass;
use ReflectionProperty;
use TypeError;

abstract class AbstractCommand implements Arrayable
{
	/**
	 * @throws ObjectException
	 */
	public function __construct()
	{
		$properties = (new ReflectionClass($this))->getProperties();

		foreach ($properties as $property)
		{
			$name = $property->getName();
			$type = $property->getType()?->getName();

			$overrideType = $this->getOverrideClass($property);

			if ($overrideType !== null)
			{
				if (!is_subclass_of($overrideType, $type))
				{
					throw new ObjectException('Cannot override property');
				}

				$type = $overrideType;
			}

			if (
				$this instanceof DefaultValueCommandInterface
				&& is_subclass_of($type, CreateWithDefaultValueInterface::class)
			)
			{
				$this->{$name} = $type::createWithDefaultValue();
			}
		}
	}

	/**
	 * @throws ArgumentException
	 */
	public static function createFromArray(array|Arrayable $data): static
	{
		if ($data instanceof Arrayable)
		{
			$data = $data->toArray();
		}

		$instance = new static();
		$reflection = new ReflectionClass($instance);

		foreach ($data as $key => $value)
		{
			if ($value === null)
			{
				continue;
			}

			if (!$reflection->hasProperty($key))
			{
				continue;
			}

			$reflectionProperty = $reflection->getProperty($key);
			$type = $reflectionProperty->getType();
			$typeName = $type?->getName();
			try
			{
				if ($type === null || $type->isBuiltin())
				{
					$instance->{$key} = $value;
				}
				elseif (is_subclass_of($typeName, BackedEnum::class))
				{
					$instance->{$key} = $typeName::tryFrom($value);
				}
				elseif (is_subclass_of($typeName, DateTime::class))
				{
					$instance->{$key} = $typeName::createFromUserTime($value);
				}
				elseif (is_subclass_of($typeName, CreateObjectInterface::class))
				{
					$instance->{$key} = $typeName::create($value);
				}
			}
			catch (TypeError)
			{
			}
			catch (ObjectNotFoundException $e)
			{
				throw new ArgumentException($e->getMessage(), $e->getCode());
			}
		}

		return $instance;
	}

	public function toArray(): array
	{
		$properties = (new ReflectionClass($this))->getProperties();

		$data = [];

		foreach ($properties as $property)
		{
			if (!$property->isInitialized($this))
			{
				continue;
			}

			$name = $property->getName();
			$value = $property->getValue($this);

			if ($value instanceof ValueObjectInterface)
			{
				$data[$name] = $value->getValue();
			}
			elseif ($value instanceof BackedEnum)
			{
				$data[$name] = $value->value;
			}
			else
			{
				$data[$name] = $value;
			}
		}

		return $data;
	}

	public function __call(string $name, array $args)
	{
		$operation = substr($name, 0, 3);
		$property = lcfirst(substr($name, 3));

		if ($operation === 'set')
		{
			return $this->setProperty($property, $args);
		}

		if ($operation === 'get')
		{
			return $this->{$property} ?? null;
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

	protected function getAccessControllerClass(): ?string
	{
		$attributes = (new ReflectionClass($this))->getAttributes();
		foreach ($attributes as $attributeReflection)
		{
			$attribute = $attributeReflection->newInstance();
			if ($attribute instanceof AccessController)
			{
				return $attribute->class;
			}
		}

		return null;
	}

	protected function getOverrideClass(ReflectionProperty $reflectionProperty): ?string
	{
		$attributes = $reflectionProperty->getAttributes();
		foreach ($attributes as $attributeReflection)
		{
			$attribute = $attributeReflection->newInstance();
			if ($attribute instanceof Override)
			{
				return $attribute->class;
			}
		}

		return null;
	}
}