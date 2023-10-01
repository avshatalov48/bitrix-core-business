<?php

namespace Bitrix\Bizproc\Task\Result;

use Bitrix\Main\Result;

class CheckDelegateTasksResult extends Result
{
	public function getAllowedDelegationTypes(): ?array
	{
		if (array_key_exists('allowedDelegationTypes', $this->data))
		{
			return $this->data['allowedDelegationTypes'];
		}

		return [\CBPTaskDelegationType::AllEmployees];
	}
}