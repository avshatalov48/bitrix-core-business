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

	public function checkInSet($key, $value) : bool
	{
		return self::$engine->sIsMember($key, $value);
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
		self::$engine->del($key);

		if (is_array($list)  && !empty($list))
		{
			if ($this->useLock)
			{
				foreach ($list as $iKey)
				{
					$delKey = $prefix . $iKey;
					$oldKey = $delKey . '|old';

					if (self::$engine->rename($delKey, $oldKey))
					{
						self::$engine->expire($oldKey, $this->ttlOld);
					}
				}
			}
			else
			{
				if ($prefix != '')
				{
					$format = $prefix . '%s';
					$list = array_map(function ($key) use ($format) {
						return sprintf($format, $key);
					}, $list);
				}

				self::$engine->del($list);
			}
		}
	}

	public function delFromSet($key, $member)
	{
		if (is_array($member))
		{
			if (!empty($member))
			{
				self::$engine->sRem($key, ...$member);
			}
		}
		else
		{
			self::$engine->sRem($key, $member);
		}
	}
}