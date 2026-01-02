<?php

namespace Bitrix\Bizproc\UI;

use Bitrix\Main\Type\DateTime;

class WorkflowUserGridView extends WorkflowUserView
{
	protected array $usedColumns = ['PROCESS', 'MODIFIED', 'TASK_PROGRESS', 'SUMMARY', 'TASK'];
	protected ?DateTime $modified = null;

	public function setUsedColumns(array $columns): static
	{
		$this->usedColumns = $columns;

		return $this;
	}

	public function setModified(?DateTime $modified): static
	{
		$this->modified = $modified;

		return $this;
	}

	/**
	 * @return array{
	 *     workflowId: string,
	 *     userId: int,
	 *     isCompleted: bool,
	 *     task: ?array,
	 *     document: array{
	 *         url: ?string,
	 *         name: ?string,
	 *     },
	 *     startedById: ?int,
	 *     name?: string,
	 *     typeName?: ?string,
	 *     description?: ?string,
	 *     modified?: ?string,
	 *     commentCnt?: int,
	 *     taskCnt?: int,
	 *     taskProgress?: WorkflowFacesView,
	 *     statusText?: ?string,
	 *     workflowResult?: ?array,
	 *     startedBy?: ?string,
	 *     overdueDate?: string,
	 *     workflowStarted?: string,
	 *     templateName?: string,
	 * }
	 */
	public function jsonSerialize(): array
	{
		$complexDocumentId = $this->workflow->getComplexDocumentId();

		$data = [
			'workflowId' => $this->getId(),
			'userId' => $this->userId,
			'isCompleted' => $this->getIsCompleted(),
			'task' => $this->getFirstRunningTask(),
			'document' => [
				'url' => $this->getDocumentUrl(),
				'name' =>\CBPRuntime::getRuntime()->getDocumentService()->getDocumentName($complexDocumentId),
			],
			'startedById' => $this->getAuthorId(),
		];

		if ($this->isShowProcessDescription())
		{
			$data['name'] = $this->getName();
			$data['typeName'] = $this->getTypeName();
			$data['description'] = $this->getDescription();
		}

		if ($this->isShowModifiedDate())
		{
			$data['modified'] = $this->getTime();
			$data['commentCnt'] = $this->getCommentCounter();
			$data['taskCnt'] = count($this->getTasks());
		}

		if ($this->isShowFaces())
		{
			$data['taskProgress'] = $this->getFaces();
		}

		if ($this->isShowProcessStatus())
		{
			$data['statusText'] = $this->getStatusText();
			$data['workflowResult'] = $this->getIsCompleted() ? $this->getWorkflowResult() : null;
		}

		if ($this->isShowStartedBy())
		{
			$authorView = $this->getAuthorView();
			$data['startedById'] = $authorView?->getUserId();
			$data['startedBy'] = $authorView?->getFullName();
		}

		if ($this->isShowOverdueDate())
		{
			$data['overdueDate'] = \CBPViewHelper::formatDateTime($this->getOverdueDate());
		}

		if ($this->isShowStartedDate())
		{
			$data['workflowStarted'] = \CBPViewHelper::formatDateTime($this->workflow->getStarted());
		}

		if ($this->isShowStatus())
		{
			$data['statusText'] = $this->getStatusText();
		}

		if ($this->isShowTemplateName())
		{
			$data['templateName'] = $this->workflow->getTemplate()?->fillName() ?? '';
		}

		if ($this->isShowDescription())
		{
			$data['description'] = $this->getDescription();
		}

		return $data;
	}

	public function getDescription(): ?string
	{
		return \CBPViewHelper::prepareTaskDescription(
			\CBPHelper::convertBBtoText(
				preg_replace('|\n+|', "\n", parent::getDescription())
			)
		);
	}

	public function getOverdueDate(): ?DateTime
	{
		$task = $this->getFirstRunningTask();

		return $task && isset($task['overdueDate']) ? DateTime::createFromUserTime($task['overdueDate']) : null;
	}

	protected function prepareTasks(array $myTasks): array
	{
		$isRpa = $this->workflow->getModuleId() === 'rpa';
		$userId = $this->userId;

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
				'isInline' => \CBPHelper::getBool($task['IS_INLINE']),
				'controls' => $isRunning ? $this->getTaskControls($task) : [],
				'overdueDate' => $task['~OVERDUE_DATE'] ?? null,
				'url' => $isRpa
					? "/rpa/task/id/$taskId/"
					: sprintf('/company/personal/bizproc/%u/?USER_ID=%u', $taskId, $userId)
				,
				'userId' => $userId,
				'isRunning' => $isRunning,
			];
		}

		return $tasks;
	}

	protected function getTaskControls(array $task): array
	{
		$controls = \CBPDocument::getTaskControls($task, $this->userId);
		$buttons = $controls['BUTTONS'] ?? null;
		if (!empty($buttons))
		{
			foreach ($buttons as &$button)
			{
				if (!empty($button['TEXT']))
				{
					$button['TEXT'] = html_entity_decode(htmlspecialcharsback($button['TEXT']));
				}
			}

			unset($button);
		}

		return ['buttons' => $buttons];
	}

	protected function getTime(): string
	{
		return \CBPViewHelper::formatDateTime($this->modified);
	}

	private function isShowStartedBy(): bool
	{
		return in_array('WORKFLOW_STARTED_BY', $this->usedColumns, true);
	}

	private function isShowOverdueDate(): bool
	{
		return in_array('OVERDUE_DATE', $this->usedColumns, true);
	}

	private function isShowStartedDate(): bool
	{
		return in_array('WORKFLOW_STARTED', $this->usedColumns, true);
	}

	private function isShowStatus(): bool
	{
		return in_array('WORKFLOW_STATE', $this->usedColumns, true);
	}

	private function isShowTemplateName(): bool
	{
		return in_array('WORKFLOW_TEMPLATE_NAME', $this->usedColumns, true);
	}

	private function isShowDescription(): bool
	{
		return in_array('TASK_DESCRIPTION', $this->usedColumns, true);
	}

	private function isShowProcessDescription(): bool
	{
		return in_array('PROCESS', $this->usedColumns, true);
	}

	private function isShowModifiedDate(): bool
	{
		return in_array('MODIFIED', $this->usedColumns, true);
	}

	private function isShowFaces(): bool
	{
		return (
			in_array('TASK_PROGRESS', $this->usedColumns, true)
			|| in_array('SUMMARY', $this->usedColumns, true)
		);
	}

	private function isShowProcessStatus(): bool
	{
		return in_array('TASK', $this->usedColumns, true);
	}
}
