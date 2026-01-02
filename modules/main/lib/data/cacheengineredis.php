<?php

namespace Bitrix\Main\Data;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Data\LocalStorage\Storage;

class CacheEngineRedis extends Cache\KeyValueEngine implements Storage\CacheEngineInterface
{
	public function getConnectionName() : string
	{
		return 'cache.redis';
	}

	public static function getConnectionClass()
	{
		return RedisConnection::class;
	}

	protected function configure($options = []): array
	{
		$config = parent::configure($options);

		$cacheConfig = Configuration::getValue('cache');

		if (isset($cacheConfig['serializer']))
		{
			$config['serializer'] = (int)$cacheConfig['serializer'];
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
			$cacheConfig['timeout'] = (float)$cacheConfig['timeout'];
			if ($cacheConfig['timeout'] > 0)
			{
				$config['timeout'] = $cacheConfig['timeout'];
			}
		}

		if (isset($cacheConfig['read_timeout']))
		{
			$cacheConfig['read_timeout'] = (float)$cacheConfig['read_timeout'];
			if ($cacheConfig['read_timeout'] > 0)
			{
				$config['read_timeout'] = $cacheConfig['read_timeout'];
			}
		}

		return $config;
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

	public function addCleanPath(array $data): void
	{
		self::$engine->lPush($this->sid . '/cacheCleanPath', $data);
	}

	public function delayedDelete(): void
	{
		$delta = 10;
		$deleted = 0;
		$etime = time() + 5;
		$needClean = self::$engine->get($this->sid . '|needClean');

		if ($needClean !== 'Y')
		{
			$this->unlock($this->sid . '|cacheClean');
			return;
		}

		$count = (int) self::$engine->get($this->sid . '|delCount');
		if ($count < 1)
		{
			$count = 1;
		}

		$step = $count + $delta;
		for ($i = 0; $i < $step; $i++)
		{
			$finished = true;
			$paths = self::$engine->rPop($this->sid . '/cacheCleanPath');
			if ($paths)
			{
				$partitionListKey = $paths['PREFIX'] . '|' . static::BX_INIT_DIR_LIST;
				$partitionKeys = $this->getSet($partitionListKey);
				foreach ($partitionKeys as $partition)
				{
					if (time() > $etime)
					{
						$finished = false;
						break;
					}

					$this->deleteBySet($partitionListKey . '|' . $partition);
				}

				if ($finished)
				{
					$deleted++;
				}
				elseif (time() > $etime)
				{
					self::$engine->lPush($this->sid . '/cacheCleanPath', $paths);
					break;
				}
			}
			else
			{
				break;
			}
		}

		if ($deleted > $count)
		{
			self::$engine->setex($this->sid . '|delCount', 604800, $deleted);
		}
		elseif ($deleted < $count && $count > 1)
		{
			self::$engine->setex($this->sid . '|delCount', 604800, --$count);
		}

		if ($deleted === 0)
		{
			self::$engine->setex($this->sid . '|needClean', 3600, 'N');
		}

		$this->unlock($this->sid . '|cacheClean');
	}
}
