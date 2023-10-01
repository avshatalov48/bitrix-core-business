<?php

namespace Bitrix\Bizproc\Task\Service;

use Bitrix\Bizproc\Task\Result\CheckDelegateTasksResult;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class AccessService
{
	private int $userId;
	private bool $isUserAdmin;

	public function __construct(int $userId)
	{
		$this->userId = $userId;
		$this->isUserAdmin = in_array(1, \CUser::GetUserGroup($userId), false);
	}

	public function checkDelegateTask(int $toUserId, int $fromUserId): CheckDelegateTasksResult
	{
		$result = new CheckDelegateTasksResult();

		if (Loader::includeModule('intranet'))
		{
			if (
				!\Bitrix\Intranet\Util::isIntranetUser($toUserId)
				|| !\Bitrix\Intranet\Util::isIntranetUser($this->userId)
			)
			{
				return $result->addError(
					new Error(Loc::getMessage('BIZPROC_LIB_TASK_ACCESS_SERVICE_DELEGATE_ERROR_ONLY_INTRANET_USER'))
				);
			}
		}

		$isHead = \CBPHelper::checkUserSubordination($this->userId, $toUserId);
		$allowedDelegationTypes = [\CBPTaskDelegationType::AllEmployees];
		if ($isHead)
		{
			$allowedDelegationTypes[] = \CBPTaskDelegationType::Subordinate;
		}

		$result->setData([
			'allowedDelegationTypes' => $this->isUserAdmin ? null : $allowedDelegationTypes,
		]);

		return $result;
	}
}
