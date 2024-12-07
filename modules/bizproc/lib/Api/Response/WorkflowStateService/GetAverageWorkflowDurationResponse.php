<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowStateService;

use Bitrix\Bizproc\Result;
use Bitrix\Bizproc\UI\Helpers\DurationFormatter;

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

	public function getRoundedAverageDuration(): ?int
	{
		$duration = $this->getAverageDuration();
		if ($duration === null)
		{
			return null;
		}

		return DurationFormatter::roundTimeInSeconds($duration);
	}
}
