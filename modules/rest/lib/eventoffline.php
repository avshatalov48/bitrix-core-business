<?php
namespace Bitrix\Rest;

use Bitrix\Main;

/**
 * Class EventOfflineTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime optional default 'CURRENT_TIMESTAMP'
 * <li> MESSAGE_ID varchar(100) mandatory
 * <li> APP_ID int mandatory
 * <li> EVENT_NAME string(255) mandatory
 * <li> EVENT_DATA string optional
 * <li> PROCESS_ID string(255) optional
 * <li> CONNECTOR_ID string(255) optional
 * </ul>
 *
 * @package Bitrix\Rest
 **/

class EventOfflineTable extends Main\Entity\DataManager
{
	const PROCESS_ID_LIFETIME = 2952000; // 30 days

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_event_offline';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
			),
			'MESSAGE_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'APP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'EVENT_NAME' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'EVENT_DATA' => array(
				'data_type' => 'text',
				'serialized' => true,
			),
			'EVENT_ADDITIONAL' => array(
				'data_type' => 'text',
				'serialized' => true,
			),
			'PROCESS_ID' => array(
				'data_type' => 'string',
			),
			'CONNECTOR_ID' => array(
				'data_type' => 'string',
			),
			'ERROR' => array(
				'data_type' => 'integer',
			),
		);
	}

	public static function cleanProcessAgent()
	{
		$connection = Main\Application::getConnection();

		$tableName = static::getTableName();
		$dateTime = $connection->getSqlHelper()->addSecondsToDateTime('-' . static::PROCESS_ID_LIFETIME);

		$sql = "DELETE FROM {$tableName} WHERE PROCESS_ID<>'' AND TIMESTAMP_X<{$dateTime}";

		$connection->query($sql);

		return "\\Bitrix\\Rest\\EventOfflineTable::cleanProcessAgent();";
	}

	public static function callEvent($fields)
	{
		if(!isset($fields['CONNECTOR_ID']))
		{
			$fields['CONNECTOR_ID'] = '';
		}

		$addFields = array(
			'TIMESTAMP_X' => new Main\Type\DateTime(),
			'MESSAGE_ID' => static::getMessageId($fields),
			'APP_ID' => $fields['APP_ID'],
			'EVENT_NAME' => $fields['EVENT_NAME'],
			'EVENT_DATA' => serialize($fields['EVENT_DATA']),
			'EVENT_ADDITIONAL' => serialize($fields['EVENT_ADDITIONAL']),
			'CONNECTOR_ID' => $fields['CONNECTOR_ID'],
		);

		$updateFields = array(
			'TIMESTAMP_X' => new Main\Type\DateTime(),
			'EVENT_DATA' => serialize($fields['EVENT_DATA']),
			'EVENT_ADDITIONAL' => serialize($fields['EVENT_ADDITIONAL']),
		);

		if(array_key_exists('ERROR', $fields))
		{
			$addFields['ERROR'] = intval($fields['ERROR']) > 0 ? 1 : 0;
			$updateFields['ERROR'] = intval($fields['ERROR']) > 0 ? 1 : 0;
		}

		$connection = Main\Application::getConnection();
		$queries = $connection->getSqlHelper()->prepareMerge(
			static::getTableName(),
			array('MESSAGE_ID', 'APP_ID', 'CONNECTOR_ID', 'PROCESS_ID'),
			$addFields,
			$updateFields
		);

		foreach($queries as $query)
		{
			$connection->queryExecute($query);
		}
	}

	public static function markEvents($filter, $order, $limit)
	{
		$processId = static::getProcessId();

		$limit = intval($limit);

		$query = new EventOfflineQuery(static::getEntity());
		$query->setOrder($order);
		$query->setFilter($filter);
		$query->setLimit($limit);

		$sql = $query->getMarkQuery($processId);

		Main\Application::getConnection()->query($sql);

		return $processId;
	}

	public static function clearEvents($processId, $appId, $connectorId, $listIds = false)
	{
		$connection = Main\Application::getConnection();

		$tableName = static::getTableName();
		$processId = $connection->getSqlHelper()->forSql($processId);
		$appId = intval($appId);
		$connectorId = $connection->getSqlHelper()->forSql($connectorId);

		$sql = "DELETE FROM {$tableName} WHERE PROCESS_ID='{$processId}' AND APP_ID='{$appId}' AND CONNECTOR_ID='{$connectorId}'";

		if($listIds !== false)
		{
			array_map('intval', $listIds);
			$sql .= " AND ID IN ('".implode("', '", $listIds)."')";
		}

		$connection->query($sql);
	}

	public static function clearEventsByMessageId($processId, $appId, $connectorId, $listIds = false)
	{
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$tableName = static::getTableName();
		$processId = $connection->getSqlHelper()->forSql($processId);
		$appId = intval($appId);
		$connectorId = $connection->getSqlHelper()->forSql($connectorId);

		$sql = "DELETE FROM {$tableName} WHERE PROCESS_ID='{$processId}' AND APP_ID='{$appId}' AND CONNECTOR_ID='{$connectorId}'";

		if($listIds !== false)
		{
			foreach($listIds as $key => $id)
			{
				$listIds[$key] = $helper->forSql($id);
			}

			$sql .= " AND MESSAGE_ID IN ('".implode("', '", $listIds)."')";
		}

		$connection->query($sql);
	}

	public static function markError($processId, $appId, $connectorId, array $listIds)
	{
		if(count($listIds) > 0)
		{
			$connection = Main\Application::getConnection();
			$helper = $connection->getSqlHelper();

			foreach($listIds as $key => $id)
			{
				$listIds[$key] = $helper->forSql($id);
			}

			$queryWhere = array(
				"APP_ID='".intval($appId)."'",
				"CONNECTOR_ID='".$helper->forSql($connectorId)."'",
				"MESSAGE_ID IN ('".implode("', '", $listIds)."')",
			);

			$sqlTable = static::getTableName();
			$sqlWhere = implode(" AND ", $queryWhere);
			$sqlProcessId = $helper->forSql($processId);

			$sql = array();
			$sql[] = "UPDATE {$sqlTable} SET ERROR=1, PROCESS_ID=IF(PROCESS_ID='{$sqlProcessId}', '', 'fake_process_id') WHERE {$sqlWhere} AND ERROR=0 ORDER BY PROCESS_ID ASC";
			$sql[] = "DELETE FROM {$sqlTable} WHERE {$sqlWhere} AND PROCESS_ID='fake_process_id'";

			foreach($sql as $query)
			{
				$connection->query($query);
			}
		}
	}

	protected static function getProcessId()
	{
		return Main\Security\Random::getString(32);
	}

	protected static function getMessageId($fields)
	{
		return isset($fields['MESSAGE_ID']) ? $fields['MESSAGE_ID'] : md5($fields['EVENT_NAME'].'|'.Main\Web\Json::encode($fields['EVENT_DATA']));
	}
}