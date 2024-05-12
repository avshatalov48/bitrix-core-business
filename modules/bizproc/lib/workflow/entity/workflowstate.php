<?php

namespace Bitrix\Bizproc\Workflow\Entity;

use Bitrix\Bizproc\Workflow\Task\TaskTable;
use Bitrix\Bizproc\Workflow\WorkflowState;
use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\Type\DateTime;

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
class WorkflowStateTable extends ORM\Data\DataManager
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
				'default_value' => function()
				{
					return new Main\Type\DateTime();
				},
			],
			'STARTED' => [
				'data_type' => 'datetime',
				'default_value' => function()
				{
					return new Main\Type\DateTime();
				},
			],
			'STARTED_BY' => [
				'data_type' => 'integer',
			],
			'STARTED_USER' => [
				'data_type' => Main\UserTable::class,
				'reference' => [
					'=this.STARTED_BY' => 'ref.ID',
				],
				'join_type' => 'LEFT',
			],
			'INSTANCE' => [
				'data_type' => WorkflowInstanceTable::class,
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
			new Main\ORM\Fields\Relations\Reference(
				'META',
				WorkflowMetadataTable::class,
				\Bitrix\Main\ORM\Query\Join::on('this.ID', 'ref.WORKFLOW_ID'),
			),
		];
	}

	public static function exists(string $workflowId): bool
	{
		return static::getCount(['=ID' => $workflowId]) > 0;
	}

	public static function getIdsByDocument(array $documentId, ?int $limit = null)
	{
		$documentId = \CBPHelper::ParseDocumentId($documentId);
		$rows = static::getList([
			'select' => ['ID'],
			'filter' => [
				'=MODULE_ID' => $documentId[0],
				'=ENTITY' => $documentId[1],
				'=DOCUMENT_ID' => $documentId[2],
			],
			'limit' => $limit,
		])->fetchAll();

		return array_column($rows, 'ID');
	}

	public static function onBeforeUpdate(ORM\Event $event): ORM\EventResult
	{
		$result = new ORM\EventResult;
		$data = $event->getParameter('fields');

		if (empty($data['MODIFIED']))
		{
			$result->modifyFields([
				'MODIFIED' => new DateTime(),
			]);
		}

		return $result;
	}

	public static function onBeforeAdd(ORM\Event $event): ORM\EventResult
	{
		$result = new ORM\EventResult;
		$fields = $event->getParameter('fields');

		if (empty($fields['MODIFIED']))
		{
			$result->modifyFields([
				'MODIFIED' => new DateTime(),
			]);
		}

		return $result;
	}

	public static function onAfterAdd(ORM\Event $event)
	{
		$fields = $event->getParameter('fields');

		// users sync automatically in WorkflowUserTable::syncOnWorkflowUpdated

		WorkflowFilterTable::add([
			'WORKFLOW_ID' => $fields['ID'] ?? '',
			'MODULE_ID' => $fields['MODULE_ID'] ?? '',
			'ENTITY' => $fields['ENTITY'] ?? '',
			'DOCUMENT_ID' => $fields['DOCUMENT_ID'] ?? '',
			'TEMPLATE_ID' => $fields['WORKFLOW_TEMPLATE_ID'] ?? 0,
			'STARTED' => $fields['STARTED'] ?? 0,
		]);
	}

	public static function onAfterDelete(ORM\Event $event)
	{
		$id = $event->getParameter('primary')['ID'];

		WorkflowUserTable::deleteByWorkflow($id);
		WorkflowFilterTable::delete($id);
	}
}
