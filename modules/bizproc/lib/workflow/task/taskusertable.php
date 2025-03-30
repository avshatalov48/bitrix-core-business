<?php

namespace Bitrix\Bizproc\Workflow\Task;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

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
	 * @throws NotImplementedException
	 * @return void
	 */
	public static function delete($primary)
	{
		throw new NotImplementedException('Use CBPTaskService class.');
	}

	/**
	 * Do not use it directly. All changes must go through CBPTaskService
	 *
	 * @param int $taskId
	 * @param array $userIds
	 * @param int $status
	 *
	 * @return UpdateResult|null
	 * @throws ArgumentException
	 * @throws SqlQueryException
	 * @throws SystemException
	 * @throws \Exception
	 */
	public static function updateStatus(int $taskId, array $userIds, int $status = \CBPTaskUserStatus::Ok): ?UpdateResult
	{
		$ids = static::getPrimariesByUniqueKey($taskId, $userIds);
		if (!$ids)
		{
			return null;
		}

		$update = ['STATUS' => $status, 'DATE_UPDATE' => new DateTime()];

		if (count($ids) > 1)
		{
			return static::updateMulti($ids, $update);
		}

		return static::update($ids[0], $update);
	}

	/**
	 * Do not use it directly. All changes must go through CBPTaskService
	 *
	 * @param int $taskId
	 * @param int $fromUserId
	 * @param int $toUserId
	 *
	 * @return UpdateResult|null
	 * @throws \Exception
	 */
	public static function delegateTask(int $taskId, int $fromUserId, int $toUserId): ?UpdateResult
	{
		$ids = static::getPrimariesByUniqueKey($taskId, [$fromUserId]);
		if (!$ids)
		{
			return null;
		}

		$originalUserId = static::getOriginalTaskUserId($taskId, $fromUserId) ?? 0;
		$update = ['USER_ID' => $toUserId];
		if ($originalUserId <= 0)
		{
			$update['ORIGINAL_USER_ID'] = $fromUserId;
		}

		return static::update($ids[0], $update);
	}

	public static function getOriginalTaskUserId(int $taskId, int $userId): ?int
	{
		$row =
			static::query()
				->setSelect(['ORIGINAL_USER_ID'])
				->where('TASK_ID', $taskId)
				->where('USER_ID', $userId)
				->exec()
				->fetch()
		;
		if ($row)
		{
			return (int)$row['ORIGINAL_USER_ID'];
		}

		return null;
	}

	protected static function getPrimariesByUniqueKey(int $taskId, array $userIds): array
	{
		$query =
			static::query()
				->setSelect(['ID'])
				->where('TASK_ID', $taskId)
		;
		if (count($userIds) > 1)
		{
			$query->whereIn('USER_ID', $userIds);
		}
		else
		{
			$query->where('USER_ID', $userIds[0] ?? 0);
		}

		return array_column($query->exec()->fetchAll(), 'ID');
	}
}
