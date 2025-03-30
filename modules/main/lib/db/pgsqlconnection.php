<?php

namespace Bitrix\Main\DB;

use Bitrix\Main\Diag;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * @method \PgSql\Connection getResource()
 * @property \PgSql\Connection $resource
 */
class PgsqlConnection extends Connection
{
	protected int $transactionLevel = 0;
	const FULLTEXT_MAXIMUM_LENGTH = 900000;

	public function connectionErrorHandler($errno, $errstr, $errfile = '', $errline = 0, $errcontext = null)
	{
		throw new ConnectionException('Pgsql connect error: ', $errstr);
	}

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

		$connectionString = " host='" . addslashes($host) . "'";
		if ($port > 0)
		{
			$connectionString .= " port='" . addslashes($port) . "'";
		}
		$connectionString .= " dbname='" . addslashes($this->database) . "'";
		$connectionString .= " user='" . addslashes($this->login) . "'";
		$connectionString .= " password='" . addslashes($this->password) . "'";

		if (isset($this->configuration['charset']))
		{
			$connectionString .= " options='--client_encoding=" . $this->configuration['charset'] . "'";
		}

		set_error_handler([$this, 'connectionErrorHandler']);
		try
		{
			if ($this->isPersistent())
			{
				$connection = @pg_pconnect($connectionString);
			}
			else
			{
				$connection = @pg_connect($connectionString);
			}
		}
		finally
		{
			restore_error_handler();
		}

		if (!$connection)
		{
			throw new ConnectionException(
				'Pgsql connect error [' . $this->host . ']',
				error_get_last()['message']
			);
		}

		$this->resource = $connection;
		$this->isConnected = true;

		$this->configureErrorVerbosity();
		$this->afterConnected();
	}

	protected function disconnectInternal()
	{
		if ($this->isConnected)
		{
			$this->isConnected = false;
			try
			{
				pg_close($this->resource);
				$this->resource = null;
			}
			catch (\Throwable)
			{
				// Ignore misterious error
				// pg_close(): supplied resource is not a valid PostgreSQL link resource (0)
			}
		}
	}

	protected function createSqlHelper()
	{
		return new PgsqlSqlHelper($this);
	}

	/**
	 * @inheritDoc
	 */
	protected function queryInternal($sql, array $binds = null, Diag\SqlTrackerQuery $trackerQuery = null)
	{
		$this->connectInternal();

		$trackerQuery?->startQuery($sql, $binds);

		// Handle E_WARNING
		set_error_handler(function () {
			// noop
		});

		$result = pg_query($this->resource, $sql);

		restore_error_handler();

		$trackerQuery?->finishQuery();

		$this->lastQueryResult = $result;

		if (!$result)
		{
			throw $this->createQueryException($this->getErrorCode(), $this->getErrorMessage(), $sql);
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function createResult($result, Diag\SqlTrackerQuery $trackerQuery = null)
	{
		return new PgsqlResult($result, $this, $trackerQuery);
	}

	/**
	 * @inheritDoc
	 */
	public function add($tableName, array $data, $identity = "ID")
	{
		$insert = $this->getSqlHelper()->prepareInsert($tableName, $data);
		if (
			$identity !== null
			&& (
				!isset($data[$identity])
				|| $data[$identity] instanceof SqlExpression
			)
		)
		{
			$sql = "INSERT INTO " . $tableName . "(" . $insert[0] . ") VALUES (" . $insert[1] . ") RETURNING " . $identity;
			$row = $this->query($sql)->fetch();
			return intval(array_shift($row));
		}
		else
		{
			$sql = "INSERT INTO " . $tableName . "(" . $insert[0] . ") VALUES (" . $insert[1] . ")";
			$this->query($sql);
			return $data[$identity] ?? null;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getInsertedId()
	{
		try
		{
			return (int)$this->query('SELECT bx_lastval() as X')->fetch()['X'];
		}
		catch (SqlQueryException)
		{
			return 0;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getAffectedRowsCount()
	{
		return pg_affected_rows($this->lastQueryResult);
	}

	/**
	 * @inheritDoc
	 */
	public function isTableExists($tableName)
	{
		$result = $this->query("
			SELECT tablename
			FROM  pg_tables
			WHERE schemaname = 'public'
			AND tablename  = '" . $this->getSqlHelper()->forSql($tableName) . "'
		");
		$row = $result->fetch();
		return is_array($row);
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

		$tableColumns = [];
		$r = $this->query("
			SELECT a.attnum, a.attname
			FROM pg_class t
			LEFT JOIN pg_attribute a ON a.attrelid = t.oid
			WHERE t.relname = '" . $this->getSqlHelper()->forSql($tableName) . "'
		");
		while ($a = $r->fetch())
		{
			if ($a['ATTNUM'] > 0)
			{
				$tableColumns[$a['ATTNUM']] = $a['ATTNAME'];
			}
		}

		$r = $this->query("
			SELECT relname, indkey, pg_get_expr(pg_index.indexprs, pg_index.indrelid) full_text
			FROM pg_class, pg_index
			WHERE pg_class.oid = pg_index.indexrelid
			AND pg_class.oid IN (
				SELECT indexrelid
				FROM pg_index, pg_class
				WHERE pg_class.relname = '" . $this->getSqlHelper()->forSql($tableName) . "'
				AND pg_class.oid = pg_index.indrelid
			)
		");
		$indexes = [];
		while ($a = $r->fetch())
		{
			$indexes[$a['RELNAME']] = [];
			if ($a['FULL_TEXT'])
			{
				$match = [];
				if (preg_match_all('/,\s*([a-z0-9_]+)/i', $a['FULL_TEXT'], $match))
				{
					foreach ($match[1] as $i => $colName)
					{
						$indexes[$a['RELNAME']][$i] = mb_strtoupper($colName);
					}
				}
			}
			else
			{
				foreach (explode(' ', $a['INDKEY']) as $i => $indkey)
				{
					$indexes[$a['RELNAME']][$i] = mb_strtoupper($tableColumns[$indkey]);
				}
			}
		}

		return static::findIndex($indexes, $columns, $strict);
	}

	protected static function findIndex(array $indexes, array $columns, $strict)
	{
		$columnsList = mb_strtolower(implode(",", $columns));

		foreach ($indexes as $indexName => $indexColumns)
		{
			ksort($indexColumns);
			$indexColumnList = mb_strtolower(implode(",", $indexColumns));
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

	public function getTableFullTextFields($tableName)
	{
		$sqlHelper = $this->getSqlHelper();
		$fullTextColumns = [];

		$sql = "
			SELECT relname, indkey, pg_get_expr(pg_index.indexprs, pg_index.indrelid) full_text
			FROM pg_class, pg_index
			WHERE pg_class.oid = pg_index.indexrelid
			AND pg_class.oid IN (
				SELECT indexrelid
				FROM pg_index, pg_class
				WHERE pg_class.relname = '" . $sqlHelper->forSql(mb_strtolower($tableName)) . "'
				AND pg_class.oid = pg_index.indrelid
			)
		";
		$res = $this->query($sql);
		while ($row = $res->fetch())
		{
			if ($row['FULL_TEXT'])
			{
				$match = [];
				if (preg_match_all('/,\s*([a-z0-9_]+)/i', $row['FULL_TEXT'], $match))
				{
					foreach ($match[1] as $i => $colName)
					{
						$fullTextColumns[mb_strtoupper($colName)] = true;
					}
				}
			}
		}

		return $fullTextColumns;
	}

	/**
	 * @inheritDoc
	 */
	public function getTableFields($tableName)
	{
		if (!isset($this->tableColumnsCache[$tableName]) || empty($this->tableColumnsCache[$tableName]))
		{
			$this->connectInternal();

			$sqlHelper = $this->getSqlHelper();

			$fullTextColumns = $this->getTableFullTextFields($tableName);

			$query = $this->query("
				SELECT
					column_name,
					data_type,
					character_maximum_length
				FROM
					information_schema.columns
				WHERE
					table_catalog = '" . $sqlHelper->forSql($this->getDatabase()) . "'
					and table_schema = 'public'
					and table_name = '" . $sqlHelper->forSql(mb_strtolower($tableName)) . "'
				ORDER BY
					ordinal_position
			");

			$this->tableColumnsCache[$tableName] = [];
			while ($fieldInfo = $query->fetch())
			{
				$fieldName = mb_strtoupper($fieldInfo['COLUMN_NAME']);
				$fieldType = $fieldInfo['DATA_TYPE'];
				$field = $sqlHelper->getFieldByColumnType($fieldName, $fieldType);
				if (is_a($field, '\Bitrix\Main\ORM\Fields\StringField'))
				{
					if (!$fieldInfo['CHARACTER_MAXIMUM_LENGTH'])
					{
						if (array_key_exists($fieldName, $fullTextColumns))
						{
							$maximumLength = static::FULLTEXT_MAXIMUM_LENGTH;
						}
						else
						{
							$maximumLength = false; // "Infinite"
						}
					}
					else
					{
						if (
							array_key_exists($fieldName, $fullTextColumns)
							&& $fieldInfo['CHARACTER_MAXIMUM_LENGTH'] > static::FULLTEXT_MAXIMUM_LENGTH
						)
						{
							$maximumLength = static::FULLTEXT_MAXIMUM_LENGTH;
						}
						else
						{
							$maximumLength = $fieldInfo['CHARACTER_MAXIMUM_LENGTH'];
						}
					}

					if ($maximumLength)
					{
						$field->configureSize($maximumLength);
					}
				}

				$this->tableColumnsCache[$tableName][$fieldName] = $field;
			}
		}

		return $this->tableColumnsCache[$tableName];
	}

	/**
	 * @inheritDoc
	 */
	public function createTable($tableName, $fields, $primary = [], $autoincrement = [])
	{
		$sql = 'CREATE TABLE IF NOT EXISTS ' . $this->getSqlHelper()->quote($tableName) . ' (';
		$sqlFields = [];

		foreach ($fields as $columnName => $field)
		{
			if (!($field instanceof ScalarField))
			{
				throw new ArgumentException(sprintf(
					'Field `%s` should be an Entity\ScalarField instance', $columnName
				));
			}

			$realColumnName = $field->getColumnName();

			if (in_array($columnName, $autoincrement, true))
			{
				$type = 'INT GENERATED BY DEFAULT AS IDENTITY'; // size = 4

				if ($field instanceof IntegerField)
				{
					switch ($field->getSize())
					{
						case 2:
							$type = 'SMALLINT GENERATED BY DEFAULT AS IDENTITY';
							break;
						case 8:
							$type = 'BIGINT GENERATED BY DEFAULT AS IDENTITY';
							break;
					}
				}
			}
			else
			{
				$type = $this->getSqlHelper()->getColumnTypeByField($field);
			}
			$sqlFields[] = $this->getSqlHelper()->quote($realColumnName)
				. ' ' . $type
				. ($field->isNullable() ? '' : ' NOT NULL');
		}

		$sql .= join(', ', $sqlFields);

		if (!empty($primary))
		{
			foreach ($primary as &$primaryColumn)
			{
				$realColumnName = $fields[$primaryColumn]->getColumnName();
				$primaryColumn = $this->getSqlHelper()->quote($realColumnName);
			}

			$sql .= ', PRIMARY KEY(' . join(', ', $primary) . ')';
		}

		$sql .= ')';

		$this->query($sql);
	}

	/**
	 * @inheritDoc
	 */
	public function createIndex($tableName, $indexName, $columnNames, $columnLengths = null, $indexType = null)
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

		if ($indexType === static::INDEX_UNIQUE)
		{
			return $this->query('CREATE UNIQUE INDEX IF NOT EXISTS ' . $sqlHelper->quote($indexName) . ' ON ' . $sqlHelper->quote($tableName) . '(' . implode(',', $columnNames) . ')');
		}
		elseif ($indexType === static::INDEX_FULLTEXT)
		{
			return $this->query('CREATE INDEX IF NOT EXISTS ' . $sqlHelper->quote($indexName) . ' ON ' . $sqlHelper->quote($tableName) . ' USING GIN (to_tsvector(\'english\', ' . implode(',', $columnNames) . '))');
		}
		else
		{
			return $this->query('CREATE INDEX IF NOT EXISTS ' . $sqlHelper->quote($indexName) . ' ON ' . $sqlHelper->quote($tableName) . '(' . implode(',', $columnNames) . ')');
		}
	}

	/**
	 * @inheritDoc
	 */
	public function renameTable($currentName, $newName)
	{
		$this->query('ALTER TABLE ' . $this->getSqlHelper()->quote($currentName) . ' RENAME TO ' . $this->getSqlHelper()->quote($newName));
	}

	/**
	 * @inheritDoc
	 */
	public function dropTable($tableName)
	{
		$this->query('DROP TABLE ' . $this->getSqlHelper()->quote($tableName));
	}

	/**
	 * @inheritDoc
	 */
	public function startTransaction()
	{
		if ($this->transactionLevel == 0)
		{
			$this->query("START TRANSACTION");
		}
		else
		{
			$this->query("SAVEPOINT TRANS{$this->transactionLevel}");
		}

		$this->transactionLevel++;
	}

	/**
	 * @inheritDoc
	 */
	public function commitTransaction()
	{
		$this->transactionLevel--;

		if ($this->transactionLevel < 0)
		{
			throw new TransactionException('Transaction was not started.');
		}

		if ($this->transactionLevel == 0)
		{
			// commits all nested transactions
			$this->query("COMMIT");
		}
	}

	/**
	 * @inheritDoc
	 */
	public function rollbackTransaction()
	{
		$this->transactionLevel--;

		if ($this->transactionLevel < 0)
		{
			throw new TransactionException('Transaction was not started.');
		}

		if ($this->transactionLevel == 0)
		{
			$this->query("ROLLBACK");
		}
		else
		{
			$this->query("ROLLBACK TO SAVEPOINT TRANS{$this->transactionLevel}");
		}
	}

	/*********************************************************
	 * Global named lock
	 *********************************************************/

	/**
	 * @inheritDoc
	 */
	public function lock($name, $timeout = 0)
	{
		$timeout = (int)$timeout;
		$name = $this->getLockName($name);

		$sql = 'SELECT bx_get_lock(' . $name . ', ' . $timeout . ') as L';
		$lock = $this->query($sql)->fetch();

		return ($lock['L'] == 0);
	}

	/**
	 * @inheritDoc
	 */
	public function unlock($name)
	{
		$name = $this->getLockName($name);

		$sql = 'SELECT bx_release_lock(' . $name . ') as L';
		$lock = $this->query($sql)->fetch();

		return ($lock['L'] == 0);
	}

	protected function getLockName($name)
	{
		$unique = \CMain::GetServerUniqID();

		return crc32($unique . '|' . $name);
	}

	/**
	 * @inheritDoc
	 */
	public function getType()
	{
		return "pgsql";
	}

	/**
	 * @inheritDoc
	 */
	public function getVersion()
	{
		if ($this->version == null)
		{
			$this->connectInternal();
			$version = trim(pg_version($this->resource)['server']);

			preg_match("#^.*?([0-9]+\\.[0-9]+)#", $version, $ar);
			$this->version = $ar[1];
		}

		return [$this->version, null];
	}

	/**
	 * @inheritDoc
	 */
	public function getErrorMessage()
	{
		return pg_last_error($this->resource);
	}

	/**
	 * @inheritDoc
	 */
	public function getErrorCode()
	{
		if (preg_match("/ERROR:\\s*([^:]+):/i", $this->getErrorMessage(), $matches))
		{
			return $matches[1];
		}
		return '';
	}

	protected function configureErrorVerbosity()
	{
		pg_set_error_verbosity($this->resource, PGSQL_ERRORS_VERBOSE);
	}

	/**
	 * @inheritdoc
	 */
	public function createQueryException($code = '', $databaseMessage = '', $query = '')
	{
		if ($code == '23505')
		{
			return new DuplicateEntryException('Pgsql query error', $databaseMessage, $query);
		}
		return new SqlQueryException('Pgsql query error', $databaseMessage, $query);
	}
}
