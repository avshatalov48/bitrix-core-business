<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Data\WorkflowFacesService\ProgressBox;
use Bitrix\Bizproc\Api\Data\WorkflowFacesService\Step;
use Bitrix\Bizproc\Api\Data\WorkflowFacesService\StepDurations;
use Bitrix\Bizproc\Api\Enum\WorkflowFacesService\WorkflowFacesStep;
use Bitrix\Bizproc\Api\Request\WorkflowAccessService\CanViewFacesRequest;
use Bitrix\Bizproc\Api\Request\WorkflowFacesService\GetDataRequest;
use Bitrix\Bizproc\Api\Response\Error;
use Bitrix\Bizproc\Api\Response\WorkflowFacesService\GetDataResponse;
use Bitrix\Bizproc\Api\Response\WorkflowFacesService\GetDataByStepsResponse;
use Bitrix\Bizproc\Workflow\Task\TaskTable;
use Bitrix\Bizproc\Workflow\WorkflowState;
use Bitrix\Bizproc\WorkflowStateTable;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\Type\DateTime;

final class WorkflowFacesService
{
	public const WORKFLOW_DOES_NOT_EXIST_ERROR_CODE = 'WORKFLOW_DOES_NOT_EXIST';
	private const WORKFLOW_FIELDS = ['ID', 'STARTED', 'STARTED_BY', 'MODIFIED', 'META.START_DURATION'];
	private const TASK_FIELDS = ['ID', 'WORKFLOW_ID', 'MODIFIED', 'STATUS', 'CREATED_DATE'];
	private const COMPLETED_TASK_LIMIT = 2;
	private const RUNNING_TASK_LIMIT = 3;

	private WorkflowAccessService $accessService;

	public function __construct(
		WorkflowAccessService $accessService
	)
	{
		$this->accessService = $accessService;
	}

	public function getData(GetDataRequest $request): GetDataResponse
	{
		if (empty($request->workflowId))
		{
			return GetDataResponse::createError(new Error('empty workflowId')); // todo: Loc
		}

		if (!$request->skipAccessCheck)
		{
			$canViewResponse = $this->accessService->canViewFaces(
				new CanViewFacesRequest(
					$request->workflowId,
					max($request->accessUserId ?? 0, 0),
					max($request->currentUserId ?? 0, 0),
				)
			);
			if (!$canViewResponse->isSuccess())
			{
				return GetDataResponse::createError($this->accessService::getViewAccessDeniedError());
			}
		}

		$workflow =
			WorkflowStateTable::query()
				->setSelect(self::WORKFLOW_FIELDS)
				->where('ID', $request->workflowId)
				->exec()
				->fetchObject()
		;
		if (!$workflow)
		{
			return GetDataResponse::createError(
				new Error(
					'workflow does not exist', // todo: Loc
					self::WORKFLOW_DOES_NOT_EXIST_ERROR_CODE
				)
			);
		}

		$response = new GetDataResponse();
		$response->setAuthorId($workflow->getStartedBy() ?? 0);

		$response->setWorkflowIsFinished(\CBPHelper::isWorkflowFinished($request->workflowId));

		$response->setCompletedTasksCount(
			TaskTable::getCount([
				'=WORKFLOW_ID' => $request->workflowId,
				'!=STATUS' => \CBPTaskStatus::Running,
			])
		);

		$completedTasks =
			$response->getCompletedTasksCount() > 0
				? $this->getCompletedTasks($request->workflowId)
				: []
		;

		$runningTasks =
			$response->getWorkflowIsFinished()
				? []
				: $this->getRunningTasks($request->workflowId, $request->runningTaskId)
		;

		$response->setTasksUserIds($this->getTasksUserIds($completedTasks, $runningTasks, $request->taskUsersLimit));

		$runningTask = current($runningTasks);
		if ($runningTask)
		{
			$response->setRunningTask($runningTask);
		}

		$completedTask = current($completedTasks);
		$doneTask = false;
		if ($response->getWorkflowIsFinished() && count($completedTasks) > 1)
		{
			$doneTask = $completedTask;
			$completedTask = next($completedTasks);
		}
		if ($doneTask)
		{
			$response->setDoneTask($doneTask);
		}
		if ($completedTask)
		{
			$response->setCompletedTask($completedTask);
		}

		$authorDuration = $workflow->getMeta()?->getStartDuration() ?? 0;
		$runningDuration = (
			$response->getWorkflowIsFinished()
				? 0
				: $this->getRunningDuration($workflow, $runningTask ?: null, $completedTask ?: null)
		);
		$completedDuration = $this->getCompletedDuration($workflow, $completedTask ?: null);
		$doneDuration = (
			$response->getWorkflowIsFinished()
				? $this->getDoneDuration($workflow, $completedTask ?: null)
				: 0
		);

		$response->setDurations(new StepDurations($authorDuration, $runningDuration, $completedDuration, $doneDuration));

		return $response;
	}

	private function getCompletedTasks(string $workflowId): array
	{
		$completedTasksIterator = \CBPTaskService::getList(
			['MODIFIED' => 'DESC'],
			['WORKFLOW_ID' => $workflowId, '!STATUS' => \CBPTaskStatus::Running],
			false,
			['nTopCount' => self::COMPLETED_TASK_LIMIT],
			self::TASK_FIELDS,
		);
		$completedTasks = [];
		while ($task = $completedTasksIterator->getNext())
		{
			$completedTasks[$task['ID']] = $task;
		}

		return $completedTasks;
	}

	private function getRunningTasks(string $workflowId, ?int $runningTaskId = null): array
	{
		$isTaskIdCorrect = $runningTaskId && $runningTaskId > 0;

		$runningTask = null;
		$hasTasks = false;

		$runningTasksIterator = \CBPTaskService::getList(
			['ID' => 'ASC'],
			['WORKFLOW_ID' => $workflowId, 'STATUS' => \CBPTaskStatus::Running],
			false,
			['nTopCount' => self::RUNNING_TASK_LIMIT],
			self::TASK_FIELDS,
		);

		$runningTasks = [];
		while ($task = $runningTasksIterator->getNext())
		{
			$hasTasks = true;
			if ($isTaskIdCorrect && ($runningTaskId === (int)$task['ID']))
			{
				$runningTask = $task;

				continue;
			}

			$runningTasks[$task['ID']] = $task;
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
			$runningTasks = [$runningTask['ID'] => $runningTask] + $runningTasks; // merge, where $runningTaskId is first task
		}

		return $runningTasks;
	}

	private function getTasksUserIds(array $completedTasks, array $runningTasks, int $usersLimit): array
	{
		$taskIds = array_merge(array_keys($runningTasks), array_keys($completedTasks));
		$taskUsers = $taskIds ? \CBPTaskService::getTaskUsers($taskIds) : [];

		$taskUserIdsMap = [];
		foreach ($taskUsers as $taskId => $users)
		{
			$ids = array_slice(array_column($users, 'USER_ID'), 0, $usersLimit);
			Collection::normalizeArrayValuesByInt($ids, false);
			$taskUserIdsMap[$taskId] = $ids;
		}

		return $taskUserIdsMap;
	}

	private function getRunningDuration(WorkflowState $workflow, ?array $runningTask, ?array $completedTask): int
	{
		$currentTimestamp = time();
		if ($runningTask)
		{
			$startTaskTimestamp = $this->getDateTimeTimestamp($runningTask['CREATED_DATE'] ?? null);
			if (!$startTaskTimestamp)
			{
				$startTaskTimestamp = $this->getDateTimeTimestamp($runningTask['MODIFIED'] ?? null);
			}

			return $startTaskTimestamp ? $currentTimestamp - $startTaskTimestamp : 0;
		}

		if ($completedTask)
		{
			$finishTaskTimestamp = $this->getDateTimeTimestamp($completedTask['MODIFIED'] ?? null);

			return $finishTaskTimestamp ? $currentTimestamp - $finishTaskTimestamp : 0;
		}

		$startWorkflowTimestamp = $this->getDateTimeTimestamp($workflow->getStarted());

		return $startWorkflowTimestamp ? ($currentTimestamp - $startWorkflowTimestamp) : 0;
	}

	private function getCompletedDuration(WorkflowState $workflow, ?array $completedTask): int
	{
		$startWorkflowTimestamp = $this->getDateTimeTimestamp($workflow->getStarted());

		$finishTaskTimestamp = (
			$completedTask ? $this->getDateTimeTimestamp($completedTask['MODIFIED'] ?? null) : null
		);

		return $startWorkflowTimestamp && $finishTaskTimestamp ? ($finishTaskTimestamp - $startWorkflowTimestamp) : 0;
	}

	private function getDoneDuration(WorkflowState $workflow, ?array $completedTask): int
	{
		$finishWorkflowTimestamp = $this->getDateTimeTimestamp($workflow->getModified());
		if ($completedTask)
		{
			$finishTaskTimestamp = $this->getDateTimeTimestamp($completedTask['MODIFIED'] ?? null);

			return (
				$finishWorkflowTimestamp && $finishTaskTimestamp && ($finishWorkflowTimestamp > $finishTaskTimestamp)
					? $finishWorkflowTimestamp - $finishTaskTimestamp
					: 0
			);
		}

		$startWorkflowTimestamp = $this->getDateTimeTimestamp($workflow->getStarted());

		return (
			$finishWorkflowTimestamp && $startWorkflowTimestamp
				? ($finishWorkflowTimestamp - $startWorkflowTimestamp)
				: 0
		);
	}

	private function getDateTimeTimestamp($datetime): ?int
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

	public function getDataBySteps(GetDataRequest $request): GetDataByStepsResponse
	{
		$response = new GetDataByStepsResponse();

		$data = $this->getData($request);
		if (!$data->isSuccess())
		{
			return $response->addErrors($data->getErrors());
		}

		$response
			->setIsWorkflowFinished($data->getWorkflowIsFinished())
			->setAuthorStep($this->createAuthorStep($data))
			->setFirstStep($response->getAuthorStep())
		;

		$completedTasksCount = $data->getCompletedTasksCount();
		if ($completedTasksCount > 0)
		{
			$response->setProgressBox(
				new ProgressBox(
					ProgressBox::calculateProgressTasksCount($completedTasksCount, $data->getWorkflowIsFinished())
				)
			);
		}

		if ($data->getCompletedTask())
		{
			$response
				->setCompletedStep($this->createCompletedStep($data))
				->setSecondStep($response->getCompletedStep())
			;
		}

		if ($data->getWorkflowIsFinished())
		{
			$response->setDoneStep($this->createDoneStep($data));
		}
		else
		{
			$response->setRunningStep($this->createRunningStep($data));
		}

		/** @var Step $finalStep */
		$finalStep = $response->getDoneStep() ?? $response->getRunningStep();
		if ($response->getSecondStep())
		{
			$response->setThirdStep($finalStep);
		}
		else
		{
			$response->setSecondStep($finalStep);
		}

		$response->setTimeStep($this->createTimeStep($data));

		return $response;
	}

	private function createAuthorStep(GetDataResponse $data): Step
	{
		$authorId = $data->getAuthorId();
		$duration = $data->getDurations();

		return (
			(new Step(WorkflowFacesStep::Author))
				->setAvatars($authorId > 0 ? [$authorId] : [])
				->setDuration((int)($duration?->getRoundedAuthorDuration()))
		);
	}

	private function createCompletedStep(GetDataResponse $data): Step
	{
		$completedTask = $data->getCompletedTask();
		$completedTaskId = (int)$completedTask['ID'];
		$duration = $data->getDurations();

		return (
			(new Step(WorkflowFacesStep::Completed))
				->setAvatars($data->getTaskUserIds($completedTaskId))
				->setDuration((int)($duration?->getRoundedCompletedDuration()))
				->setSuccess($data->isCompletedTaskStatusSuccess())
				->setTaskId($completedTaskId)
		);
	}

	private function createDoneStep(GetDataResponse $data): Step
	{
		$doneTask = $data->getDoneTask();
		$doneTaskId = $doneTask ? (int)$doneTask['ID'] : null;
		$duration = $data->getDurations();

		return (
			(new Step(WorkflowFacesStep::Done))
				->setAvatars($doneTaskId ? $data->getTaskUserIds($doneTaskId) : [])
				->setDuration((int)($duration?->getRoundedDoneDuration()))
				->setSuccess($data->isDoneTaskStatusSuccess())
				->setTaskId($doneTaskId ?: 0)
		);
	}

	private function createRunningStep(GetDataResponse $data): Step
	{
		$runningTask = $data->getRunningTask();
		$runningTaskId = $runningTask ? $runningTask['ID'] : null;
		$duration = $data->getDurations();

		return (
			(new Step(WorkflowFacesStep::Running))
				->setAvatars($runningTaskId ? $data->getTaskUserIds($runningTaskId) : [])
				->setDuration((int)($duration?->getRoundedRunningDuration()))
				->setTaskId($runningTaskId ? : 0)
		);
	}

	private function createTimeStep(GetDataResponse $data): Step
	{
		if ($data->getWorkflowIsFinished())
		{
			$durations = $data->getDurations();
			$totalDuration = (
				($durations?->getRoundedAuthorDuration() ?? 0)
				+ ($durations?->getRoundedCompletedDuration() ?? 0)
				+ ($durations?->getRoundedDoneDuration() ?? 0)
			);

			return (
				(new Step(WorkflowFacesStep::TimeFinal))
					->setDuration($totalDuration)
			);
		}

		return new Step(WorkflowFacesStep::TimeInWork);
	}

	public static function getStepById(string $id): ?Step
	{
		$step = WorkflowFacesStep::tryFrom($id);

		return $step ? new Step($step) : null;
	}
}
