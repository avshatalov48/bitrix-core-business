<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateFilter;
use Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateToGet;
use Bitrix\Bizproc\Api\Request\WorkflowAccessService\CanViewTimelineRequest;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetAverageWorkflowDurationRequest;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetExecutionTimeRequest;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetEfficiencyDataRequest;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetTimelineRequest;
use Bitrix\Bizproc\Api\Response\Error;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetAverageWorkflowDurationResponse;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetExecutionTimeResponse;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetEfficiencyDataResponse;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetFullFilledListResponse;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetListResponse;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetTimelineResponse;
use Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection;
use Bitrix\Bizproc\Workflow\Entity\WorkflowDurationStatTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable;
use Bitrix\Bizproc\Workflow\Task\EO_Task_Collection;
use Bitrix\Bizproc\Workflow\Task\TaskTable;
use Bitrix\Bizproc\Workflow\Timeline;
use Bitrix\Bizproc\Workflow\WorkflowState;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class WorkflowStateService
{
	private const CONVERTER_VERSION = 2;

	private const THREE_DAYS_IN_SECONDS = 259200; // 3600 * 24 *3

	public function getList(WorkflowStateToGet $toGet): GetListResponse
	{
		$this->convertProcesses($toGet->getFilterUserId());

		$response = new GetListResponse();
		$responseCollection = new EO_WorkflowState_Collection();

		$query = WorkflowUserTable::query()
			->addSelect('WORKFLOW_ID')
			->addSelect('MODIFIED')
			->addSelect('WORKFLOW_STATE.STARTED', 'WORKFLOW_STARTED')
			->setFilter($toGet->getOrmFilter())
			->countTotal($toGet->isCountingTotal())
		;
		if ($toGet->getFilterSearchQuery())
		{
			$query->addSelect('SEARCH_CONTENT');
		}

		if ($toGet->getFilterPresetId() === WorkflowStateFilter::PRESET_IN_WORK)
		{
			$unionQuery =
				WorkflowUserTable::query()
					->setSelect($query->getSelect())
					->setFilter($this->applyStatusFilter($query->getFilter()))
			;

			$query
				->setFilter($this->applyCommentsFilter($query->getFilter()))
				->union($unionQuery)
				->setUnionOrder($toGet->getOrder())
				->setUnionLimit($toGet->getLimit())
				->setUnionOffset($toGet->getOffset())
			;
		}
		else
		{
			$query
				->setOrder($toGet->getOrder())
				->setLimit($toGet->getLimit())
				->setOffset($toGet->getOffset())
			;
		}

		$queryResult = $query->exec();

		if ($toGet->isCountingTotal())
		{
			$response->setTotalCount($queryResult->getCount());
		}
		$workflowStates = $queryResult->fetchAll();
		$ids = array_column($workflowStates, 'WORKFLOW_ID');
		$mods = array_column($workflowStates, 'MODIFIED');

		if ($ids)
		{
			$query = WorkflowStateTable::query()->setSelect($toGet->getSelect());
			if (count($ids) === 1)
			{
				$query->where('ID', '=', $ids[0]);
			}
			else
			{
				$query->whereIn('ID', $ids);
			}

			$collection = $query->exec()->fetchCollection();

			foreach ($ids as $k => $id)
			{
				$workflowState = $collection->getByPrimary($id);
				if ($workflowState)
				{
					$responseCollection->add($workflowState);
					$response->setUserModified($id, $mods[$k]);
					$workflowTasks = $this->getWorkflowTasks($workflowState, $toGet);
					if (isset($workflowTasks))
					{
						$response->setWorkflowTasks($id, $workflowTasks);
					}
				}
			}
		}

		return $response->setWorkflowStatesCollection($responseCollection);
	}

	private function applyStatusFilter(array $filter): array
	{
		$filter[] = [
			'=WORKFLOW_STATUS' => new SqlExpression('?i', WorkflowUserTable::WORKFLOW_STATUS_ACTIVE),
			0 => [
				'LOGIC' => 'OR',
				'=IS_AUTHOR' => 1,
				'=TASK_STATUS' => WorkflowUserTable::TASK_STATUS_ACTIVE,
			],
		];

		return $filter;
	}

	private function applyCommentsFilter(array $filter): array
	{
		$filter[] = [
			'!=COMMENTS.UNREAD_CNT' => null,
			0 => [
				'LOGIC' => 'OR',
				'=COMMENTS.LAST_TYPE' => new SqlExpression('?i', WorkflowUserCommentTable::COMMENT_TYPE_DEFAULT),
				0 => [
					'=COMMENTS.LAST_TYPE' => WorkflowUserCommentTable::COMMENT_TYPE_SYSTEM,
					'>COMMENTS.MODIFIED' => DateTime::createFromTimestamp(time() - 86400), // one day
				],
			],
		];

		return $filter;
	}

	private function getWorkflowTasks(WorkflowState $workflowState, WorkflowStateToGet $toGet): ?EO_Task_Collection
	{
		$taskFields = $toGet->getSelectTaskFields();
		if ($taskFields)
		{
			$activeTasksQuery = TaskTable::query()
				->setSelect($taskFields)
				->setFilter([
					'=WORKFLOW_ID' => $workflowState->getId(),
					'=TASK_USERS.USER_ID' => $toGet->getFilterUserId(),
					'=TASK_USERS.STATUS' => \CBPTaskUserStatus::Waiting,
				])
				->setOrder(['ID' => 'DESC'])
			;

			$taskLimit = $toGet->getSelectTaskLimit();
			if (isset($taskLimit))
			{
				$activeTasksQuery->setLimit($taskLimit);
			}

			$userActiveTasks = $activeTasksQuery->exec()->fetchCollection();

			$remainingTasksCount = null;
			if (isset($taskLimit))
			{
				$remainingTasksCount = $taskLimit - $userActiveTasks->count();
			}

			if (isset($remainingTasksCount) && $remainingTasksCount <= 0)
			{
				return $userActiveTasks;
			}

			$workflowTasksQuery = TaskTable::query()
				->setSelect($taskFields)
				->setFilter([
					'=WORKFLOW_ID' => $workflowState->getId(),
					[
						'LOGIC' => 'OR',
						'!=TASK_USERS.USER_ID' => $toGet->getFilterUserId(),
						'!=TASK_USERS.STATUS' => \CBPTaskUserStatus::Waiting,
					],
				])
				->setOrder(['ID' => 'DESC'])
			;

			if (isset($remainingTasksCount))
			{
				$workflowTasksQuery->setLimit($remainingTasksCount);
			}

			return $userActiveTasks->merge($workflowTasksQuery->exec()->fetchCollection());
		}

		return null;
	}

	public function getFullFilledList(WorkflowStateToGet $toGet): GetFullFilledListResponse
	{
		$response = new GetFullFilledListResponse();

		$toGet->setSelectAllFields();
		$getListResult = $this->getList($toGet);
		$collection = $getListResult->getWorkflowStatesCollection();
		if (!$collection)
		{
			return $response->setWorkflowStatesList([]);
		}

		$fullFilledList = [];
		$userIds = [];
		foreach ($collection as $stateElement)
		{
			$tasksInfo = $stateElement->getTasksInfo();
			foreach ($tasksInfo as $task)
			{
				if (!empty($task['TASK_USERS']))
				{
					foreach ($task['TASK_USERS'] as $row)
					{
						$userIds[$row['USER_ID']] = true;
						$userIds[$row['ORIGINAL_USER_ID']] = true;
					}
				}
			}

			$fullFilledList[] = [
				'ID' => $stateElement->getId(),
				'STARTED' => $stateElement->getStarted(),
				'MODIFIED' => $stateElement->getModified(),
				'STATE_INFO' => $stateElement->getStateInfo(),
				'DOCUMENT_INFO' => $this->getDocumentInfo($stateElement->getComplexDocumentId()),
				'STARTED_USER_INFO' => [
					'ID' => $stateElement->getStartedBy(),
				],
				'TASKS_INFO' => $tasksInfo,
				'WORKFLOW_TEMPLATE_ID' => $stateElement->getWorkflowTemplateId(),
				'TEMPLATE_NAME' => $stateElement->getTemplate()?->getName(),
				'META' => $stateElement->getMeta()?->collectValues() ?? [],
			];

			$userIds[$stateElement->getStartedBy()] = true;
		}

		return (
			$response
				->setWorkflowStatesList($fullFilledList)
				->setMembersInfo($this->getMembersInfo(array_keys($userIds)))
		);
	}

	public function getTimeline(GetTimelineRequest $request): GetTimelineResponse
	{
		if (!$request->workflowId)
		{
			return GetTimelineResponse::createError(Error::fromCode(Error::WORKFLOW_NOT_FOUND));
		}

		$isAdmin = (new \CBPWorkflowTemplateUser($request->userId))->isAdmin();

		if (!$isAdmin)
		{
			$accessService = new WorkflowAccessService();
			$accessRequest = new CanViewTimelineRequest(workflowId: $request->workflowId, userId: $request->userId);

			$accessResponse = $accessService->canViewTimeline($accessRequest);
			if (!$accessResponse->isSuccess())
			{
				return (new GetTimelineResponse())->addErrors($accessResponse->getErrors());
			}
		}

		$timeline = Timeline::createByWorkflowId($request->workflowId);

		if (!$timeline)
		{
			return GetTimelineResponse::createError(Error::fromCode(Error::WORKFLOW_NOT_FOUND));
		}

		return GetTimelineResponse::createOk(['timeline' => $timeline->setUserId($request->userId)]);
	}

	private function getDocumentInfo(array $complexDocumentId): array
	{
		$documentService = \CBPRuntime::getRuntime()->getDocumentService();

		$key = null;
		try
		{
			$complexDocumentType = $documentService->getDocumentType($complexDocumentId);
			if ($complexDocumentType)
			{
				$key = $complexDocumentId[0] . '@' . $complexDocumentId[1] . '@' . $complexDocumentType[2];
			}
		}
		catch (SystemException | \Exception $exception)
		{
			$complexDocumentType = null;
		}

		static $cache = [];
		if ($key && !isset($cache[$key]))
		{
			$cache[$key] = $documentService->getDocumentTypeCaption($complexDocumentType);
		}
		$typeCaption = $key ? $cache[$key] : '';

		return [
			'COMPLEX_ID' => $complexDocumentId,
			'COMPLEX_TYPE' => $complexDocumentType,
			'NAME' => $documentService->getDocumentName($complexDocumentId),
			'TYPE_CAPTION' => $typeCaption,
		];
	}

	private function getMembersInfo(array $ids): array
	{
		if (empty($ids))
		{
			return [];
		}

		$userFields = ['ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'LOGIN', 'TITLE', 'EMAIL', 'PERSONAL_PHOTO'];

		$users = \CUser::GetList(
			'id',
			'asc',
			['ID' => implode('|', $ids)],
			['FIELDS' => $userFields]
		);

		$info = [];
		while ($user = $users->Fetch())
		{
			$fullName = \CUser::FormatName(\CSite::GetNameFormat(false), $user, true, false);
			$personalPhoto = (int)$user['PERSONAL_PHOTO'];

			$info[] = [
				'ID' => (int)($user['ID'] ?? 0),
				'FULL_NAME' => $fullName,
				'PERSONAL_PHOTO' => $personalPhoto,
			];
		}

		return $info;
	}

	private function convertProcesses(int $userId)
	{
		if (empty($userId))
		{
			return;
		}

		$converterVersion = \CUserOptions::getOption(
			'bizproc',
			'processes_converted',
			0,
			$userId
		);

		if ($converterVersion === self::CONVERTER_VERSION)
		{
			return;
		}

		WorkflowUserTable::convertUserProcesses($userId);

		\CUserOptions::setOption(
			'bizproc',
			'processes_converted',
			self::CONVERTER_VERSION,
			false,
			$userId
		);
	}

	public function getAverageWorkflowDuration(
		GetAverageWorkflowDurationRequest $request
	): GetAverageWorkflowDurationResponse
	{
		// rights? canUserOperateDocumentType(CBPCanUserOperateOperation::ReadDocument, ...)

		$response = new GetAverageWorkflowDurationResponse();
		if ($request->templateId <= 0)
		{
			$response->addError(new Error('incorrect template id'));
		}

		if ($response->isSuccess())
		{
			$averageDuration = WorkflowDurationStatTable::getAverageDurationByTemplateId($request->templateId);
			if ($averageDuration !== null)
			{
				$response->setAverageDuration($averageDuration);
			}
		}

		return $response;
	}

	public function getExecutionTime(GetExecutionTimeRequest $request): GetExecutionTimeResponse
	{
		$response = new GetExecutionTimeResponse();

		if ($request->workflowStarted === null)
		{
			return $response->addError(new Error('incorrect workflowStarted'));
		}

		$startedTimestamp = $request->workflowStarted?->getTimestamp();
		if (WorkflowInstanceTable::exists($request->workflowId))
		{
			$currentTimestamp = (new DateTime())->getTimestamp();

			return $response->setExecutionTime($currentTimestamp - $startedTimestamp);
		}

		$modifiedTimestamp = $request->workflowModified->getTimestamp();

		return $response->setExecutionTime($modifiedTimestamp - $startedTimestamp);
	}

	public function getCompletedWorkflowEfficiency(string $workflowId): GetEfficiencyDataResponse
	{
		$workflow = WorkflowStateTable::query()
			->setSelect([
				'ID',
				'MODIFIED',
				'STARTED',
				'WORKFLOW_TEMPLATE_ID',
			])
			->setFilter([
				'=ID' => $workflowId,
				'=STATE' => 'Completed',
			])
			->exec()
			->fetchObject()
		;

		if ($workflow)
		{
			$executionTime = $this->getExecutionTime(
				new GetExecutionTimeRequest(
					workflowId: $workflow->getId(),
					workflowStarted: $workflow->getStarted(),
					workflowModified: $workflow->getModified()
				)
			)->getRoundedExecutionTime();
			$averageDuration = $this->getAverageWorkflowDuration(
				new GetAverageWorkflowDurationRequest(
					templateId: $workflow->getWorkflowTemplateId(),
				)
			)->getRoundedAverageDuration();
			$request = new GetEfficiencyDataRequest(
				executionTime: $executionTime,
				averageDuration: $averageDuration ?? $executionTime
			);

			return $this->getEfficiencyData($request);
		}

		$response = new GetEfficiencyDataResponse();
		$response->addError(new Error('WorkflowState not found'));

		return $response;
	}

	public function getEfficiencyData(GetEfficiencyDataRequest $request): GetEfficiencyDataResponse
	{
		$response = new GetEfficiencyDataResponse();

		$averageDuration = $request->averageDuration;
		$currentDuration = $request->executionTime;

		$efficiency = 'stopped';
		if (null === $averageDuration)
		{
			$efficiency = 'first';
		}
		elseif ($currentDuration <= $averageDuration)
		{
			$efficiency = 'fast';
		}
		elseif ($currentDuration <= ($averageDuration + self::THREE_DAYS_IN_SECONDS))
		{
			$efficiency = 'slow';
		}

		$response->setAverageDuration($request->averageDuration);
		$response->setExecutionTime($request->executionTime);
		$response->setEfficiency($efficiency);

		return $response;
	}
}
