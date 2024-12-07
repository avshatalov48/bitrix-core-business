<?php

namespace Bitrix\Bizproc\Workflow\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\Type\DateTime;

/**
 * Class WorkflowUserCommentTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkflowUserComment_Query query()
 * @method static EO_WorkflowUserComment_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkflowUserComment_Result getById($id)
 * @method static EO_WorkflowUserComment_Result getList(array $parameters = [])
 * @method static EO_WorkflowUserComment_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment_Collection wakeUpCollection($rows)
 */
class WorkflowUserCommentTable extends DataManager
{
	public const COMMENT_TYPE_DEFAULT = 0;

	public const COMMENT_TYPE_SYSTEM = 1;

	public static function getTableName()
	{
		return 'b_bp_workflow_user_comment';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('USER_ID'))
				->configurePrimary()
			,
			(new StringField('WORKFLOW_ID'))
				->configureSize(32)
				->configurePrimary()
			,
			(new IntegerField('UNREAD_CNT'))
				->configureNullable(false)
			,
			(new IntegerField('LAST_TYPE'))
				->configureNullable(false)
				->configureDefaultValue(0)
			,
			(new DatetimeField('MODIFIED'))
				->configureNullable(false)
			,
		];
	}

	public static function incrementUnreadCounter(
		string $workflowId,
		array $userIds,
		int $commentType = self::COMMENT_TYPE_DEFAULT
	): void
	{
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$modified = new DateTime();

		$tableName = static::getTableName();

		$insert = [
			'WORKFLOW_ID' => $workflowId,
			'UNREAD_CNT' => 1,
			'MODIFIED' => $modified,
			'LAST_TYPE' => $commentType,
		];
		$update = [
			'UNREAD_CNT' => new \Bitrix\Main\DB\SqlExpression('?#.?# + ?i', $tableName, 'UNREAD_CNT', 1),
			'MODIFIED' => $modified,
			'LAST_TYPE' => $commentType,
		];

		$primary = [
			'WORKFLOW_ID',
			'USER_ID',
		];

		foreach ($userIds as $userId)
		{
			$insert['USER_ID'] = $userId;

			$queries = $sqlHelper->prepareMerge($tableName, $primary, $insert, $update);

			foreach ($queries as $query)
			{
				$connection->queryExecute($query);
			}
		}
	}

	public static function decrementUnreadCounterByDate(string $workflowId, DateTime $modified): array
	{
		$rows = static::getList([
			'filter' => [
				'=WORKFLOW_ID' => $workflowId,
				'=MODIFIED' => $modified,
			],
			'select' => ['USER_ID', 'UNREAD_CNT'],
		])->fetchAll();

		foreach ($rows as $row)
		{
			$key = [
				'WORKFLOW_ID' => $workflowId,
				'USER_ID' => $row['USER_ID'],
			];
			if ($row['UNREAD_CNT'] <= 1)
			{
				static::delete($key);

				continue;
			}

			static::update($key, [
				'UNREAD_CNT' => new \Bitrix\Main\DB\SqlExpression('?# - ?i', 'UNREAD_CNT', 1),
			]);
		}

		return array_map(
			static fn ($row) => (int)$row['USER_ID'],
			$rows,
		);
	}

	public static function verifyUserUnread(int $userId): void
	{
		$rows = static::query()
			->where('USER_ID', $userId)
			->setSelect(['WORKFLOW_ID'])
			->fetchAll()
		;

		$workflowIds = array_column($rows, 'WORKFLOW_ID');

		if (!$workflowIds)
		{
			return;
		}

		$workflowRows = WorkflowUserTable::query()
			->whereIn('WORKFLOW_ID', $workflowIds)
			->where('USER_ID', $userId)
			->setSelect(['WORKFLOW_ID'])
			->fetchAll();

		$realIds = array_column($workflowRows, 'WORKFLOW_ID');
		$oldIds = array_diff($workflowIds, $realIds);

		foreach ($oldIds as $id)
		{
			static::delete([
				'USER_ID' => $userId,
				'WORKFLOW_ID' => $id,
			]);
		}
	}

	public static function getCountUserUnread(int $userId): int
	{
		$row = static::query()
			->where('USER_ID', $userId)
			->setSelect([new ExpressionField('SUM', 'SUM(%s)', 'UNREAD_CNT')])
			->fetch()
		;

		return (int)($row['SUM'] ?? 0);
	}

	public static function deleteByWorkflow(string $workflowId): void
	{
		$iterator = static::query()
			->setSelect(['USER_ID', 'WORKFLOW_ID'])
			->setFilter(['=WORKFLOW_ID' => $workflowId])
			->exec()
		;

		while ($row = $iterator->fetch())
		{
			static::delete($row);
		}
	}

	public static function deleteUsersByWorkflow(array $userIds, string $workflowId): void
	{
		foreach ($userIds as $userId)
		{
			static::delete([
				'USER_ID' => $userId,
				'WORKFLOW_ID' => $workflowId,
			]);
		}
	}
}
