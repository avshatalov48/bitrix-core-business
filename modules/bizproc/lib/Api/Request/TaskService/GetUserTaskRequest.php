<?php

namespace Bitrix\Bizproc\Api\Request\TaskService;

final class GetUserTaskRequest
{
	public function __construct(
		public /*readonly*/ int $taskId,
		public /*readonly*/ int $userId,
	)
	{}
}
