<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateToGet;
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
use Bitrix\Bizproc\Workflow\Timeline;
use Bitrix\Main\SystemException;

class WorkflowStateService
{
	private const CONVERTER_VERSION = 2;

	public function getList(WorkflowStateToGet $toGet): GetListResponse
	{
		$this->convertProcesses($toGet->getFilterUserId());

		$response = new GetListResponse();
		$responseCollection = new EO_WorkflowState_Collection();

		$queryResult = WorkflowUserTable::query()
			->addSelect('WORKFLOW_ID')
			->setFilter($toGet->getOrmFilter())
			->setOrder($toGet->getOrder())
			->setLimit($toGet->getLimit())
			->setOffset($toGet->getOffset())
			->countTotal($toGet->isCountingTotal())
			->exec()
		;

		if ($toGet->isCountingTotal())
		{
			$response->setTotalCount($queryResult->getCount());
		}
		$workflowStates = $queryResult->fetchAll();
		$ids = array_column($workflowStates, 'WORKFLOW_ID');
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

			foreach ($ids as $id)
			{
				$workflowState = $collection->getByPrimary($id);
				if ($workflowState)
				{
					$responseCollection->add($workflowState);
				}
			}
		}

		return $response->setWorkflowStatesCollection($responseCollection);
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
				'STATE_INFO' => $stateElement->getStateInfo(),
				'DOCUMENT_INFO' => $this->getDocumentInfo($stateElement->getComplexDocumentId()),
				'STARTED_USER_INFO' => [
					'ID' => $stateElement->getStartedBy(),
				],
				'TASKS_INFO' => $tasksInfo,
				'TEMPLATE_NAME' => $stateElement->getTemplate()?->getName(),
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
			$key = $complexDocumentId[0] . '@' . $complexDocumentId[1] . '@' . $complexDocumentType[2];
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
}
