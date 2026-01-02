<?php

namespace Bitrix\Bizproc\Infrastructure\Agent;

use Bitrix\Bizproc\Public\Command\WorkflowState\ClearStaleWorkflowCommand\ClearStaleWorkflowCommand;
use Bitrix\Bizproc\Public\Command\WorkflowState\ClearStaleWorkflowCommand\ClearStaleWorkflowResult;
use Bitrix\Main\Config\Option;

class ClearWorkflowStateAgent extends BaseAgent
{
	private const DEFAULT_OFFSET = 20;

	public static function run()
	{
		$command = new ClearStaleWorkflowCommand();
		/** @var ClearStaleWorkflowResult $result */
		$result = $command->run();

		global $pPERIOD;
		if ($result->isReachedLimit)
		{
			$pPERIOD = (int)Option::get('bizproc', 'clear_workflow_state_offset', self::DEFAULT_OFFSET);
		}
		else
		{
			$pPERIOD = strtotime('tomorrow 01:00') - time();
		}

		return self::next();
	}
}
