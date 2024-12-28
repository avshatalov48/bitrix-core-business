<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowService;

use Bitrix\Bizproc\Result;

final class StartWorkflowResponse extends Result
{
	public function getWorkflowId(): ?string
	{
		$workflowId = $this->data['workflowId'] ?? null;

		return is_string($workflowId) ? $workflowId : null;
	}

	public function setWorkflowId(string $workflowId): self
	{
		$this->data['workflowId'] = $workflowId;

		return $this;
	}
}
