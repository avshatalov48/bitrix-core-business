<?php
namespace Bitrix\Main\Data;

class CacheEngineRedis extends CacheEngine
{
	public function getConnectionName() : string
	{
		return 'cache.redis';
	}

	public static function getConnectionClass()
	{
		return RedisConnection::class;
	}

	public function set($key, $ttl, $value) : bool
	{
		$ttl = (int) $ttl;
		if ($ttl > 0)
		{
			return self::$engine->setex($key, $ttl, $value);
		}
		else
		{
			return self::$engine->set($key, $value);
		}
	}

	public function get($key)
	{
		return self::$engine->get($key);
	}

	public function del($key)
	{
		self::$engine->del($key);
	}

	public function setNotExists($key, $ttl, $value)
	{
		$ttl = (int) $ttl;
		if (self::$engine->setnx($key, $value))
		{
			if ($ttl > 0)
			{
				self::$engine->expire($key, $ttl);
			}

			return true;
		}

		return false;
	}

	public function addToSet($key, $value)
	{
		self::$engine->sAdd($key, $value);
	}

	public function getSet($key) : array
	{
		$list = self::$engine->sMembers($key);
		if (!is_array($list))
		{
			$list = [];
		}
		return $list;
	}

	public function deleteBySet($key, $prefix = '')
	{
		$list = self::$engine->sMembers($key);

		if (is_array($list)  && !empty($list))
		{
			if ($prefix == '')
			{
				self::$engine->del($list);
			}
			else
			{
				foreach ($list as $key)
				{
					self::$engine->del($prefix . $key);
				}
			}
		}
	}

	public function delFromSet($key, $member)
	{
		if (is_array($member))
		{
			foreach ($member as $keyID)
			{
				self::$engine->sRem($key, $keyID);
			}
		}
		else
		{
			self::$engine->sRem($key, $member);
		}
	}
}