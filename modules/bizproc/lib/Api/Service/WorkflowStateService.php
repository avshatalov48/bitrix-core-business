<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateToGet;
use Bitrix\Bizproc\Api\Request\WorkflowAccessService\CanViewTimelineRequest;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetAverageWorkflowDurationRequest;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetTimelineRequest;
use Bitrix\Bizproc\Api\Response\Error;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetAverageWorkflowDurationResponse;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetFullFilledListResponse;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetListResponse;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetTimelineResponse;
use Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection;
use Bitrix\Bizproc\Workflow\Entity\WorkflowDurationStatTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable;
use Bitrix\Bizproc\Workflow\Task\EO_Task_Collection;
use Bitrix\Bizproc\Workflow\Task\TaskTable;
use Bitrix\Bizproc\Workflow\Timeline;
use Bitrix\Bizproc\Workflow\WorkflowState;
use Bitrix\Main\SystemException;

class WorkflowStateService
{
	private const CONVERTER_VERSION = 2;

	public function getList(WorkflowStateToGet $toGet): GetListResponse
	{
		$this->convertProcesses($toGet->getFilterUserId());
		$this->createFilterIndex($toGet->getFilterUserId()); // remove after 6 month from release

		$response = new GetListResponse();
		$responseCollection = new EO_WorkflowState_Collection();

		$query = WorkflowUserTable::query()
			->addSelect('WORKFLOW_ID')
			->addSelect('MODIFIED')
			->setFilter($toGet->getOrmFilter())
			->setOrder($toGet->getOrder())
			->setLimit($toGet->getLimit())
			->setOffset($toGet->getOffset())
			->countTotal($toGet->isCountingTotal())
		;
		$runtimeField = $toGet->getOrmRuntime();

		if ($runtimeField)
		{
			$query->registerRuntimeField($runtimeField);
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

	private function createFilterIndex(int $userId)
	{
		if (empty($userId))
		{
			return;
		}

		$converterVersion = \CUserOptions::getOption(
			'bizproc',
			'processes_filter',
			0,
			$userId
		);

		if ($converterVersion)
		{
			return;
		}

		\Bitrix\Bizproc\Worker\Workflow\CreateUserFilterStepper::bindUser($userId);

		\CUserOptions::setOption(
			'bizproc',
			'processes_filter',
			1,
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
}
