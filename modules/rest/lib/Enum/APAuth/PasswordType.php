<?php

declare(strict_types=1);

namespace Bitrix\Rest\Enum\APAuth;

enum PasswordType: string
{
	case User = 'user';
	case System = 'system';

	public static function getValues(): array
	{
		$values = [];
		$cases = self::cases();

		foreach ($cases as $case)
		{
			$values[] = $case->value;
		}

		return $values;
	}
}
