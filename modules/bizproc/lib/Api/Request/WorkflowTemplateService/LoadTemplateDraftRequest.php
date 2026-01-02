<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowTemplateService;

final class LoadTemplateDraftRequest
{
	public function __construct(
		public readonly int $id,
	)
	{}
}
