<?php

namespace Bitrix\Catalog\Config\Options;

/**
 * Entity for working with options.
 *
 * Any option, no matter how small it is, can have different variants of values.
 *
 * By creating a separate class for an option, we can describe its purpose,
 * available options, as well as language messages.
 */
abstract class Option
{
	public const NAME = null;
	public const DEFAULT_VALUE = null;

	/**
	 * Get option value.
	 *
	 * @return string
	 */
	public static function get(): string
	{
		return (string)\Bitrix\Main\Config\Option::get('catalog', static::NAME, static::DEFAULT_VALUE);
	}

	/**
	 * Set option value.
	 *
	 * @param string $value
	 *
	 * @return void
	 */
	public static function set(string $value): void
	{
		\Bitrix\Main\Config\Option::set('catalog', static::NAME, $value);
	}
}
