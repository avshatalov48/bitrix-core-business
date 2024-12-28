<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowService;

final class TerminateByTemplateRequest
{
	public function __construct(
		public readonly int $templateId,
		public readonly array $documentId,
		public readonly int $userId,
	) {}
}
