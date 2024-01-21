<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowStateService;

use Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection;
use Bitrix\Main\Result;

class GetListResponse extends Result
{
	public function getWorkflowStatesCollection(): ?EO_WorkflowState_Collection
	{
		return $this->data['collection'] ?? null;
	}

	public function getTotalCount(): int
	{
		return $this->data['totalCount'] ?? $this->getWorkflowStatesCollection()->count();
	}

	public function setWorkflowStatesCollection(EO_WorkflowState_Collection $workflowStates): static
	{
		$this->data['collection'] = $workflowStates;

		return $this;
	}

	public function setTotalCount(int $totalCount): static
	{
		$this->data['totalCount'] = $totalCount;

		return $this;
	}
}
