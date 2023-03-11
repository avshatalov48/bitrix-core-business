<?php

namespace Bitrix\Rest\Engine\Access;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Rest\OAuth\Client;
use Bitrix\Main\Application;
use Bitrix\Rest\APAuth;
use Bitrix\Rest\OAuth;
use Bitrix\Bitrix24\Feature;

/**
 * Class LoadLimiter
 * @package \Bitrix\Rest\Engine\Access;
 */
class LoadLimiter
{
	private const MODULE_ID = 'rest';
	private const BITRIX24_CONNECTOR_NAME = 'cache.redis';
	private const CACHE_EXPIRE_TIME_PREFIX = 'expire';
	private const DEFAULT_DOMAIN = 'default';
	private static int $version = 2;
	private static int $bucketSize = 60; //sec
	private static int $bucketCount = 10;
	private static int $limitTime = 420; // total hits duration per 10 min
	private static float $minimalFixTime = 0.1;
	private static string $domain = '';
	private static ?bool $isActive = null;
	private static bool $isFinaliseInit = false;
	private static array $ignoreMethod = [
		Client::METHOD_BATCH,
	];
	private static array $limitedEntityTypes = [
		APAuth\Auth::AUTH_TYPE,
		OAuth\Auth::AUTH_TYPE,
	];
	private static int $numBucket = 0;
	private static array $timeRegistered = [];

	/**
	 * Returns loads time limit per 10 min.
	 *
	 * @return int
	 */
	public static function getLimitTime(): int
	{
		if (Loader::includeModule('bitrix24'))
		{
			$result = static::$limitTime;
			$seconds = (int)Feature::getVariable('rest_load_limiter_seconds');
			if ($seconds > 0)
			{
				$result = $seconds;
			}
		}
		else
		{
			$result = (int)Option::get(static::MODULE_ID, 'load_limiter_second_limit', static::$limitTime);
		}

		return $result;
	}

	/**
	 * Checks limiter status.
	 *
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function isActive(): bool
	{
		if (is_null(static::$isActive))
		{
			if (Loader::includeModule('bitrix24'))
			{
				static::$isActive = true;
			}
			else
			{
				static::$isActive = Option::get(static::MODULE_ID, 'load_limiter_active', 'N') === 'Y';
			}
		}

		return static::$isActive;
	}

	private static function getDomain(): string
	{
		if (static::$domain === '')
		{
			if (Loader::includeModule('bitrix24') && defined('BX24_HOST_NAME'))
			{
				static::$domain = BX24_HOST_NAME;
			}
			else
			{
				static::$domain = static::DEFAULT_DOMAIN;
			}
		}

		return static::$domain;
	}

	/**
	 * Register starting doing method.
	 * @param $entityType
	 * @param $entity
	 * @param $method
	 */
	public static function registerStarting($entityType, $entity, $method): void
	{
		if (
			static::isActive()
			|| in_array($entityType, static::$limitedEntityTypes, true)
			|| !in_array($method, static::$ignoreMethod, true)
		)
		{
			$key = static::getKey($entityType, $entity, $method);
			if (!(static::$timeRegistered[$key] ?? null))
			{
				static::$timeRegistered[$key] = [
					'entityType' => $entityType,
					'entity' => $entity,
					'method' => $method,
					'timeStart' => [],
					'timeFinish' => [],
				];
			}

			static::$timeRegistered[$key]['timeStart'][] = microtime(true);
		}
	}

	/**
	 * Register ending doing method.
	 *
	 * @param $entityType
	 * @param $entity
	 * @param $method
	 */
	public static function registerEnding($entityType, $entity, $method): void
	{
		if (
			static::isActive()
			&& in_array($entityType, static::$limitedEntityTypes, true)
			&& !in_array($method, static::$ignoreMethod, true)
		)
		{
			$key = static::getKey($entityType, $entity, $method);
			if (static::$timeRegistered[$key])
			{
				static::$timeRegistered[$key]['timeFinish'][] = microtime(true);
			}

			if (!static::$isFinaliseInit)
			{
				static::$isFinaliseInit = true;
				Application::getInstance()->addBackgroundJob([__CLASS__, 'finalize']);
			}
		}
	}

	/**
	 * Checks block by limiter.
	 * @param $entityType
	 * @param $entity
	 * @param $method
	 * @return bool ( true - block, false - don't block)
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function is($entityType, $entity, $method): bool
	{
		if (
			!static::isActive()
			|| !in_array($entityType, static::$limitedEntityTypes, true)
			|| in_array($method, static::$ignoreMethod, true)
		)
		{
			return false;
		}

		$totalTime = static::getRestTime($entityType, $entity, $method);
		if ($totalTime > static::getLimitTime())
		{
			if (Loader::includeModule('bitrix24') && function_exists('saveRestStat'))
			{
				saveRestStat(static::getDomain(), $entityType, $entity, $method, $totalTime);
			}

			return true;
		}

		return false;
	}

	private static function getKey($entityType, $entity, $method, $bucketNum = null): string
	{
		return
			static::getDomain() . '|v' . static::$version . '|'
			. sha1($entityType . '|' .$entity . '|' . $method)
			. '|' . $bucketNum;
	}

	/**
	 * Returns time to reset limits.
	 *
	 * @param $entityType
	 * @param $entity
	 * @param $method
	 *
	 * @return int|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getResetTime($entityType, $entity, $method): ?int
	{
		$result = null;

		if (static::isActive())
		{
			$resource = static::getConnectionResource();
			if ($resource)
			{
				$numBucket = static::getNumBucket();
				$key =  static::getKey($entityType, $entity, $method);
				$keyExpire =  static::CACHE_EXPIRE_TIME_PREFIX . '|' . $key;
				for ($i = static::$bucketCount - 1; $i >= 0; $i--)
				{
					$time = (float)$resource->get($key . ($numBucket - $i));
					if ($time > 0 && $resource->exists($keyExpire . ($numBucket - $i)))
					{
						$result = (int)$resource->get($keyExpire . ($numBucket - $i));
						break;
					}
				}
				if (!$result)
				{
					if (!empty(static::$timeRegistered))
					{
						$item = reset(static::$timeRegistered);
						if (!empty($item['timeStart']))
						{
							$firstTimeStart = reset($item['timeStart']);
							$result = $firstTimeStart + static::$bucketCount * static::$bucketSize;
						}
					}
				}
			}
		}

		return $result;
	}

	protected static function getNumBucket()
	{
		if (!static::$numBucket)
		{
			static::$numBucket = intdiv(time(), static::$bucketSize);
		}

		return static::$numBucket;
	}

	/**
	 * Returns methods working time.
	 * @param $entityType
	 * @param $entity
	 * @param $method
	 * @return float
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getRestTime($entityType, $entity, $method): float
	{
		$result = [];
		if (static::isActive())
		{
			$numBucket = static::getNumBucket();

			$key = static::getKey($entityType, $entity, $method);
			$resource = static::getConnectionResource();
			if ($resource)
			{
				for ($i = 0; $i < static::$bucketCount; $i++)
				{
					$result[] = (float)$resource->get($key . ($numBucket - $i));
				}
			}
			if (!empty(static::$timeRegistered[$key]['timeStart']))
			{
				foreach (static::$timeRegistered[$key]['timeStart'] as $k => $timeStart)
				{
					if (static::$timeRegistered[$key]['timeFinish'][$k] ?? null)
					{
						$time = static::$timeRegistered[$key]['timeFinish'][$k] - $timeStart;
						if ($time > static::$minimalFixTime)
						{
							$result[] = $time;
						}
					}
				}
			}
		}

		return array_sum($result);
	}

	/**
	 * Saves working time by Background Job
	 *
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function finalize(): void
	{
		if (static::$timeRegistered && static::isActive())
		{
			$resource = static::getConnectionResource();
			if ($resource)
			{
				foreach (static::$timeRegistered as $item)
				{
					$time = 0;
					$firstTime = reset($item['timeStart']);
					foreach ($item['timeStart'] as $k => $timeStart)
					{
						if ($item['timeFinish'][$k])
						{
							$time += $item['timeFinish'][$k] - $timeStart;
						}
					}

					if ($time > static::$minimalFixTime)
					{
						$key = static::getKey($item['entityType'], $item['entity'], $item['method'], static::getNumBucket());
						if ($resource->exists($key))
						{
							$resource->incrByFloat($key, $time);
						}
						else
						{
							$expireAt = $firstTime + static::$bucketCount * static::$bucketSize;
							$resource->incrByFloat($key, $time);
							$resource->expire($key, $expireAt);

							$keyExpire = static::CACHE_EXPIRE_TIME_PREFIX . '|' . $key;
							$resource->incrByFloat($keyExpire, $expireAt);
							$resource->expire($keyExpire, $expireAt);
						}
					}
				}
				static::$timeRegistered = [];
			}
		}
	}

	private static function getConnectionResource(): ?object
	{
		$result = null;
		$connectionName = static::getConnectionName();
		if ($connectionName)
		{
			$connection = Application::getInstance()
				->getConnectionPool()
				->getConnection($connectionName);

			if ($connection && $connection->isConnected() === true)
			{
				$result = $connection->getResource();
			}
		}

		return $result;
	}

	private static function getConnectionName(): string
	{
		if (Loader::includeModule('bitrix24'))
		{
			return static::BITRIX24_CONNECTOR_NAME;
		}

		return '';
	}
}