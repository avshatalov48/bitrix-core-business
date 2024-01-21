<?php

namespace Bitrix\Catalog\Config\Options;

use Bitrix\Catalog\Config\State;

/**
 * Option for toggle decrease store product quantity checking algorithm.
 *
 * Is option 'E' - checks catalog rights;
 * Is option 'D' - nothing action;
 * Is option 'N' - see product field `NEGATIVE_AMOUNT_TRACE`.
 */
class CheckRightsOnDecreaseStoreAmount extends Option
{
	public const NAME = 'check_rights_on_decrease_store_quantity';
	public const DEFAULT_VALUE = self::NOT_USED;

	public const ENABLED_VALUE = 'E';
	public const DISABLED_VALUE = 'D';
	public const NOT_USED = 'N';

	/**
	 * Checks rights - not used.
	 *
	 * @return bool
	 */
	public static function isNotUsed(): bool
	{
		return self::get() === self::NOT_USED;
	}

	/**
	 * Checks rights - is enabled.
	 *
	 * @return bool
	 */
	public static function isEnabled(): bool
	{
		return self::get() === self::ENABLED_VALUE && !State::isProductBatchMethodSelected();
	}

	/**
	 * Checks rights - is disabled.
	 *
	 * @return bool
	 */
	public static function isDisabled(): bool
	{
		return self::get() === self::DISABLED_VALUE;
	}

	/**
	 * Checks is available value.
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	public static function isAvailableValue(string $value): bool
	{
		$available = [
			self::ENABLED_VALUE,
			self::DISABLED_VALUE,
			self::NOT_USED,
		];

		return in_array($value, $available, true);
	}
}
