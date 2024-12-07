<?php

namespace Bitrix\Catalog\Store\EnableWizard;

class ModeList
{
	public const B24 = 'B24';
	public const ONEC = '1C';

	public static function isValidMode(string $mode): bool
	{
		return in_array($mode, [self::B24, self::ONEC], true);
	}
}
