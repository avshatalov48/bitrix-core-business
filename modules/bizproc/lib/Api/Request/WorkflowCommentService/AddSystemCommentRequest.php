<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowCommentService;

final class AddSystemCommentRequest
{
	public function __construct(
		public /*readonly*/ string $workflowId,
		public /*readonly*/ int $authorId,
		public /*readonly*/ string $message,
	) {}
}
