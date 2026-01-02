<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowStateService;

use Bitrix\Bizproc\Result;

class GetEfficiencyDataResponse extends Result
{

	public function setAverageDuration(?int $averageDuration): static
	{
		$this->data['averageDuration'] = $averageDuration;

		return $this;
	}

	public function getAverageDuration(): ?int
	{
		$averageTime = $this->data['averageDuration'] ?? null;

		return is_int($averageTime) ? $averageTime : null;
	}

	public function setExecutionTime(?int $executionTime): static
	{
		$this->data['executionTime'] = $executionTime;

		return $this;
	}

	public function getExecutionTime(): ?int
	{
		$executionTime = $this->data['executionTime'] ?? null;

		return is_int($executionTime) ? $executionTime : null;
	}

	public function setEfficiency(string $efficiency): static
	{
		$this->data['efficiency'] = $efficiency;

		return $this;
	}

	public function getEfficiency(): string
	{
		return $this->data['efficiency'];
	}
}
