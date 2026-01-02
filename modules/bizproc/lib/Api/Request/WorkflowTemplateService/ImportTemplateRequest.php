<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowTemplateService;

final class ImportTemplateRequest
{
	public function __construct(
		public readonly int $id,
		public readonly array $parameters,
		public readonly string $name,
		public readonly string $description,
		public readonly int $autostart,
		public readonly array $file,
		public readonly \CBPWorkflowTemplateUser $user,
		public readonly bool $checkAccess = true,
	)
	{}
}
