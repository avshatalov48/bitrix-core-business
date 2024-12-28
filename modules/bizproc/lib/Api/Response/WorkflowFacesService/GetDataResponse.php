<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowFacesService;

use Bitrix\Bizproc\Api\Data\WorkflowFacesService\StepDurations;
use Bitrix\Bizproc\Result;

final class GetDataResponse extends Result
{
	public function setWorkflowIsFinished(bool $workflowIsCompleted): self
	{
		$this->data['workflowIsFinished'] = $workflowIsCompleted;

		return $this;
	}

	public function getWorkflowIsFinished(): bool
	{
		$workflowIsCompleted = $this->data['workflowIsFinished'] ?? true;

		return is_bool($workflowIsCompleted) ? $workflowIsCompleted : true;
	}

	public function setCompletedTasksCount(int $count): self
	{
		$this->data['completedTasksCount'] = $count;

		return $this;
	}

	public function getCompletedTasksCount(): int
	{
		$completedTasksCount = $this->data['completedTasksCount'] ?? 0;

		return is_int($completedTasksCount) ? $completedTasksCount : 0;
	}

	public function setTasksUserIds(array $taskUserIds): self
	{
		$this->data['tasksUserIds'] = $taskUserIds;

		return $this;
	}

	public function getTasksUserIds(): array
	{
		$taskUserIds = $this->data['tasksUserIds'] ?? [];

		return is_array($taskUserIds) ? $taskUserIds : [];
	}

	public function getUniqueTaskUserIds(): array
	{
		$tasksUserIds = $this->getTasksUserIds();

		$ids = [];
		foreach ($tasksUserIds as $userIds)
		{
			foreach ($userIds as $userId)
			{
				$ids[$userId] = $userId;
			}
		}

		return $ids;
	}

	public function getTaskUserIds(int $taskId)
	{
		$tasksUserIds = $this->getTasksUserIds();
		if (isset($tasksUserIds[$taskId]))
		{
			return $tasksUserIds[$taskId];
		}

		return $tasksUserIds[(string)$taskId] ?? [];
	}

	public function setAuthorId(int $authorId): self
	{
		$this->data['authorId'] = $authorId;

		return $this;
	}

	public function getAuthorId(): int
	{
		$authorId = $this->data['authorId'] ?? 0;

		return is_int($authorId) && $authorId >= 0 ? $authorId : 0;
	}

	public function setRunningTask(array $runningTask): self
	{
		$this->data['runningTask'] = $runningTask;

		return $this;
	}

	public function getRunningTask(): ?array
	{
		$runningTask = $this->data['runningTask'] ?? null;

		return is_array($runningTask) ? $runningTask : null;
	}

	public function setCompletedTask(array $completedTask): self
	{
		$this->data['completedTask'] = $completedTask;

		return $this;
	}

	public function getCompletedTask(): ?array
	{
		$completedTask = $this->data['completedTask'] ?? null;

		return is_array($completedTask) ? $completedTask : null;
	}

	public function setDoneTask(array $doneTask): self
	{
		$this->data['doneTask'] = $doneTask;

		return $this;
	}

	public function getDoneTask(): ?array
	{
		$doneTask = $this->data['doneTask'] ?? null;

		return is_array($doneTask) ? $doneTask : null;
	}

	public function isCompletedTaskStatusSuccess(): bool
	{
		$completedTask = $this->getCompletedTask();

		return $completedTask && \CBPTaskStatus::isSuccess($completedTask['STATUS']);
	}

	public function isDoneTaskStatusSuccess(): bool
	{
		$doneTask = $this->getDoneTask();

		return $doneTask && \CBPTaskStatus::isSuccess($doneTask['STATUS']);
	}

	public function setDurations(StepDurations $durations): self
	{
		$this->data['durations'] = $durations;

		return $this;
	}

	public function getDurations(): ?StepDurations
	{
		return $this->data['durations'] ?? null;
	}
}
