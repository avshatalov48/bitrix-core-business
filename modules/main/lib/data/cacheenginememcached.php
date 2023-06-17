<?php
namespace Bitrix\Main\Data;

class CacheEngineMemcached extends CacheEngineMemcache
{
	public function getConnectionName() : string
	{
		return 'cache.memcached';
	}

	public static function getConnectionClass()
	{
		return MemcachedConnection::class;
	}

	public function set($key, $ttl, $value) : bool
	{
		$ttl = self::getExpire($ttl);
		return self::$engine->set($key, $value, $ttl);
	}

	public function del($key)
	{
		if (!is_array($key))
		{
			$key = [$key];
		}

		self::$engine->deleteMulti($key);
	}
}