<?php

namespace Bitrix\Sale\Exchange\Internals;

use Bitrix\Main;
use Bitrix\Sale\Exchange\EntityType;

/**
 * Class ExchangeLogTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ExchangeLog_Query query()
 * @method static EO_ExchangeLog_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ExchangeLog_Result getById($id)
 * @method static EO_ExchangeLog_Result getList(array $parameters = array())
 * @method static EO_ExchangeLog_Entity getEntity()
 * @method static \Bitrix\Sale\Exchange\Internals\EO_ExchangeLog createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Exchange\Internals\EO_ExchangeLog_Collection createCollection()
 * @method static \Bitrix\Sale\Exchange\Internals\EO_ExchangeLog wakeUpObject($row)
 * @method static \Bitrix\Sale\Exchange\Internals\EO_ExchangeLog_Collection wakeUpCollection($rows)
 */
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
				'default_value' => function(){return new \Bitrix\Main\Type\DateTime();}
			),
			'DIRECTION' => array(
				'data_type' => 'string',
				'require' => true,
				'values' => array('E', 'I')
			),
			'PROVIDER' => array(
				'data_type' => 'string'
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
	public static function deleteOldRecords($direction, $provider, $interval)
	{
		$tableName = static::getTableName();

		if ($direction == '')
			throw new Main\ArgumentOutOfRangeException("$direction");
		if ($provider == '')
			throw new Main\ArgumentOutOfRangeException("$provider");

		$r = ExchangeLogTable::getList(array(
			'select' => array(
				new Main\Entity\ExpressionField('MAX_DATE_INSERT', 'MAX(%s)', array('DATE_INSERT'))
			),
			'filter' => array(
				'=DIRECTION'=>$direction,
				'=PROVIDER'=>$provider
			)
		));

		if ($loggingRecord = $r->fetch())
		{
			if($loggingRecord['MAX_DATE_INSERT'] <> '')
			{
				$maxDateInsert = $loggingRecord['MAX_DATE_INSERT'];
				$date = new Main\Type\DateTime($maxDateInsert);
				$connection = Main\Application::getConnection();
				$connection->queryExecute("delete from {$tableName} where DATE_INSERT < DATE_SUB('{$date->format("Y-m-d")}', INTERVAL {$interval} DAY) and DIRECTION='{$direction}' and PROVIDER='{$provider}'");
			}
		}
	}
}