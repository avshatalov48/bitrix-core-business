<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Data\TaskService\TasksToGet;
use Bitrix\Bizproc\Api\Data\TaskService\TasksToBeDelegated;
use Bitrix\Bizproc\Api\Request\TaskService\DelegateTasksRequest;
use Bitrix\Bizproc\Api\Request\TaskService\DoInlineTasksRequest;
use Bitrix\Bizproc\Api\Request\TaskService\DoTaskRequest;
use Bitrix\Bizproc\Api\Request\TaskService\GetUserTasksRequest;
use Bitrix\Bizproc\Api\Response;
use Bitrix\Bizproc\Api\Response\TaskService\DelegateTasksResponse;
use Bitrix\Bizproc\Api\Response\TaskService\GetUserTasksResponse;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class TaskService
{
	public function __construct(
		private TaskAccessService $accessService
		// private UserService $userService,
	)
	{
	}

	public function delegateTasks(DelegateTasksRequest $request): DelegateTasksResponse
	{
		$delegateResponse = new DelegateTasksResponse();

		$tasksToDelegate = null;
		try
		{
			$tasksToDelegate = TasksToBeDelegated::createFromRequest($request);
		}
		catch (ArgumentOutOfRangeException $e)
		{
			$errorMessage =
				in_array($e->getParameter(), ['fromUserId', 'toUserId'], true)
					? Loc::getMessage('BIZPROC_LIB_API_TASK_SERVICE_DELEGATE_TASK_ERROR_INCORRECT_USER_ID')
					: $e->getMessage()
			;
			$delegateResponse->addError(new Error($errorMessage));
		}
		catch (ArgumentException $e)
		{
			$delegateResponse->addError(
				new Error(Loc::getMessage('BIZPROC_LIB_API_TASK_SERVICE_DELEGATE_TASK_ERROR_NO_TASKS'))
			);
		}
		if (!$tasksToDelegate || !$delegateResponse->isSuccess())
		{
			return $delegateResponse;
		}

		$checkAccessResult = $this->accessService->checkDelegateTask(
			$tasksToDelegate->getToUserId(),
			$tasksToDelegate->getFromUserId()
		);
		if (!$checkAccessResult->isSuccess())
		{
			return $delegateResponse->addErrors($checkAccessResult->getErrors());
		}

		$allowedDelegationType = $checkAccessResult->getAllowedDelegationTypes();

		$errors = [];
		$isDelegated = \CBPDocument::delegateTasks(
			$tasksToDelegate->getFromUserId(),
			$tasksToDelegate->getToUserId(),
			$tasksToDelegate->getTaskIds(),
			$errors,
			$allowedDelegationType
		);

		if (!$isDelegated && !$errors)
		{
			$errors = [Loc::getMessage('BIZPROC_LIB_API_TASK_SERVICE_DELEGATE_TASK_ERROR_TASKS_NOT_FOUND')];
		}
		foreach ($errors as $errorMessage)
		{
			$delegateResponse->addError(new Error($errorMessage));
		}

		if ($delegateResponse->isSuccess())
		{
			$delegateResponse->setData([
				'successMessage' => Loc::getMessage(
					'BIZPROC_LIB_API_TASK_SERVICE_DELEGATE_TASK_SUCCESS_MESSAGE',
					['#USER_NAME#' => \CBPHelper::convertUserToPrintableForm($tasksToDelegate->getToUserId(), '', false)]
				),
			]);
		}

		return $delegateResponse;
	}

	public function getTasks(GetUserTasksRequest $request): GetUserTasksResponse
	{
		// $getCurrentUserResult = $this->userService->getCurrentUser();
		//		if (!$getCurrentUserResult->isSuccess())
		//		{
		//			return GetUserTasksResult::createError($getCurrentUserResult->getErrors()[0]);
		//		}

		$getTasksResponse = new GetUserTasksResponse();

		$tasksToGet = null;
		try
		{
			$tasksToGet = TasksToGet::createFromRequest($request);
		}
		catch (ArgumentOutOfRangeException $exception)
		{
			// todo: user friendly error message
			// use ::createError?
			$getTasksResponse->addError(new Error($exception->getMessage()));
		}
		if (!$tasksToGet || !$getTasksResponse->isSuccess())
		{
			return $getTasksResponse;
		}

		$checkAccessResult = $this->accessService->checkViewTasks($tasksToGet->getTargetUserId());
		if (!$checkAccessResult->isSuccess())
		{
			return $getTasksResponse->addErrors($checkAccessResult->getErrors());
		}

		$tasksIterator = \CBPTaskService::getList(
			$tasksToGet->getSort(),
			$tasksToGet->getFilter(),
			false,
			[
				'nPageSize' => $tasksToGet->getLimit(),
				'iNumPage' => (int)($tasksToGet->getOffset() / $tasksToGet->getLimit()) + 1,
			],
			$tasksToGet->buildSelectFields()
		);
		$taskList = [];
		while ($task = $tasksIterator->fetch())
		{
			// todo: create DTO Task
			$task['STATUS'] = (int)$task['STATUS'];
			$task['MODIFIED'] = FormatDateFromDB($task['MODIFIED']);
			$documentId =
				is_array($task['PARAMETERS']['DOCUMENT_ID'] ?? null)
					? $task['PARAMETERS']['DOCUMENT_ID']
					: null
			;
			$task['DOCUMENT_URL'] = $documentId ? \CBPDocument::getDocumentAdminPage($documentId) : '';

			$task['MODULE_ID'] = $documentId ? $documentId[0] : '';
			$task['ENTITY'] = $documentId ? $documentId[1] : '';
			$task['DOCUMENT_ID'] = $documentId ? $documentId[2] : '';
			$task['COMPLEX_DOCUMENT_ID'] = $documentId;

			if (isset($arRecord['WORKFLOW_TEMPLATE_NAME']))
			{
				$task['WORKFLOW_NAME'] = $task['WORKFLOW_TEMPLATE_NAME']; // compatibility
			}
			if (isset($arRecord['WORKFLOW_STARTED']))
			{
				$task['WORKFLOW_STARTED'] = FormatDateFromDB($task['WORKFLOW_STARTED']);
			}

			$controls = \CBPDocument::getTaskControls($task);

			$task['BUTTONS'] = $controls['BUTTONS'] ?? null;
			$task['FIELDS'] = $controls['FIELDS'] ?? null;

			$taskList[] = $task;
		}

		return $getTasksResponse->setData(['tasks' => $taskList]);
	}

	public function doTask(DoTaskRequest $request)
	{
		$result = new Response\TaskService\DoTaskResponse();
		$task = false;

		$checkAccessResult = $this->accessService->checkViewTasks($request->userId);
		if (!$checkAccessResult->isSuccess())
		{
			return $result->addErrors($checkAccessResult->getErrors());
		}

		if ($request->taskId > 0)
		{
			$task = \CBPTaskService::GetList(
				[],
				[
					"ID" => $request->taskId,
					"USER_ID" => $request->userId
				],
				false,
				false,
				[
					"ID",
					"WORKFLOW_ID",
					"ACTIVITY",
					"ACTIVITY_NAME",
					"MODIFIED",
					"OVERDUE_DATE",
					"NAME",
					"DESCRIPTION",
					"PARAMETERS",
					"USER_STATUS",
				]
			)->fetch();
		}

		if (!$task)
		{
			return $result->addError(new Error(Loc::getMessage('BIZPROC_LIB_API_TASK_SERVICE_DO_TASK_ERROR_NO_TASK')));
		}

		if ((int)$task['USER_STATUS'] !== \CBPTaskUserStatus::Waiting)
		{
			return $result->addError(new Error(Loc::getMessage('BIZPROC_LIB_API_TASK_SERVICE_DO_TASK_ERROR_ALREADY_DONE')));
		}

		$task["PARAMETERS"]["DOCUMENT_ID"] = \CBPStateService::GetStateDocumentId($task['WORKFLOW_ID']);
		$task["MODULE_ID"] = $task["PARAMETERS"]["DOCUMENT_ID"][0];
		$task["ENTITY"] = $task["PARAMETERS"]["DOCUMENT_ID"][1];
		$task["DOCUMENT_ID"] = $task["PARAMETERS"]["DOCUMENT_ID"][2];

		if (!\CBPDocument::PostTaskForm($task, $request->userId, $request->taskRequest, $errors))
		{
			$error = reset($errors);
			if ($task['MODULE_ID'] === 'rpa' && $error['code'] === \CBPRuntime::EXCEPTION_CODE_INSTANCE_TERMINATED)
			{
				return $result;
			}

			$result->addError(new Error($error['message']));
		}

		return $result;
	}

	public function doInlineTasks(DoInlineTasksRequest $request): Response\TaskService\DoInlineTaskResponse
	{
		$taskIds = [];
		foreach ($request->taskIds as $id)
		{
			// TODO - throw argument or return error if $id not integer?
			if (is_int($id))
			{
				$taskIds[] = $id;
			}
		}

		$result = new Response\TaskService\DoInlineTaskResponse();
		if ($taskIds)
		{
			$errors = [];
			\CBPDocument::setTasksUserStatus($request->userId, $request->newTaskStatusId, $taskIds, $errors);

			if ($errors)
			{
				foreach ($errors as $errorMessage)
				{
					$result->addError(new \Bitrix\Bizproc\Error($errorMessage));
				}
			}
		}

		return $result;
	}
}
