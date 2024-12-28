<?php

namespace Bitrix\Bizproc\Api\Data\WorkflowFacesService;

use Bitrix\Bizproc\UI\Helpers\DurationFormatter;

final class StepDurations
{
	public readonly int $authorDuration;
	public readonly int $runningDuration;
	public readonly int $completedDuration;
	public readonly int $doneDuration;

	public function __construct(int $author, int $running, int $completed, int $done)
	{
		$this->authorDuration = max($author, 0);
		$this->runningDuration = max($running, 0);
		$this->completedDuration = max($completed, 0);
		$this->doneDuration = max($done, 0);
	}

	public function getRoundedAuthorDuration(): int
	{
		return $this->getRoundedDuration($this->authorDuration);
	}

	public function getRoundedRunningDuration(): int
	{
		return $this->getRoundedDuration($this->runningDuration);
	}

	public function getRoundedCompletedDuration(): int
	{
		return $this->getRoundedDuration($this->completedDuration);
	}

	public function getRoundedDoneDuration(): int
	{
		return $this->getRoundedDuration($this->doneDuration);
	}

	public function getRoundedDuration(int $durationInSeconds): int
	{
		return DurationFormatter::roundTimeInSeconds($durationInSeconds);
	}
}
