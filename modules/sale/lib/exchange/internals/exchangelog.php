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
 * @method static EO_ExchangeLog_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ExchangeLog_Result getById($id)
 * @method static EO_ExchangeLog_Result getList(array $parameters = [])
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
	public static function deleteAll(): void
	{
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->queryExecute('DELETE FROM ' . $helper->quote(static::getTableName()));
	}

	/**
	 * Clears old logging data
	 */
	public static function deleteOldRecords($direction, $provider, $interval): void
	{
		$direction = (string)$direction;
		$provider = (string)$provider;
		$interval = (int)$interval;
		if ($direction === '')
		{
			throw new Main\ArgumentOutOfRangeException('$direction');
		}
		if ($provider === '')
		{
			throw new Main\ArgumentOutOfRangeException('$provider');
		}

		$loggingRecord = ExchangeLogTable::getList([
			'select' => [
				new Main\Entity\ExpressionField('MAX_DATE_INSERT', 'MAX(%s)', ['DATE_INSERT'])
			],
			'filter' => [
				'=DIRECTION' => $direction,
				'=PROVIDER' => $provider,
			],
		])->fetch();

		if ($loggingRecord)
		{
			if ($loggingRecord['MAX_DATE_INSERT'] <> '')
			{
				$date = new Main\Type\DateTime($loggingRecord['MAX_DATE_INSERT']);
				$connection = Main\Application::getConnection();
				$helper = $connection->getSqlHelper();
				$connection->queryExecute("delete from " . $helper->quote(static::getTableName())
					. " where"
					. " DATE_INSERT < " . $helper->addDaysToDateTime(-$interval, "'" . $date->format('Y-m-d') . "'")
					. " and DIRECTION = '" . $helper->forSql($direction) . "'"
					. " and PROVIDER = '" . $helper->forSql($provider) . "'"
				);
			}
		}
	}
}