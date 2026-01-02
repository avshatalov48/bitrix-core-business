<?php

namespace Bitrix\Main\Data;

interface CacheEngineStatInterface
{
	/**
	 * Returns number of bytes read from cache.
	 *
	 * @return integer
	 */
	public function getReadBytes();

	/**
	 * Returns number of bytes written to cache.
	 *
	 * @return integer
	 */
	public function getWrittenBytes();

	/**
	 * Returns the cache key (path) after read or write operation.
	 *
	 * @return string
	 */
	public function getCachePath();
}
