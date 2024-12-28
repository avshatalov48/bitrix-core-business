<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Meta;

use Bitrix\Main\UI\EntitySelector\Converter;
use Bitrix\Socialnetwork\Collab\Controller\Meta\Attribute\MetaType;
use Bitrix\Socialnetwork\Collab\Controller\Meta\Attribute\PropertyMetaType;
use ReflectionProperty;

class MetaTypeConverter
{
	public static function getMetaType(string $class, string $property): ?PropertyMetaType
	{
		$reflection = new ReflectionProperty($class, $property);
		$attributes = $reflection->getAttributes(MetaType::class);

		foreach ($attributes as $attribute)
		{
			/** @var MetaType $attributeInstance */
			$attributeInstance = $attribute->newInstance();

			return $attributeInstance->type;
		}

		return null;
	}

	public static function convert(PropertyMetaType $type, mixed $value): mixed
	{
		if ($type === PropertyMetaType::MemberSelectorCodes)
		{
			return Converter::convertToFinderCodes($value);
		}

		return $value;
	}
}