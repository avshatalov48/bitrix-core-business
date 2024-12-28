<?php

namespace Bitrix\Bizproc\UI;

use Bitrix\Bizproc\Api\Data\UserService\UsersToGet;
use Bitrix\Bizproc\Api\Service\UserService;
use Bitrix\Bizproc\Api\Request\WorkflowFacesService\GetDataRequest;
use Bitrix\Bizproc\Api\Service\WorkflowAccessService;
use Bitrix\Bizproc\Api\Service\WorkflowFacesService;
use Bitrix\Bizproc\UI\Helpers\DurationFormatter;
use Bitrix\Bizproc\Workflow\Task\TaskTable;
use Bitrix\Bizproc\Workflow\WorkflowState;
use Bitrix\Bizproc\WorkflowInstanceTable;
use Bitrix\Bizproc\WorkflowStateTable;
use Bitrix\Main\Type\DateTime;

class WorkflowFacesView implements \JsonSerializable
{
	private const WORKFLOW_FIELDS = ['ID', 'STARTED', 'STARTED_BY', 'MODIFIED', 'META.START_DURATION'];
	private const TASK_FIELDS = ['ID', 'WORKFLOW_ID', 'MODIFIED', 'STATUS', 'CREATED_DATE'];
	private const COMPLETED_TASK_LIMIT = 2;
	private const RUNNING_TASK_LIMIT = 3;

	private string $workflowId;
	private ?WorkflowState $workflow = null;
	private bool $workflowIsCompleted = true;
	private array $usersView = [];
	private array $completedTasks = [];
	private int $completedTasksCount = 0;
	private array $runningTasks = [];
	private int $runningTaskId = 0;
	private array $taskUsersMap = [];

	public function __construct(string $workflowId, ?int $runningTaskId = null)
	{
		$this->workflowId = $workflowId;
		$this->runningTaskId = max($runningTaskId ?? 0, 0);

		$this->loadWorkflow();
		$this->loadTasks($runningTaskId);
		$this->loadUsersView();
	}

	private function loadWorkflow(): void
	{
		if (!$this->workflowId)
		{
			return;
		}

		$this->workflow = (
			WorkflowStateTable::query()
				->setSelect(self::WORKFLOW_FIELDS)
				->where('ID', $this->workflowId)
				->exec()
				->fetchObject()
		);
		$this->workflowIsCompleted = !WorkflowInstanceTable::exists($this->workflowId);
	}

	private function loadTasks(?int $runningTaskId = null): void
	{
		$this->loadCompletedTasks();
		$this->loadRunningTasks($runningTaskId);
	}

	private function loadCompletedTasks(): void
	{
		if (!$this->workflowId)
		{
			return;
		}

		$runningStatus = \CBPTaskStatus::Running;
		$this->completedTasksCount = TaskTable::getCount([
			'=WORKFLOW_ID' => $this->workflowId,
			'!=STATUS' => $runningStatus,
		]);

		if ($this->completedTasksCount > 0)
		{
			$completedTasksIterator = \CBPTaskService::getList(
				['MODIFIED' => 'DESC'],
				['WORKFLOW_ID' => $this->workflowId, '!STATUS' => $runningStatus],
				false,
				['nTopCount' => self::COMPLETED_TASK_LIMIT],
				self::TASK_FIELDS,
			);
			while ($task = $completedTasksIterator->getNext())
			{
				$this->completedTasks[$task['ID']] = $task;
			}
		}
	}

	private function loadRunningTasks(?int $runningTaskId = null): void
	{
		if (!$this->workflowId || $this->workflowIsCompleted)
		{
			return;
		}

		$runningTasksIterator = \CBPTaskService::getList(
			['ID' => 'ASC'],
			['WORKFLOW_ID' => $this->workflowId, 'STATUS' => \CBPTaskStatus::Running],
			false,
			['nTopCount' => self::RUNNING_TASK_LIMIT],
			self::TASK_FIELDS,
		);

		$isTaskIdCorrect = $runningTaskId && $runningTaskId > 0;
		$runningTask = null;
		$hasTasks = false;
		while ($task = $runningTasksIterator->getNext())
		{
			$hasTasks = true;
			if ($isTaskIdCorrect && ($runningTaskId === (int)$task['ID']))
			{
				$runningTask = $task;

				continue;
			}

			$this->runningTasks[$task['ID']] = $task;
		}

		// parallel or already completed taskId
		if ($hasTasks && $isTaskIdCorrect && !$runningTask)
		{
			$iterator = \CBPTaskService::getList(
				[], ['ID' => $runningTaskId], false, false, self::TASK_FIELDS
			);
			$task = $iterator->getNext();
			if ($task && (int)$task['STATUS'] === \CBPTaskStatus::Running)
			{
				$runningTask = $task;
			}
		}

		if ($runningTask)
		{
			$this->runningTasks = [$runningTask['ID'] => $runningTask] + $this->runningTasks;
			$this->runningTaskId = (int)$runningTask['ID'];
		}
	}

	public function jsonSerialize(): array
	{
		$workflowFacesService = new WorkflowFacesService(
			new WorkflowAccessService()
		);

		$request = new GetDataRequest(
			workflowId: $this->workflowId,
			runningTaskId: $this->runningTaskId,
			skipAccessCheck: true,
		);

		$data = $workflowFacesService->getDataBySteps($request);
		if (!$data->isSuccess())
		{
			return [];
		}

		$this->loadUsersView($data->getUniqueUserIds());

		$steps = [];
		foreach ($data->getSteps() as $step)
		{
			if ($step)
			{
				$stepData = $step->getData();
				$stepData['avatarsData'] = $this->getStepAvatars($step->getAvatars());

				if ($step->getDuration() <= 0)
				{
					$stepData['duration'] = $step::getEmptyDurationText();
				}

				$steps[] = $stepData;
			}
		}

		$result = [
			'workflowId' => $this->workflowId,
			'steps' => $steps,
			'timeStep' => $data->getTimeStep()?->getData(),
			'isWorkflowFinished' => $data->getIsWorkflowFinished(),
			'avatars' => $this->getAvatars(),
			'statuses' => $this->getStatuses(),
			'time' => $this->getDuration(),
			'completedTaskCount' => $this->completedTasksCount,
			'workflowIsCompleted' => $this->workflowIsCompleted,
			'runningTaskId' => $this->runningTaskId,
		];

		$progressBox = $data->getProgressBox();
		if ($progressBox && $progressBox->getProgressTasksCount() > 0)
		{
			$result['progressBox'] = $progressBox->getData();
		}

		return $result;
	}

	private function loadUsersView(): void
	{
		if (!$this->workflowId)
		{
			return;
		}

		$taskIds = array_merge(array_keys($this->runningTasks), array_keys($this->completedTasks));

		$taskUsers = $taskIds ? \CBPTaskService::getTaskUsers($taskIds) : [];
		$userIdsByTasks = [];
		foreach ($taskUsers as $taskId => $users)
		{
			$ids = array_column($users, 'USER_ID');
			if ($ids)
			{
				$userIdsByTasks[] = $ids;
				$this->taskUsersMap[$taskId] = $ids;
			}
		}

		$userIds = $userIdsByTasks ? array_merge(...$userIdsByTasks) : [];
		if ($this->workflow)
		{
			$userIds[] = $this->workflow->getStartedBy();
		}

		$userService = new UserService();
		$response = $userService->getUsersView(new UsersToGet($userIds));
		if ($response->isSuccess())
		{
			foreach ($response->getUserViews() as $userView)
			{
				$userId = $userView->getUserId();

				$this->usersView[$userId] = [
					'id' => $userId,
					'avatarUrl' => $userView->getUserAvatar(),
				];
			}
		}
	}

	private function getStepAvatars(array $userIds): array
	{
		$result = [];
		foreach ($userIds as $userId)
		{
			$result[] = $this->getUserById((int)$userId);
		}

		return $result;
	}

	private function getAvatars(): array
	{
		$authorId = ($this->workflow?->getStartedBy()) ?? 0;

		$runningTask = $this->getRunningTask();
		$completedTask = $this->getCompletedTask();
		$doneTask = $this->getDoneTask();

		return [
			'author' => [$this->getUserById($authorId)],
			'running' => $runningTask ? $this->getTaskAvatars((int)$runningTask['ID']) : [],
			'completed' => $completedTask ? $this->getTaskAvatars((int)$completedTask['ID']) : [],
			'done' => $doneTask ? $this->getTaskAvatars((int)$doneTask['ID']) : [],
		];
	}

	private function getTaskAvatars(int $taskId): array
	{
		if (!array_key_exists($taskId, $this->taskUsersMap))
		{
			return [];
		}

		$users = $this->taskUsersMap[$taskId];

		$count = 0;
		$result = [];
		foreach ($users as $userId)
		{
			++$count;
			$result[] = $this->getUserById((int)$userId);

			if ($count === 3)
			{
				break;
			}
		}

		return $result;
	}

	private function getUserById(int $userId): array
	{
		if ($userId <= 0)
		{
			return [
				'id' => 0,
				'avatarUrl' => null,
			];
		}

		if (array_key_exists($userId, $this->usersView))
		{
			return $this->usersView[$userId];
		}

		return [];
	}

	private function getStatuses(): array
	{
		$completedTask = $this->getCompletedTask();
		$doneTask = $this->getDoneTask();

		return [
			'completedSuccess' => \CBPTaskStatus::isSuccess($completedTask['STATUS'] ?? 0),
			'doneSuccess' => \CBPTaskStatus::isSuccess($doneTask['STATUS'] ?? 0),
		];
	}

	private function getDuration(): array
	{
		$authorDuration = DurationFormatter::roundTimeInSeconds(
			($this->workflow?->getMeta()?->getStartDuration()) ?? 0
		);
		$runningDuration = DurationFormatter::roundTimeInSeconds((int)$this->getRunningDuration());
		$completedDuration = DurationFormatter::roundTimeInSeconds((int)$this->getCompletedDuration());
		$doneDuration = DurationFormatter::roundTimeInSeconds((int)$this->getDoneDuration());

		$totalDuration = null;
		if ($this->workflowIsCompleted)
		{
			$totalDuration = $authorDuration + $completedDuration + $doneDuration;
		}

		$currentDuration = $this->getCurrentDuration();

		return [
			'author' => $authorDuration,
			'running' => $runningDuration,
			'completed' => $completedDuration,
			'done' => $doneDuration,
			'total' => $totalDuration !== null ? DurationFormatter::roundUpTimeInSeconds($totalDuration) : null,
			'current' => $currentDuration !== null ? DurationFormatter::roundUpTimeInSeconds($currentDuration) : null,
		];
	}

	private function getRunningDuration(): ?int
	{
		if ($this->workflowIsCompleted)
		{
			return null;
		}

		$currentTimestamp = (new DateTime())->getTimestamp();
		$runningTask = $this->getRunningTask();
		if ($runningTask)
		{
			$startTaskTimestamp = $this->getDateTimeTimestamp($runningTask['CREATED_DATE'] ?? null);
			if (!$startTaskTimestamp)
			{
				$startTaskTimestamp = $this->getDateTimeTimestamp($runningTask['MODIFIED'] ?? null);
			}

			return $startTaskTimestamp ? $currentTimestamp - $startTaskTimestamp : null;
		}

		$completedTask = $this->getCompletedTask();
		if ($completedTask)
		{
			$finishTaskTimestamp = $this->getDateTimeTimestamp($completedTask['MODIFIED'] ?? null);

			return $finishTaskTimestamp ? $currentTimestamp - $finishTaskTimestamp : null;
		}

		$startWorkflowTimestamp = $this->getDateTimeTimestamp($this->workflow?->getStarted());

		return $startWorkflowTimestamp ? ($currentTimestamp - $startWorkflowTimestamp) : null;
	}

	private function getCompletedDuration(): ?int
	{
		$startWorkflowTimestamp = $this->getDateTimeTimestamp($this->workflow?->getStarted());

		$completedTask = $this->getCompletedTask();
		$finishTaskTimestamp = (
			$completedTask ? $this->getDateTimeTimestamp($completedTask['MODIFIED'] ?? null) : null
		);

		return $startWorkflowTimestamp && $finishTaskTimestamp ? ($finishTaskTimestamp - $startWorkflowTimestamp) : null;
	}

	private function getDoneDuration(): ?int
	{
		if (!$this->workflowIsCompleted)
		{
			return null;
		}

		$finishWorkflowTimestamp = $this->getDateTimeTimestamp($this->workflow?->getModified());
		$completedTask = $this->getCompletedTask();
		if ($completedTask)
		{
			$finishTaskTimestamp = $this->getDateTimeTimestamp($completedTask['MODIFIED'] ?? null);

			return (
				$finishWorkflowTimestamp && $finishTaskTimestamp && ($finishWorkflowTimestamp > $finishTaskTimestamp)
					? $finishWorkflowTimestamp - $finishTaskTimestamp
					: null
			);
		}

		$startWorkflowTimestamp = $this->getDateTimeTimestamp($this->workflow?->getStarted());

		return (
			$finishWorkflowTimestamp && $startWorkflowTimestamp
				? ($finishWorkflowTimestamp - $startWorkflowTimestamp)
				: null
		);
	}

	private function getCurrentDuration(): ?int
	{
		$startWorkflowTimestamp = $this->getDateTimeTimestamp($this->workflow?->getStarted());

		return $startWorkflowTimestamp ? time() - $startWorkflowTimestamp : null;
	}

	private function getRunningTask(): array|false
	{
		reset($this->runningTasks);

		return current($this->runningTasks);
	}

	private function getCompletedTask(): array|false
	{
		reset($this->completedTasks);

		$completedTasks = $this->completedTasks;

		$completedTask = current($completedTasks);
		if ($this->workflowIsCompleted && count($completedTasks) > 1)
		{
			$completedTask = next($completedTasks);
		}

		return $completedTask;
	}

	private function getDoneTask(): array|false
	{
		reset($this->completedTasks);

		$completedTasks = $this->completedTasks;

		$completedTask = current($completedTasks);
		$doneTask = false;
		if ($this->workflowIsCompleted && count($completedTasks) > 1)
		{
			$doneTask = $completedTask;
		}

		return $doneTask;
	}

	private function getDateTimeTimestamp($datetime)
	{
		if ($datetime instanceof DateTime)
		{
			return $datetime->getTimestamp();
		}

		if (is_string($datetime) && DateTime::isCorrect($datetime))
		{
			return DateTime::createFromUserTime($datetime)->getTimestamp();
		}

		return null;
	}
}
