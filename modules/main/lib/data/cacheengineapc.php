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
		self::$isConnected = function_exists('apcu_fetch');
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

	public function addToSet($key, $value)
	{
		$cacheKey = sha1($key . '|' . $value);
		if (array_key_exists($cacheKey, self::$listKeys))
		{
			return;
		}

		$iexKey = $key . '|iex|' . $cacheKey;
		$itemExist = apcu_fetch($iexKey);
		if ($itemExist == $cacheKey)
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
			self::$listKeys[$cacheKey] = 1;
		}

		$this->set($iexKey, 2591000, $cacheKey);
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
		if (is_array($list) && !empty($list))
		{
			foreach ($list as $iKey => $value)
			{
				if ($prefix == '')
				{
					apcu_delete($iKey);

				}
				else
				{
					apcu_delete($prefix . $iKey);
				}

				$cacheKey = sha1($key . '|' . $iKey);
				$iexKey = $key . '|iex|' . $cacheKey;
				$this->del($iexKey);
			}
		}
	}

	public function delFromSet($key, $member)
	{
		$list = apcu_fetch($key);

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