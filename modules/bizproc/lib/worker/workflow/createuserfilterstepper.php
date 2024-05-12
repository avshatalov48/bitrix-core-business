<?php

namespace Bitrix\Bizproc\Worker\Workflow;

use Bitrix\Main;
use Bitrix\Main\Application;

class CreateUserFilterStepper extends Main\Update\Stepper
{
	protected static $moduleId = 'bizproc';

	private const STEP_ROWS_LIMIT = 100;

	public function execute(array &$option)
	{
		$connection = Application::getConnection();
		if ($connection->getType() !== 'mysql')
		{
			return self::FINISH_EXECUTION;
		}

		$userId = (int)$this->getOuterParams()[0];
		$lastTs = (int)($this->getOuterParams()[1] ?? 0);
		$limit = self::STEP_ROWS_LIMIT;

		$oldSql = ' AND wu.MODIFIED > ' . $connection->getSqlHelper()->addDaysToDateTime(-180);
		$modCondition = '';
		if ($lastTs > 0)
		{
			$modCondition = ' AND wu.MODIFIED <= ' . "'" . date('Y-m-d H:i:s', $lastTs) . "'";
		}
		$queryRows = $connection->query(
			<<<SQL
				select wu.WORKFLOW_ID, wu.MODIFIED from b_bp_workflow_user wu
				where wu.USER_ID = {$userId} {$modCondition} {$oldSql} order by wu.MODIFIED DESC LIMIT {$limit}
			SQL
		)->fetchAll();

		$ids = array_column($queryRows, 'WORKFLOW_ID');

		if (empty($ids))
		{
			return self::FINISH_EXECUTION;
		}

		$newLastTs = strtotime(end($queryRows)['MODIFIED']);
		if ($newLastTs === $lastTs)
		{
			--$newLastTs;
		}

		$this->setOuterParams([$userId, $newLastTs]);

		$idsSql = "'" . implode("','", $ids) . "'";

		$connection->query(
			<<<SQL
				INSERT IGNORE INTO b_bp_workflow_filter
				(WORKFLOW_ID, MODULE_ID, ENTITY, DOCUMENT_ID, TEMPLATE_ID, STARTED)
				(
					select ws.ID,
					case when ws.MODULE_ID is null then '' else ws.MODULE_ID end,
					ws.ENTITY,
					ws.DOCUMENT_ID,
					ws.WORKFLOW_TEMPLATE_ID,
					case when ws.STARTED is null then now() else ws.STARTED end 
					from b_bp_workflow_state ws
					where ws.ID IN ({$idsSql})
				)
			SQL
		);

		if (count($ids) < $limit)
		{
			return self::FINISH_EXECUTION;
		}

		return self::CONTINUE_EXECUTION;
	}

	public static function bindUser(int $userId): void
	{
		static::bind(0, [$userId]);
	}
}
