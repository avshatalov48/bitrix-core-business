<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowCommentService;

use Bitrix\Main\Type\DateTime;

final class CommentRequest
{
	public function __construct(
		public /*readonly*/ string $workflowId,
		public /*readonly*/ int $authorId,
		public /*readonly*/ DateTime $created,
		//public /*readonly*/ DateTime $modified,
		public /*readonly*/ array $mentionUserIds = [],
	) {}
}
