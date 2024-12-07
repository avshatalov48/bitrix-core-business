<?php

namespace Bitrix\Bizproc\Api\Request\TaskService;

final class GetUserTaskByWorkflowIdRequest
{
	public function __construct(
		public /*readonly*/ string $workflowId,
		public /*readonly*/ int $userId,
	) {}
}