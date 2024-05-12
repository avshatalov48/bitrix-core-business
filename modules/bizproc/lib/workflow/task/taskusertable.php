<?php

namespace Bitrix\Bizproc\Workflow\Task;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class TaskUserTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_TaskUser_Query query()
 * @method static EO_TaskUser_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_TaskUser_Result getById($id)
 * @method static EO_TaskUser_Result getList(array $parameters = [])
 * @method static EO_TaskUser_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskUser createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskUser wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection wakeUpCollection($rows)
 */
class TaskUserTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_bp_task_user';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new IntegerField('USER_ID'))
				->configureNullable(false)
			,
			(new IntegerField('TASK_ID'))
				->configureNullable(false)
			,
			(new IntegerField('STATUS'))
				->configureNullable(false)
				->configureDefaultValue(0)
			,
			(new DatetimeField('DATE_UPDATE'))
				->configureNullable()
			,
			(new IntegerField('ORIGINAL_USER_ID'))
				->configureNullable(false)
				->configureDefaultValue(0)
			,
			new \Bitrix\Main\ORM\Fields\Relations\Reference(
				'USER_TASKS',
				TaskTable::class,
				Join::on('this.TASK_ID', 'ref.ID')
			),
			(new \Bitrix\Main\ORM\Fields\Relations\Reference(
				'USER_TASKS_SEARCH_CONTENT',
				TaskSearchContentTable::class,
				Join::on('this.TASK_ID', 'ref.TASK_ID')
			))
				->configureJoinType(Join::TYPE_INNER)
			,
		];
	}

	/**
	 * @param array $data Entity data.
	 * @throws NotImplementedException
	 * @return void
	 */
	public static function add(array $data)
	{
		throw new NotImplementedException('Use CBPTaskService class.');
	}

	/**
	 * @param mixed $primary Primary key.
	 * @param array $data Entity data.
	 * @throws NotImplementedException
	 * @return void
	 */
	public static function update($primary, array $data)
	{
		throw new NotImplementedException('Use CBPTaskService class.');
	}

	/**
	 * @param mixed $primary Primary key.
	 * @throws NotImplementedException
	 * @return void
	 */
	public static function delete($primary)
	{
		throw new NotImplementedException('Use CBPTaskService class.');
	}
}
