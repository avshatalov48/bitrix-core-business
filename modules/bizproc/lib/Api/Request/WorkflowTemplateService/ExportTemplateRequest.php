<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowTemplateService;

final class ExportTemplateRequest
{
	public function __construct(
		public readonly int $id,
		public readonly array $parameters,
		public readonly \CBPWorkflowTemplateUser $user,
		public readonly bool $checkAccess = true,
	)
	{}
}
