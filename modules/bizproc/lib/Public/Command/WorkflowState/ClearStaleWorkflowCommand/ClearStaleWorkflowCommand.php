<?php

namespace Bitrix\Bizproc\Public\Command\WorkflowState\ClearStaleWorkflowCommand;

use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\Config\Option;

class ClearStaleWorkflowCommand extends AbstractCommand
{
	private const DEFAULT_LIMIT = 50;
	public readonly int $limit;

	public function __construct()
	{
		$this->limit = (int)Option::get('bizproc', 'clear_workflow_state_limit', self::DEFAULT_LIMIT);
	}

	protected function execute(): ClearStaleWorkflowResult
	{
		$isLimitReached = (new ClearStaleWorkflowCommandHandler())($this);

		return new ClearStaleWorkflowResult($isLimitReached);
	}
}
