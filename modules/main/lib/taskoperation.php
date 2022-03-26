<?php

namespace Bitrix\Main;

use Bitrix\Main\Entity;

/**
 * Class TaskOperationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_TaskOperation_Query query()
 * @method static EO_TaskOperation_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_TaskOperation_Result getById($id)
 * @method static EO_TaskOperation_Result getList(array $parameters = [])
 * @method static EO_TaskOperation_Entity getEntity()
 * @method static \Bitrix\Main\EO_TaskOperation createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_TaskOperation_Collection createCollection()
 * @method static \Bitrix\Main\EO_TaskOperation wakeUpObject($row)
 * @method static \Bitrix\Main\EO_TaskOperation_Collection wakeUpCollection($rows)
 */
class TaskOperationTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_task_operation';
	}

	public static function getMap()
	{
		return array(
			'TASK_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'OPERATION_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'OPERATION' => array(
				'data_type' => 'Bitrix\Main\OperationTable',
				'reference' => array('=this.OPERATION_ID' => 'ref.ID'),
			),
			'TASK' => array(
				'data_type' => 'Bitrix\Main\TaskTable',
				'reference' => array('=this.TASK_ID' => 'ref.ID'),
			),
		);
	}
}