<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Role;

use Bitrix\Main\Localization\Loc;

abstract class RoleDictionary
	implements AccessibleRoleDictionary
{
	protected static $locLoaded = [];

	public static function getRoleName(string $code): string
	{
		static::loadLoc();

		$name = Loc::getMessage($code);
		if ($name)
		{
			return $name;
		}
		return $code;
	}

	protected static function loadLoc()
	{
		if (
			!array_key_exists(static::class, static::$locLoaded)
			|| !static::$locLoaded[static::class]
		)
		{
			$r = new \ReflectionClass(static::class);
			Loc::loadMessages($r->getFileName());
			static::$locLoaded[static::class] = true;
		}
	}
}