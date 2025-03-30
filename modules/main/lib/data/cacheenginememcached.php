<?php
namespace Bitrix\Main\Data;

class CacheEngineMemcached extends CacheEngineMemcache
{
	public function getConnectionName(): string
	{
		return 'cache.memcached';
	}

	protected function modifyConfigByEngine(&$config, $cacheConfig, array $options = []): void
	{
		parent::modifyConfigByEngine($config, $cacheConfig, $options);

		if (isset($cacheConfig['serializer']))
		{
			$config['serializer'] = (int) $cacheConfig['serializer'];
		}
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

	public function setNotExists($key, $ttl, $value)
	{
		$ttl = self::getExpire($ttl);
		return self::$engine->add($key, $value, $ttl);
	}

	public function deleteBySet($key, $prefix = '')
	{
		$list = self::$engine->get($key);
		self::$engine->delete($key);

		if (is_array($list) && !empty($list))
		{
			$list = array_keys($list);
			self::$engine->deleteMulti($list);
			unset($list);
		}
	}
}