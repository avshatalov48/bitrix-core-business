<?php

namespace Bitrix\Main\Data;

interface CacheEngineInterface
{
	/**
	 * Returns true if cache can be read or written.
	 *
	 * @return bool
	 */
	public function isAvailable();

	/**
	 * Cleans (removes) the value from the cache by the key (directory or file).
	 *
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $filename File name.
	 * @return void
	 */
	public function clean($baseDir, $initDir = false, $filename = false);

	/**
	 * Reads the value from the cache. Returns true if the value exists, not expired, and successfully read.
	 *
	 * @param mixed &$vars Cached result.
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $filename File name.
	 * @param integer $ttl Expiration period in seconds.
	 * @return boolean
	 */
	public function read(&$vars, $baseDir, $initDir, $filename, $ttl);

	/**
	 * Writes the value into the cache by the key.
	 *
	 * @param mixed $vars Cached result.
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $filename File name.
	 * @param integer $ttl Expiration period in seconds.
	 * @return void
	 */
	public function write($vars, $baseDir, $initDir, $filename, $ttl);

	/**
	 * Returns true if the cache file has expired.
	 *
	 * @param string $path Absolute physical path.
	 * @return boolean
	 */
	public function isCacheExpired($path);
}
