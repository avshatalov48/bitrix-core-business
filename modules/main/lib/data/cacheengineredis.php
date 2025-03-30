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

	protected function modifyConfigByEngine(&$config, $cacheConfig, array $options = []): void
	{
		if (isset($cacheConfig['serializer']))
		{
			$config['serializer'] = (int) $cacheConfig['serializer'];
		}

		$config['persistent'] = true;
		if (isset($cacheConfig['persistent']) && $cacheConfig['persistent'] == 0)
		{
			$config['persistent'] = false;
		}

		if (isset($cacheConfig['compression']))
		{
			$config['compression'] = $cacheConfig['compression'];
		}

		if (isset($cacheConfig['compression_level']))
		{
			$config['compression_level'] = $cacheConfig['compression_level'];
		}

		if (isset($cacheConfig['timeout']))
		{
			$cacheConfig['timeout'] = (float) $cacheConfig['timeout'];
			if ($cacheConfig['timeout'] > 0)
			{
				$config['timeout'] = $cacheConfig['timeout'];
			}
		}

		if (isset($cacheConfig['read_timeout']))
		{
			$cacheConfig['read_timeout'] = (float) $cacheConfig['read_timeout'];
			if ($cacheConfig['read_timeout'] > 0)
			{
				$config['read_timeout'] = $cacheConfig['read_timeout'];
			}
		}
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
			self::$engine->del($list);
		}
	}

	public function delFromSet($key, $member)
	{
		if (!is_array($member))
		{
			$member = [0 => $member];
		}

		if (!empty($member))
		{
			self::$engine->sRem($key, ...$member);
		}
	}
}