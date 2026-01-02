<?php

namespace Bitrix\Rest\V3\Dto;

use Bitrix\Rest\V3\Attributes\AbstractAttribute;
use ReflectionClass;

final class PropertyHelper
{
	/**
	 * @param string|ReflectionClass|Dto $dtoClass
	 * @return \ReflectionProperty[]
	 */
	public static function getProperties(string|ReflectionClass|Dto $dtoClass): array
	{
		$reflection = self::getReflection($dtoClass);

		$properties = [];

		foreach ($reflection->getProperties() as $property)
		{
			if ($property->isPublic() && !$property->isStatic())
			{
				$properties[] = $property;
			}
		}

		return $properties;
	}

	public static function getProperty(string|ReflectionClass|Dto $dtoClass, string $propertyName): \ReflectionProperty|null
	{
		$reflection = self::getReflection($dtoClass);

		if ($reflection->hasProperty($propertyName))
		{
			$property = $reflection->getProperty($propertyName);

			if ($property->isPublic() && !$property->isStatic())
			{
				return $property;
			}
		}

		return null;
	}

	public static function isValidProperty(string|ReflectionClass|Dto $dtoClass, $propertyName): bool
	{
		$reflection = self::getReflection($dtoClass);

		$isValid = false;

		if ($reflection->hasProperty($propertyName))
		{
			$property = $reflection->getProperty($propertyName);

			if ($property->isPublic() && !$property->isStatic())
			{
				$isValid = true;
			}
		}

		return $isValid;
	}

	/**
	 * @throws \ReflectionException
	 */
	public static function hasAttribute(string|ReflectionClass|Dto $dtoClass, string $propertyName, string $attributeName): bool
	{
		$reflection = self::getReflection($dtoClass);
		$result = false;

		$reflectionProperty = $reflection->getProperty($propertyName);
		$attributes = $reflectionProperty->getAttributes();
		foreach ($attributes as $attribute)
		{
			if ($attribute->getName() === $attributeName)
			{
				$result = true;
			}
		}

		return $result;
	}

	public static function getAttribute(string|ReflectionClass|Dto $dtoClass, string $attributeName): ?AbstractAttribute
	{
		$reflection = self::getReflection($dtoClass);
		$result = null;

		$attributes = $reflection->getAttributes($attributeName);

		if (!empty($attributes) && isset($attributes[0]))
		{
			$attributeInstance = $attributes[0]->newInstance();
			if ($attributeInstance instanceof AbstractAttribute)
			{
				$result = $attributeInstance;
			}
		}

		return $result;
	}

	public static function getPropertiesWithAttribute(string|ReflectionClass|Dto $dtoClass, string $attributeName): array
	{
		$reflection = self::getReflection($dtoClass);
		$reflectionProperties = $reflection->getProperties();
		$resultProperties = [];
		foreach ($reflectionProperties as $reflectionProperty)
		{
			$attributes = $reflectionProperty->getAttributes();
			foreach ($attributes as $attribute)
			{
				if ($attribute->getName() === $attributeName)
				{
					$resultProperties[] = $reflectionProperty;
				}
			}
		}

		return $resultProperties;
	}

	public static function getReflection(string|ReflectionClass|Dto $dtoClass): ReflectionClass
	{
		if ($dtoClass instanceof ReflectionClass)
		{
			return $dtoClass;
		}

		return new ReflectionClass($dtoClass);
	}
}
