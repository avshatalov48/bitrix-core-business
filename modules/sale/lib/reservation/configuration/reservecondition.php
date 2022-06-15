<?php

namespace Bitrix\Sale\Reservation\Configuration;

use Bitrix\Main\SystemException;

/**
 * Reservation condition in the sale.
 *
 * This class is similar to `enum` with constants, so you don't need to create instances for it.
 */
class ReserveCondition
{
	public const ON_CREATE = 'O';
	public const ON_PAY = 'R';
	public const ON_FULL_PAY = 'P';
	public const ON_ALLOW_DELIVERY = 'D';
	public const ON_SHIP = 'S';

	/**
	 * Validating value of reserve condition.
	 *
	 * @see `ReserveCondition::isValid` if you need only checking, without throwed exception.
	 *
	 * @param string $value
	 *
	 * @return void
	 * @throws Exception if value is invalid.
	 */
	public static function validate(string $value): void
	{
		if (!self::isValid($value))
		{
			throw new SystemException("Invalid reserve condition value: '{$value}'");
		}
	}

	/**
	 * Available values of reserve condition.
	 *
	 * @return array
	 */
	public static function getAvailableValues(): array
	{
		return [
			self::ON_CREATE,
			self::ON_PAY,
			self::ON_FULL_PAY,
			self::ON_ALLOW_DELIVERY,
			self::ON_SHIP,
		];
	}

	/**
	 * Validating value of reserve condition.
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	public static function isValid(string $value): bool
	{
		return in_array($value, self::getAvailableValues(), true);
	}
}
