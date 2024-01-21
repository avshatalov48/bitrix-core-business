<?php

namespace Bitrix\Bizproc\Workflow\Task;

use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Bizproc\Workflow\Task;
use Bitrix\Main\Entity;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class TaskTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Task_Query query()
 * @method static EO_Task_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Task_Result getById($id)
 * @method static EO_Task_Result getList(array $parameters = [])
 * @method static EO_Task_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Task createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Task wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection wakeUpCollection($rows)
 */
class TaskTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_bp_task';
	}

	public static function getObjectClass()
	{
		return Task::class;
	}

	public static function getMap(): array
	{
		return [
			(new Entity\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new Entity\StringField('WORKFLOW_ID'))
				->addValidator(new LengthValidator(1, 32)),

			(new Entity\StringField('ACTIVITY'))
				->addValidator(new LengthValidator(1, 128)),

			(new Entity\StringField('ACTIVITY_NAME'))
				->addValidator(new LengthValidator(1, 128)),

			(new Entity\DatetimeField('CREATED_DATE'))->configureNullable(),

			(new Entity\DatetimeField('MODIFIED')),

			(new Entity\DatetimeField('OVERDUE_DATE'))
				->configureNullable(),

			(new Entity\StringField('NAME'))
				->addValidator(new LengthValidator(1, 128)),

			(new Entity\TextField('DESCRIPTION'))
				->configureNullable(),

			(new ArrayField('PARAMETERS'))
				->configureSerializeCallback(static fn($value) => serialize($value))
				->configureUnserializeCallback(
					static fn($value) => unserialize(
						$value,
						[
							'allowed_classes' => [
								\Bitrix\Bizproc\BaseType\Value\Date::class,
								\Bitrix\Bizproc\BaseType\Value\DateTime::class,
								\Bitrix\Main\Type\Date::class,
								\Bitrix\Main\Type\DateTime::class,
								\DateTime::class,
								\DateTimeZone::class,
								\Bitrix\Main\Web\Uri::class,
							]
						]
					)
				)
			,

			(new Entity\IntegerField('STATUS'))
				->configureDefaultValue(0),

			(new Entity\EnumField('IS_INLINE'))
				->configureValues(['Y', 'N'])
				->configureDefaultValue('N'),

			(new Entity\IntegerField('DELEGATION_TYPE'))
				->configureDefaultValue(0),

			(new Entity\StringField('DOCUMENT_NAME'))
				->addValidator(new LengthValidator(1, 255)),

			new \Bitrix\Main\ORM\Fields\Relations\Reference(
				'WORKFLOW_STATE',
				WorkflowStateTable::class,
				Join::on('this.WORKFLOW_ID', 'ref.ID')
			),
			new \Bitrix\Main\ORM\Fields\Relations\OneToMany(
				'TASK_USERS',
				TaskUserTable::class,
				'USER_TASKS'
			)
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
