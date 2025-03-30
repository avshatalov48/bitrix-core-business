<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Data\TaskService\TasksToGet;
use Bitrix\Bizproc\Api\Data\TaskService\TasksToBeDelegated;
use Bitrix\Bizproc\Api\Request\TaskService\DelegateTasksRequest;
use Bitrix\Bizproc\Api\Request\TaskService\DoInlineTasksRequest;
use Bitrix\Bizproc\Api\Request\TaskService\DoTaskRequest;
use Bitrix\Bizproc\Api\Request\TaskService\GetUserTaskRequest;
use Bitrix\Bizproc\Api\Request\TaskService\GetUserTaskListRequest;
use Bitrix\Bizproc\Api\Request\TaskService\GetUserTaskByWorkflowIdRequest;
use Bitrix\Bizproc\Api\Response;
use Bitrix\Bizproc\Api\Response\TaskService\DelegateTasksResponse;
use Bitrix\Bizproc\Api\Response\TaskService\GetUserTaskListResponse;
use Bitrix\Bizproc\Api\Response\TaskService\GetUserTaskByWorkflowIdResponse;
use Bitrix\Bizproc\Result;
use Bitrix\Bizproc\Workflow\Task\TaskTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class TaskService
{
	private const TASK_ALREADY_DONE_ERROR_CODE = 'TASK_ALREADY_DONE';
	private const TASK_USER_NOT_MEMBER_ERROR_CODE = 'TASK_USER_NOT_MEMBER';
	private const TASK_NOT_FOUND_ERROR_CODE = 'TASK_NOT_FOUND';

	public function __construct(
		private TaskAccessService $accessService
	)
	{}

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

	public function getTasks(GetUserTaskListRequest $request): GetUserTaskListResponse
	{
		$getTasksResponse = new GetUserTaskListResponse();

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
			$taskList[] = $this->prepareTaskInfo($task, $tasksToGet->getTargetUserId());
		}

		return $getTasksResponse->setData(['tasks' => $taskList]);
	}

	public function doTask(DoTaskRequest $request)
	{
		$result = new Response\TaskService\DoTaskResponse();
		$task = false;

		$checkAccessResult = $this->accessService->checkDoTasks($request->userId);
		if (!$checkAccessResult->isSuccess())
		{
			return $result->addErrors($checkAccessResult->getErrors());
		}

		if ($request->taskId > 0)
		{
			$task = \CBPTaskService::GetList(
				[],
				[
					'ID' => $request->taskId,
					'USER_ID' => $request->userId
				],
				false,
				false,
				[
					'ID',
					'WORKFLOW_ID',
					'ACTIVITY',
					'ACTIVITY_NAME',
					'MODIFIED',
					'OVERDUE_DATE',
					'NAME',
					'DESCRIPTION',
					'PARAMETERS',
					'USER_STATUS',
				]
			)->fetch();
		}

		if (!$task)
		{
			$this->addUserTaskNotFoundError($result, $request->taskId, $request->userId);

			return $result;
		}

		if ((int)$task['USER_STATUS'] !== \CBPTaskUserStatus::Waiting)
		{
			return $result->addError(
				new Error(
					Loc::getMessage('BIZPROC_LIB_API_TASK_SERVICE_DO_TASK_ERROR_ALREADY_DONE'),
					self::TASK_ALREADY_DONE_ERROR_CODE
				)
			);
		}

		$task['PARAMETERS']['DOCUMENT_ID'] = \CBPStateService::GetStateDocumentId($task['WORKFLOW_ID']);
		$task['MODULE_ID'] = $task['PARAMETERS']['DOCUMENT_ID'][0];
		$task['ENTITY'] = $task['PARAMETERS']['DOCUMENT_ID'][1];
		$task['DOCUMENT_ID'] = $task['PARAMETERS']['DOCUMENT_ID'][2];

		if (!\CBPDocument::PostTaskForm($task, $request->userId, $request->taskRequest, $errors))
		{
			$error = reset($errors);
			if ($task['MODULE_ID'] === 'rpa' && $error['code'] === \CBPRuntime::EXCEPTION_CODE_INSTANCE_TERMINATED)
			{
				return $result;
			}

			if (!empty($error['customData']))
			{
				foreach ($errors as $error)
				{
					$result->addError(new Error($error['message'], 0, $error['customData']));
				}

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

	public function getUserTask(GetUserTaskRequest $request): Response\TaskService\GetUserTaskResponse
	{
		$response = new Response\TaskService\GetUserTaskResponse();

		if ($request->taskId <= 0 || $request->userId <= 0)
		{
			$this->addUserTaskNotFoundError($response, 0, $request->userId);

			return $response;
		}

		$checkAccessResult = $this->accessService->checkViewTasks($request->userId);
		if (!$checkAccessResult->isSuccess())
		{
			return $response->addErrors($checkAccessResult->getErrors());
		}

		$task = \CBPTaskService::getList(
			[],
			['ID' => $request->taskId, 'USER_ID' => $request->userId],
			false,
			false,
			[
				'ID',
				'USER_ID',
				'NAME',
				'DESCRIPTION',
				'WORKFLOW_ID',
				'ACTIVITY',
				'STATUS',
				'USER_STATUS',
				'IS_INLINE',
				'PARAMETERS',
				'DELEGATION_TYPE',
			],
		)->fetch();

		if (!$task)
		{
			$this->addUserTaskNotFoundError($response, $request->taskId, $request->userId);

			return $response;
		}

		$task = $this->prepareTaskInfo($task, $request->userId);

		return $response->setTask($task);
	}

	public function getUserTaskByWorkflowId(GetUserTaskByWorkflowIdRequest $request): GetUserTaskByWorkflowIdResponse
	{
		$response = new GetUserTaskByWorkflowIdResponse();
		$renderer = new \Bitrix\Bizproc\Controller\Response\RenderControlCollectionContent();
		$task = false;

		if ($request->workflowId)
		{
			$task = \Bitrix\Bizproc\Workflow\Task\TaskTable::query()
				->setSelect(['*'])
				->setFilter([
					'=WORKFLOW_ID' => $request->workflowId,
					'=TASK_USERS.USER_ID' => $request->userId,
					'=TASK_USERS.STATUS' => \CBPTaskUserStatus::Waiting,
				])
				->setOrder(['ID' => 'DESC'])
				->fetch()
			;
		}

		if (!$task)
		{
			$response->setContent($renderer);

			return $response;
		}

		$controls = \CBPDocument::getTaskControls($task, $request->userId);

		$task['BUTTONS'] = $controls['BUTTONS'] ?? null;
		$task['FIELDS'] = $controls['FIELDS'] ?? null;
		if (isset($task['DESCRIPTION']))
		{
			$task['DESCRIPTION'] = \CBPViewHelper::prepareTaskDescription(
				\CBPHelper::convertBBtoText(
					preg_replace('|\n+|', "\n", trim($task['DESCRIPTION']))
				)
			);
		}
		$response->setTask($task);

		$documentService = \CBPRuntime::getRuntime()->getDocumentService();
		$documentType = $documentService->getDocumentType($task['PARAMETERS']['DOCUMENT_ID']);
		if (isset($task['FIELDS']) && is_array($task['FIELDS']))
		{
			foreach ($task['FIELDS'] as $parameter)
			{
				$params['Field'] = $parameter['FieldId'] ?? $parameter['Id'];
				$params['Value'] = $parameter['Default'] ?? null;
				$params['Als'] = false;
				$params['RenderMode'] = 'public';

				$renderer->addProperty($documentType, $parameter, $params);
			}
		}
		$response->setContent($renderer);

		return $response;
	}

	private function addUserTaskNotFoundError(Result $response, int $taskId, int $userId): void
	{
		if ($taskId > 0)
		{
			$task = TaskTable::getByPrimary($taskId)->fetch();
			if ($task)
			{
				if ((int)$task['STATUS'] !== \CBPTaskStatus::Running)
				{
					$response->addError(
						new Error(
							Loc::getMessage('BIZPROC_LIB_API_TASK_SERVICE_ERROR_TASK_ALREADY_DONE'),
							self::TASK_ALREADY_DONE_ERROR_CODE
						)
					);
				}
				elseif ($this->accessService->isCurrentUser($userId))
				{
					$response->addError(
						new Error(
							Loc::getMessage('BIZPROC_LIB_API_TASK_SERVICE_ERROR_CURRENT_USER_NOT_MEMBER'),
							self::TASK_USER_NOT_MEMBER_ERROR_CODE,
						)
					);
				}
				else
				{
					$response->addError(
						new Error(
							Loc::getMessage('BIZPROC_LIB_API_TASK_SERVICE_ERROR_TARGET_USER_NOT_MEMBER'),
							self::TASK_USER_NOT_MEMBER_ERROR_CODE,
						)
					);
				}

				return;
			}
		}

		$response->addError(
			new Error(
				Loc::getMessage('BIZPROC_LIB_API_TASK_SERVICE_DO_TASK_ERROR_NO_TASK'),
				self::TASK_NOT_FOUND_ERROR_CODE
			)
		);
	}

	private function prepareTaskInfo(array $task, int $userId = 0): array
	{
		$task['STATUS'] = (int)$task['STATUS'];

		if (isset($task['MODIFIED']))
		{
			$task['MODIFIED'] = FormatDateFromDB($task['MODIFIED']);
		}

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

		if (isset($task['WORKFLOW_TEMPLATE_NAME']))
		{
			$task['WORKFLOW_NAME'] = $task['WORKFLOW_TEMPLATE_NAME']; // compatibility
		}
		if (isset($task['WORKFLOW_STARTED']))
		{
			$task['WORKFLOW_STARTED'] = FormatDateFromDB($task['WORKFLOW_STARTED']);
		}

		$controls = \CBPDocument::getTaskControls($task, $userId);

		$task['BUTTONS'] = $controls['BUTTONS'] ?? null;
		$task['FIELDS'] = $controls['FIELDS'] ?? null;

		if (isset($task['DELEGATION_TYPE']))
		{
			$task['DELEGATION_TYPE'] = (int)$task['DELEGATION_TYPE'];
		}

		return $task;
	}
}
