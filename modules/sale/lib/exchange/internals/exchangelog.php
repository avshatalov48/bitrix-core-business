<?php

namespace Bitrix\Sale\Exchange\Internals;

use Bitrix\Main;
use Bitrix\Sale\Exchange\EntityType;

class ExchangeLogTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_exchange_log';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
			'ENTITY_ID' => array(
				'required' => true,
				'data_type' => 'integer'
			),
			'ENTITY_TYPE_ID' => array(
				'required' => true,
				'data_type' => 'integer'
			),
			'PARENT_ID' => array(
				'data_type' => 'integer'
			),
			'OWNER_ENTITY_ID' => array(
				'data_type' => 'integer'
			),
			'ENTITY_DATE_UPDATE' => array(
				'data_type' => 'datetime',
			),
			'XML_ID' => array(
				'data_type' => 'string'
			),
			'MARKED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'DESCRIPTION' => array(
				'data_type' => 'text'
			),
			'MESSAGE' => array(
				'data_type' => 'text'
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'require' => true,
				'default_value' => new Main\Type\DateTime()
			),
			'DIRECTION' => array(
				'data_type' => 'string',
				'require' => true,
				'values' => array('E', 'I')
			),
		);
	}

	/**
	 * Clears all logging data
	 */
	public static function deleteAll()
	{
		$tableName = static::getTableName();
		$connection = Main\Application::getConnection();
		$connection->queryExecute("DELETE FROM {$tableName}");
	}

	/**
	 * Clears old logging data
	 */
	public static function deleteOldRecords($direction)
	{
		$tableName = static::getTableName();

		if (strlen($direction)<= 0)
			throw new Main\ArgumentOutOfRangeException("$direction");

		$r = ExchangeLogTable::getList(array(
			'select' => array(
				new Main\Entity\ExpressionField('MAX_DATE_INSERT', 'MAX(%s)', array('DATE_INSERT'))
			),
			'filter' => array(
				'=DIRECTION'=>$direction
			)
		));

		if ($loggingRecord = $r->fetch())
		{
			if(strlen($loggingRecord['MAX_DATE_INSERT'])>0)
			{
				$maxDateInsert = $loggingRecord['MAX_DATE_INSERT'];
				$date = new Main\Type\DateTime($maxDateInsert);
				$interval = Logger::getInterval();
				$connection = Main\Application::getConnection();
				$connection->queryExecute("delete from {$tableName} where DATE_INSERT < DATE_SUB('{$date->format("Y-m-d")}', INTERVAL {$interval} DAY) and DIRECTION='{$direction}'");
			}
		}
	}
}