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

		$taskService = new Bizproc\Task\Service\TaskService(
			new Bizproc\Task\Service\AccessService($currentUserId)
		);

		$options = new Bizproc\Task\Options\DelegateTasksOptions($taskIds, $fromUserId, $toUserId, $currentUserId);
		$delegateTaskResult = $taskService->delegateTasks($options);

		if (!$delegateTaskResult->isSuccess())
		{
			$this->addErrors($delegateTaskResult->getErrors());

			return null;
		}

		return [
			'message' => $delegateTaskResult->getSuccessDelegateTaskMessage(),
		];
	}
}
