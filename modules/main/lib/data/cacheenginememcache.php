<?php
namespace Bitrix\Main\Data;

class CacheEngineMemcache extends CacheEngine
{
	public function getConnectionName() : string
	{
		return 'cache.memcache';
	}

	public static function getConnectionClass()
	{
		return MemcacheConnection::class;
	}

	protected static function getExpire($ttl)
	{
		$ttl = (int) $ttl;
		if ($ttl > 2592000)
		{
			$ttl = microtime(1) + $ttl;
		}

		return $ttl;
	}

	public function set($key, $ttl, $value)
	{
		$ttl = self::getExpire($ttl);
		return self::$engine->set($key, $value, 0, $ttl);
	}

	public function get($key)
	{
		return self::$engine->get($key);
	}

	public function del($key)
	{
		if (is_array($key))
		{
			foreach ($key as $item)
			{
				self::$engine->delete($item);
			}
		}
		else
		{
			self::$engine->delete($key);
		}
	}

	public function setNotExists($key, $ttl, $value)
	{
		$ttl = self::getExpire($ttl);
		return self::$engine->add($key, $value, 0, $ttl);
	}

	public function addToSet($key, $value)
	{
		$list = self::$engine->get($key);
		if (!is_array($list))
		{
			$list = [];
		}
		$list[$value] = 1;

		$this->set($key, 0, $list);
	}

	public function getSet($key) : array
	{
		$list = self::$engine->get($key);
		if (!is_array($list) || empty($list))
		{
			return [];
		}

		return array_keys($list);
	}

	public function delFromSet($key, $member)
	{
		$list = self::$engine->get($key);

		if (is_array($list) && !empty($list))
		{
			if (is_array($member))
			{
				foreach ($member as $keyID)
				{
					unset($list[$keyID]);
				}
			}
			else
			{
				unset($list[$member]);
			}

			$this->set($key, 0, $list);
		}
	}
}