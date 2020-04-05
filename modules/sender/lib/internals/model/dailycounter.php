<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage bitrix24
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;
use Bitrix\Main\DB;

/**
 * Class DailyCounterTable
 * @package Bitrix\Sender\Internals\Model
 */
class DailyCounterTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_counter_daily';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'DATE_STAT' => array(
				'data_type' => 'date',
				'primary' => true,
			),
			'SENT_CNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'TEST_SENT_CNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'ERROR_CNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'ABUSE_CNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
		);
	}

	/**
	 * Merge data.
	 *
	 * @param array $insert Insert data.
	 * @param array $update Update data.
	 * @return void
	 */
	public static function mergeData(array $insert, array $update)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();
		$helper = $connection->getSqlHelper();

		$sql = $helper->prepareMerge($entity->getDBTableName(), $entity->getPrimaryArray(), $insert, $update);

		$sql = current($sql);
		if($sql <> '')
		{
			$connection->queryExecute($sql);
			$entity->cleanCache();
		}
	}

	/**
	 * Increment field value.
	 *
	 * @param string $fieldName Field name.
	 * @param int $increment Increment.
	 * @return void
	 */
	public static function incrementFieldValue($fieldName, $increment = 1)
	{
		if (!array_key_exists($fieldName, static::getMap()))
		{
			return;
		}

		$insert = array(
			"DATE_STAT" => new Type\Date(),
			$fieldName => $increment,
		);


		$update = array(
			$fieldName => new DB\SqlExpression("?# + ?i", $fieldName, $increment),
		);

		static::mergeData($insert, $update);
	}

	/**
	 * Get current field value.
	 *
	 * @param string $fieldName Field name.
	 * @return int
	 */
	public static function getCurrentFieldValue($fieldName)
	{
		if (!array_key_exists($fieldName, static::getMap()))
		{
			return 0;
		}

		$result = static::getRowByDate();
		return ($result && isset($result[$fieldName])) ? (int) $result[$fieldName] : 0;
	}

	/**
	 * Get row by days left.
	 *
	 * @param integer $daysLeft Days left.
	 * @return array|null
	 */
	public static function getRowByDate($daysLeft = 0)
	{
		$date = new Type\Date;
		if ($daysLeft)
		{
			$date->add("-$daysLeft day");
		}

		return static::getRow(array(
			"filter" => array("=DATE_STAT" => $date),
			"cache" => array("ttl" => 60)
		));
	}
}
