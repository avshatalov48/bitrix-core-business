<?php

namespace Bitrix\Lists\Api\Response\WorkflowService;

use Bitrix\Lists\Api\Response\Response;

class StartWorkflowsResponse extends Response
{
	public function setWorkflowIds(array $workflowIds): static
	{
		$this->data['workflowIds'] = $workflowIds;

		return $this;
	}

	public function getWorkflowIds(): array
	{
		return $this->data['workflowIds'] ?? [];
	}
}
