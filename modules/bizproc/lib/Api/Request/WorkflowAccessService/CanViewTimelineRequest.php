<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowAccessService;

final class CanViewTimelineRequest
{
	public function __construct(
		public readonly string $workflowId,
		public readonly int $userId,
	)
	{}
}
