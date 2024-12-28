<?php

namespace Bitrix\Main\DB;

use Bitrix\Main;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Data;
use Bitrix\Main\Diag;
use Bitrix\Main\ORM\Fields\ScalarField;

/**
 * Class Connection
 *
 * Base abstract class for database connections.
 * @package Bitrix\Main\DB
 */
abstract class Connection extends Data\Connection
{
	const PERSISTENT = 1;
	const DEFERRED = 2;
	const INDEX_UNIQUE = 'UNIQUE';
	const INDEX_FULLTEXT = 'FULLTEXT';
	const INDEX_SPATIAL = 'SPATIAL';

	/** @var MysqliSqlHelper | PgsqlSqlHelper */
	protected $sqlHelper;
	/** @var Diag\SqlTracker */
	protected $sqlTracker;
	protected $trackSql = false;
	protected $version;
	protected $versionExpress;
	protected $host;
	protected $database;
	protected $login;
	protected $password;
	protected $initCommand = 0;
	protected $options = 0;
	protected $nodeId = 0;
	protected $utf8mb4 = [];
	protected $tableColumnsCache = [];
	protected $lastQueryResult;
	/**
	 * @var bool Flag for static::query - if needed to execute query or just to collect it
	 * @see $disabledQueryExecutingDump
	 */
	protected $queryExecutingEnabled = true;
	/** @var null|string[] Queries that were collected while Query Executing was Disabled */
	protected $disabledQueryExecutingDump;

	/**
	 * $configuration may contain following keys:
	 * <ul>
	 * <li>host
	 * <li>database
	 * <li>login
	 * <li>password
	 * <li>initCommand
	 * <li>options
	 * </ul>
	 *
	 * @param array $configuration Array of Name => Value pairs.
	 */
	public function __construct(array $configuration)
	{
		parent::__construct($configuration);

		$this->host = $configuration['host'] ?? '';
		$this->database = $configuration['database'] ?? '';
		$this->login = $configuration['login'] ?? '';
		$this->password = $configuration['password'] ?? '';
		$this->initCommand = $configuration['initCommand'] ?? '';
		$this->options = intval($configuration['options'] ?? 2);
		$this->utf8mb4 = (isset($configuration['utf8mb4']) && is_array($configuration['utf8mb4']) ? $configuration['utf8mb4'] : []);
	}

	/**
	 * @return string
	 * @deprecated Use getDatabase()
	 */
	public function getDbName()
	{
		return $this->getDatabase();
	}

	/**
	 * Returns database host.
	 *
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Returns database login.
	 *
	 * @return string
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * Returns database password.
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Returns database name.
	 *
	 * @return string
	 */
	public function getDatabase()
	{
		return $this->database;
	}

	/**
	 * Temporary disables query executing. All queries being collected in disabledQueryExecutingDump
	 *
	 * @return void
	 * @see enableQueryExecuting
	 * @see getDisabledQueryExecutingDump
	 *
	 * @api
	 */
	public function disableQueryExecuting()
	{
		$this->queryExecutingEnabled = false;
	}

	/**
	 * Enables query executing after it has been temporary disabled
	 *
	 * @return void
	 * @see disableQueryExecuting
	 *
	 * @api
	 */
	public function enableQueryExecuting()
	{
		$this->queryExecutingEnabled = true;
	}

	/**
	 * @return bool
	 * @see disableQueryExecuting
	 *
	 * @api
	 */
	public function isQueryExecutingEnabled()
	{
		return $this->queryExecutingEnabled;
	}

	/**
	 * Returns queries that were collected while Query Executing was disabled and clears the dump.
	 *
	 * @return null|string[]
	 * @see disableQueryExecuting
	 *
	 * @api
	 */
	public function getDisabledQueryExecutingDump()
	{
		$dump = $this->disabledQueryExecutingDump;
		$this->disabledQueryExecutingDump = null;

		return $dump;
	}

	/**********************************************************
	 * SqlHelper
	 **********************************************************/

	abstract protected function createSqlHelper();

	/**
	 * Returns database-depended SqlHelper object.
	 * Creates new one on the first call per Connection object instance.
	 *
	 * @return MysqliSqlHelper | PgsqlSqlHelper
	 */
	public function getSqlHelper()
	{
		if ($this->sqlHelper == null)
		{
			$this->sqlHelper = $this->createSqlHelper();
		}

		return $this->sqlHelper;
	}

	/***********************************************************
	 * Connection and disconnection
	 ***********************************************************/

	/**
	 * Connects to the database.
	 *
	 * @return void
	 */
	public function connect()
	{
		$this->isConnected = false;

		if (!$this->isDeferred())
		{
			parent::connect();
		}
	}

	/**
	 * Disconnects from the database.
	 *
	 * @return void
	 */
	public function disconnect()
	{
		if (!$this->isPersistent())
		{
			parent::disconnect();
		}
	}

	/**
	 * Returns true if the connection is deferred.
	 * @return bool
	 */
	public function isDeferred()
	{
		return (($this->options & self::DEFERRED) !== 0);
	}

	/**
	 * Returns true if the connection is persistent.
	 * @return bool
	 */
	public function isPersistent()
	{
		return (($this->options & self::PERSISTENT) !== 0);
	}

	/*********************************************************
	 * Query
	 *********************************************************/

	/**
	 * Executes a query against connected database.
	 * Rises SqlQueryException on any database error.
	 * <p>
	 * When object $trackerQuery passed then calls its startQuery and finishQuery
	 * methods before and after query execution.
	 *
	 * @param string $sql Sql query.
	 * @param array|null $binds Array of binds.
	 * @param Diag\SqlTrackerQuery|null $trackerQuery Debug collector object.
	 *
	 * @return resource
	 * @throws SqlQueryException | DuplicateEntryException
	 */
	abstract protected function queryInternal($sql, array $binds = null, Diag\SqlTrackerQuery $trackerQuery = null);

	/**
	 * Returns database-depended result of the query.
	 *
	 * @param resource $result Result of internal query function.
	 * @param Diag\SqlTrackerQuery|null $trackerQuery Debug collector object.
	 *
	 * @return Result
	 */
	abstract protected function createResult($result, Diag\SqlTrackerQuery $trackerQuery = null);

	/**
	 * Executes a query to the database.
	 *
	 * - query($sql)
	 * - query($sql, $limit)
	 * - query($sql, $offset, $limit)
	 * - query($sql, $binds)
	 * - query($sql, $binds, $limit)
	 * - query($sql, $binds, $offset, $limit)
	 *
	 * @param string $sql Sql query.
	 * @param array $binds Array of binds.
	 * @param int $offset Offset the of the first row to return, starting from 0.
	 * @param int $limit Limit rows count.
	 *
	 * @return Result
	 * @throws SqlQueryException
	 */
	public function query($sql)
	{
		[$sql, $binds, $offset, $limit] = self::parseQueryFunctionArgs(func_get_args());

		if ($limit > 0)
		{
			$sql = $this->getSqlHelper()->getTopSql($sql, $limit, $offset);
		}

		$trackerQuery = null;

		if ($this->queryExecutingEnabled)
		{
			$connection = Main\Application::getInstance()->getConnectionPool()->getSlaveConnection($sql);
			if ($connection === null)
			{
				$connection = $this;
			}

			if ($this->trackSql)
			{
				$trackerQuery = $this->sqlTracker->getNewTrackerQuery();
				$trackerQuery->setNode($connection->getNodeId());
			}

			$result = $connection->queryInternal($sql, $binds, $trackerQuery);
		}
		else
		{
			if ($this->disabledQueryExecutingDump === null)
			{
				$this->disabledQueryExecutingDump = [];
			}

			$this->disabledQueryExecutingDump[] = $sql;
			$result = true;
		}

		return $this->createResult($result, $trackerQuery);
	}

	/**
	 * Executes a query, fetches a row and returns single field value
	 * from the first column of the result.
	 *
	 * @param string $sql Sql text.
	 * @param array|null $binds Binding array.
	 *
	 * @return string|null
	 * @throws SqlQueryException
	 */
	public function queryScalar($sql, array $binds = null)
	{
		$result = $this->query($sql, $binds, 0, 1);

		if ($row = $result->fetch())
		{
			return array_shift($row);
		}

		return null;
	}

	/**
	 * Executes a query without returning result, i.e. INSERT, UPDATE, DELETE
	 *
	 * @param string $sql Sql text.
	 * @param array|null $binds Binding array.
	 *
	 * @return void
	 * @throws SqlQueryException
	 */
	public function queryExecute($sql, array $binds = null)
	{
		$this->query($sql, $binds);
	}

	/**
	 * Helper function for parameters handling.
	 *
	 * @param mixed $args Variable list of parameters.
	 *
	 * @return array
	 * @throws ArgumentNullException
	 */
	protected static function parseQueryFunctionArgs($args)
	{
		/*
		 * query($sql)
		 * query($sql, $limit)
		 * query($sql, $offset, $limit)
		 * query($sql, $arBinds)
		 * query($sql, $arBinds, $limit)
		 * query($sql, $arBinds, $offset, $limit)
		 */
		$numArgs = count($args);
		if ($numArgs < 1)
		{
			throw new ArgumentNullException("sql");
		}

		$binds = [];
		$offset = 0;
		$limit = 0;

		if ($numArgs == 1)
		{
			$sql = $args[0];
		}
		elseif ($numArgs == 2)
		{
			if (is_array($args[1]))
			{
				[$sql, $binds] = $args;
			}
			else
			{
				[$sql, $limit] = $args;
			}
		}
		elseif ($numArgs == 3)
		{
			if (is_array($args[1]))
			{
				[$sql, $binds, $limit] = $args;
			}
			else
			{
				[$sql, $offset, $limit] = $args;
			}
		}
		else
		{
			[$sql, $binds, $offset, $limit] = $args;
		}

		return [$sql, $binds, $offset, $limit];
	}

	/**
	 * Adds row to table and returns ID of the added row.
	 * <p>
	 * $identity parameter must be null when table does not have autoincrement column.
	 *
	 * @param string $tableName Name of the table for insertion of new row.
	 * @param array $data Array of columnName => Value pairs.
	 * @param string $identity For Oracle only.
	 *
	 * @return integer
	 * @throws SqlQueryException
	 */
	public function add($tableName, array $data, $identity = "ID")
	{
		$insert = $this->getSqlHelper()->prepareInsert($tableName, $data);

		$sql =
			"INSERT INTO " . $this->getSqlHelper()->quote($tableName) . "(" . $insert[0] . ") " .
			"VALUES (" . $insert[1] . ")";

		$this->queryExecute($sql);

		return $this->getInsertedId();
	}

	/**
	 * @param string $tableName
	 * @param array $rows
	 * @param string $identity
	 *
	 * @return int
	 * @throws SqlQueryException
	 */
	public function addMulti($tableName, $rows, $identity = "ID")
	{
		$uniqueColumns = [];
		$inserts = [];

		// prepare data
		foreach ($rows as $data)
		{
			$insert = $this->getSqlHelper()->prepareInsert($tableName, $data, true);
			$inserts[] = $insert;

			// and get unique column names
			foreach ($insert[0] as $column)
			{
				$uniqueColumns[$column] = true;
			}
		}

		// prepare sql
		$sqlValues = [];

		foreach ($inserts as $insert)
		{
			$columns = array_flip($insert[0]);
			$values = $insert[1];

			$finalValues = [];

			foreach (array_keys($uniqueColumns) as $column)
			{
				if (array_key_exists($column, $columns))
				{
					// set real value
					$finalValues[] = $values[$columns[$column]];
				}
				else
				{
					// set default
					$finalValues[] = 'DEFAULT';
				}
			}

			$sqlValues[] = '(' . join(', ', $finalValues) . ')';
		}

		$sql = "INSERT INTO {$this->getSqlHelper()->quote($tableName)} (" . join(', ', array_keys($uniqueColumns)) . ") " .
			"VALUES " . join(', ', $sqlValues);

		$this->queryExecute($sql);

		return $this->getInsertedId();
	}

	/**
	 * @return integer
	 */
	abstract public function getInsertedId();

	/**
	 * Parses the string containing multiple queries and executes the queries one by one.
	 * Queries delimiter depends on database type.
	 * @param string $sqlBatch String with queries, separated by database-specific delimiters.
	 * @param bool $stopOnError Whether return after the first error.
	 * @return array Array of errors or empty array on success.
	 * @see SqlHelper->getQueryDelimiter
	 *
	 */
	public function executeSqlBatch($sqlBatch, $stopOnError = false)
	{
		$result = [];
		foreach ($this->parseSqlBatch($sqlBatch) as $sql)
		{
			try
			{
				$this->queryExecute($sql);
			}
			catch (SqlException $ex)
			{
				$result[] = $ex->getMessage();
				if ($stopOnError)
				{
					return $result;
				}
			}
		}

		return $result;
	}

	/**
	 * Parses the text containing sqls into separate queries.
	 *
	 * @param string $sqlBatch
	 * @return array
	 */
	public function parseSqlBatch($sqlBatch)
	{
		$delimiter = $this->getSqlHelper()->getQueryDelimiter();

		$sqlBatch = trim($sqlBatch);

		$statements = [];
		$sql = "";

		do
		{
			if (preg_match("%^(.*?)(['\"`#]|--|\\$\\$|" . $delimiter . ")%is", $sqlBatch, $match))
			{
				//Found string start
				if ($match[2] == "\"" || $match[2] == "'" || $match[2] == "`")
				{
					$sqlBatch = mb_substr($sqlBatch, mb_strlen($match[0]));
					$sql .= $match[0];
					//find a quote not preceded by \
					if (preg_match("%^(.*?)(?<!\\\\)" . $match[2] . "%s", $sqlBatch, $stringMatch))
					{
						$sqlBatch = mb_substr($sqlBatch, mb_strlen($stringMatch[0]));
						$sql .= $stringMatch[0];
					}
					else
					{
						//String foll beyond end of file
						$sql .= $sqlBatch;
						$sqlBatch = "";
					}
				}
				//Comment found
				elseif ($match[2] == "#" || $match[2] == "--")
				{
					//Take that was before comment as part of sql
					$sqlBatch = mb_substr($sqlBatch, mb_strlen($match[1]));
					$sql .= $match[1];
					//And cut the rest
					$p = mb_strpos($sqlBatch, "\n");
					if ($p === false)
					{
						$p1 = mb_strpos($sqlBatch, "\r");
						if ($p1 === false)
						{
							$sqlBatch = "";
						}
						elseif ($p < $p1)
						{
							$sqlBatch = mb_substr($sqlBatch, $p);
						}
						else
						{
							$sqlBatch = mb_substr($sqlBatch, $p1);
						}
					}
					else
					{
						$sqlBatch = mb_substr($sqlBatch, $p);
					}
				}
				//$$ plpgsql body
				elseif ($match[2] == '$$')
				{
					//Take that was before delimiter as part of sql
					$sqlBatch = mb_substr($sqlBatch, mb_strlen($match[0]));
					//Including $$
					$sql .= $match[0];
					//Find closing $$
					$p = mb_strpos($sqlBatch, '$$');
					if ($p === false)
					{
						$sql .= $sqlBatch;
						$sqlBatch = '';
					}
					else
					{
						$sql .= mb_substr($sqlBatch, 0, $p + 2);
						$sqlBatch = mb_substr($sqlBatch, $p + 2);
					}
				}
				//Delimiter!
				else
				{
					//Take that was before delimiter as part of sql
					$sqlBatch = mb_substr($sqlBatch, mb_strlen($match[0]));
					$sql .= $match[1];
					//Delimiter must be followed by whitespace
					if (preg_match("%^[\n\r\t ]%", $sqlBatch))
					{
						$sql = trim($sql);
						if (!empty($sql))
						{
							$statements[] = str_replace("\r\n", "\n", $sql);
							$sql = "";
						}
					}
					//It was not delimiter!
					elseif (!empty($sqlBatch))
					{
						$sql .= $match[2];
					}
				}
			}
			else //End of file is our delimiter
			{
				$sql .= $sqlBatch;
				$sqlBatch = "";
			}
		}
		while (!empty($sqlBatch));

		$sql = trim($sql, " \t\n\r");
		if (!empty($sql))
		{
			$statements[] = str_replace("\r\n", "\n", $sql);
		}

		return $statements;
	}

	/**
	 * Returns affected rows count from last executed query.
	 *
	 * @return integer
	 */
	abstract public function getAffectedRowsCount();

	/*********************************************************
	 * DDL
	 *********************************************************/

	/**
	 * Checks if a table exists.
	 *
	 * @param string $tableName The table name.
	 *
	 * @return boolean
	 */
	abstract public function isTableExists($tableName);

	/**
	 * Checks if an index exists.
	 * Actual columns in the index may differ from requested.
	 * $columns may present a "prefix" of actual index columns.
	 *
	 * @param string $tableName A table name.
	 * @param array $columns An array of columns in the index.
	 *
	 * @return boolean
	 * @throws SqlQueryException
	 */
	abstract public function isIndexExists($tableName, array $columns);

	/**
	 * Returns the name of an index.
	 *
	 * @param string $tableName A table name.
	 * @param array $columns An array of columns in the index.
	 * @param bool $strict The flag indicating that the columns in the index must exactly match the columns in the $arColumns parameter.
	 *
	 * @return string|null Name of the index or null if the index doesn't exist.
	 */
	abstract public function getIndexName($tableName, array $columns, $strict = false);

	/**
	 * Returns fields objects according to the columns of a table.
	 * Table must exist.
	 *
	 * @param string $tableName The table name.
	 *
	 * @return ScalarField[] An array of objects with columns information.
	 * @throws SqlQueryException
	 */
	abstract public function getTableFields($tableName);

	/**
	 * @param string $tableName Name of the new table.
	 * @param ScalarField[] $fields Array with columns descriptions.
	 * @param string[] $primary Array with primary key column names.
	 * @param string[] $autoincrement Which columns will be auto incremented ones.
	 *
	 * @return void
	 * @throws SqlQueryException
	 */
	abstract public function createTable($tableName, $fields, $primary = [], $autoincrement = []);

	/**
	 * Creates primary index on column(s)
	 * @param string $tableName Name of the table.
	 * @param string|string[] $columnNames Name of the column or array of column names to be included into the index.
	 *
	 * @return Result
	 * @throws SqlQueryException
	 * @api
	 *
	 */
	public function createPrimaryIndex($tableName, $columnNames)
	{
		if (!is_array($columnNames))
		{
			$columnNames = [$columnNames];
		}

		foreach ($columnNames as &$columnName)
		{
			$columnName = $this->getSqlHelper()->quote($columnName);
		}

		$sql = 'ALTER TABLE ' . $this->getSqlHelper()->quote($tableName) . ' ADD PRIMARY KEY(' . join(', ', $columnNames) . ')';

		return $this->query($sql);
	}

	/**
	 * Creates index on column(s)
	 * @param string $tableName Name of the table.
	 * @param string $indexName Name of the new index.
	 * @param string|string[] $columnNames Name of the column or array of column names to be included into the index.
	 *
	 * @return Result | false
	 * @throws SqlQueryException
	 * @api
	 *
	 */
	public function createIndex($tableName, $indexName, $columnNames)
	{
		if (!is_array($columnNames))
		{
			$columnNames = [$columnNames];
		}

		$sqlHelper = $this->getSqlHelper();

		foreach ($columnNames as &$columnName)
		{
			$columnName = $sqlHelper->quote($columnName);
		}
		unset($columnName);

		$sql = 'CREATE INDEX ' . $sqlHelper->quote($indexName) . ' ON ' . $sqlHelper->quote($tableName) . ' (' . join(', ', $columnNames) . ')';

		return $this->query($sql);
	}

	/**
	 * Returns an object for the single column according to the column type.
	 *
	 * @param string $tableName Name of the table.
	 * @param string $columnName Name of the column.
	 *
	 * @return ScalarField | null
	 * @throws SqlQueryException
	 */
	public function getTableField($tableName, $columnName)
	{
		$tableFields = $this->getTableFields($tableName);

		return ($tableFields[$columnName] ?? null);
	}

	/**
	 * Truncates all table data.
	 *
	 * @param string $tableName Name of the table.
	 * @return Result
	 */
	public function truncateTable($tableName)
	{
		return $this->query('TRUNCATE TABLE ' . $this->getSqlHelper()->quote($tableName));
	}

	/**
	 * Renames the table. Renamed table must exist and new name must not be occupied by any database object.
	 *
	 * @param string $currentName Old name of the table.
	 * @param string $newName New name of the table.
	 *
	 * @return void
	 * @throws SqlQueryException
	 */
	abstract public function renameTable($currentName, $newName);

	/**
	 * Drops a column. This column must exist and must be not the part of primary constraint.
	 * and must be not the last one in the table.
	 *
	 * @param string $tableName Name of the table to which column will be dropped.
	 * @param string $columnName Name of the column to be dropped.
	 *
	 * @return void
	 * @throws SqlQueryException
	 */
	public function dropColumn($tableName, $columnName)
	{
		$this->query('ALTER TABLE ' . $this->getSqlHelper()->quote($tableName) . ' DROP COLUMN ' . $this->getSqlHelper()->quote($columnName));
	}

	/**
	 * Drops the table.
	 *
	 * @param string $tableName Name of the table to be dropped.
	 *
	 * @return void
	 * @throws SqlQueryException
	 */
	abstract public function dropTable($tableName);

	/*********************************************************
	 * Transaction
	 *********************************************************/

	/**
	 * Starts new database transaction.
	 *
	 * @return void
	 * @throws SqlQueryException
	 */
	abstract public function startTransaction();

	/**
	 * Commits started database transaction.
	 *
	 * @return void
	 * @throws SqlQueryException
	 */
	abstract public function commitTransaction();

	/**
	 * Rollbacks started database transaction.
	 *
	 * @return void
	 * @throws SqlQueryException
	 */
	abstract public function rollbackTransaction();

	/*********************************************************
	 * Global named lock
	 *********************************************************/

	/**
	 * Sets a global named lock. Currently only Mysql is supported.
	 * @param string $name The lock name.
	 * @param int $timeout
	 * @return bool
	 */
	public function lock($name, $timeout = 0)
	{
		return true;
	}

	/**
	 * Releases a global named lock. Currently only Mysql is supported.
	 * @param string $name The lock name.
	 * @return bool
	 */
	public function unlock($name)
	{
		return true;
	}

	/*********************************************************
	 * Tracker
	 *********************************************************/

	/**
	 * Starts collecting information about all queries executed.
	 *
	 * @param boolean $reset Clears all previously collected information when set to true.
	 *
	 * @return Diag\SqlTracker
	 */
	public function startTracker($reset = false)
	{
		if ($this->sqlTracker == null)
		{
			$this->sqlTracker = new Diag\SqlTracker();
		}
		if ($reset)
		{
			$this->sqlTracker->reset();
		}

		$this->trackSql = true;
		return $this->sqlTracker;
	}

	/**
	 * Stops collecting information about all queries executed.
	 *
	 * @return void
	 */
	public function stopTracker()
	{
		$this->trackSql = false;
	}

	/**
	 * Returns an object with information about queries executed.
	 * or null if no tracking was started.
	 *
	 * @return null|Diag\SqlTracker
	 */
	public function getTracker()
	{
		return $this->sqlTracker;
	}

	/**
	 * Sets new sql tracker.
	 *
	 * @param null|Diag\SqlTracker $sqlTracker New tracker.
	 *
	 * @return void
	 */
	public function setTracker(Diag\SqlTracker $sqlTracker = null)
	{
		$this->sqlTracker = $sqlTracker;
	}

	/*********************************************************
	 * Type, version, cache, etc.
	 *********************************************************/

	/**
	 * Returns database type.
	 * <ul>
	 * <li> mysql
	 * <li> oracle
	 * <li> mssql
	 * </ul>
	 *
	 * @return string
	 */
	abstract public function getType();

	/**
	 * Returns connected database version.
	 * Version presented in array of two elements.
	 * - First (with index 0) is database version.
	 * - Second (with index 1) is true when light/express version of database is used.
	 *
	 * @return array
	 * @throws SqlQueryException
	 */
	abstract public function getVersion();

	/**
	 * Returns error message of last failed database operation.
	 *
	 * @return string
	 */
	abstract public function getErrorMessage();

	/**
	 * Returns the error code of the last failed database operation.
	 *
	 * @return int|string
	 */
	public function getErrorCode()
	{
		return 0;
	}

	/**
	 * Clears all internal caches which may be used by some dictionary functions.
	 *
	 * @return void
	 */
	public function clearCaches()
	{
		$this->tableColumnsCache = [];
	}

	/**
	 * Sets connection node identifier.
	 *
	 * @param string $nodeId Node identifier.
	 * @return void
	 */
	public function setNodeId($nodeId)
	{
		$this->nodeId = $nodeId;
	}

	/**
	 * Returns connection node identifier.
	 *
	 * @return string|null
	 */
	public function getNodeId()
	{
		return $this->nodeId;
	}

	protected function afterConnected()
	{
		if (isset($this->configuration["include_after_connected"]) && $this->configuration["include_after_connected"] <> '')
		{
			include($this->configuration["include_after_connected"]);
		}
	}

	/**
	 * Returns utfmb4 flag for the specific table/column.
	 *
	 * @param string|null $table
	 * @param string|null $column
	 * @return bool
	 */
	public function isUtf8mb4($table = null, $column = null)
	{
		if (isset($this->utf8mb4["global"]) && $this->utf8mb4["global"] === true)
		{
			return true;
		}

		if ($table !== null && isset($this->utf8mb4["tables"][$table]) && $this->utf8mb4["tables"][$table] === true)
		{
			return true;
		}

		if ($table !== null && $column !== null && isset($this->utf8mb4["tables"][$table][$column]) && $this->utf8mb4["tables"][$table][$column] === true)
		{
			return true;
		}

		return false;
	}

	protected static function findIndex(array $indexes, array $columns, $strict)
	{
		$columnsList = implode(",", $columns);

		foreach ($indexes as $indexName => $indexColumns)
		{
			ksort($indexColumns);
			$indexColumnList = implode(",", $indexColumns);
			if ($strict)
			{
				if ($indexColumnList === $columnsList)
				{
					return $indexName;
				}
			}
			else
			{
				if (str_starts_with($indexColumnList, $columnsList))
				{
					return $indexName;
				}
			}
		}

		return null;
	}

	/**
	 * Creates an exception by the error code.
	 *
	 * @param int|string $code
	 * @param string $databaseMessage
	 * @param string $query
	 */
	public function createQueryException($code = 0, $databaseMessage = '', $query = '')
	{
		return new SqlQueryException('Query error', $databaseMessage, $query);
	}
}
