<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowAccessService;

final class CheckStartWorkflowRequest
{
	public function __construct(
		public /* readonly */ int $userId,
		public /* readonly */ array $complexDocumentId,
		public /* readonly */ array $parameters = [],
	) {}
}