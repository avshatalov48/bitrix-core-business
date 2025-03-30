<?php
namespace Bitrix\Main\Data;

class CacheEngineMemcache extends CacheEngine
{
	public function getConnectionName(): string
	{
		return 'cache.memcache';
	}

	public static function getConnectionClass()
	{
		return MemcacheConnection::class;
	}

	protected function modifyConfigByEngine(&$config, $cacheConfig, array $options = []): void
	{
		$config['persistent'] = true;
		if (isset($cacheConfig['persistent']) && $cacheConfig['persistent'] == 0)
		{
			$config['persistent'] = false;
		}

		if (isset($cacheConfig['connectionTimeout']))
		{
			$config['connectionTimeout'] = (int) $cacheConfig['connectionTimeout'];
		}
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
		return self::$engine->add($key, $value, null, $ttl);
	}

	public function checkInSet($key, $value) : bool
	{
		$list = self::$engine->get($key);

		if (!is_array($list))
		{
			$list = [];
		}

		if (array_key_exists($value, $list))
		{
			return true;
		}

		return false;
	}

	public function addToSet($key, $value)
	{
		$list = self::$engine->get($key);

		if (!is_array($list))
		{
			$list = [];
		}

		if (!array_key_exists($value, $list))
		{
			$list[$value] = 1;
			$this->set($key, 0, $list);
		}
	}

	public function getSet($key): array
	{
		$list = self::$engine->get($key);
		if (!is_array($list) || empty($list))
		{
			return [];
		}

		return array_keys($list);
	}

	public function deleteBySet($key, $prefix = '')
	{
		$list = self::$engine->get($key);
		self::$engine->delete($key);

		if (is_array($list) && !empty($list))
		{
			array_walk($list, function ($value, $key) {
				self::$engine->delete($key);
			});
			unset($list);
		}
	}

	public function delFromSet($key, $member)
	{
		$list = self::$engine->get($key);

		if (is_array($list) && !empty($list))
		{
			$rewrite = false;
			if (!is_array($member))
			{
				$member = [0 => $member];
			}

			foreach ($member as $keyID)
			{
				if (array_key_exists($keyID, $list))
				{
					$rewrite = true;
					unset($list[$keyID]);
				}
			}

			if ($rewrite)
			{
				if (empty($list))
				{
					self::$engine->delete($key);
				}
				else
				{
					$this->set($key, 0, $list);
				}
			}
			unset($list);
		}
	}
}