<?php

namespace Bitrix\Bizproc\Workflow\Entity;

use Bitrix\Bizproc\Workflow\Task\TaskTable;
use Bitrix\Bizproc\Workflow\WorkflowState;
use Bitrix\Main;
use Bitrix\Main\Entity;

/**
 * Class WorkflowStateTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkflowState_Query query()
 * @method static EO_WorkflowState_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkflowState_Result getById($id)
 * @method static EO_WorkflowState_Result getList(array $parameters = [])
 * @method static EO_WorkflowState_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\WorkflowState createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\WorkflowState wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection wakeUpCollection($rows)
 */
class WorkflowStateTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_workflow_state';
	}

	/**
	 * @return string
	 */
	public static function getObjectClass()
	{
		return WorkflowState::class;
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'string',
				'primary' => true,
			],
			'MODULE_ID' => [
				'data_type' => 'string',
			],
			'ENTITY' => [
				'data_type' => 'string',
			],
			'DOCUMENT_ID' => [
				'data_type' => 'string',
			],
			'DOCUMENT_ID_INT' => [
				'data_type' => 'integer',
			],
			'WORKFLOW_TEMPLATE_ID' => [
				'data_type' => 'integer',
			],
			'STATE' => [
				'data_type' => 'string',
			],
			'STATE_TITLE' => [
				'data_type' => 'string',
			],
			'STATE_PARAMETERS' => [
				'data_type' => 'string',
			],
			'MODIFIED' => [
				'data_type' => 'datetime',
			],
			'STARTED' => [
				'data_type' => 'datetime',
			],
			'STARTED_BY' => [
				'data_type' => 'integer',
			],
			'STARTED_USER' => [
				'data_type' => '\Bitrix\Main\UserTable',
				'reference' => [
					'=this.STARTED_BY' => 'ref.ID',
				],
				'join_type' => 'LEFT',
			],
			'INSTANCE' => [
				'data_type' => '\Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable',
				'reference' => [
					'=this.ID' => 'ref.ID',
				],
				'join_type' => 'LEFT',
			],
			'TEMPLATE' => [
				'data_type' => '\Bitrix\Bizproc\WorkflowTemplateTable',
				'reference' => [
					'=this.WORKFLOW_TEMPLATE_ID' => 'ref.ID',
				],
				'join_type' => 'LEFT',
			],
			new Main\ORM\Fields\Relations\OneToMany(
				'TASKS',
				TaskTable::class,
				'WORKFLOW_STATE'
			),
		];
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

	/**
	 * @param mixed $primary Primary key.
	 * @throws Main\NotImplementedException
	 * @return void
	 */
	public static function delete($primary)
	{
		throw new Main\NotImplementedException("Use CBPStateService class.");
	}
}
