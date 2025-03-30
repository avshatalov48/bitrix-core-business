<?php

namespace Bitrix\Bizproc\Task;

use Bitrix\Bizproc\Error;
use Bitrix\Bizproc\FieldType;
use Bitrix\Bizproc\Result;
use Bitrix\Bizproc\Task\Data\ExternalEventTask\ExternalEventTaskData;
use Bitrix\Bizproc\Task\Data\ExternalEventTask\UsersByStatus;
use Bitrix\Bizproc\Task\Data\TaskData;
use Bitrix\Bizproc\Task\Dto\AddTaskDto;
use Bitrix\Bizproc\Task\Dto\CompleteTaskDto;
use Bitrix\Bizproc\Task\Dto\DeleteTaskDto;
use Bitrix\Bizproc\Task\Dto\ExternalEventTask\AddCommandDto;
use Bitrix\Bizproc\Task\Dto\ExternalEventTask\RemoveCommandDto;
use Bitrix\Bizproc\Task\Dto\MarkCompletedTaskDto;
use Bitrix\Bizproc\Task\Dto\TaskSettings;
use Bitrix\Bizproc\Task\Dto\UpdateTaskDto;
use Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable;
use Bitrix\Main\Localization\Loc;

final class ExternalEventTask extends BaseTask
{
	/**
	 * @var ExternalEventTaskData $task
	 */
	protected TaskData $task;

	private const BUTTON_NAME = 'execute';
	private const FIELD_NAME = 'command';
	private const USER_STATUS = \CBPTaskUserStatus::Ok;

	public function __construct(TaskData $task, int $userId)
	{
		parent::__construct($task, $userId);

		$this->task = ExternalEventTaskData::createFromArray($task->getData());
	}

	public static function getAssociatedActivity(): string
	{
		return 'HandleExternalEventActivity';
	}

	public static function addToTask(AddCommandDto $command): ?ExternalEventTask
	{
		$runtime = \CBPRuntime::getRuntime();
		if (!$runtime->hasWorkflow($command->workflowId))
		{
			return null;
		}

		$currentTask = self::getCurrentTask($command->workflowId);
		if ($currentTask)
		{
			$externalTask = new self(TaskData::createFromArray($currentTask), 0);

			$users = $externalTask->getTaskUsersByStatus();
			if ($users)
			{
				$intersect = array_intersect($users->completed, $command->userIds);
				if ($intersect)
				{
					$externalTask->markTaskUnCompleted($intersect);
					$externalTask->addUsersToCompletedUsersParameter($intersect);
				}

				$allUsers = array_merge($users->completed, $users->waiting);
				$diff = array_diff($command->userIds, $allUsers);
				if ($diff)
				{
					$newUsers = array_merge($allUsers, $command->userIds);
					$externalTask->update(new UpdateTaskDto(users: $newUsers));
				}
			}

			return $externalTask;
		}

		$workflow = $runtime->getWorkflow($command->workflowId);

		return self::add(new AddTaskDto(
			workflowId: $command->workflowId,
			complexDocumentId: $workflow->getDocumentId(),
			userIds: $command->userIds,
			activityName: $command->id,
		));
	}

	public static function add(Dto\AddTaskDto $task): ?ExternalEventTask
	{
		return parent::add(new AddTaskDto(
			workflowId: $task->workflowId,
			complexDocumentId: $task->complexDocumentId,
			userIds: $task->userIds,
			activityName: $task->activityName,
			settings: new TaskSettings(
				name: Loc::getMessage('BIZPROC_LIB_TASK_EXTERNAL_EVENT_TASK_NAME') ?? '',
				description: Loc::getMessage('BIZPROC_LIB_TASK_EXTERNAL_EVENT_TASK_DESCRIPTION') ?? '',
				isInline: false,
				delegationType: \CBPTaskDelegationType::ExactlyNone,
				parameters: ['COMPLETED_USERS' => []],
			),
		));
	}

	public static function getCurrentTask(string $workflowId): bool|array
	{
		if (!$workflowId)
		{
			return false;
		}

		$taskService = self::getTaskService();

		return $taskService::getList(
			[],
			[
				'WORKFLOW_ID' => $workflowId,
				'ACTIVITY' => self::getAssociatedActivity(),
				'STATUS' => \CBPTaskStatus::Running,
			],
			false,
			false,
			['ID', 'WORKFLOW_ID', 'PARAMETERS']
		)->fetch();
	}

	public function markCompleted(MarkCompletedTaskDto $markCompletedData): Result
	{
		if ($this->userId <= 0)
		{
			return Result::createError(new Error('negative userId', 'negative userId'));
		}

		if (!$this->getEvents())
		{
			$this->markTaskCompleted([$this->userId]);
		}
		else
		{
			$this->addUsersToCompletedUsersParameter([$this->userId]);
		}

		return Result::createOk();
	}

	private function addUsersToCompletedUsersParameter(array $userIds): void
	{
		$completedUsers = $this->task->getCompletedUsersParameter();
		if ($completedUsers !== null && $userIds)
		{
			$isAdded = false;
			foreach ($userIds as $userId)
			{
				if (!in_array($userId, $completedUsers, true))
				{
					$isAdded = true;
					$completedUsers[] = $userId;
				}
			}

			if ($isAdded)
			{
				$this->task->setCompletedUsersParameter($completedUsers);
				$this->update(new UpdateTaskDto(parameters: $this->task->getParameters()));
			}
		}
	}

	public function removeFromTask(RemoveCommandDto $command): Result
	{
		$users = $this->getTaskUsersByStatus($command->userIds);
		if (!$users) // no users, no task
		{
			return Result::createOk();
		}

		if (!$users->completed && !$users->waiting && !$users->markCompleted)
		{
			$this->delete(new DeleteTaskDto());

			return Result::createOk();
		}

		if ($users->markCompleted)
		{
			$this->markTaskCompleted($users->markCompleted);
		}

		if (!$users->waiting)
		{
			$this->complete(new CompleteTaskDto());

			return Result::createOk();
		}

		$actualUsers = array_merge($users->completed, $users->waiting, $users->markCompleted);
		$this->update(new UpdateTaskDto(users: $actualUsers));

		return Result::createOk();
	}

	public function complete(CompleteTaskDto $completeData): Result
	{
		return parent::complete(new CompleteTaskDto(status: \CBPTaskStatus::CompleteOk));
	}

	private function getTaskUsersByStatus(array $removeUsers = []): ?UsersByStatus
	{
		$taskService = self::getTaskService();

		$taskUsers = $taskService::getTaskUsers($this->getId())[$this->getId()] ?? [];
		if (!$taskUsers)
		{
			return null;
		}

		$state = $this->getWorkflowState();
		if (!$state)
		{
			return null;
		}

		return new UsersByStatus($taskUsers, $state, $removeUsers, $this->task->getCompletedUsersParameter());
	}

	private function markTaskCompleted(array $userIds): void
	{
		$taskService = self::getTaskService();
		foreach ($userIds as $userId)
		{
			$taskService->markCompleted($this->getId(), $userId, self::USER_STATUS);
		}
	}

	private function markTaskUnCompleted(array $userIds): void
	{
		$taskService = self::getTaskService();
		$taskService->markUnCompleted($this->getId(), $userIds);
	}

	public function getTaskControls(): array
	{
		$field = array_merge(
			$this->getAllowableCommandFieldProperty(),
			[
				'Id' => self::FIELD_NAME,
				'Name' => Loc::getMessage('BIZPROC_LIB_TASK_EXTERNAL_EVENT_TASK_FIELD_NAME') ?? '',
			]
		);

		return [
			'BUTTONS' => [
				[
					'TYPE' => 'submit',
					'TARGET_USER_STATUS' => self::USER_STATUS,
					'NAME' => self::BUTTON_NAME,
					'VALUE' => 'Y',
					'TEXT' => Loc::getMessage('BIZPROC_LIB_TASK_EXTERNAL_EVENT_TASK_SEND_BUTTON_NAME') ?? '',
				],
			],
			'FIELDS' => [$field],
		];
	}

	private function getAllowableCommandFieldProperty(): array
	{
		$options = ['' => Loc::getMessage('BIZPROC_LIB_TASK_EXTERNAL_EVENT_TASK_DEFAULT_OPTION_NAME')];

		$events = $this->getEvents();
		if ($events)
		{
			$options = array_merge($options, $events);
		}

		return [
			'Type' => FieldType::SELECT,
			'Required' => true,
			'Options' => $options,
			'Settings' => ['ShowEmptyValue' => false],
		];
	}

	public function postTaskForm(array $request): Result
	{
		$fields = $request['fields'] ?? $request;

		$command = trim($fields[self::FIELD_NAME] ?? '');
		if (empty($command))
		{
			if (
				!$this->getEvents()
				&& $this->removeFromTask(new RemoveCommandDto(id: $command, userIds: [$this->userId]))->isSuccess()
			)
			{
				WorkflowUserTable::syncOnTaskUpdated($this->task->workflowId);
			}

			return Result::createOk();
		}

		if (!$this->validateCommand($command))
		{
			return Result::createError(new Error(
				Loc::getMessage('BIZPROC_LIB_TASK_EXTERNAL_EVENT_TASK_ERROR_UNKNOWN_COMMAND') ?? '',
				'unknown_command'
			));
		}

		$eventParameters = ['Groups' => $this->getUserGroups($this->userId), 'User' => $this->userId];

		\CBPRuntime::sendExternalEvent($this->task->workflowId, $command, $eventParameters);

		return Result::createOk();
	}

	private function validateCommand(string $command): bool
	{
		return array_key_exists($command, $this->getEvents());
	}

	private function getEvents(): array
	{
		if ($this->userId > 0)
		{
			$state = $this->getWorkflowState();
			if ($state)
			{
				return $this->getAllowableEventsFromState($this->userId, $state);
			}
		}

		return [];
	}

	private function getWorkflowState(): ?array
	{
		$workflowId = $this->task->workflowId;
		$documentId = $this->task->getDocumentId();

		if ($workflowId !== '' && $documentId)
		{
			$state = \CBPDocument::getDocumentState($documentId, $workflowId)[$workflowId] ?? null;
			if ($state)
			{
				return $state;
			}
		}

		return null;
	}

	private function getAllowableEventsFromState(int $userId, array $state): array
	{
		$allowableEvents = \CBPDocument::getAllowableEvents($userId, $this->getUserGroups($userId), $state, true);
		$events = [];
		foreach ($allowableEvents as $event)
		{
			$events[$event['NAME']] = $event['TITLE'];
		}

		return $events;
	}

	private function getUserGroups(int $userId): array
	{
		$currentUser = \Bitrix\Main\Engine\CurrentUser::get();

		return (int)$currentUser->getId() === $userId ? $currentUser->getUserGroups() : \CUser::GetUserGroup($userId);
	}
}
