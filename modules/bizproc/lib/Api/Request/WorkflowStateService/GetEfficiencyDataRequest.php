<?php

namespace Bitrix\Bizproc\Api\Request\WorkflowStateService;

class GetEfficiencyDataRequest
{
	public function __construct(
		public readonly ?int $executionTime,
		public readonly ?int $averageDuration,
	)
	{}
}
