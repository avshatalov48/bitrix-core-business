<?php

namespace Bitrix\Bizproc\Task\Data\ExternalEventTask;

use Bitrix\Bizproc\Task\Data\TaskData;

final class ExternalEventTaskData extends TaskData
{
	public function getCompletedUsersParameter(): ?array
	{
		$parameters = $this->getParameters();

		return $parameters ? $parameters['COMPLETED_USERS'] : null;
	}

	public function setCompletedUsersParameter(array $completedUsers): self
	{
		$parameters = $this->getParameters();
		if ($parameters)
		{
			$parameters['COMPLETED_USERS'] = $completedUsers;
			$this->setParameters($parameters);
		}

		return $this;
	}
}
