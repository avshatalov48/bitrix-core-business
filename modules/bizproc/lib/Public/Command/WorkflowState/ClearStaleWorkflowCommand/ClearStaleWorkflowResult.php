<?php

namespace Bitrix\Bizproc\Public\Command\WorkflowState\ClearStaleWorkflowCommand;

use Bitrix\Main\Result;

class ClearStaleWorkflowResult extends Result
{
	public function __construct(public readonly bool $isReachedLimit = false)
	{
		parent::__construct();
	}
}
