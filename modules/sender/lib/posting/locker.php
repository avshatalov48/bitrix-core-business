<?php

namespace Bitrix\Sender\Posting;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB;

class Locker
{
	/**
	 * Lock
	 *
	 * @param string $key
	 * @param int $id
	 *
	 * @return bool
	 */
	public static function lock(string $key, int $id)
	{
		$lockName = self::getLockName($key, $id);

		return Application::getInstance()->getConnection()->lock($lockName);
	}

	/**
	 * Unlock
	 *
	 * @param string $key
	 * @param int $id
	 *
	 * @return bool
	 */
	public static function unlock(string $key, int $id)
	{
		$lockName = self::getLockName($key, $id);

		return Application::getInstance()->getConnection()->unlock($lockName);
	}

	/**
	 * Get lock name
	 *
	 * @param string $key
	 * @param int $id
	 *
	 * @return string
	 */
	private static function getLockName(string $key, int $id): string
	{
		return "{$key}_{$id}";
	}
}
