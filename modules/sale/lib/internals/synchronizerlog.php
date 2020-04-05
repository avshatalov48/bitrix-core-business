<?php


namespace Bitrix\Sale\Internals;


use Bitrix\Main\Application;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Exchange\Internals\Logger;
use Bitrix\Sale\Rest\Synchronization\LoggerDiag;

class SynchronizerLogTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_synchronizer_log';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
			'MESSAGE_ID' => array(
				'data_type' => 'text'
			),
			'MESSAGE' => array(
				'data_type' => 'text'
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'require' => true,
				'default_value' => function(){return new \Bitrix\Main\Type\DateTime();}
			)
		);
	}

	/**
	 * Clears old logging data
	 */
	public static function deleteOldRecords($direction)
	{
		$tableName = static::getTableName();

		$r = SynchronizerLogTable::getList(array(
			'select' => array(
				new ExpressionField('MAX_DATE_INSERT', 'MAX(%s)', array('DATE_INSERT'))
			)
		));

		if ($loggingRecord = $r->fetch())
		{
			if(strlen($loggingRecord['MAX_DATE_INSERT'])>0)
			{
				$maxDateInsert = $loggingRecord['MAX_DATE_INSERT'];
				$date = new DateTime($maxDateInsert);
				$interval = LoggerDiag::getInterval();
				$connection = Application::getConnection();
				$connection->queryExecute("delete from {$tableName} where DATE_INSERT < DATE_SUB('{$date->format("Y-m-d")}', INTERVAL {$interval} DAY)");
			}
		}
	}
}