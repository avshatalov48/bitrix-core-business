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
			$exKey = array_map(function ($iKey) use($key) {
				return $key . '|iex|' . sha1($key . '|' . $iKey);
			}, $list);

			self::$engine->deleteMulti($exKey);
			unset($exKey);

			if ($this->useLock)
			{
				foreach ($list as $iKey)
				{
					$delKey = $prefix . $iKey;
					if ($cachedData = $this->get($delKey))
					{
						$this->set($delKey . '|old', $this->ttlOld, $cachedData);
					}

					self::$engine->delete($delKey);
				}
			}
			else
			{
				if ($prefix != '')
				{
					$format = $prefix . '%s';
					$list = array_map(function ($key) use($format) {
						return sprintf($format, $key);
					}, $list);
				}

				self::$engine->deleteMulti($list);
			}
			unset($list);
		}
	}
}