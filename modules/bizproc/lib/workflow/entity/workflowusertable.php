<?php

namespace Bitrix\Bizproc\Workflow\Entity;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Bizproc\Integration\Push\WorkflowPush;

/**
 * Class WorkflowUserTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkflowUser_Query query()
 * @method static EO_WorkflowUser_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkflowUser_Result getById($id)
 * @method static EO_WorkflowUser_Result getList(array $parameters = [])
 * @method static EO_WorkflowUser_Entity getEntity()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser createObject($setDefaultValues = true)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser_Collection createCollection()
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser wakeUpObject($row)
 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser_Collection wakeUpCollection($rows)
 */
class WorkflowUserTable extends DataManager
{
	public const WORKFLOW_STATUS_ACTIVE = 0;
	public const WORKFLOW_STATUS_COMPLETED = 1;

	public const TASK_STATUS_NONE = 0;
	public const TASK_STATUS_COMPLETED = 1;
	public const TASK_STATUS_ACTIVE = 2;


	public static function getTableName()
	{
		return 'b_bp_workflow_user';
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
			(new IntegerField('IS_AUTHOR'))
				->configureNullable(false)
			,
			(new IntegerField('WORKFLOW_STATUS'))
				->configureNullable(false)
			,
			(new IntegerField('TASK_STATUS'))
				->configureNullable(false)
			,
			(new DatetimeField('MODIFIED'))
				->configureNullable(false)
			,
			(new Reference(
				'FILTER',
				WorkflowFilterTable::class,
				Join::on('this.WORKFLOW_ID', 'ref.WORKFLOW_ID')
			))
				->configureJoinType(Join::TYPE_INNER)
			,
		];
	}

	public static function syncOnWorkflowUpdated(\CBPWorkflow $workflow, int $status): void
	{
		$workflowId = $workflow->getInstanceId();
		$users = static::getTaskUsers($workflowId);
		$hasUsers = !empty($users) || static::hasStoredUsers($workflowId);

		if (!$hasUsers && !static::isLiveFeedProcess($workflow->getDocumentId()))
		{
			return;
		}

		$authorId = $workflow->getStartedBy();
		$workflowStatus = \CBPWorkflowStatus::isFinished($status)
			? static::WORKFLOW_STATUS_COMPLETED
			: static::WORKFLOW_STATUS_ACTIVE
		;

		if ($authorId)
		{
			$users[$authorId]['IS_AUTHOR'] ??= 1;
		}

		foreach ($users as $id => $user)
		{
			$users[$id]['WORKFLOW_STATUS'] = $workflowStatus;
		}

		static::syncUsers($workflowId, $users);
	}

	private static function isLiveFeedProcess(array $documentId): bool
	{
		return ($documentId[0] ?? '') === 'lists' && ($documentId[1] ?? '') === 'BizprocDocument';
	}

	public static function syncOnTaskUpdated(string $workflowId): array
	{
		$users = static::getTaskUsers($workflowId);

		return static::syncUsers($workflowId, $users);
	}

	private static function getTaskUsers(string $workflowId): array
	{
		$taskUsers = \CBPTaskService::getWorkflowUsers($workflowId);
		$users = [];

		foreach ($taskUsers as $id => $taskStatus)
		{
			$users[$id] = [
				'TASK_STATUS' => $taskStatus === \CBPTaskUserStatus::Waiting
					? static::TASK_STATUS_ACTIVE
					: static::TASK_STATUS_COMPLETED
				,
			];
		}

		$authorId = static::getAuthorId($workflowId);
		if ($authorId && !isset($users[$authorId]))
		{
			$users[$authorId] = [
				'TASK_STATUS' => static::TASK_STATUS_NONE,
			];
		}

		return $users;
	}

	private static function syncUsers(string $workflowId, array $users): array
	{
		$stored = static::getStoredUsers($workflowId);

		$toDelete = array_diff_key($stored, $users);
		$toAdd = array_diff_key($users, $stored);
		$toUpdate = array_intersect_key($users, $stored);

		self::deleteOnSync($workflowId, $toDelete);
		self::addOnSync($workflowId, $toAdd);
		self::updateOnSync($workflowId, $toUpdate);

		return [array_keys($toAdd), array_keys($toUpdate), array_keys($toDelete)];
	}

	private static function getAuthorId(string $workflowId): int
	{
		$result = static::getList([
			'select' => ['USER_ID'],
			'filter' => [
				'=WORKFLOW_ID' => $workflowId,
				'=IS_AUTHOR' => 1,
			],
		])->fetch();

		return (int)($result['USER_ID'] ?? 0);
	}

	private static function deleteOnSync(string $workflowId, array $toDelete): void
	{
		if (!$toDelete)
		{
			return;
		}

		$deleted = array_keys($toDelete);
		foreach ($deleted as $userId)
		{
			static::delete([
				'USER_ID' => $userId,
				'WORKFLOW_ID' => $workflowId,
			]);
		}

		WorkflowPush::pushDeleted($workflowId, $deleted);
	}

	private static function addOnSync(string $workflowId, array $toAdd): void
	{
		if (!$toAdd)
		{
			return;
		}

		foreach ($toAdd as $userId => $user)
		{
			static::add([
				'USER_ID' => $userId,
				'WORKFLOW_ID' => $workflowId,
				'IS_AUTHOR' => $user['IS_AUTHOR'] ?? 0,
				'WORKFLOW_STATUS' => $user['WORKFLOW_STATUS'] ?? static::WORKFLOW_STATUS_ACTIVE,
				'TASK_STATUS' => $user['TASK_STATUS'] ?? static::TASK_STATUS_NONE,
				'MODIFIED' => new DateTime(),
			]);
		}

		WorkflowPush::pushAdded($workflowId, array_keys($toAdd));
	}

	private static function updateOnSync(string $workflowId, array $toUpdate): void
	{
		if (!$toUpdate)
		{
			return;
		}

		$modified = new DateTime();

		foreach ($toUpdate as $userId => $user)
		{
			$user['MODIFIED'] = $modified;

			static::update(
				[
					'USER_ID' => $userId,
					'WORKFLOW_ID' => $workflowId,
				],
				$user
			);
		}

		WorkflowPush::pushUpdated($workflowId, array_keys($toUpdate));
	}

	private static function getStoredUsers(string $workflowId): array
	{
		$result = static::getList([
			'select' => ['USER_ID', 'IS_AUTHOR', 'WORKFLOW_STATUS', 'TASK_STATUS'],
			'filter' => ['=WORKFLOW_ID' => $workflowId],
		]);

		$users = [];

		while ($row = $result->fetch())
		{
			$users[(int)$row['USER_ID']] = [
				'IS_AUTHOR' => (int)$row['IS_AUTHOR'],
				'WORKFLOW_STATUS' => (int)$row['WORKFLOW_STATUS'],
				'TASK_STATUS' => (int)$row['TASK_STATUS'],
			];
		}

		return $users;
	}

	private static function hasStoredUsers(string $workflowId): bool
	{
		return static::getCount(['=WORKFLOW_ID' => $workflowId]) > 0;
	}

	public static function deleteByWorkflow(string $workflowId): array
	{
		$stored = static::getStoredUsers($workflowId);
		self::deleteOnSync($workflowId, $stored);

		return array_keys($stored);
	}

	public static function convertUserProcesses(int $userId): void
	{
		$connection = Application::getConnection();

		if ($connection->getType() !== 'mysql')
		{
			return;
		}

		//truncate first
		$connection->query(
			<<<SQL
				DELETE FROM b_bp_workflow_user WHERE USER_ID = {$userId}
			SQL
		);

		// convert user "Live Feed" workflows (lists + BizprocDocument)
		$connection->query(
			<<<SQL
				INSERT INTO b_bp_workflow_user
				(USER_ID, WORKFLOW_ID, IS_AUTHOR, WORKFLOW_STATUS, MODIFIED)
			 	(
					 select wt.STARTED_BY, wt.ID, 1, case when wi.id is null then 1 else 0 end, wt.MODIFIED
					 from b_bp_workflow_state wt
					 left join b_bp_workflow_instance wi on (wt.id = wi.id)
					 where wt.STARTED_BY = {$userId} and wt.MODULE_ID = 'lists' and wt.ENTITY = 'BizprocDocument'
				)
				ON DUPLICATE KEY UPDATE IS_AUTHOR = 1, WORKFLOW_STATUS = VALUES(WORKFLOW_STATUS), MODIFIED = VALUES(MODIFIED)
			SQL
		);

		// convert my active tasks
		$connection->query(
			<<<SQL
				INSERT INTO b_bp_workflow_user
				(USER_ID, WORKFLOW_ID, IS_AUTHOR, WORKFLOW_STATUS, TASK_STATUS, MODIFIED)
				(
					select tu.USER_ID, t.WORKFLOW_ID, 0, 0, 2,
						case when tu.DATE_UPDATE is null then now() else tu.DATE_UPDATE end
					from b_bp_task_user tu
					inner join b_bp_task t on (t.ID = tu.TASK_ID)
					where tu.USER_ID = {$userId} and tu.STATUS = '0'
				)
				ON DUPLICATE KEY UPDATE TASK_STATUS = VALUES(TASK_STATUS), MODIFIED = VALUES(MODIFIED)
			SQL
		);

		//convert other tasks
		\Bitrix\Bizproc\Worker\Task\UserToWorkflowStepper::bindUser($userId);
	}
}
