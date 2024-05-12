<?php

namespace Bitrix\Bizproc\Worker\Task;

use Bitrix\Bizproc\Workflow\Task\TaskSearchContentTable;
use Bitrix\Bizproc\Workflow\Task\TaskTable;
use Bitrix\Main;

class CreateSearchContentStepper extends Main\Update\Stepper
{
	protected static $moduleId = 'bizproc';

	private const STEP_ROWS_LIMIT = 100;

	public function execute(array &$option)
	{
		$lastId = (int)($this->getOuterParams()[0] ?? 0); //deprecated
		$lastModifiedOption = (int)($this->getOuterParams()[1] ?? 0);

		$oldDate = Main\Type\Date::createFromTimestamp(time() - 180 * 86400);

		$taskQuery = TaskTable::query();
		$taskQuery->setSelect(['ID', 'WORKFLOW_ID', 'MODIFIED', 'NAME', 'DESCRIPTION']);
		$taskQuery->setOrder(['MODIFIED' => 'DESC']);
		$taskQuery->setLimit(self::STEP_ROWS_LIMIT);
		$taskQuery->where('MODIFIED', '>', $oldDate);

		if ($lastModifiedOption > 0)
		{
			$taskQuery->where('MODIFIED', '<=', Main\Type\DateTime::createFromTimestamp($lastModifiedOption));
		}

		$taskResult = $taskQuery->exec();
		$modified = null;

		while ($row = $taskResult->fetch())
		{
			$modified = $row['MODIFIED'];
			try
			{
				TaskSearchContentTable::add([
					'TASK_ID' => $row['ID'],
					'WORKFLOW_ID' => $row['WORKFLOW_ID'],
					'SEARCH_CONTENT' => $row['NAME'] . ' ' . $row['DESCRIPTION'],
				]);
			}
			catch (Main\DB\SqlQueryException $e)
			{
				//duplicate, ignore
			}
		}

		$lastModified = $modified instanceof Main\Type\DateTime ? $modified->getTimestamp() : null;

		if ($lastModified === null)
		{
			return self::FINISH_EXECUTION;
		}

		if ($lastModified === $lastModifiedOption)
		{
			--$lastModified; // step one second
		}

		$this->setOuterParams([$lastId, $lastModified]);

		return self::CONTINUE_EXECUTION;
	}
}
