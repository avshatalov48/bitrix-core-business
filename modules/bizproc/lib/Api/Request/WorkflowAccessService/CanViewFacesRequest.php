<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowAccessService;

final class CanViewFacesRequest
{
	public function __construct(
		public readonly string $workflowId,
		public readonly int $userId,
		public readonly int $currentUserId = 0,
	)
	{}
}
