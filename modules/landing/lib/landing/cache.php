<?php
namespace Bitrix\Landing\Landing;

use \Bitrix\Landing\Manager;

class Cache
{
	/**
	 * Tag prefix.
	 */
	const TAG_PREFIX = 'landing_page_';

	/**
	 * Cache is enabled.
	 * @return bool
	 */
	public static function isCaching()
	{
		return defined('BX_COMP_MANAGED_CACHE') &&
			   BX_COMP_MANAGED_CACHE === true;
	}

	/**
	 * Registers the page in the cache.
	 * @param int $id Landing id.
	 * @return void
	 */
	public static function register($id)
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
	 * Clears cache for the page.
	 * @param int $id Landing id.
	 * @return void
	 */
	public static function clear($id)
	{
		$id = intval($id);

		if ($id > 0 && self::isCaching())
		{
			Manager::getCacheManager()->ClearByTag(
				self::TAG_PREFIX . $id
			);
		}
	}
}