<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Response\TaskAccessService\CheckDelegateTasksResponse;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

class TaskAccessService
{
	private int $userId;
	private bool $isUserAdmin;

	public function __construct(int $userId)
	{
		$this->userId = $userId;
		$this->isUserAdmin = in_array(1, \CUser::GetUserGroup($userId), false);
	}

	public function checkDelegateTask(int $toUserId, int $fromUserId): CheckDelegateTasksResponse
	{
		$response = new CheckDelegateTasksResponse();

		if (Loader::includeModule('intranet'))
		{
			if (
				!\Bitrix\Intranet\Util::isIntranetUser($toUserId)
				|| !\Bitrix\Intranet\Util::isIntranetUser($this->userId)
			)
			{
				return $response->addError(
					new Error(Loc::getMessage('BIZPROC_LIB_API_TASK_ACCESS_SERVICE_DELEGATE_ERROR_ONLY_INTRANET_USER'))
				);
			}
		}

		$isHead = \CBPHelper::checkUserSubordination($this->userId, $toUserId);
		$allowedDelegationTypes = [\CBPTaskDelegationType::AllEmployees];
		if ($isHead)
		{
			$allowedDelegationTypes[] = \CBPTaskDelegationType::Subordinate;
		}

		$response->setData([
			'allowedDelegationTypes' => $this->isUserAdmin ? null : $allowedDelegationTypes,
		]);

		return $response;
	}

	public function checkViewTasks(int $targetUserId): Result
	{
		$result = new Result();

		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser($this->userId))
		{
			// todo: message
			return $result->addError(new Error('only intranet'));
		}

		$isHead = \CBPHelper::checkUserSubordination($this->userId, $targetUserId);
		if ($this->userId !== $targetUserId && !$this->isUserAdmin && !$isHead)
		{
			return $result->addError(new Error(Loc::getMessage('BIZPROC_LIB_API_TASK_ACCESS_SERVICE_ERROR_SUBORDINATION')));
		}

		return $result;
	}
}
