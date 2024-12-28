<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Item\Workgroup;

enum Type: string
{
	case Group = 'group';
	case Project = 'project';
	case Scrum = 'scrum';
	case Collab = 'collab';

	public static function getDefault(): self
	{
		return self::Group;
	}

	public static function getValue(self|string $enum): string
	{
		if ($enum instanceof self)
		{
			return $enum->value;
		}

		return $enum;
	}

	public static function isValid(mixed $value): bool
	{
		if ($value instanceof self)
		{
			return true;
		}

		if (is_string($value) && self::tryFrom($value) !== null)
		{
			return true;
		}

		return false;
	}
}