<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowFacesService;

final class GetDataRequest
{
	public function __construct(
		public readonly string $workflowId,
		public readonly ?int $runningTaskId = null,
		public readonly int $taskUsersLimit = 3,

		/* access check zone */
		public readonly bool $skipAccessCheck = false,
		public readonly ?int $currentUserId = null,
		public readonly ?int $accessUserId = null,
	)
	{}
}
