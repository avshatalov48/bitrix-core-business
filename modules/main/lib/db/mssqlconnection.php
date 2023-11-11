<?php

namespace Bitrix\Main\DB;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Fields\ScalarField;

/**
 * Class MssqlConnection
 *
 * Class for MS SQL database connections.
 * @package Bitrix\Main\DB
 */
class MssqlConnection extends Connection
{
	/**********************************************************
	 * SqlHelper
	 **********************************************************/

	protected function createSqlHelper()
	{
		return new MssqlSqlHelper($this);
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
			return;

		$connectionInfo = array(
			"UID" => $this->login,
			"PWD" => $this->password,
			"Database" => $this->database,
			"ReturnDatesAsStrings" => true,
			/*"CharacterSet" => "utf-8",*/
		);

		if (($this->options & self::PERSISTENT) != 0)
			$connectionInfo["ConnectionPooling"] = true;
		else
			$connectionInfo["ConnectionPooling"] = false;

		$connection = sqlsrv_connect($this->host, $connectionInfo);

		if (!$connection)
			throw new ConnectionException('MS Sql connect error', $this->getErrorMessage());

		$this->resource = $connection;
		$this->isConnected = true;

		// hide cautions
		sqlsrv_configure("WarningsReturnAsErrors", 0);

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
		if (!$this->isConnected)
			return;

		$this->isConnected = false;
		sqlsrv_close($this->resource);
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

		$trackerQuery?->startQuery($sql, $binds);

		$result = sqlsrv_query($this->resource, $sql, array(), array("Scrollable" => 'forward'));

		$trackerQuery?->finishQuery();

		$this->lastQueryResult = $result;

		if (!$result)
			throw new SqlQueryException('MS Sql query error', $this->getErrorMessage(), $sql);

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function createResult($result, \Bitrix\Main\Diag\SqlTrackerQuery $trackerQuery = null)
	{
		return new MssqlResult($result, $this, $trackerQuery);
	}

	/**
	 * @inheritDoc
	 */
	public function getInsertedId()
	{
		return $this->queryScalar("SELECT @@IDENTITY as ID");
	}

	/**
	 * @inheritDoc
	 */
	public function getAffectedRowsCount()
	{
		return sqlsrv_rows_affected($this->lastQueryResult);
	}

	/**
	 * @inheritDoc
	 */
	public function isTableExists($tableName)
	{
		$tableName = preg_replace("/[^A-Za-z0-9%_]+/i", "", $tableName);
		$tableName = trim($tableName);

		if ($tableName == '')
			return false;

		$result = $this->queryScalar(
			"SELECT COUNT(TABLE_NAME) ".
			"FROM INFORMATION_SCHEMA.TABLES ".
			"WHERE TABLE_NAME LIKE '".$this->getSqlHelper()->forSql($tableName)."'"
		);
		return ($result > 0);
	}

	/**
	 * @inheritDoc
	 */
	public function isIndexExists($tableName, array $columns)
	{
		return $this->getIndexName($tableName, $columns) !== null;
	}

	/**
	 * @inheritDoc
	 */
	public function getIndexName($tableName, array $columns, $strict = false)
	{
		if (empty($columns))
		{
			return null;
		}

		//2005
		//$rs = $this->query("SELECT index_id, COL_NAME(object_id, column_id) AS column_name, key_ordinal FROM SYS.INDEX_COLUMNS WHERE object_id=OBJECT_ID('".$this->forSql($tableName)."')", true);

		//2000
		$rs = $this->query(
			"SELECT s.indid as index_id, s.keyno as key_ordinal, c.name column_name, si.name index_name ".
			"FROM sysindexkeys s ".
			"   INNER JOIN syscolumns c ON s.id = c.id AND s.colid = c.colid ".
			"   INNER JOIN sysobjects o ON s.id = o.Id AND o.xtype = 'U' ".
			"   LEFT JOIN sysindexes si ON si.indid = s.indid AND si.id = s.id ".
			"WHERE o.name = UPPER('".$this->getSqlHelper()->forSql($tableName)."')");

		$indexes = array();
		while ($ar = $rs->fetch())
		{
			$indexes[$ar["index_name"]][$ar["key_ordinal"] - 1] = $ar["column_name"];
		}

		return static::findIndex($indexes, $columns, $strict);
	}

	/**
	 * @inheritDoc
	 */
	public function getTableFields($tableName)
	{
		if (!isset($this->tableColumnsCache[$tableName]))
		{
			$this->connectInternal();

			$query = $this->queryInternal("SELECT TOP 0 * FROM ".$this->getSqlHelper()->quote($tableName));

			$result = $this->createResult($query);

			$this->tableColumnsCache[$tableName] = $result->getFields();
		}
		return $this->tableColumnsCache[$tableName];
	}

	/**
	 * @inheritDoc
	 */
	public function createTable($tableName, $fields, $primary = array(), $autoincrement = array())
	{
		$sql = 'CREATE TABLE '.$this->getSqlHelper()->quote($tableName).' (';
		$sqlFields = array();

		foreach ($fields as $columnName => $field)
		{
			if (!($field instanceof ScalarField))
			{
				throw new ArgumentException(sprintf(
					'Field `%s` should be an Entity\ScalarField instance', $columnName
				));
			}

			$realColumnName = $field->getColumnName();

			$sqlFields[] = $this->getSqlHelper()->quote($realColumnName)
				. ' ' . $this->getSqlHelper()->getColumnTypeByField($field)
				. ' NOT NULL'
				. (in_array($columnName, $autoincrement, true) ? ' IDENTITY (1, 1)' : '')
			;
		}

		$sql .= join(', ', $sqlFields);

		if (!empty($primary))
		{
			foreach ($primary as &$primaryColumn)
			{
				$realColumnName = $fields[$primaryColumn]->getColumnName();
				$primaryColumn = $this->getSqlHelper()->quote($realColumnName);
			}

			$sql .= ', PRIMARY KEY('.join(', ', $primary).')';
		}

		$sql .= ')';

		$this->query($sql);
	}

	/**
	 * @inheritDoc
	 */
	public function renameTable($currentName, $newName)
	{
		$this->query('EXEC sp_rename '.$this->getSqlHelper()->quote($currentName).', '.$this->getSqlHelper()->quote($newName));
	}

	/**
	 * @inheritDoc
	 */
	public function dropTable($tableName)
	{
		$this->query('DROP TABLE '.$this->getSqlHelper()->quote($tableName));
	}

	/*********************************************************
	 * Transaction
	 *********************************************************/

	/**
	 * @inheritDoc
	 */
	public function startTransaction()
	{
		$this->connectInternal();
		sqlsrv_begin_transaction($this->resource);
	}

	/**
	 * @inheritDoc
	 */
	public function commitTransaction()
	{
		$this->connectInternal();
		sqlsrv_commit($this->resource);
	}

	/**
	 * @inheritDoc
	 */
	public function rollbackTransaction()
	{
		$this->connectInternal();
		sqlsrv_rollback($this->resource);
	}

	/*********************************************************
	 * Type, version, cache, etc.
	 *********************************************************/

	/**
	 * @inheritDoc
	 */
	public function getType()
	{
		return "mssql";
	}

	/**
	 * @inheritDoc
	 */
	public function getVersion()
	{
		if ($this->version == null)
		{
			$version = $this->queryScalar("SELECT @@VERSION");
			if ($version != null)
			{
				$version = trim($version);
				$this->versionExpress = (mb_strpos($version, "Express Edition") > 0);
				preg_match("#[0-9]+\\.[0-9]+\\.[0-9]+#", $version, $arr);
				$this->version = $arr[0];
			}
		}

		return array($this->version, $this->versionExpress);
	}

	/**
	 * @inheritDoc
	 */
	public function getErrorMessage()
	{
		$errors = "";
		foreach (sqlsrv_errors(SQLSRV_ERR_ERRORS) as $error)
		{
			$errors .= "SQLSTATE: ".$error['SQLSTATE'].";"." code: ".$error['code']."; message: ".$error[ 'message']."\n";
		}
		return $errors;
	}
}
