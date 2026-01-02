<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Storage\Db\Model;

enum MessageStatus: string
{
	case New = 'new';
	case Processing = 'processing';

	/**
	 * @return array<string>
	 */
	public static function getValues(): array
	{
		$values = [];

		foreach (self::cases() as $case)
		{
			$values[] = $case->value;
		}

		return $values;
	}
}
