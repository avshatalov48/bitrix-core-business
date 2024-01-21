<?php

namespace Bitrix\Bizproc\Api\Request\TaskService;

final class DoTaskRequest
{
	public function __construct(
		public /*readonly*/ int $taskId,
		public /*readonly*/ int $userId,
		public /*readonly*/ array $taskRequest = [],
	) {}
}
