<?php

namespace Bitrix\Bizproc\Starter\Result;

use Bitrix\Bizproc\Result;

class StartResult extends Result
{
	public function getWorkflowIds(): array
	{
		return $this->data['workflowIds'] ?? [];
	}

	public function addWorkflowIds(array $workflowIds): static
	{
		$this->data['workflowIds'] = array_merge($this->data['workflowIds'] ?? [], $workflowIds);

		return $this;
	}

	public function setWorkflowIds(array $workflowIds): static
	{
		$this->data['workflowIds'] = $workflowIds;

		return $this;
	}

	public function isAutomationTriggerApplied(): bool
	{
		return $this->data['automationTriggerApplied'] ?? false;
	}

	public function setAutomationTriggerApplied(bool $automationTriggerApplied): static
	{
		$this->data['automationTriggerApplied'] = $automationTriggerApplied;

		return $this;
	}

	public function isProcessTriggerApplied(): bool
	{
		return $this->data['processTriggerApplied'] ?? false;
	}

	public function setProcessTriggerApplied(bool $processTriggerApplied): static
	{
		$this->data['processTriggerApplied'] = $processTriggerApplied;

		return $this;
	}

	public function isTriggerApplied(): bool
	{
		if (array_key_exists('triggerApplied', $this->data))
		{
			return $this->data['triggerApplied'];
		}

		return $this->isAutomationTriggerApplied() || $this->isProcessTriggerApplied();
	}

	public function setTriggerApplied(bool $triggerApplied): static
	{
		$this->data['triggerApplied'] = $triggerApplied;

		return $this;
	}
}
