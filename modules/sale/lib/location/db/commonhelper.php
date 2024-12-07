<?php
/**
 * Bitrix Framework
 * @package Bitrix\Sale\Location
 * @subpackage sale
 * @copyright 2001-2015 Bitrix
 *
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */

namespace Bitrix\Sale\Location\DB;

use Bitrix\Main;

abstract class CommonHelper
{
	public static function getSqlForDataType($type, $len = 0)
	{
		if($type == 'int')
			return 'int';

		if($type == 'varchar')
			return 'varchar('.(intval($len) ? intval($len) : '1').')';

		if($type == 'char')
			return 'char('.(intval($len) ? intval($len) : '1').')';

		return '';
	}

	public static function getBatchInsertHead($tableName, $fields = array())
	{
		$map = array();

		$dbConnection = Main\HttpApplication::getConnection();
		$dbHelper = $dbConnection->getSqlHelper();

		if(is_array($fields))
		{
			foreach($fields as $fld)
				$map[] = $dbHelper->forSql($fld);
		}

		return 'INSERT ' . ($dbConnection->getType() === 'pgsql' ? '' : 'IGNORE ') . 'INTO '.$dbHelper->forSql($tableName).' ('.implode(',', $map).') values ';
	}

	public static function getBatchInsertTail()
	{
		$dbConnection = Main\HttpApplication::getConnection();

		return $dbConnection->getType() === 'pgsql' ? ' ON CONFLICT DO NOTHING' : '';
	}

	public static function getBatchInsertSeparator()
	{
		return ', ';
	}

	public static function getBatchInsertValues($row, $tableName, $fields, $map)
	{
		return static::prepareSql($row, $fields, $map);
	}

	public static function getMaxTransferUnit()
	{
		return PHP_INT_MAX;
	}

	protected static function prepareSql($row, $fields, $map)
	{
		if (!is_array($row) || empty($row) || !is_array($fields) || empty($fields) || !is_array($map) || empty($map))
		{
			return '';
		}

		$connection = Main\HttpApplication::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		unset($connection);

		$sql = [];
		foreach ($fields as $fld => $none)
		{
			$val = $row[$fld];

			// only numeric and literal fields supported at the moment
			if (
				isset($map[$fld]['data_type'])
				&& $map[$fld]['data_type'] == 'integer'
			)
			{
				$sql[] = (int)$val;
			}
			else
			{
				$sql[] = "'" . $sqlHelper->forSql($val) . "'";
			}
		}

		return '(' . implode(',', $sql) . ')';
	}

	// makes sense only for mssql
	public static function dropAutoIncrementRestrictions($tableName)
	{
		return false;
	}

	// same
	public static function restoreAutoIncrementRestrictions($tableName)
	{
		return false;
	}

	// makes sense only for oracle
	public static function incrementSequenceForTable($tableName)
	{
		return false;
	}

	// makes sense only for oracle
	protected static function checkSequenceExistsForTable($tableName)
	{
		return false;
	}

	/*
	public static function addPrimaryKey($tableName, $columns = array())
	{
		if(!strlen($tableName) || !is_array($columns) || empty($columns))
			return false;

		$dbConnection = Main\HttpApplication::getConnection();
		$dbHelper = $dbConnection->getSqlHelper();

		$tableName = $dbHelper->forSql($tableName);
		$columns = static::escapeArray($columns);

		$dbConnection->query("ALTER TABLE ".$tableName." ADD CONSTRAINT PK_".ToUpper($tableName)." PRIMARY KEY (".implode(', ', $columns).")");

		return true;
	}
	*/

	// do nothing but for oracle
	public static function addAutoIncrement()
	{
		return false;
	}

	public static function createIndex($tableName, $ixNamePostfix, $columns = array(), $unique = false)
	{
		if(!mb_strlen($tableName) || !mb_strlen($ixNamePostfix) || !is_array($columns) || empty($columns))
			return false;

		$dbConnection = Main\HttpApplication::getConnection();
		$dbHelper = $dbConnection->getSqlHelper();

		$tableName = 		$dbHelper->forSql($tableName);
		$ixNamePostfix = 	$dbHelper->forSql($ixNamePostfix);
		$columns = 			static::escapeArray($columns);

		$ixName = static::getIndexName($tableName, $ixNamePostfix, $columns);

		if(mb_strlen($ixName) > 30)
			return false;

		if(!static::checkIndexNameExists($ixName, $tableName))
		{
			$dbConnection->query("CREATE ".($unique ? "UNIQUE" : "")." INDEX ".$ixName." ON ".$tableName." (".implode(', ', $columns).")");
			return true;
		}

		return false;
	}

	protected static function getIndexName($tableName, $ixNamePostfix, $columns = array())
	{
		return 'IX_'.preg_replace('#^B_#', '', mb_strtoupper($tableName))."_".mb_strtoupper($ixNamePostfix);
	}

	protected static function escapeArray($columns)
	{
		foreach($columns as &$col)
			$col = Main\HttpApplication::getConnection()->getSqlHelper()->forSql($col);

		return $columns;
	}

	public static function dropTable($tableName)
	{
		$dbConnection = Main\HttpApplication::getConnection();

		$tableName = $dbConnection->getSqlHelper()->forSql($tableName);

		if($dbConnection->isTableExists($tableName))
			Main\HttpApplication::getConnection()->query('drop table '.$tableName);
	}

	public static function checkTableExists($tableName)
	{
		return Main\HttpApplication::getConnection()->isTableExists($tableName);
	}

	public static function truncateTable($tableName)
	{
		$dbConnection = Main\HttpApplication::getConnection();

		$tableName = $dbConnection->getSqlHelper()->forSql($tableName);

		if($dbConnection->isTableExists($tableName))
			Main\HttpApplication::getConnection()->query('truncate table '.$tableName);
	}

	public static function getQuerySeparatorSql()
	{
	}

	public static function needSelectFieldsInOrderByWhenDistinct()
	{
		return true;
	}
}