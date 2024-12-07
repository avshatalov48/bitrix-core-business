<?php

namespace Bitrix\Bizproc\Workflow;

use Bitrix\Bizproc\Workflow\Task\EO_Task;
use Bitrix\Bizproc\Workflow\Task\EO_TaskUser;

class Task extends EO_Task
{
	public function getValues(): array
	{
		$values = $this->collectValues();

		if (isset($values['TASK_USERS']))
		{
			$values['TASK_USERS'] = [];

			foreach ($this->getTaskUsers() as $taskUser)
			{
				$values['TASK_USERS'][] = $taskUser->collectValues();
			}
		}

		return $values;
	}

	public function isCompleted(): bool
	{
		return $this->getStatus() !== \CBPTaskStatus::Running;
	}

	public function isCompletedByUser(int $userId): bool
	{
		foreach ($this->getTaskUsers() as $taskUser)
		{
			if ($taskUser->getUserId() === $userId && $taskUser->getStatus() !== \CBPTaskUserStatus::Waiting)
			{
				return true;
			}
		}

		return false;
	}

	public function hasRights(int $userId): bool
	{
		if (!$this->isRightsRestricted())
		{
			return true;
		}

		return $this->isResponsibleForTask($userId);
	}

	public function hasViewRights(int $userId): bool
	{
		if (!$this->isRightsRestricted())
		{
			return true;
		}

		if ($this->isResponsibleForTask($userId))
		{
			return true;
		}

		return (new \CBPWorkflowTemplateUser($userId))->isAdmin();
	}

	public function getTaskUserById(int $userId): ?EO_TaskUser
	{
		foreach ($this->getTaskUsers() as $taskUser)
		{
			if ($taskUser->getUserId() === $userId)
			{
				return $taskUser;
			}
		}

		return null;
	}

	public function isResponsibleForTask(int $userId): bool
	{
		return $this->getTaskUserById($userId) !== null;
	}

	public function isInline(): bool
	{
		return $this->getIsInline() === 'Y';
	}

	public function isRightsRestricted(): bool
	{
		$accessControl = $this->getParameters()['AccessControl'] ?? 'N';

		return $accessControl === 'Y';
	}
}
