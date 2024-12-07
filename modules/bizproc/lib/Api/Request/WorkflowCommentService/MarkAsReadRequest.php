<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowCommentService;

final class MarkAsReadRequest
{
	public function __construct(
		public /*readonly*/ string $workflowId,
		public /*readonly*/ int $userId,
	) {}
}
