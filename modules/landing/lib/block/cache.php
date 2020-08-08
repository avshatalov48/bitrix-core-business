<?php
namespace Bitrix\Landing\Block;

use \Bitrix\Landing\Manager;

class Cache
{
	/**
	 * Tag prefix.
	 */
	const TAG_PREFIX = 'landing_block_';

	protected static $cacheDisabled = false;

	/**
	 * Disables caching.
	 * @return void
	 */
	public static function disableCache(): void
	{
		self::$cacheDisabled = true;
	}

	/**
	 * Enables caching.
	 * @return void
	 */
	public static function enableCache(): void
	{
		self::$cacheDisabled = false;
	}

	/**
	 * Cache is enabled.
	 * @return bool
	 */
	public static function isCaching(): bool
	{
		if (self::$cacheDisabled)
		{
			return false;
		}
		return defined('BX_COMP_MANAGED_CACHE') &&
			   BX_COMP_MANAGED_CACHE === true;
	}

	/**
	 * Registers the block in the cache.
	 * @param int $id Block id.
	 * @return void
	 */
	public static function register($id): void
	{
		$id = intval($id);

		if ($id > 0 && self::isCaching())
		{
			Manager::getCacheManager()->registerTag(
				self::TAG_PREFIX . $id
			);
		}
	}

	/**
	 * Clears cache for the block.
	 * @param int $id Block id.
	 * @return void
	 */
	public static function clear($id): void
	{
		$id = intval($id);

		if ($id > 0 && self::isCaching())
		{
			Manager::getCacheManager()->clearByTag(
				self::TAG_PREFIX . $id
			);
		}
	}
}