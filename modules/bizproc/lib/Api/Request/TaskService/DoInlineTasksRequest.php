<?php

namespace Bitrix\Bizproc\Api\Request\TaskService;

final class DoInlineTasksRequest
{
	public function __construct(
		public /* readonly */ array $taskIds,
		public /* readonly */ int $userId,
		public /* readonly */ int $newTaskStatusId,
	)
	{
	}
}