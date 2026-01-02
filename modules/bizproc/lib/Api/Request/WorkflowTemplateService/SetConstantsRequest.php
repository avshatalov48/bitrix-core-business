<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowTemplateService;

final class SetConstantsRequest
{
	public function __construct(
		public readonly int $templateId,
		public readonly array $requestConstants,
		public readonly array $complexDocumentType,
		public readonly int $userId,
	)
	{}
}
