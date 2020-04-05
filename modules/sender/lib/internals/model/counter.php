<?php

namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\DB;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;


/**
 * Class CounterTable
 * @package Bitrix\Sender\Internals\Model
 **/
class CounterTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_counter';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'CODE' =>[
				'data_type' => 'string',
				'primary' => true,
				'required' => true,
			],
			'VALUE' => [
				'data_type' => 'integer',
				'required' => false
			],
			'DATE_UPDATE' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime(),
			),
		];
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
	 * Increment value.
	 *
	 * @param string $code Code.
	 * @param int $increment Increment.
	 * @return void
	 */
	public static function incrementByCode($code, $increment = 1)
	{
		$date = new DateTime();
		$insert = ['CODE' => $code, 'VALUE' => $increment, 'DATE_UPDATE' => $date];
		$update = [
			'VALUE' => new DB\SqlExpression("?# + ?i", 'VALUE', $increment),
			'DATE_UPDATE' => $date
		];

		static::mergeData($insert, $update);
	}

	/**
	 * Get value by code.
	 *
	 * @param string $code Code.
	 * @return int
	 */
	public static function getValueByCode($code)
	{
		$row = static::getRow(['filter' => ['=CODE' => $code], 'cache' => ['ttl' => 36000]]);
		return intval($row ? $row['VALUE'] : 0);
	}

	/**
	 * Get value by code.
	 *
	 * @param string $code Code.
	 * @return bool
	 */
	public static function resetValueByCode($code)
	{
		return static::delete(['CODE' => $code])->isSuccess();
	}
}