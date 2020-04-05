<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Application;

class LogIndexTable extends Entity\DataManager
{
	const ITEM_TYPE_LOG = 'L';
	const ITEM_TYPE_COMMENT = 'LC';

	public static function getItemTypes()
	{
		return array(
			self::ITEM_TYPE_LOG,
			self::ITEM_TYPE_COMMENT
		);
	}

	public static function getTableName()
	{
		return 'b_sonet_log_index';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'LOG_ID' => array(
				'data_type' => 'integer',
			),
			'LOG_UPDATE' => array(
				'data_type' => 'datetime',
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
			),
			'ITEM_TYPE' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'ITEM_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'CONTENT' => array(
				'data_type' => 'text',
			),
		);

		return $fieldsMap;
	}

	public static function set($params = array())
	{
		$itemType = (isset($params['itemType']) ? $params['itemType'] : self::ITEM_TYPE_LOG);
		$itemId = (isset($params['itemId']) ? intval($params['itemId']) : 0);
		$logId = (isset($params['logId']) ? intval($params['logId']) : 0);
		$content = (isset($params['content']) ? trim($params['content']) : '');

		if (
			!in_array($itemType, self::getItemTypes())
			|| $itemId <= 0
			|| $logId <= 0
			|| empty($content)
		)
		{
			return false;
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$insertFields = array(
			"ITEM_TYPE" => $helper->forSql($itemType),
			"ITEM_ID" => $itemId,
			"LOG_ID" => $logId,
			"CONTENT" => $helper->forSql($content)
		);

		$updateFields = array(
			"CONTENT" => $helper->forSql($content)
		);

		if (
			isset($params['logDateUpdate'])
			&& $params['logDateUpdate'] instanceof \Bitrix\Main\Type\DateTime
		)
		{
			$insertFields["LOG_UPDATE"] = $params['logDateUpdate'];
			$updateFields["LOG_UPDATE"] = $params['logDateUpdate'];
		}

		if (
			isset($params['dateCreate'])
			&& $params['dateCreate'] instanceof \Bitrix\Main\Type\DateTime
		)
		{
			$insertFields["DATE_CREATE"] = $params['dateCreate'];
			$updateFields["DATE_CREATE"] = $params['dateCreate'];
		}

		$merge = $helper->prepareMerge(
			static::getTableName(),
			array("ITEM_TYPE", "ITEM_ID"),
			$insertFields,
			$updateFields
		);

		if ($merge[0] != "")
		{
			$connection->query($merge[0]);
		}

		return true;
	}

	public static function setLogUpdate($params = array())
	{
		$logId = (isset($params['logId']) ? intval($params['logId']) : 0);
		$value = (!empty($params['value']) ? $params['value'] : false);

		if ($logId <= 0)
		{
			return false;
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$now = $connection->getSqlHelper()->getCurrentDateTimeFunction();
		if (
			!$value
			|| strtolower($value) == strtolower($now)
		)
		{
			$value = new SqlExpression($now);
		}

		$updateFields = array(
			"LOG_UPDATE" => $value,
		);

		$tableName = self::getTableName();
		list($prefix, $values) = $helper->prepareUpdate($tableName, $updateFields);
		$connection->queryExecute("UPDATE {$tableName} SET {$prefix} WHERE `LOG_ID` = ".$logId);

		return true;
	}
}
