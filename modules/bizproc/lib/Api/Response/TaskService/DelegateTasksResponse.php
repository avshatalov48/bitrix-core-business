<?php

namespace Bitrix\Bizproc\Api\Response\TaskService;

use Bitrix\Main\Result;

class DelegateTasksResponse extends Result
{
	public function getSuccessDelegateTaskMessage(): ?string
	{
		if (isset($this->data['successMessage']) && is_string($this->data['successMessage']))
		{
			return $this->data['successMessage'];
		}

		return null;
	}
}
