<?php

class CSecurityDB
{
	/** @var string Connection name used for SQL queries */
	const CONNECTION_NAME = 'user_session';
	/** @var \Bitrix\Main\Db\Connection  */
	protected static $connection = null;

	public static function Init($bDoConnect = false)
	{
		if (is_object(self::$connection))
		{
			return true;
		}

		$pool = \Bitrix\Main\Application::getInstance()->getConnectionPool();
		$isConnectionExists = $pool->getConnection(static::CONNECTION_NAME) !== null;
		if (!$isConnectionExists)
		{
			$pool->cloneConnection(
				$pool::DEFAULT_CONNECTION_NAME,
				static::CONNECTION_NAME
			);
		}

		if ($bDoConnect)
		{
			self::$connection = $pool->getConnection(static::CONNECTION_NAME);
		}
		else
		{
			return true;
		}

		//In case of error just skip it over
		if (!is_object(self::$connection))
		{
			return false;
		}

		if (
			defined("BX_SECURITY_SQL_LOG_BIN")
			&& (
				BX_SECURITY_SQL_LOG_BIN === false
				|| BX_SECURITY_SQL_LOG_BIN === "N"
			)
		)
		{
			CSecurityDB::Query("SET sql_log_bin = 0", "Module: security; Class: CSecurityDB; Function: Init; File: ".__FILE__."; Line: ".__LINE__);
		}

		$rs = CSecurityDB::Query("SHOW TABLES LIKE 'b_sec_session'", "Module: security; Class: CSecurityDB; Function: Init; File: ".__FILE__."; Line: ".__LINE__);
		if (!is_object($rs))
		{
			return false;
		}

		$ar = CSecurityDB::Fetch($rs);
		if ($ar)
		{
			return true;
		}

		if (defined("MYSQL_TABLE_TYPE") && MYSQL_TABLE_TYPE <> '')
		{
			$rs = CSecurityDB::Query("SET storage_engine = '".MYSQL_TABLE_TYPE."'", "Module: security; Class: CSecurityDB; Function: Init; File: ".__FILE__."; Line: ".__LINE__);
			if (!is_object($rs))
			{
				return false;
			}
		}

		$rs = CSecurityDB::Query("CREATE TABLE b_sec_session
			(
				SESSION_ID VARCHAR(250) NOT NULL,
				TIMESTAMP_X TIMESTAMP NOT NULL,
				SESSION_DATA LONGTEXT,
				PRIMARY KEY(SESSION_ID),
				KEY ix_b_sec_session_time (TIMESTAMP_X)
			)
		", "Module: security; Class: CSecurityDB; Function: Init; File: ".__FILE__."; Line: ".__LINE__);

		return is_object($rs);
	}

	public static function Disconnect()
	{
		if (is_object(self::$connection))
		{
			self::$connection->disconnect();
			self::$connection = null;
		}
	}

	public static function CurrentTimeFunction()
	{
		return "now()";
	}

	public static function SecondsAgo($sec)
	{
		return "DATE_ADD(now(), INTERVAL - ".intval($sec)." SECOND)";
	}

	public static function Query($strSql, $error_position)
	{
		if (!is_object(self::$connection))
		{
			CSecurityDB::Init(true);
		}

		if (is_object(self::$connection))
		{
			$strSql = preg_replace("/^\\s*SELECT\\s+(?!GET_LOCK|RELEASE_LOCK)/i", "SELECT SQL_NO_CACHE ", $strSql);
			try
			{
				$result = self::$connection->query($strSql);
				return $result;
			}
			catch (\Bitrix\Main\Db\SqlQueryException $e)
			{
				AddMessage2Log($error_position." MySql Query Error: ".$strSql." [".$e."]", "security");
			}
		}

		return false;
	}

	public static function QueryBind($strSql, $arBinds, $error_position)
	{
		foreach ($arBinds as $key => $value)
			$strSql = str_replace(":".$key, "'".$value."'", $strSql);
		return CSecurityDB::Query($strSql, $error_position);
	}

	/**
	 * @param \Bitrix\Main\Db\Result $result
	 * @return bool
	 */
	public static function Fetch($result)
	{
		if ($result)
			return $result->fetch();
		else
			return false;
	}

	public static function Lock($id, $timeout = 60)
	{
		static $lock_id = "";

		if ($id === false)
		{
			if ($lock_id)
			{
				$rsLock = CSecurityDB::Query("DO RELEASE_LOCK('".$lock_id."')", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
			}
			else
			{
				$rsLock = false;
			}
		}
		else
		{
			$rsLock = CSecurityDB::Query("SELECT GET_LOCK('".md5($id)."', ".intval($timeout).") as L", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
			if ($rsLock)
			{
				$arLock = CSecurityDB::Fetch($rsLock);
				if ($arLock["L"] == "0")
					return false;
				else
					$lock_id = md5($id);
			}
		}
		return is_object($rsLock);
	}

	public static function LockTable($table_name, $lock_id)
	{
		$rsLock = CSecurityDB::Query("SELECT GET_LOCK('".md5($lock_id)."', 0) as L", "Module: security; Class: CSecurityDB; Function: LockTable; File: ".__FILE__."; Line: ".__LINE__);
		if ($rsLock)
		{
			$arLock = CSecurityDB::Fetch($rsLock);
			if ($arLock["L"] == "0")
				return false;
			else
				return array("lock_id" => $lock_id);
		}
		else
		{
			return false;
		}
	}

	public static function UnlockTable($table_lock)
	{
		if (is_array($table_lock))
		{
			CSecurityDB::Query("SELECT RELEASE_LOCK('".$table_lock["lock_id"]."')", "Module: security; Class: CSecurityDB; Function: UnlockTable; File: ".__FILE__."; Line: ".__LINE__);
		}
	}
}
