<?php

namespace Bitrix\Bizproc\Script\Entity;

use Bitrix\Bizproc\Script\Queue\Status;
use Bitrix\Main;

/**
 * Class ScriptTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Script_Query query()
 * @method static EO_Script_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Script_Result getById($id)
 * @method static EO_Script_Result getList(array $parameters = [])
 * @method static EO_Script_Entity getEntity()
 * @method static \Bitrix\Bizproc\Script\Entity\EO_Script createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Script\Entity\EO_Script_Collection createCollection()
 * @method static \Bitrix\Bizproc\Script\Entity\EO_Script wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Script\Entity\EO_Script_Collection wakeUpCollection($rows)
 */
class ScriptTable extends Main\Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_script';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'MODULE_ID' => [
				'data_type' => 'string'
			],
			'ENTITY' => [
				'data_type' => 'string'
			],
			'DOCUMENT_TYPE' => [
				'data_type' => 'string'
			],
			'NAME' => [
				'data_type' => 'string'
			],
			'DESCRIPTION' => [
				'data_type' => 'string'
			],
			'WORKFLOW_TEMPLATE_ID' => [
				'data_type' => 'integer'
			],
			'WORKFLOW_TEMPLATE' => array(
				'data_type' => \Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable::class,
				'reference' => array(
					'=this.WORKFLOW_TEMPLATE_ID' => 'ref.ID'
				),
				'join_type' => 'LEFT'
			),
			'CREATED_DATE' => [
				'data_type' => 'datetime'
			],
			'CREATED_BY' => [
				'data_type' => 'integer'
			],
			'MODIFIED_DATE' => [
				'data_type' => 'datetime'
			],
			'MODIFIED_BY' => [
				'data_type' => 'integer'
			],
			'ORIGINATOR_ID' => [
				'data_type' => 'string'
			],
			'ORIGIN_ID' => [
				'data_type' => 'string'
			],
			'SORT' => [
				'data_type' => 'integer',
				'default_value' => 10
			],
			'ACTIVE' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
				'default_value' => 'Y'
			],
		];
	}
	public static function getQueueCount(int $scriptId): int
	{
		return ScriptQueueTable::getCount(['=SCRIPT_ID' => $scriptId]);
	}

	public static function getActiveQueueCount(int $scriptId): int
	{
		return ScriptQueueTable::getCount(
			[
				'=SCRIPT_ID' => $scriptId,
				'=STATUS' => [Status::QUEUED, Status::EXECUTING]
			]
		);
	}

	public static function getLastStartedDate(int $scriptId): ?Main\Type\DateTime
	{
		$row = ScriptQueueTable::getList(
			[
				'filter' => ['=SCRIPT_ID' => $scriptId],
				'order' => ['STARTED_DATE' => 'DESC'],
				'limit' => 1,
				'select' => ['STARTED_DATE']
			]
		)->fetch();

		return $row? $row['STARTED_DATE'] : null;
	}
}