<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowService;

use Bitrix\Bizproc\Result;

final class StartWorkflowResponse extends Result
{
	public function getWorkflowId(): string
	{
		return $this->data['workflowId'];
	}

	public function setWorkflowId(string $workflowId): self
	{
		$this->data['workflowId'] = $workflowId;

		return $this;
	}
}