<?php

namespace Bitrix\Bizproc\Worker\Task;

use Bitrix\Main;
use Bitrix\Main\Application;

class UserToWorkflowStepper extends Main\Update\Stepper
{
	protected static $moduleId = 'bizproc';

	private const STEP_ROWS_LIMIT = 100;

	public function execute(array &$option)
	{
		$userId = (int)$this->getOuterParams()[0];
		$lastId = (int)($this->getOuterParams()[1] ?? 0);
		$limit = self::STEP_ROWS_LIMIT;

		$connection = Application::getConnection();

		$idCondition = $lastId > 0 ? " AND tu.ID < {$lastId} " : '';
		$queryRows = $connection->query(
			<<<SQL
				select tu.ID from b_bp_task_user tu
				where tu.USER_ID = {$userId} {$idCondition} order by tu.ID DESC LIMIT {$limit}
			SQL
		)->fetchAll();

		$ids = array_column($queryRows, 'ID');

		if (empty($ids))
		{
			return self::FINISH_EXECUTION;
		}

		$this->setOuterParams([$userId, end($ids)]);

		$idsSql = implode(',', $ids);

		$connection->query(
			<<<SQL
				INSERT INTO b_bp_workflow_user
				(USER_ID, WORKFLOW_ID, IS_AUTHOR, WORKFLOW_STATUS, TASK_STATUS, MODIFIED)
				(
					select tu.USER_ID, t.WORKFLOW_ID, 0, case when wi.id is null then 1 else 0 end,
						case when tu.STATUS = '0' then 2 else 1 end,
						case when tu.DATE_UPDATE is null then now() else tu.DATE_UPDATE end
					from b_bp_task_user tu
					inner join b_bp_task t on (t.ID = tu.TASK_ID)
					left join b_bp_workflow_instance wi on (t.WORKFLOW_ID = wi.ID)
					where tu.ID IN ({$idsSql})
				)
				ON DUPLICATE KEY UPDATE WORKFLOW_STATUS = VALUES(WORKFLOW_STATUS), TASK_STATUS = VALUES(TASK_STATUS), MODIFIED = VALUES(MODIFIED)
			SQL
		);

		return self::CONTINUE_EXECUTION;
	}

	public static function bindUser(int $userId): void
	{
		static::bind(0, [$userId]);
	}
}
