<?php
namespace Bitrix\Rest;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;

/**
 * Class UsageStatTable
 *
 * Fields:
 * <ul>
 * <li> STAT_DATE date mandatory
 * <li> ENTITY_ID int mandatory
 * <li> IS_SENT bool optional default 'N'
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
class UsageStatTable extends Main\Entity\DataManager
{

	const SEND_STATISTIC_URL = '';//todo: set current url

	protected static $data = array();

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_usage_stat';
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
				'primary' => true
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'IS_SENT' => array(
				'data_type' => 'boolean',
				'values' => array(
					'N',
					'Y'
				)
			),
			'HOUR_0' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_1' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_2' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_3' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_4' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_5' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_6' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_7' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_8' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_9' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_10' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_11' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_12' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_13' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_14' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_15' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_16' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_17' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_18' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_19' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_20' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_21' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_22' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'HOUR_23' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'ENTITY' => array(
				'data_type' => 'Bitrix\Rest\UsageEntityTable',
				'reference' => array(
					'=this.ENTITY_ID' => 'ref.ID',
				),
			),
		);
	}

	public static function log(\CRestServer $server)
	{
		if (Main\ModuleManager::isModuleInstalled('oauth'))
		{
			return;
		}

		if ($server->getClientId())
		{
			static::logMethod($server->getClientId(), $server->getMethod());
		}
		elseif ($server->getPasswordId())
		{
			static::logHookMethod($server->getPasswordId(), $server->getMethod());
		}
	}

	public static function logHookMethod($passwordID, $methodName)
	{
		static::increment(UsageEntityTable::ENTITY_TYPE_WEBHOOK, $passwordID, UsageEntityTable::SUB_ENTITY_TYPE_METHOD, $methodName);
	}

	public static function logMethod($clientId, $methodName)
	{
		static::increment(UsageEntityTable::ENTITY_TYPE_APPLICATION, $clientId, UsageEntityTable::SUB_ENTITY_TYPE_METHOD, $methodName);
	}

	public static function logEvent($clientId, $eventName)
	{
		static::increment(UsageEntityTable::ENTITY_TYPE_APPLICATION, $clientId, UsageEntityTable::SUB_ENTITY_TYPE_EVENT, $eventName);
	}

	public static function logPlacement($clientId, $placementName)
	{
		static::increment(UsageEntityTable::ENTITY_TYPE_APPLICATION, $clientId, UsageEntityTable::SUB_ENTITY_TYPE_PLACEMENT, $placementName);
	}

	public static function logRobot($clientId, $clientCode)
	{
		static::increment(UsageEntityTable::ENTITY_TYPE_APPLICATION, $clientId, UsageEntityTable::SUB_ENTITY_TYPE_ROBOT, $clientCode);
	}

	public static function logActivity($clientId, $clientCode)
	{
		static::increment(UsageEntityTable::ENTITY_TYPE_APPLICATION, $clientId, UsageEntityTable::SUB_ENTITY_TYPE_ACTIVITY, $clientCode);
	}

	public static function logConfiguration($clientId, $clientCode)
	{
		static::increment(
			UsageEntityTable::ENTITY_TYPE_APPLICATION,
			$clientId,
			UsageEntityTable::SUB_ENTITY_TYPE_CONFIGURATION,
			$clientCode
		);
	}

	protected static function increment($entityType, $entityId, $subEntityType, $subEntityName)
	{
		$entityKey = $entityType."|".$entityId."|".$subEntityType."|".$subEntityName;
		if (!isset(static::$data[$entityKey]))
		{
			static::$data[$entityKey] = 0;
		}
		static::$data[$entityKey]++;
	}

	public static function finalize()
	{
		if (Main\ModuleManager::isModuleInstalled('oauth'))
		{
			return;
		}

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$hour = intval(date('G'));
		$curDateSql = new Main\Type\Date();

		ksort(static::$data);
		foreach (static::$data as $entityKey => $count)
		{
			list($entityType, $entityId, $subEntityType, $subEntityName) = explode("|", $entityKey, 4);
			try
			{
				$statId = UsageEntityTable::register($entityType, $entityId, $subEntityType, $subEntityName);
			}
			catch (SqlQueryException $e)
			{
				$statId = false;
			}

			if ($statId)
			{
				$insertFields = array(
					'STAT_DATE' => $curDateSql,
					'ENTITY_ID' => $statId,
					'HOUR_'.$hour => $count,
				);
				$updateFields = array(
					'HOUR_'.$hour => new Main\DB\SqlExpression('?#+?i', 'HOUR_'.$hour, $count)
				);

				$queries = $helper->prepareMerge(static::getTableName(), array(
						'STAT_DATE',
						'ENTITY_ID'
					), $insertFields, $updateFields);

				foreach ($queries as $query)
				{
					$connection->queryExecute($query);
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
	 * @param array $fields
	 */
	public static function updateByFilter(array $filter, array $fields)
	{
		$entity = static::getEntity();
		$sqlHelper = $entity->getConnection()->getSqlHelper();
		$sqlTableName = static::getTableName();

		$update = $sqlHelper->prepareUpdate($sqlTableName, $fields);
		$where = Main\Entity\Query::buildFilterSql($entity, $filter);
		if ($where <> '' && $update[0] <> '')
		{
			$sql = "UPDATE {$sqlTableName} SET $update[0] WHERE $where";
			$entity->getConnection()->queryExecute($sql);
		}
	}

	/**
	 * @param array $filter
	 */
	public static function deleteByFilter(array $filter)
	{
		$entity = static::getEntity();
		$sqlTableName = static::getTableName();

		$where = Main\Entity\Query::buildFilterSql($entity, $filter);
		if ($where <> '')
		{
			$sql = "DELETE FROM {$sqlTableName} WHERE ".$where;
			$entity->getConnection()->queryExecute($sql);
		}
	}

	public static function cleanUpAgent()
	{
		$date = new Main\Type\DateTime();
		$date->add("-60D");

		static::deleteByFilter(array(
			"<STAT_DATE" => $date,
			"=IS_SENT" => "Y",
		));

		return "\\Bitrix\\Rest\\UsageStatTable::cleanUpAgent();";
	}

	public static function sendAgent()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$sqlTableName = static::getTableName();

		$select = "
			SELECT MIN(STAT_DATE) STAT_DATE_MIN
			FROM {$sqlTableName}
			WHERE IS_SENT = 'N'
			AND (STAT_DATE < ".$helper->getCurrentDateFunction().")
		";
		$result = $connection->query($select);
		$date = $result->fetch();
		if($date && $date["STAT_DATE_MIN"])
		{
			if (static::sendDateStat($date["STAT_DATE_MIN"]))
			{
				static::updateByFilter(array("=STAT_DATE" => $date["STAT_DATE_MIN"]), array("IS_SENT" => "Y"));
			}
		}
		return "\\Bitrix\\Rest\\UsageStatTable::sendAgent();";
	}

	public static function sendDateStat($date)//todo: finish it
	{
		$return = true;

		$statList = static::getList(array(
			"select" => array(
				"ENTITY_ID" => "ENTITY_ID",
				"ENTITY_TYPE" => "ENTITY.ENTITY_TYPE",
				"ENTITY_CODE" => "ENTITY.ENTITY_CODE",
				"SUB_ENTITY_TYPE" => "ENTITY.SUB_ENTITY_TYPE",
				"SUB_ENTITY_NAME" => "ENTITY.SUB_ENTITY_NAME",
				"STAT_DATE",
				"HOUR_0",
				"HOUR_1",
				"HOUR_2",
				"HOUR_3",
				"HOUR_4",
				"HOUR_5",
				"HOUR_6",
				"HOUR_7",
				"HOUR_8",
				"HOUR_9",
				"HOUR_10",
				"HOUR_11",
				"HOUR_12",
				"HOUR_13",
				"HOUR_14",
				"HOUR_15",
				"HOUR_16",
				"HOUR_17",
				"HOUR_18",
				"HOUR_19",
				"HOUR_20",
				"HOUR_21",
				"HOUR_22",
				"HOUR_23",
			),
			"filter" => array(
				"=STAT_DATE" => $date,
			),
		));

		$usage = array();
		while ($dayStat = $statList->fetch())
		{
			if ($dayStat["ENTITY_CODE"] && $dayStat["STAT_DATE"])
			{
				$dayStat["STAT_DATE"] = $dayStat["STAT_DATE"]->format("Y-m-d");
				$usage[] = $dayStat;
			}
		}

		if ($usage)
		{
			$response = \Bitrix\Rest\OAuthService::getEngine()->getClient()->sendApplicationUsage($usage);
			$return = is_array($response) && $response['result'] === true;
		}

		return $return;
	}
}
