<?php

namespace Bitrix\Rest\V3\Structures;

use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class FieldsValidator
{
	public static function validateReflectionPropertyAndValue(\ReflectionProperty $property, mixed $value): bool
	{
		if ($property->getType() === null)
		{
			return true;
		}

		return match ($property->getType()->getName())
		{
			'int' => is_int($value),
			'float' => is_float($value),
			'string' => is_string($value),
			'bool' => is_bool($value),
			'array' => is_array($value),
			DateTime::class => $value instanceof DateTime,
			Date::class => $value instanceof Date,
			default => false,
		};
	}
}