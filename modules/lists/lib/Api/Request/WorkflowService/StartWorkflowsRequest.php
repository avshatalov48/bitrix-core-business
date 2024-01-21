<?php

namespace Bitrix\Lists\Api\Request\WorkflowService;

final class StartWorkflowsRequest
{
	public function __construct(
		public /*readonly*/ int $elementId,
		public /*readonly*/int $currentUserId,
		public /*readonly*/ array $parameters = [],
		public /*readonly*/ array $changedFields = [],
		public /*readonly*/ bool $isNewElement = false,
		public /* readonly */ ?int $timeToStart = null,
	)
	{}
}
