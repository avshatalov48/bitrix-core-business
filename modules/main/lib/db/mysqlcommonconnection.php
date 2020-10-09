<?php
namespace Bitrix\Main\DB;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Fields\ScalarField;

abstract class MysqlCommonConnection extends Connection
{
	const INDEX_UNIQUE = 'UNIQUE';
	const INDEX_FULLTEXT = 'FULLTEXT';
	const INDEX_SPATIAL = 'SPATIAL';

	protected $engine = "";

	/**
	 * @inheritDoc
	 */
	public function __construct(array $configuration)
	{
		parent::__construct($configuration);
		$this->engine = isset($configuration['engine']) ? $configuration['engine'] : "";
	}

	/**
	 * @inheritDoc
	 */
	public function isTableExists($tableName)
	{
		$tableName = preg_replace("/[^a-z0-9%_]+/i", "", $tableName);
		$tableName = trim($tableName);

		if ($tableName == '')
		{
			return false;
		}

		$result = $this->query("SHOW TABLES LIKE '".$this->getSqlHelper()->forSql($tableName)."'");

		return (bool) $result->fetch();
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
		if (!is_array($columns) || count($columns) <= 0)
			return null;

		$tableName = preg_replace("/[^a-z0-9_]+/i", "", $tableName);
		$tableName = trim($tableName);

		$rs = $this->query("SHOW INDEX FROM `".$this->getSqlHelper()->forSql($tableName)."`");
		if (!$rs)
			return null;

		$indexes = array();
		while ($ar = $rs->fetch())
		{
			$indexes[$ar["Key_name"]][$ar["Seq_in_index"] - 1] = $ar["Column_name"];
		}

		$columnsList = implode(",", $columns);
		foreach ($indexes as $indexName => $indexColumns)
		{
			ksort($indexColumns);
			$indexColumnList = implode(",", $indexColumns);
			if ($strict)
			{
				if ($indexColumnList === $columnsList)
					return $indexName;
			}
			else
			{
				if (mb_substr($indexColumnList, 0, mb_strlen($columnsList)) === $columnsList)
					return $indexName;
			}
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getTableFields($tableName)
	{
		if (!isset($this->tableColumnsCache[$tableName]))
		{
			$this->connectInternal();

			$sqlTableName = ($tableName{0} === '(')
				? $sqlTableName = $tableName.' AS xyz' // subquery
				: $sqlTableName = $this->getSqlHelper()->quote($tableName); // regular table name

			$query = $this->queryInternal("SELECT * FROM {$sqlTableName} LIMIT 0");

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
				. ' NOT NULL' // null for oracle if is not primary
				. (in_array($columnName, $autoincrement, true) ? ' AUTO_INCREMENT' : '')
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

		if ($this->engine)
		{
			$sql .= ' Engine='.$this->engine;
		}

		$this->query($sql);
	}

	/**
	 * Creates index on column(s)
	 * @api
	 *
	 * @param string          $tableName     Name of the table.
	 * @param string          $indexName     Name of the new index.
	 * @param string|string[] $columnNames   Name of the column or array of column names to be included into the index.
	 * @param string[]        $columnLengths Array of column names and maximum length for them.
	 * @param null            $indexType
	 *
	 * @return Result
	 * @throws SqlQueryException
	 */
	public function createIndex($tableName, $indexName, $columnNames, $columnLengths = null, $indexType = null)
	{
		if (!is_array($columnNames))
		{
			$columnNames = array($columnNames);
		}

		$sqlHelper = $this->getSqlHelper();

		foreach ($columnNames as &$columnName)
		{
			if (is_array($columnLengths) && isset($columnLengths[$columnName]) && $columnLengths[$columnName] > 0)
			{
				$maxLength = intval($columnLengths[$columnName]);
			}
			else
			{
				$maxLength = 0;
			}

			$columnName = $sqlHelper->quote($columnName);
			if ($maxLength > 0)
			{
				$columnName .= '('.$maxLength.')';
			}
		}
		unset($columnName);

		$indexTypeSql = '';

		if ($indexType !== null
			&& in_array(mb_strtoupper($indexType), [static::INDEX_UNIQUE, static::INDEX_FULLTEXT, static::INDEX_SPATIAL], true)
		)
		{
			$indexTypeSql = mb_strtoupper($indexType);
		}

		$sql = 'CREATE '.$indexTypeSql.' INDEX '.$sqlHelper->quote($indexName).' ON '.$sqlHelper->quote($tableName)
			.' ('.join(', ', $columnNames).')';

		return $this->query($sql);
	}

	/**
	 * @inheritDoc
	 */
	public function renameTable($currentName, $newName)
	{
		$this->query('RENAME TABLE '.$this->getSqlHelper()->quote($currentName).' TO '.$this->getSqlHelper()->quote($newName));
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
		$this->query("START TRANSACTION");
	}

	/**
	 * @inheritDoc
	 */
	public function commitTransaction()
	{
		$this->query("COMMIT");
	}

	/**
	 * @inheritDoc
	 */
	public function rollbackTransaction()
	{
		$this->query("ROLLBACK");
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

		$lock = $this->query("SELECT GET_LOCK('{$name}', {$timeout}) as L")->fetch();

		return ($lock["L"] == "1");
	}

	/**
	 * @inheritDoc
	 */
	public function unlock($name)
	{
		$name = $this->getLockName($name);

		$lock = $this->query("SELECT RELEASE_LOCK('{$name}') as L")->fetch();

		return ($lock["L"] == "1");
	}

	protected function getLockName($name)
	{
		$unique = \CMain::GetServerUniqID();

		//64 characters max for mysql 5.7+
		return $unique.md5($name);
	}

	/*********************************************************
	 * Type, version, cache, etc.
	 *********************************************************/

	/**
	 * Sets default storage engine for all consequent CREATE TABLE statements and all other relevant DDL.
	 * Storage engine read from .settings.php file. It is 'engine' key of the 'default' from the 'connections'.
	 *
	 * @return void
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public function setStorageEngine()
	{
		if ($this->engine)
		{
			$this->query("SET storage_engine = '".$this->engine."'");
		}
	}

	/**
	 * Selects the default database for database queries.
	 *
	 * @param string $database Database name.
	 * @return bool
	 */
	abstract public function selectDatabase($database);

	/**
	 * Returns max packet length to send to or receive from the database server.
	 *
	 * @return int
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public function getMaxAllowedPacket()
	{
		static $mtu;

		if (is_null($mtu))
		{
			$mtu = 0;

			$res = $this->query("SHOW VARIABLES LIKE 'max_allowed_packet'")->fetch();
			if ($res['Variable_name'] == 'max_allowed_packet')
			{
				$mtu = intval($res['Value']);
			}
		}

		return $mtu;
	}
}