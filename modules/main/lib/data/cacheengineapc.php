<?php
namespace Bitrix\Main\Data;

class CacheEngineApc extends CacheEngine
{
	public function getConnectionName() : string
	{
		return '';
	}

	public static function getConnectionClass()
	{
		return CacheEngineApc::class;
	}

	protected function connect($config)
	{
		self::$isConnected = function_exists('apcu_fetch') && 'cli' !== \PHP_SAPI;
	}

	public function set($key, $ttl, $value)
	{
		return apcu_store($key, $value, $ttl);
	}

	public function get($key)
	{
		return apcu_fetch($key);
	}

	public function del($key)
	{
		apcu_delete($key);
	}

	public function setNotExists($key, $ttl, $value)
	{
		$ttl = (int) $ttl;
		return apcu_add($key, $value, $ttl);
	}

	public function checkInSet($key, $value) : bool
	{
		$cacheKey = sha1($key . '|' . $value);
		$iexKey = $key . '|iex|' . $cacheKey;
		$itemExist = apcu_fetch($iexKey);

		if ($itemExist === $cacheKey)
		{
			return true;
		}

		$list = apcu_fetch($key);

		if (!is_array($list))
		{
			$list = [];
		}

		if (array_key_exists($value, $list))
		{
			apcu_store($iexKey, $cacheKey, 2591000);
			return true;
		}

		return false;
	}

	public function addToSet($key, $value)
	{
		$cacheKey = sha1($key . '|' . $value);
		$iexKey = $key . '|iex|' . $cacheKey;
		$itemExist = apcu_fetch($iexKey);
		if ($itemExist === $cacheKey)
		{
			return;
		}

		$list = apcu_fetch($key);

		if (!is_array($list))
		{
			$list = [];
		}

		if (!array_key_exists($value, $list))
		{
			$list[$value] = 1;
			apcu_store($key, $list, 0);
		}
		apcu_store($iexKey, $cacheKey, 2591000);
	}

	public function getSet($key) : array
	{
		$list = apcu_fetch($key);
		if (!is_array($list) || empty($list))
		{
			return [];
		}

		return array_keys($list);
	}

	public function deleteBySet($key, $prefix = '')
	{
		$list = apcu_fetch($key);
		apcu_delete($key);

		if (is_array($list) && !empty($list))
		{
			$list = array_keys($list);
			foreach ($list as $iKey)
			{
				$delKey = $prefix . $iKey;
				apcu_delete($key . '|iex|' . sha1($key . '|' . $iKey));

				if ($this->useLock)
				{
					if ($cachedData = apcu_fetch($delKey))
					{
						apcu_store($delKey . '|old', $cachedData, $this->ttlOld);
					}
				}

				apcu_delete($delKey);
			}
		}
		unset($list);
	}

	public function delFromSet($key, $member)
	{
		$list = apcu_fetch($key);

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
						$cacheKey = sha1($key . '|' . $keyID);
						unset($list[$keyID]);

						apcu_delete($tmpKey . $cacheKey);
					}
				}
			}
			elseif (array_key_exists($member, $list))
			{
				$rewrite = true;
				$cacheKey = sha1($key . '|' . $member);
				unset($list[$member]);

				apcu_delete($tmpKey . $cacheKey);
			}

			if ($rewrite)
			{
				if (empty($list))
				{
					apcu_delete($key);
				}
				else
				{
					apcu_store($key, $list, 0);
				}
			}
		}
	}
}