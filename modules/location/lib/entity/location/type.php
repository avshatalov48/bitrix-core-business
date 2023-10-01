<?php

namespace Bitrix\Location\Entity\Location;

/**
 * Location types
 * Class Type
 * @package Bitrix\Location\Entity\Location
 */
class Type
{
	public const UNKNOWN = 0;

	public const COUNTRY = 100;

	public const ADM_LEVEL_1 = 200;
	public const ADM_LEVEL_2 = 210;
	public const ADM_LEVEL_3 = 220;
	public const ADM_LEVEL_4 = 230;

	public const LOCALITY = 300;
	public const SUB_LOCALITY = 310;
	public const SUB_LOCALITY_LEVEL_1 = 320;
	public const SUB_LOCALITY_LEVEL_2 = 330;
	public const STREET = 340;

	public const BUILDING = 400;
	public const ADDRESS_LINE_1 = 410;

	public const FLOOR = 420;
	public const ROOM = 430;

	public static function isTypeExist(string $type): bool
	{
		return defined(static::class.'::'.$type);
	}

	public static function isValueExist(int $value): bool
	{
		$reflection = new \ReflectionClass(static::class);
		$values = array_values($reflection->getConstants());

		return in_array($value, $values, true);
	}
}
