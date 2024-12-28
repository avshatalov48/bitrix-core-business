<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowService;

final class PrepareStartParametersRequest
{
	public function __construct(
		public readonly int $templateId,
		public readonly array $complexDocumentType,
		public readonly array $requestParameters,
		public readonly int $targetUserId,
		public readonly int $eventType = \CBPDocumentEventType::Manual // Create, Edit
	)
	{}
}
