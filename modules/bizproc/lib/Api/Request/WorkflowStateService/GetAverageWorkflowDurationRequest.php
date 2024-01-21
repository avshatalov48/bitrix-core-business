<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowStateService;

class GetAverageWorkflowDurationRequest
{
	public function __construct(
		public /*readonly*/ int $templateId,
	)
	{}
}
