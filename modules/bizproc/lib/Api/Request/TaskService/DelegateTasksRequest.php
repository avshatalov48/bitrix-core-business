<?php

namespace Bitrix\Bizproc\Api\Request\TaskService;

final class DelegateTasksRequest
{
	public function __construct(
		public /*readonly*/ array $taskIds,
		public /*readonly*/ int $fromUserId,
		public /*readonly*/ int $toUserId,
		public /*readonly*/ int $currentUserId
	) {}
}
