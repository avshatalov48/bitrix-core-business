<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowStateService;

use Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection;
use Bitrix\Bizproc\Workflow\Task\EO_Task_Collection;
use Bitrix\Main\Result;

class GetListResponse extends Result
{
	public function __construct()
	{
		parent::__construct();

		$this->data['workflowTasks'] = [];
	}

	public function getWorkflowStatesCollection(): ?EO_WorkflowState_Collection
	{
		return $this->data['collection'] ?? null;
	}

	public function getWorkflowTasks(string $workflowId): ?EO_Task_Collection
	{
		if (isset($this->data['workflowTasks'][$workflowId]))
		{
			return $this->data['workflowTasks'][$workflowId];
		}

		return null;
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

	public function setWorkflowTasks(string $workflowId, EO_Task_Collection $tasks): static
	{
		$this->data['workflowTasks'][$workflowId] = $tasks;

		return $this;
	}
}
