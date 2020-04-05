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

final class Helper extends CommonHelper
{
	public static function getSqlForAutoIncrement()
	{
		return 'IDENTITY(1,1)';
	}

	public static function mergeTables($toTable, $fromTable, $fldMap, $fldCondition)
	{
		$dbConnection = Main\HttpApplication::getConnection();
		$dbHelper = $dbConnection->getSqlHelper();

		$toTable = $dbHelper->forSql(trim($toTable));
		$fromTable = $dbHelper->forSql(trim($fromTable));

		if(!strlen($toTable) || !strlen($toTable) || !is_array($fldMap) || empty($fldMap) || empty($fldCondition))
			return false;

		// update tab1 set aa = tab2.bb, cc = tab2.dd from tab1 join tab2 on tab1.ee = tab2.ff

		$sql = 'update '.$toTable.' set ';
		$additFldCnt = count($fldMap);

		$fields = array();
		foreach($fldMap as $toFld => $fromFld)
		{
			$fields[] = $dbHelper->forSql(trim($toFld)).' = '.$fromTable.'.'.$dbHelper->forSql(trim($fromFld));
		}

		$sql .= implode(', ', $fields);

		$join = array();
		foreach($fldCondition as $left => $right)
			$join[] = $toTable.'.'.$dbHelper->forSql(trim($left)).' = '.$fromTable.'.'.$dbHelper->forSql(trim($right));

		$sql .= ' from '.$toTable.' join '.$fromTable.' on '.implode(' and ', $join);

		$dbConnection->query($sql);

		return true;
	}

	public static function checkIndexNameExists($indexName, $tableName)
	{
		$dbConnection = Main\HttpApplication::getConnection();
		$dbHelper = $dbConnection->getSqlHelper();

		$indexName = trim($indexName);
		$tableName = $dbHelper->forSql(trim($tableName));

		if(!strlen($indexName) || !strlen($tableName))
			return false;

		$res = $dbConnection->query("SELECT si.name Key_name
			FROM sysindexkeys s
				INNER JOIN syscolumns c ON s.id = c.id AND s.colid = c.colid
				INNER JOIN sysobjects o ON s.id = o.Id AND o.xtype = 'U'
				LEFT JOIN sysindexes si ON si.indid = s.indid AND si.id = s.id
			WHERE o.name = '".ToUpper($tableName)."'");

		while($item = $res->fetch())
		{
			if($item['Key_name'] == $indexName || $item['KEY_NAME'] == $indexName)
				return true;
		}

		return false;
	}

	public static function dropIndexByName($indexName, $tableName)
	{
		$dbConnection = Main\HttpApplication::getConnection();
		$dbHelper = $dbConnection->getSqlHelper();

		$indexName = $dbHelper->forSql(trim($indexName));
		$tableName = $dbHelper->forSql(trim($tableName));

		if(!strlen($indexName) || !strlen($tableName))
			return false;

		if(!static::checkIndexNameExists($indexName, $tableName))
			return false;

		$dbConnection->query("drop index {$indexName} on {$tableName}");

		return true;
	}

	public static function getMaxTransferUnit()
	{
		$dbConnection = Main\HttpApplication::getConnection();

		try
		{
			$sao = $dbConnection->query("EXEC sp_configure 'show advanced option'")->fetch();
			if($sao = ($sao['config_value'] == '0'))
			{
				$dbConnection->query("EXEC sp_configure 'show advanced option', '1'");
				$dbConnection->query("RECONFIGURE");
			}

			$mtu = $dbConnection->query("EXEC sp_configure 'network packet size'")->fetch();
			if($sao)
			{
				$dbConnection->query("EXEC sp_configure 'show advanced option', '0'");
				$dbConnection->query("RECONFIGURE");
			}

			if(!($mtu = intval($mtu['config_value'])))
				return 0;

			return $mtu;
		}
		catch(\Bitrix\Main\DB\SqlQueryException $e)
		{
			return 9999;
		}
	}

	public static function dropAutoIncrementRestrictions($tableName)
	{
		static::changeAutoIncrementRestrictions($tableName, false);
	}

	public static function restoreAutoIncrementRestrictions($tableName)
	{
		static::changeAutoIncrementRestrictions($tableName, true);
	}

	protected static function changeAutoIncrementRestrictions($tableName, $way)
	{
		$dbConnection = Main\HttpApplication::getConnection();
		$dbHelper = $dbConnection->getSqlHelper();

		Main\HttpApplication::getConnection()->query('SET IDENTITY_INSERT '.$dbHelper->forSql($tableName).' '.($way ? 'OFF' : 'ON'));
	}

	// this function is used to adjust auto_increment value of a table to a certain position
	public static function resetAutoIncrement($tableName, $startIndex = 1)
	{
		$startIndex = intval($startIndex);
		if($startIndex <= 0 || !strlen($tableName))
			return false;

		$dbConnection = Main\HttpApplication::getConnection();
		$dbHelper = $dbConnection->getSqlHelper();
		$dbName = $dbConnection->getDbName();

		$tableName = $dbHelper->forSql(trim($tableName));

		$dbConnection->query("DBCC CHECKIDENT('".$dbName.".dbo.".$tableName."', RESEED, ".($startIndex - 1).")");

		return true;
	}

	public static function getQuerySeparatorSql()
	{
		return "\nGO";
	}

	public static function needSelectFieldsInOrderByWhenDistinct()
	{
		return true;
	}
}