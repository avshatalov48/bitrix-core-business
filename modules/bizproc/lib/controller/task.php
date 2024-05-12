<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Bizproc;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class Task extends Base
{
	public function delegateAction(array $taskIds, int $toUserId, int $fromUserId): ?array
	{
		if (!$taskIds)
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_TASK_DELEGATE_EMPTY_TASK_IDS')));

			return null;
		}
		if ($toUserId <= 0 || $fromUserId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_TASK_DELEGATE_INCORRECT_USER_ID')));

			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();

		$taskService = new Bizproc\Api\Service\TaskService(
			new Bizproc\Api\Service\TaskAccessService($currentUserId)
		);

		$tasksRequest = new Bizproc\Api\Request\TaskService\DelegateTasksRequest($taskIds, $fromUserId, $toUserId, $currentUserId);
		$delegateTaskResult = $taskService->delegateTasks($tasksRequest);

		if (!$delegateTaskResult->isSuccess())
		{
			$this->addErrors($delegateTaskResult->getErrors());

			return null;
		}

		return [
			'message' => $delegateTaskResult->getSuccessDelegateTaskMessage(),
		];
	}

	/**
	 * @internal Experimental, not ready now
	 */
	private function getListAction(?int $targetUserId = null): ?array
	{
		$currentUserId = $this->getCurrentUser()->getId();
		if (!$targetUserId)
		{
			$targetUserId = $currentUserId;
		}

		// todo: check that $targetUserId > 0

		$taskService = new Bizproc\Api\Service\TaskService(
			new Bizproc\Api\Service\TaskAccessService($currentUserId)
		);

		$tasksRequest = new Bizproc\Api\Request\TaskService\GetUserTaskListRequest(
			additionalSelectFields: ['NAME', 'DESCRIPTION'],
			filter: [
				'USER_ID' => $targetUserId,
			],
		);
		$getTasksResult = $taskService->getTasks($tasksRequest);
		if (!$getTasksResult->isSuccess())
		{
			$this->addErrors($getTasksResult->getErrors());

			return null;
		}

		return [
			'tasks' => $getTasksResult->getTasks(),
		];
	}

	public function doAction(int $taskId, array $taskRequest): ?bool
	{
		$currentUserId = $this->getCurrentUser()->getId();

		$taskService = new Bizproc\Api\Service\TaskService(
			new Bizproc\Api\Service\TaskAccessService($currentUserId)
		);

		$request = new Bizproc\Api\Request\TaskService\DoTaskRequest(
			taskId: $taskId,
			userId: $currentUserId,
			taskRequest: $taskRequest,
		);

		$getTasksResult = $taskService->doTask($request);
		if (!$getTasksResult->isSuccess())
		{
			$this->addErrors($getTasksResult->getErrors());

			return null;
		}

		return true;
	}

	/**
	 * @param numeric[] $taskIds
	 * @param int $newStatus
	 * @return bool|null
	 */
	public function doInlineTasksAction(array $taskIds, int $newStatus): ?bool
	{
		$currentUserId = $this->getCurrentUser()->getId();

		$preparedTaskIds = [];
		foreach ($taskIds as $id)
		{
			if (is_numeric($id))
			{
				$preparedTaskIds[] = (int)$id;
			}
		}

		$request = new Bizproc\Api\Request\TaskService\DoInlineTasksRequest(
			taskIds: $preparedTaskIds,
			userId: $currentUserId,
			newTaskStatusId: $newStatus,
		);

		$service = new Bizproc\Api\Service\TaskService(
			new Bizproc\Api\Service\TaskAccessService($request->userId),
		);

		$response = $service->doInlineTasks($request);

		if (!$response->isSuccess())
		{
			$this->addErrors($response->getErrors());
			return null;
		}

		return true;
	}
}
