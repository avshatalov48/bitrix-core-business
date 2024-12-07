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
		$cacheKey = sha1($key . '|' . $value);
		$iexKey = $key . '|iex|' . $cacheKey;
		$itemExist = self::$engine->get($iexKey);

		if ($itemExist === $cacheKey)
		{
			return true;
		}

		$list = self::$engine->get($key);

		if (!is_array($list))
		{
			$list = [];
		}

		if (array_key_exists($value, $list))
		{
			$this->set($iexKey, 2591000, $cacheKey);
			return true;
		}

		return false;
	}

	public function addToSet($key, $value)
	{
		$cacheKey = sha1($key . '|' . $value);
		$iexKey = $key . '|iex|' . $cacheKey;
		$itemExist = self::$engine->get($iexKey);
		if ($itemExist === $cacheKey)
		{
			return;
		}

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

		$this->set($iexKey, 2591000, $cacheKey);
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
			$list = array_keys($list);
			foreach ($list as $iKey)
			{
				$delKey = $prefix . $iKey;
				self::$engine->delete($key . '|iex|' . sha1($key . '|' . $iKey));

				if ($this->useLock)
				{
					if ($cachedData = self::$engine->get($delKey))
					{
						$this->set($delKey . '|old', $this->ttlOld, $cachedData);
					}
				}
				self::$engine->delete($delKey);
			}
			unset($list);
		}
	}

	public function delFromSet($key, $member)
	{
		$list = self::$engine->get($key);

		if (is_array($list) && !empty($list))
		{
			$rewrite = false;
			$tmpKey = $key . '|iex|';
			if (is_array($member))
			{
				foreach ($member as $keyID)
				{
					if (array_key_exists($keyID, $list))
					{
						$rewrite = true;
						unset($list[$keyID]);

						$iexKey = $tmpKey . sha1($key . '|' . $keyID);
						self::$engine->delete($iexKey);
					}
				}
			}
			elseif (array_key_exists($member, $list))
			{
				$rewrite = true;
				unset($list[$member]);

				$iexKey = $tmpKey . sha1($key . '|' . $member);
				self::$engine->delete($iexKey);
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