<?php

namespace Bitrix\Bizproc\Workflow;

use Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowMetadataTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Bizproc\Workflow\Task\TimelineTask;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class Timeline implements \JsonSerializable
{
	private ?int $userId = null;
	private WorkflowState $workflow;

	public static function createByWorkflowId(string $workflowId): ?static
	{
		$workflow = WorkflowStateTable::query()
			->setSelect([
				'ID',
				'MODULE_ID',
				'ENTITY',
				'DOCUMENT_ID',
				'MODIFIED',
				'STARTED_BY',
				'STARTED',
				'STATE',
				'WORKFLOW_TEMPLATE_ID',
				'TASKS.ID',
				'TASKS.ACTIVITY',
				'TASKS.ACTIVITY_NAME',
				'TASKS.NAME',
				'TASKS.STATUS',
				'TASKS.CREATED_DATE',
				'TASKS.MODIFIED',
				'TASKS.PARAMETERS',
				'TASKS.TASK_USERS.USER_ID',
				'TASKS.TASK_USERS.STATUS',
				'TASKS.TASK_USERS.DATE_UPDATE',
			])
			->setOrder(['TASKS.ID' => 'ASC'])
			->setFilter(['=ID' => $workflowId])
			->exec()
			->fetchObject()
		;

		return $workflow ? new static($workflow) : null;
	}

	public function __construct(WorkflowState $workflow)
	{
		$this->workflow = $workflow;
	}

	public function setUserId(int $userId): static
	{
		$this->userId = $userId;

		return $this;
	}

	public function getWorkflowState(): WorkflowState
	{
		return $this->workflow;
	}

	public function getExecutionTime(): int
	{
		if ($this->isWorkflowRunning())
		{
			return (new DateTime())->getTimestamp() - $this->workflow->getStarted()->getTimestamp();
		}
		else
		{
			return $this->workflow->getModified()->getTimestamp() - $this->workflow->getStarted()->getTimestamp();
		}
	}

	public function getTimeToStart(): ?int
	{
		$metadata = WorkflowMetadataTable::query()
			->setSelect(['START_DURATION'])
			->setFilter(['=WORKFLOW_ID' => $this->workflow->getId()])
			->setLimit(1)
			->exec()
			->fetchObject()
		;

		return $metadata?->getStartDuration();
	}

	/**
	 * @return TimelineTask[]
	 */
	public function getTasks(): array
	{
		$tasks = $this->workflow->getTasks();

		$timelineTasks = [];
		foreach ($tasks as $task)
		{
			$timelineTask = new TimelineTask($task);
			if (isset($this->userId))
			{
				$timelineTask->setUserId($this->userId);
			}

			$timelineTasks[] = $timelineTask;
		}

		return $timelineTasks;
	}

	public function jsonSerialize(): array
	{
		$documentService = \CBPRuntime::getRuntime()->getDocumentService();
		$complexDocumentId = $this->workflow->getComplexDocumentId();
		try
		{
			$complexDocumentType = $documentService->getDocumentType($complexDocumentId);
		}
		catch (SystemException | \Exception $exception)
		{
			$complexDocumentType = null;
		}

		$entityName =
			isset($complexDocumentType)
				? $documentService->getDocumentTypeCaption($complexDocumentType)
				: null
		;

		$documentUrl = $documentService->getDocumentDetailUrl($complexDocumentId);

		return [
			'documentId' => $complexDocumentId,
			'documentType' => $complexDocumentType,
			'moduleName' => $this->getModuleName($complexDocumentId[0] ?? ''),
			'entityName' => $entityName ?? '',
			'documentUrl' => empty($documentUrl) ? null : $documentUrl,
			'documentName' => $documentService->getDocumentName($complexDocumentId) ?? '',
			'isWorkflowRunning' => $this->isWorkflowRunning(),
			'timeToStart' => $this->getTimeToStart(),
			'executionTime' => $this->getExecutionTime(),
			'workflowModifiedDate' => $this->workflow->getModified()->getTimestamp(),
			'started' => $this->workflow->getStarted()->getTimestamp(),
			'startedBy' => $this->workflow->getStartedBy(),
			'tasks' => $this->getTasks(),
		];
	}

	private function isWorkflowRunning(): bool
	{
		return WorkflowInstanceTable::exists($this->workflow->getId());
	}

	private function getModuleName(string $moduleId): string
	{
		return match ($moduleId) {
			'crm' => 'CRM',
			'disk' => Loc::getMessage('BIZPROC_TIMELINE_DISK_MODULE_NAME'),
			'lists' => Loc::getMessage('BIZPROC_TIMELINE_LISTS_MODULE_NAME'),
			'rpa' => 'RPA',
			default => '',
		};
	}
}
