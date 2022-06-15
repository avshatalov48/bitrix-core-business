<?php

namespace Bitrix\Sale\PaySystem;

/**
 * Client type for pay system
 */
abstract class ClientType
{
	/**
	 * Default value 
	 * 
	 * For example, if the value is not filled in the payment system
	 */
	public const DEFAULT = self::B2C;
	
	/**
	 * The buyer is a individual
	 */
	public const B2C = 'b2c';
	
	/**
	 * The buyer is a legal entity
	 */
	public const B2B = 'b2b';
	
	/**
	 * Available client type values
	 *
	 * @return array
	 */
	public static function getAvailableValues(): array
	{
		return [
			self::B2C,
			self::B2B,
		];
	}
	
	/**
	 * Validation client type value
	 * 
	 * @param string $value
	 * @return bool true - if the $value matches the available case-sensitive values
	 */
	public static function isValid(string $value): bool
	{
		return in_array($value, self::getAvailableValues(), true);
	}
}