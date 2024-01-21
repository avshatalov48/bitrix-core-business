<?php

namespace Bitrix\Bizproc\Api\Response\TaskAccessService;

use Bitrix\Main\Result;

class CheckDelegateTasksResponse extends Result
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
