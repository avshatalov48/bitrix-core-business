<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowService;

final class TerminateWorkflowRequest
{
	public function __construct(
		public readonly string $workflowId,
		public readonly int $userId,
	) {}
}