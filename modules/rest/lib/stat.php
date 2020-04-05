<?php
namespace Bitrix\Rest;

use Bitrix\Main;

/**
 * Class StatTable
 *
 * Fields:
 * <ul>
 * <li> STAT_DATE date mandatory
 * <li> APP_ID int mandatory
 * <li> METHOD_ID int mandatory
 * <li> HOUR_0 int mandatory
 * <li> HOUR_1 int mandatory
 * <li> HOUR_2 int mandatory
 * <li> HOUR_3 int mandatory
 * <li> HOUR_4 int mandatory
 * <li> HOUR_5 int mandatory
 * <li> HOUR_6 int mandatory
 * <li> HOUR_7 int mandatory
 * <li> HOUR_8 int mandatory
 * <li> HOUR_9 int mandatory
 * <li> HOUR_10 int mandatory
 * <li> HOUR_11 int mandatory
 * <li> HOUR_12 int mandatory
 * <li> HOUR_13 int mandatory
 * <li> HOUR_14 int mandatory
 * <li> HOUR_15 int mandatory
 * <li> HOUR_16 int mandatory
 * <li> HOUR_17 int mandatory
 * <li> HOUR_18 int mandatory
 * <li> HOUR_19 int mandatory
 * <li> HOUR_20 int mandatory
 * <li> HOUR_21 int mandatory
 * <li> HOUR_22 int mandatory
 * <li> HOUR_23 int mandatory
 * </ul>
 *
 * @package Bitrix\Rest
 **/

class StatTable extends Main\Entity\DataManager
{
	protected static $data = array();

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_stat';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'STAT_DATE' => array(
				'data_type' => 'date',
				'primary' => true,
			),
			'APP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'METHOD_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'HOUR_0' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_1' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_2' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_3' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_4' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_5' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_6' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_7' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_8' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_9' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_10' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_11' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_12' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_13' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_14' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_15' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_16' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_17' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_18' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_19' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_20' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_21' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_22' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'HOUR_23' => array(
				'data_type' => 'integer',
				'required' => true,
			),
		);
	}

	public static function log(\CRestServer $server)
	{
		if(Main\ModuleManager::isModuleInstalled('oauth'))
		{
			return;
		}

		if($server->getClientId())
		{
			if(!array_key_exists($server->getClientId(), static::$data))
			{
				static::$data[$server->getClientId()] = array($server->getMethod() => 1);
			}
			elseif(!array_key_exists($server->getMethod(), static::$data[$server->getClientId()]))
			{
				static::$data[$server->getClientId()][$server->getMethod()] = 1;
			}
			else
			{
				static::$data[$server->getClientId()][$server->getMethod()]++;
			}
		}
	}

	public static function finalize()
	{
		if(Main\ModuleManager::isModuleInstalled('oauth'))
		{
			return;
		}

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$hour = intval(date('G'));
		$curDateSql = new Main\DB\SqlExpression($helper->getCurrentDateFunction());

		foreach(static::$data as $clientId => $stat)
		{
			StatMethodTable::checkList(array_keys($stat));

			$appInfo = AppTable::getByClientId($clientId);

			foreach($stat as $method => $count)
			{
				$methodId = StatMethodTable::getId($method);
				if($methodId > 0)
				{
					$insertFields = array(
						'STAT_DATE' => $curDateSql,
						'APP_ID' => $appInfo['ID'],
						'METHOD_ID' => $methodId,
						'HOUR_'.$hour => $count,
					);

					$updateFields = array(
						'HOUR_'.$hour => new Main\DB\SqlExpression('?#+?i', 'HOUR_'.$hour, $count)
					);

					$queries = $helper->prepareMerge(
						static::getTableName(),
						array('DATE', 'APP_ID', 'METHOD_ID'),
						$insertFields,
						$updateFields
					);

					foreach($queries as $query)
					{
						$connection->queryExecute($query);
					}
				}
			}
		}
	}
}