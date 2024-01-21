<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowStateService;

final class GetTimelineRequest
{
	public function __construct(
		public /* readonly */ string $workflowId,
		public /* readonly */ int $userId,
	)
	{}
}