<?php

namespace Bitrix\Bizproc\Workflow\Entity;

use Bitrix\Main;
use Bitrix\Main\Entity;

/**
 * Class WorkflowInstanceTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkflowInstance_Query query()
 * @method static EO_WorkflowInstance_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkflowInstance_Result getById($id)
 * @method static EO_WorkflowInstance_Result getList(array $parameters = [])
 * @method static EO_WorkflowInstance_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection wakeUpCollection($rows)
 */
class WorkflowInstanceTable extends Entity\DataManager
{
	const LOCKED_TIME_INTERVAL = 300;

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_workflow_instance';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'MODULE_ID' => array(
				'data_type' => 'string'
			),
			'ENTITY' => array(
				'data_type' => 'string'
			),
			'DOCUMENT_ID' => array(
				'data_type' => 'string'
			),
			'WORKFLOW_TEMPLATE_ID' => array(
				'data_type' => 'integer'
			),
			'WORKFLOW' => array(
				'data_type' => 'string'
			),
			'WORKFLOW_RO' => array(
				'data_type' => 'string'
			),
			'STARTED' => array(
				'data_type' => 'datetime'
			),
			'STARTED_BY' => array(
				'data_type' => 'integer'
			),
			'STARTED_USER' => array(
				'data_type' => '\Bitrix\Main\UserTable',
				'reference' => array(
					'=this.STARTED_BY' => 'ref.ID'
				),
				'join_type' => 'LEFT',
			),
			'STARTED_EVENT_TYPE' => array(
				'data_type' => 'integer'
			),
			'STATUS' => array(
				'data_type' => 'integer'
			),
			'MODIFIED' => array(
				'data_type' => 'datetime'
			),
			'OWNER_ID' => array(
				'data_type' => 'string'
			),
			'OWNED_UNTIL' => array(
				'data_type' => 'datetime'
			),
			'STATE' => array(
				'data_type' => '\Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable',
				'reference' => array(
					'=this.ID' => 'ref.ID'
				),
				'join_type' => 'LEFT',
			),
			'TEMPLATE' => array(
				'data_type' => '\Bitrix\Bizproc\WorkflowTemplateTable',
				'reference' => array(
					'=this.WORKFLOW_TEMPLATE_ID' => 'ref.ID'
				),
				'join_type' => 'LEFT'
			),
		);
	}

	public static function exists(string $workflowId)
	{
		return static::getCount(['=ID' => $workflowId]) > 0;
	}

	public static function getIdsByDocument(array $documentId)
	{
		$documentId = \CBPHelper::ParseDocumentId($documentId);
		$rows = static::getList([
			'select' => ['ID'],
			'filter' => [
				'=MODULE_ID' => $documentId[0],
				'=ENTITY' => $documentId[1],
				'=DOCUMENT_ID' => $documentId[2]
			]
		])->fetchAll();

		return array_column($rows, 'ID');
	}

	public static function getIdsByTemplateId(int ...$tplIds)
	{
		$filterKeyPrefix = count($tplIds) < 2 ? '=' : '@';
		if (count($tplIds) < 2)
		{
			$tplIds = reset($tplIds);
		}

		$rows = static::getList([
			'select' => ['ID'],
			'filter' => [
				$filterKeyPrefix.'WORKFLOW_TEMPLATE_ID' => $tplIds,
			]
		])->fetchAll();

		return array_column($rows, 'ID');
	}

	public static function mergeByDocument($paramFirstDocumentId, $paramSecondDocumentId)
	{
		$firstDocumentId = \CBPHelper::parseDocumentId($paramFirstDocumentId);
		$secondDocumentId = \CBPHelper::parseDocumentId($paramSecondDocumentId);

		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$table = $sqlHelper->forSql(static::getTableName());

		$firstDocId = $sqlHelper->forSql($firstDocumentId[2]);
		$firstEntity = $sqlHelper->forSql($firstDocumentId[1]);
		$firstModule = $sqlHelper->forSql($firstDocumentId[0]);

		$secondDocId = $sqlHelper->forSql($secondDocumentId[2]);
		$secondEntity = $sqlHelper->forSql($secondDocumentId[1]);
		$secondModule = $sqlHelper->forSql($secondDocumentId[0]);

		$connection->queryExecute("UPDATE {$table} 
			SET 
				DOCUMENT_ID = '{$firstDocId}',
				ENTITY = '{$firstEntity}',
				MODULE_ID = '{$firstModule}' 
			WHERE 
				DOCUMENT_ID = '{$secondDocId}' 
				AND ENTITY = '{$secondEntity}' 
				AND MODULE_ID = '{$secondModule}'
		");

		return true;
	}

	public static function migrateDocumentType($paramOldType, $paramNewType, $workflowTemplateIds)
	{
		$oldType = \CBPHelper::parseDocumentId($paramOldType);
		$newType = \CBPHelper::parseDocumentId($paramNewType);

		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$table = $sqlHelper->forSql(static::getTableName());

		$firstEntity = $sqlHelper->forSql($oldType[1]);
		$firstModule = $sqlHelper->forSql($oldType[0]);

		$secondEntity = $sqlHelper->forSql($newType[1]);
		$secondModule = $sqlHelper->forSql($newType[0]);

		$templates = implode(",", array_map('intval', $workflowTemplateIds));

		$connection->queryExecute("UPDATE {$table} 
			SET 
				ENTITY = '{$firstEntity}',
				MODULE_ID = '{$firstModule}' 
			WHERE 
				ENTITY = '{$secondEntity}' 
				AND MODULE_ID = '{$secondModule}' 
				AND WORKFLOW_TEMPLATE_ID IN ({$templates})
		");

		return true;
	}

	public static function isDebugWorkflow(string $workflowId)
	{
		$row = static::getList([
			'select' => ['STARTED_EVENT_TYPE'],
			'filter' => [
				'=ID' => $workflowId,
			]
		])->fetch();

		return $row && (int)$row['STARTED_EVENT_TYPE'] === \CBPDocumentEventType::Debug;
	}

	/**
	 * @param array $data Entity data.
	 * @throws Main\NotImplementedException
	 * @return void
	 */
	public static function add(array $data)
	{
		throw new Main\NotImplementedException("Use CBPStateService class.");
	}

	/**
	 * @param mixed $primary Primary key.
	 * @param array $data Entity data.
	 * @throws Main\NotImplementedException
	 * @return void
	 */
	public static function update($primary, array $data)
	{
		throw new Main\NotImplementedException("Use CBPStateService class.");
	}
}
