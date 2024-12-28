<?php

namespace Bitrix\Bizproc\Api\Data\WorkflowFacesService;

use Bitrix\Main\Localization\Loc;

final class ProgressBox
{
	private int $progressTasksCount;

	public function __construct(int $progressTasksCount)
	{
		$this->progressTasksCount = $progressTasksCount;
	}

	public function getData(): array
	{
		return [
			'text' => $this->getFormattedText(),
			'progressTasksCount' => $this->getProgressTasksCount(),
		];
	}

	public function getFormattedText(): string
	{
		return Loc::getMessage(
			'BIZPROC_API_DATA_WORKFLOW_FACES_SERVICE_PROGRESS_BOX_TEXT',
			['#COUNT#' => $this->getProgressTasksCount()]
		) ?? '';
	}

	public function getProgressTasksCount(): int
	{
		return $this->progressTasksCount;
	}

	public static function calculateProgressTasksCount(int $completedTasksCount, bool $isWorkflowFinished): int
	{
		$minTasks = $isWorkflowFinished ? 2 : 1;

		return $completedTasksCount > $minTasks ? $completedTasksCount - $minTasks : 0;
	}
}
