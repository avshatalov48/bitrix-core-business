<?php

namespace Bitrix\Bizproc\UI;

use Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable;
use Bitrix\Main\Type\DateTime;

class WorkflowUserDetailView extends WorkflowUserView
{
	protected ?array $task = null;
	private int $taskId = 0;
	protected ?DateTime $modified = null;

	public function setTaskId(int $taskId): static
	{
		$this->taskId = $taskId;

		return $this;
	}

	public function jsonSerialize(): array
	{
		$userView = \Bitrix\Bizproc\UI\UserView::createFromId($this->userId);

		return [
			'workflowId' => $this->getId(),
			'typeName' => $this->getTypeName(),
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'documentUrl' => $this->getDocumentUrl(),
			'task' => $this->extractTask() ?: null,
			'userName' => $userView?->getFullName(),
			'modified' => $this->getTime(),
			'status' => $this->getStatusText(),
			'result' => $this->getIsCompleted() ? $this->getWorkflowResult() : null,
		];
	}

	public function getTime(): string
	{
		if (is_null($this->modified))
		{
			$row = WorkflowUserTable::getByPrimary(
				['USER_ID' => $this->userId, 'WORKFLOW_ID' => $this->workflow->getId()],
				['select' => ['MODIFIED']]
			)->fetch();
			if ($row)
			{
				$this->modified = $row['MODIFIED'];
			}
		}

		return \CBPViewHelper::formatDateTime($this->modified);
	}

	public function getName(): mixed
	{
		$task = $this->extractTask();
		if ($task)
		{
			return $task['name'];
		}

		return parent::getName();
	}

	public function getDescription(): ?string
	{
		return \CBPViewHelper::prepareTaskDescription(
			\CBPHelper::convertBBtoText(
				preg_replace('|\n+|', "\n", trim($this->getClearDescription()))
			)
		);
	}

	protected function getClearDescription(): string
	{
		$task = $this->extractTask();
		if ($task && isset($task['description']))
		{
			return (string)$task['description'];
		}

		return (string)parent::getDescription();
	}

	protected function extractTask(): ?array
	{
		if (is_null($this->task))
		{
			$this->task = $this->getFirstRunningTask() ?? [];

			if ($this->taskId > 0)
			{
				$task = $this->getTaskById($this->taskId);
				if ($task)
				{
					$this->task = $task;
				}
			}

			if (!$this->task && !$this->isWorkflowAuthorView() && $this->getCompletedTasks())
			{
				$this->task = current($this->getCompletedTasks());
			}
		}

		return $this->task;
	}

	public function getExtractedTaskId(): ?int
	{
		$task = $this->extractTask();

		return $task ? (int)$task['id'] : null;
	}

	protected function prepareTasks(array $myTasks): array
	{
		$tasks = [];
		foreach ($myTasks as $task)
		{
			$isRunning = (int)$task['STATUS'] === \CBPTaskStatus::Running;
			if ($isRunning)
			{
				$isRunning = $this->isRunningTaskUser($task);
			}

			$taskId = (int)$task['ID'];
			$tasks[] = [
				'id' => $taskId,
				'name' => html_entity_decode($task['~NAME']),
				'description' => $task['~DESCRIPTION'],
				'userId' => $this->userId,
				'controls' => $isRunning ? $this->getTaskControls($task) : [],
				'isRunning' => $isRunning,
				'activityName' => $task['~ACTIVITY_NAME'],
				'activity' => $task['~ACTIVITY'],
				'saveVariables' => $task['~PARAMETERS']['SaveVariables'] ?? false,
				'delegationType' => $task['~DELEGATION_TYPE'] ?? null,
			];
		}

		return $tasks;
	}
}
