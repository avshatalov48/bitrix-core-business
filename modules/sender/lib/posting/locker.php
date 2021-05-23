<?php

namespace Bitrix\Sender\Posting;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB;

class Locker
{
	/**
	 * @param string $key
	 * @param int $id
	 *
	 * @return bool
	 * @throws DB\SqlQueryException
	 */
	public static function lock(string $key, int $id)
	{
		$uniqueSalt = self::getLockUniqueSalt();
		$connection = Application::getInstance()->getConnection();
		if ($connection instanceof DB\MysqlCommonConnection)
		{
			$lockDb = $connection->query(
				sprintf(
					"SELECT GET_LOCK('%s_%s_%d', 0) as L",
					$uniqueSalt,
					$key,
					$id
				),
				false,
				"File: ".__FILE__."<br>Line: ".__LINE__
			);
			$lock   = $lockDb->fetch();
			if ($lock["L"] == "1")
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	public static function unlock(string $key, int $id)
	{
		$id = intval($id);

		$connection = Application::getInstance()->getConnection();
		if ($connection instanceof DB\MysqlCommonConnection)
		{
			$uniqueSalt = self::getLockUniqueSalt(false);
			if (!$uniqueSalt)
			{
				return false;
			}

			$lockDb = $connection->query(
				sprintf(
					"SELECT RELEASE_LOCK('%s_%s_%d') as L",
					$uniqueSalt,
					$key,
					$id
				)
			);
			$lock   = $lockDb->fetch();
			if ($lock["L"] == "0")
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return false;
	}


	protected static function getLockUniqueSalt($generate = true)
	{
		$uniqueSalt = Option::get("main", "server_uniq_id", "");
		if ($uniqueSalt == '' && $generate)
		{
			$uniqueSalt = hash('sha256', uniqid(rand(), true));
			Option::set("main", "server_uniq_id", $uniqueSalt);
		}

		return $uniqueSalt;
	}
}