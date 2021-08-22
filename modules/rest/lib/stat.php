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
 * <li> PASSWORD_ID int mandatory
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

/**
 * Class StatTable
 * @deprecated
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Stat_Query query()
 * @method static EO_Stat_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Stat_Result getById($id)
 * @method static EO_Stat_Result getList(array $parameters = array())
 * @method static EO_Stat_Entity getEntity()
 * @method static \Bitrix\Rest\EO_Stat createObject($setDefaultValues = true)
 * @method static \Bitrix\Rest\EO_Stat_Collection createCollection()
 * @method static \Bitrix\Rest\EO_Stat wakeUpObject($row)
 * @method static \Bitrix\Rest\EO_Stat_Collection wakeUpCollection($rows)
 */
class StatTable extends Main\Entity\DataManager
{
	const STORE_PERIOD = 5184000; // 60*24*3600

	protected static $data = array();
	protected static $dataPassword = array();

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
			'PASSWORD_ID' => array(
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
			'APP' => array(
				'data_type' => 'Bitrix\Rest\StatAppTable',
				'reference' => array(
					'=this.APP_ID' => 'ref.APP_ID',
				),
			),
			'METHOD' => array(
				'data_type' => 'Bitrix\Rest\StatMethodTable',
				'reference' => array(
					'=this.METHOD_ID' => 'ref.ID',
				),
			),
			'PASSWORD' => array(
				'data_type' => '\Bitrix\Rest\APAuth\PasswordTable',
				'reference' => array(
					'=this.PASSWORD_ID' => 'ref.ID',
				),
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
			static::logMethod($server->getClientId(), $server->getMethod());
		}
		elseif($server->getPasswordId())
		{
			static::logApMethod($server->getPasswordId(), $server->getMethod());
		}
	}

	public static function logApMethod($passwordID, $methodName)
	{
		static::addApToLog($passwordID, $methodName, StatMethodTable::METHOD_TYPE_METHOD);
	}

	public static function logMethod($clientId, $methodName)
	{
		static::addToLog($clientId, $methodName, StatMethodTable::METHOD_TYPE_METHOD);
	}

	public static function logEvent($clientId, $eventName)
	{
		static::addToLog($clientId, $eventName, StatMethodTable::METHOD_TYPE_EVENT);
	}

	public static function logPlacement($clientId, $placementName)
	{
		static::addToLog($clientId, $placementName, StatMethodTable::METHOD_TYPE_PLACEMENT);
	}

	public static function logRobot($clientId)
	{
		static::addToLog($clientId, 'ROBOT', StatMethodTable::METHOD_TYPE_ROBOT);
	}

	public static function logActivity($clientId)
	{
		static::addToLog($clientId, 'ACTIVITY', StatMethodTable::METHOD_TYPE_ACTIVITY);
	}

	protected static function addApToLog($passwordID, $methodName, $methodType)
	{
		if (!isset(static::$dataPassword[$passwordID]))
		{
			static::$dataPassword[$passwordID] = array();
		}

		if (!isset(static::$dataPassword[$passwordID][$methodName]))
		{
			static::$dataPassword[$passwordID][$methodName] = 0;
		}

		static::$dataPassword[$passwordID][$methodName]++;
	}

	protected static function addToLog($clientId, $methodName, $methodType)
	{
		if (!isset(static::$data[$clientId]))
		{
			static::$data[$clientId] = array();
		}

		if (!isset(static::$data[$clientId][$methodName]))
		{
			static::$data[$clientId][$methodName] = 0;
		}

		static::$data[$clientId][$methodName]++;
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
		$curDateSql = new Main\Type\Date();
		if(count(static::$data) > 0)
		{
			foreach(static::$data as $clientId => $stat)
				$appInfo = AppTable::getByClientId($clientId);
			{
				if($appInfo)
				{
					StatAppTable::register($appInfo);
					foreach($stat as $methodName => $count)
					{
						$methodId = StatMethodTable::getId($methodName);
						if (!$methodId)
						{
							continue;
						}

						$insertFields = array(
							'STAT_DATE' => $curDateSql,
							'APP_ID' => $appInfo['ID'],
							'METHOD_ID' => $methodId,
							'HOUR_'.$hour => $count,
							'PASSWORD_ID' => 0
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

		if(count(static::$dataPassword) > 0)
		{
			foreach(static::$dataPassword as $passwordID => $stat)
			{

				foreach ($stat as $methodName => $count)
				{
					$methodId = StatMethodTable::getId($methodName);
					if (!$methodId)
					{
						continue;
					}

					$insertFields = array(
						'STAT_DATE' => $curDateSql,
						'PASSWORD_ID' => $passwordID,
						'METHOD_ID' => $methodId,
						'HOUR_' . $hour => $count,
						'APP_ID' => 0
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

		static::reset();
	}

	public static function reset()
	{
		static::$data = array();
	}

	/**
	 * @param array $filter
	 */
	public static function deleteByFilter(array $filter)
	{
		$entity = static::getEntity();
		$sqlTableName = static::getTableName();

		$where = Main\Entity\Query::buildFilterSql($entity, $filter);
		if($where <> '')
		{
			$sql = "DELETE FROM {$sqlTableName} WHERE ".$where;
			$entity->getConnection()->queryExecute($sql);
		}
	}

	/**
	 * @param array $filter
	 * @param array $fields
	 */
	public static function updateByFilter(array $filter, array $fields)
	{
		$entity = static::getEntity();
		$sqlHelper = $entity->getConnection()->getSqlHelper();
		$sqlTableName = static::getTableName();

		$update = $sqlHelper->prepareUpdate($sqlTableName, $fields);
		$where = Main\Entity\Query::buildFilterSql($entity, $filter);
		if($where <> '' && $update[0] <> '')
		{
			$sql = "UPDATE {$sqlTableName} SET $update[0] WHERE $where";
			$entity->getConnection()->queryExecute($sql);
		}
	}

	public static function cleanUpAgent()
	{
		$date = new Main\Type\DateTime();
		$date->add("-60D");

		static::deleteByFilter(array(
			"<STAT_DATE" => $date,
		));

		return "\\Bitrix\\Rest\\StatTable::cleanUpAgent();";
	}
}
