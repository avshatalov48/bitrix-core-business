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
		return self::$engine->add($key, $value, 0, $ttl);
	}

	public function addToSet($key, $value)
	{
		$cacheKey = sha1($key . '|' . $value);
		if (array_key_exists($cacheKey, self::$listKeys))
		{
			return;
		}

		$iexKey = $key . '|iex|' . $cacheKey;
		$itemExist = self::$engine->get($iexKey);
		if ($itemExist == $cacheKey)
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
			self::$listKeys[$cacheKey] = 1;
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
		if (is_array($list) && !empty($list))
		{
			foreach ($list as $iKey => $value)
			{
				if ($prefix == '')
				{
					$this->del($iKey);
				}
				else
				{
					$this->del($prefix . $iKey);
				}

				$cacheKey = sha1($key . '|' . $iKey);
				$iexKey = $key . '|iex|' . $cacheKey;
				$this->del($iexKey);
			}
		}
	}

	public function delFromSet($key, $member)
	{
		$list = self::$engine->get($key);

		if (is_array($list) && !empty($list))
		{
			$rewrite = false;
			if (is_array($member))
			{
				foreach ($member as $keyID)
				{
					if (array_key_exists($keyID, $list))
					{
						$rewrite = true;
						$cacheKey = sha1($key . '|' . $keyID);
						unset($list[$keyID]);
						unset(self::$listKeys[$cacheKey]);

						$iexKey = $key . '|iex|' . $cacheKey;
						$this->del($iexKey);
					}
				}
			}
			elseif (array_key_exists($member, $list))
			{
				$rewrite = true;
				$cacheKey = sha1($key . '|' . $member);
				unset(self::$listKeys[$cacheKey]);
				unset($list[$member]);

				$iexKey = $key . '|iex|' . $cacheKey;
				$this->del($iexKey);
			}

			if ($rewrite)
			{
				if (empty($list))
				{
					$this->del($key);
				}
				else
				{
					$this->set($key, 0, $list);
				}
			}
		}
	}
}