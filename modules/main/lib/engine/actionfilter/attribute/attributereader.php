<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\ActionFilter\Attribute;

use ReflectionAttribute;
use ReflectionMethod;

class AttributeReader
{
	public function buildConfig(ReflectionMethod $method): array
	{
		$configuration = [];

		$attributes = $method->getAttributes(FilterAttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF);
		foreach ($attributes as $reflectionAttribute)
		{
			$attribute = $reflectionAttribute->newInstance();

			$type = $attribute->getType()->value;

			$configuration[$type] = [...$configuration[$type] ?? [], ...$attribute->getFilters()];
		}

		return $configuration;
	}

	public function hasMethodFilterAttributes(ReflectionMethod $method): bool
	{
		return !empty(
			$method->getAttributes(FilterAttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF)
		);
	}
}