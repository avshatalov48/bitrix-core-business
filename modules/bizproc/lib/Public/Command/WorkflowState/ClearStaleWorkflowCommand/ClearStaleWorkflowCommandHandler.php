<?php

namespace Bitrix\Bizproc\Public\Command\WorkflowState\ClearStaleWorkflowCommand;

use Bitrix\Bizproc\Public\Provider\WorkflowStateProvider;
use Bitrix\Main\Type\Date;

class ClearStaleWorkflowCommandHandler
{

	public function __construct()
	{}

	public function __invoke(ClearStaleWorkflowCommand $command): bool
	{
		$workflows = (new WorkflowStateProvider)->getStaleWorkflowsWithoutTasks(
			['ID'],
			Date::createFromTimestamp(strtotime('-1 year')),
			$command->limit,
		);

		foreach ($workflows as $workflow)
		{
			\CBPDocument::killCompletedWorkflowWithoutTasks($workflow->getId());
		}

		return $workflows->count() === $command->limit;
	}
}
