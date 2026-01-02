<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowStateService;

use Bitrix\Main\Type\DateTime;

class GetExecutionTimeRequest
{
	public function __construct(
		public readonly string $workflowId,
		public readonly ?DateTime $workflowStarted,
		public readonly DateTime $workflowModified,
	)
	{}
}
