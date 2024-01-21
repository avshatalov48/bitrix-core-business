<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowStateService;

use Bitrix\Bizproc\Result;

class GetAverageWorkflowDurationResponse extends Result
{
	public function setAverageDuration(int $averageDuration): static
	{
		$this->data['averageDuration'] = $averageDuration;

		return $this;
	}

	public function getAverageDuration(): ?int
	{
		$averageTime = $this->data['averageDuration'] ?? null;

		return is_int($averageTime) ? $averageTime : null;
	}
}
