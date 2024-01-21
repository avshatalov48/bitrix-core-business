<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowStateService;

use Bitrix\Bizproc\Result;
use Bitrix\Bizproc\Workflow\Timeline;

class GetTimelineResponse extends Result
{
	public function getTimeline(): ?Timeline
	{
		return $this->data['timeline'] ?? null;
	}
}