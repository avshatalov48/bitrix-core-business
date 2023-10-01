<?php

namespace Bitrix\Bizproc\Task\Service;

use Bitrix\Bizproc\Task\Options\DelegateTasksOptions;
use Bitrix\Bizproc\Task\Data\TasksToBeDelegated;
use Bitrix\Bizproc\Task\Result\DelegateTasksResult;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class TaskService
{
	private AccessService $accessService;

	public function __construct(
		AccessService $accessService
	)
	{
		$this->accessService = $accessService;
	}

	public function delegateTasks(DelegateTasksOptions $options): DelegateTasksResult
	{
		$delegateResult = new DelegateTasksResult();

		$tasksToDelegate = null;
		try
		{
			$tasksToDelegate = TasksToBeDelegated::createFromOptions($options);
		}
		catch (ArgumentOutOfRangeException $e)
		{
			$errorMessage =
				in_array($e->getParameter(), ['fromUserId', 'toUserId'], true)
					? Loc::getMessage('BIZPROC_LIB_TASK_TASK_SERVICE_DELEGATE_TASK_ERROR_INCORRECT_USER_ID')
					: $e->getMessage()
			;
			$delegateResult->addError(new Error($errorMessage));
		}
		catch (ArgumentException $e)
		{
			$delegateResult->addError(
				new Error(Loc::getMessage('BIZPROC_LIB_TASK_TASK_SERVICE_DELEGATE_TASK_ERROR_NO_TASKS'))
			);
		}
		if (!$tasksToDelegate || !$delegateResult->isSuccess())
		{
			return $delegateResult;
		}

		$checkAccessResult = $this->accessService->checkDelegateTask(
			$tasksToDelegate->getToUserId(),
			$tasksToDelegate->getFromUserId()
		);
		if (!$checkAccessResult->isSuccess())
		{
			return $delegateResult->addErrors($checkAccessResult->getErrors());
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
			$errors = [Loc::getMessage('BIZPROC_LIB_TASK_TASK_SERVICE_DELEGATE_TASK_ERROR_NO_TASKS')];
		}
		foreach ($errors as $errorMessage)
		{
			$delegateResult->addError(new Error($errorMessage));
		}

		if ($delegateResult->isSuccess())
		{
			$delegateResult->setData([
				'successMessage' => Loc::getMessage(
					'BIZPROC_LIB_TASK_TASK_SERVICE_DELEGATE_TASK_SUCCESS_MESSAGE',
					['#USER_NAME#' => \CBPHelper::convertUserToPrintableForm($tasksToDelegate->getToUserId(), '', false)]
				),
			]);
		}

		return $delegateResult;
	}
}