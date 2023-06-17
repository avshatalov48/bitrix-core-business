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
		$list = apcu_fetch($key);
		if (!is_array($list))
		{
			$list = [];
		}
		$list[$value] = 1;

		apcu_store($key, $list, 0);
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

	public function delFromSet($key, $member)
	{
		$list = apcu_fetch($key);
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

			apcu_store($key, $list, 0);
		}
	}
}