<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowStateService;

use Bitrix\Bizproc\Result;
use Bitrix\Bizproc\UI\Helpers\DurationFormatter;

class GetExecutionTimeResponse extends Result
{
	public function setExecutionTime(int $executionTime): static
	{
		$this->data['executionTime'] = $executionTime;

		return $this;
	}

	public function getExecutionTime(): ?int
	{
		$executionTime = $this->data['executionTime'] ?? null;

		return is_int($executionTime) ? $executionTime : null;
	}

	public function getRoundedExecutionTime(): ?int
	{
		$executionTime = $this->getExecutionTime();
		if ($executionTime === null)
		{
			return null;
		}

		return DurationFormatter::roundTimeInSeconds($executionTime);
	}
}
