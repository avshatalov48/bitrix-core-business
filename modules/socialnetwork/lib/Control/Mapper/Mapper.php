<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper;

use BackedEnum;
use Bitrix\Socialnetwork\Control\Command\AbstractCommand;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Map;
use Bitrix\Socialnetwork\ValueObjectInterface;
use ReflectionClass;
use ReflectionProperty;

class Mapper
{
	public function toArray(AbstractCommand $command): array
	{
		$data = [];
		$reflection = new ReflectionClass($command);
		$properties = $reflection->getProperties();

		foreach ($properties as $property)
		{
			if (!$property->isInitialized($command))
			{
				continue;
			}

			$value = $property->getValue($command);

			if ($value instanceof ValueObjectInterface)
			{
				$value = $value->getValue();
			}
			elseif ($value instanceof BackedEnum)
			{
				$value = $value->value;
			}

			$mappers = $this->getFieldMappers($property);

			foreach ($mappers as $mapper)
			{
				[$fieldName, $fieldValue] = $mapper->getNameAndValue($value);

				if ($fieldValue === null)
				{
					continue;
				}

				if (!isset($data[$fieldName]))
				{
					$data[$fieldName] = $fieldValue;
				}
			}
		}

		return $data;
	}

	/** @return Map[] */
	protected function getFieldMappers(ReflectionProperty $property): array
	{
		$result = [];

		$attributes = $property->getAttributes();

		foreach ($attributes as $attributeReflection)
		{
			$attribute = $attributeReflection->newInstance();

			if ($attribute instanceof Map)
			{
				$result[] = $attribute;
			}
		}

		return $result;
	}
}