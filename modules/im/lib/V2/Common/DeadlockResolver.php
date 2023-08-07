<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;

trait DeadlockResolver
{
	protected static int $defaultMaxTryCount = 3;

	protected static function executeDeadlockSafeQuery(string $sql, ?int $maxRetryCount = null): void
	{
		$maxRetryCount ??= self::$defaultMaxTryCount;

		try
		{
			Application::getConnection()->queryExecute($sql);
		}
		catch (SqlQueryException $e)
		{
			if (self::isDeadlock($e))
			{
				Application::getInstance()->addBackgroundJob(static fn () => self::retryQuery($sql, $maxRetryCount));
			}
		}
	}

	protected static function retryQuery(string $sql, ?int $maxRetryCount = null): void
	{
		$maxRetryCount ??= self::$defaultMaxTryCount;
		$retryCount = 1;
		$isSuccess = false;

		while (!$isSuccess)
		{
			$isSuccess = true;
			try
			{
				Application::getConnection()->queryExecute($sql);
			}
			catch (SqlQueryException $e)
			{
				if (self::isDeadlock($e))
				{
					$isSuccess = false;
					if ($retryCount >= $maxRetryCount)
					{
						throw $e;
					}
					$retryCount++;
					usleep(500000); // 0.5 sec
				}
			}
		}
	}

	protected static function isDeadlock(SqlQueryException $exception): bool
	{
		return mb_stripos($exception->getMessage(), '1213') !== false;
	}
}