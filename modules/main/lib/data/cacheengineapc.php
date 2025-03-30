<?php
namespace Bitrix\Main\Data;

class CacheEngineApc extends CacheEngine
{
	public function getConnectionName(): string
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
		$list = apcu_fetch($key);

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
			array_walk($list, function ($value, $key) {
				apcu_delete($key);
			});
			unset($list);
		}
	}

	public function delFromSet($key, $member)
	{
		$list = apcu_fetch($key);

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