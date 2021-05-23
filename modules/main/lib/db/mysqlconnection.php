<?php
namespace Bitrix\Main\DB;

use Bitrix\Main\Diag;

class MysqlConnection extends MysqlCommonConnection
{
	/**********************************************************
	 * SqlHelper
	 **********************************************************/

	/**
	 * @inheritDoc
	 */
	protected function createSqlHelper()
	{
		return new MysqlSqlHelper($this);
	}

	/***********************************************************
	 * Connection and disconnection
	 ***********************************************************/

	/**
	 * Establishes a connection to the database.
	 * Includes php_interface/after_connect_d7.php on success.
	 * Throws exception on failure.
	 *
	 * @return void
	 * @throws \Bitrix\Main\DB\ConnectionException
	 */
	protected function connectInternal()
	{
		if($this->isConnected)
		{
			return;
		}

		if(($this->options & self::PERSISTENT) != 0)
		{
			$connection = mysql_pconnect($this->host, $this->login, $this->password);
		}
		else
		{
			$connection = mysql_connect($this->host, $this->login, $this->password, true);
		}

		if(!$connection)
		{
			throw new ConnectionException('Mysql connect error ['.$this->host.', '.gethostbyname($this->host).']', mysql_error());
		}

		if($this->database !== null)
		{
			if(!mysql_select_db($this->database, $connection))
			{
				throw new ConnectionException('Mysql select db error ['.$this->database.']', mysql_error($connection));
			}
		}

		$this->resource = $connection;
		$this->isConnected = true;

		$this->afterConnected();
	}

	/**
	 * Disconnects from the database.
	 * Does nothing if there was no connection established.
	 *
	 * @return void
	 */
	public function disconnectInternal()
	{
		if (!$this->isConnected)
			return;

		mysql_close($this->resource);

		$this->isConnected = false;
	}

	/*********************************************************
	 * Query
	 *********************************************************/

	/**
	 * @inheritDoc
	 */
	protected function queryInternal($sql, array $binds = null, \Bitrix\Main\Diag\SqlTrackerQuery $trackerQuery = null)
	{
		$this->connectInternal();

		if ($trackerQuery != null)
			$trackerQuery->startQuery($sql, $binds);

		$result = mysql_query($sql, $this->resource);

		if ($trackerQuery != null)
			$trackerQuery->finishQuery();

		$this->lastQueryResult = $result;

		if (!$result)
			throw new SqlQueryException('Mysql query error', mysql_error($this->resource), $sql);

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function createResult($result, \Bitrix\Main\Diag\SqlTrackerQuery $trackerQuery = null)
	{
		return new MysqlResult($result, $this, $trackerQuery);
	}

	/**
	 * @inheritDoc
	 */
	public function getInsertedId()
	{
		$this->connectInternal();
		return mysql_insert_id($this->resource);
	}

	/**
	 * @inheritDoc
	 */
	public function getAffectedRowsCount()
	{
		return mysql_affected_rows($this->getResource());
	}

	/*********************************************************
	 * Type, version, cache, etc.
	 *********************************************************/

	/**
	 * @inheritDoc
	 */
	public function getVersion()
	{
		if ($this->version == null)
		{
			$version = $this->queryScalar("SELECT VERSION()");
			if ($version != null)
			{
				$version = trim($version);
				preg_match("#[0-9]+\\.[0-9]+\\.[0-9]+#", $version, $ar);
				$this->version = $ar[0];
			}
		}

		return array($this->version, null);
	}

	/**
	 * @inheritDoc
	 */
	protected function getErrorMessage()
	{
		return sprintf("[%s] %s", mysql_errno($this->resource), mysql_error($this->resource));
	}

	/**
	 * Selects the default database for database queries.
	 *
	 * @param string $database Database name.
	 * @return bool
	 */
	public function selectDatabase($database)
	{
		return mysql_select_db($database, $this->resource);
	}
}
