<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowService;

final class StartWorkflowRequest
{
	public function __construct(
		public /* readonly */ int $userId,
		public /* readonly */ int $targetUserId,

		public /* readonly */ int $templateId,
		public /* readonly */ array $complexDocumentId,
		public /* readonly */ array $parameters,
		// TODO - add method CBPDocument::getDocumentCategoryId or add DocumentId class with category id?
		public /* readonly */ ?int $documentCategoryId = null,
		public /* readonly */ ?array $parentWorkflow = null,
		public /* readonly */ ?int $startDuration = null,
	) {}
}