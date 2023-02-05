<?php

namespace Bitrix\Main\DB;

use Bitrix\Main\Diag;

/**
 * Class MysqliConnection
 * @method \mysqli getResource()
 * @property \mysqli $resource
 */
class MysqliConnection extends MysqlCommonConnection
{
	/**********************************************************
	 * SqlHelper
	 **********************************************************/

	/**
	 * @inheritDoc
	 */
	protected function createSqlHelper()
	{
		return new MysqliSqlHelper($this);
	}

	protected function configureReportLevel(): void
	{
		// back to default before PHP 8.1
		mysqli_report(MYSQLI_REPORT_OFF);
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
	 * @throws ConnectionException
	 */
	protected function connectInternal()
	{
		if ($this->isConnected)
		{
			return;
		}

		$host = $this->host;
		$port = 0;
		if (($pos = strpos($host, ":")) !== false)
		{
			$port = intval(substr($host, $pos + 1));
			$host = substr($host, 0, $pos);
		}
		if (($this->options & self::PERSISTENT) != 0)
		{
			$host = "p:".$host;
		}

		$connection = \mysqli_init();
		if (!$connection)
		{
			throw new ConnectionException('Mysql init failed');
		}

		if (!empty($this->initCommand))
		{
			if (!$connection->options(MYSQLI_INIT_COMMAND, $this->initCommand))
			{
				throw new ConnectionException('Setting mysql init command failed');
			}
		}

		if ($port > 0)
		{
			$success = $connection->real_connect($host, $this->login, $this->password, $this->database, $port);
		}
		else
		{
			$success = $connection->real_connect($host, $this->login, $this->password, $this->database);
		}

		if (!$success)
		{
			throw new ConnectionException(
				'Mysql connect error ['.$this->host.']',
				sprintf('(%s) %s', $connection->connect_errno, $connection->connect_error)
			);
		}

		$this->resource = $connection;
		$this->isConnected = true;

		// nosql memcached driver
		if (isset($this->configuration['memcache']))
		{
			if (function_exists('mysqlnd_memcache_set'))
			{
				$memcached = \Bitrix\Main\Application::getInstance()->getConnectionPool()->getConnection($this->configuration['memcache']);
				mysqlnd_memcache_set($this->resource, $memcached->getResource());
			}
		}

		$this->afterConnected();
	}

	/**
	 * Disconnects from the database.
	 * Does nothing if there was no connection established.
	 *
	 * @return void
	 */
	protected function disconnectInternal()
	{
		if ($this->isConnected)
		{
			$this->isConnected = false;
			$this->resource->close();
		}
	}

	/*********************************************************
	 * Query
	 *********************************************************/

	/**
	 * @inheritDoc
	 */
	protected function queryInternal($sql, array $binds = null, Diag\SqlTrackerQuery $trackerQuery = null)
	{
		$this->configureReportLevel();
		$this->connectInternal();

		if ($trackerQuery != null)
		{
			$trackerQuery->startQuery($sql, $binds);
		}

		$result = $this->resource->query($sql, MYSQLI_STORE_RESULT);

		if ($trackerQuery != null)
		{
			$trackerQuery->finishQuery();
		}

		$this->lastQueryResult = $result;

		if (!$result)
		{
			throw new SqlQueryException('Mysql query error', $this->getErrorMessage(), $sql);
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function createResult($result, Diag\SqlTrackerQuery $trackerQuery = null)
	{
		return new MysqliResult($result, $this, $trackerQuery);
	}

	/**
	 * @inheritDoc
	 */
	public function getInsertedId()
	{
		return $this->getResource()->insert_id;
	}

	/**
	 * @inheritDoc
	 */
	public function getAffectedRowsCount()
	{
		return $this->getResource()->affected_rows;
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
			$version = trim($this->getResource()->server_info);

			preg_match("#[0-9]+\\.[0-9]+\\.[0-9]+#", $version, $ar);
			$this->version = $ar[0];
		}

		return array($this->version, null);
	}

	/**
	 * @inheritDoc
	 */
	protected function getErrorMessage()
	{
		return sprintf("(%s) %s", $this->resource->errno, $this->resource->error);
	}

	/**
	 * Selects the default database for database queries.
	 *
	 * @param string $database Database name.
	 * @return bool
	 */
	public function selectDatabase($database)
	{
		return $this->resource->select_db($database);
	}
}
