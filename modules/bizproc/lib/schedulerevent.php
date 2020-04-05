<?php

namespace Bitrix\Bizproc;

use Bitrix\Main;;

class SchedulerEventTable extends Main\Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_scheduler_event';
	}

	/**
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
			'WORKFLOW_ID' => array(
				'data_type' => 'string'
			),
			'HANDLER' => array(
				'data_type' => 'string'
			),
			'EVENT_MODULE' => array(
				'data_type' => 'string'
			),
			'EVENT_TYPE' => array(
				'data_type' => 'string'
			),
			'ENTITY_ID' => array(
				'data_type' => 'string'
			)
		);
	}

	public static function deleteBySubscription($workflowId, $handler, $eventModule, $eventType, $entityId = null)
	{
		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$table = $sqlHelper->forSql(static::getTableName());
		$workflowId = $sqlHelper->forSql($workflowId);
		$handler = $sqlHelper->forSql($handler);
		$eventModule = $sqlHelper->forSql($eventModule);
		$eventType = $sqlHelper->forSql($eventType);
		$entityId = $entityId !== null ? $sqlHelper->forSql($entityId) : null;

		$connection->queryExecute("DELETE 
			FROM {$table} 
			WHERE 
				WORKFLOW_ID = '{$workflowId}' 
				AND HANDLER = '{$handler}' 
				AND EVENT_MODULE = '{$eventModule}' 
				AND EVENT_TYPE = '{$eventType}'"
				.($entityId !== null ? " AND ENTITY_ID = '{$entityId}'" : '')
		);
	}

	public static function deleteByWorkflow($workflowId)
	{
		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$table = $sqlHelper->forSql(static::getTableName());
		$workflowId = $sqlHelper->forSql($workflowId);

		$connection->queryExecute("DELETE FROM {$table} WHERE WORKFLOW_ID = '{$workflowId}'");
	}

	public static function isSubscribed($workflowId, $handler, $eventModule, $eventType, $entityId = null)
	{
		$filter = array(
			'=WORKFLOW_ID' => (string)$workflowId,
			'=HANDLER' => (string)$handler,
			'=EVENT_MODULE' => (string)$eventModule,
			'=EVENT_TYPE' => (string)$eventType
		);

		if ($entityId !== null)
			$filter['=ENTITY_ID'] = (string)$entityId;

		$row = static::getList(array(
			'select' => array('ID'),
			'filter' => $filter
		))->fetch();

		return (is_array($row));
	}

	public static function hasSubscriptions($eventModule, $eventType)
	{
		$filter = array(
			'=EVENT_MODULE' => $eventModule,
			'=EVENT_TYPE' => $eventType
		);

		$row = static::getList(array(
			'select' => array('ID'),
			'filter' => $filter,
			'limit' => 1
		))->fetch();

		return (is_array($row));
	}
}
