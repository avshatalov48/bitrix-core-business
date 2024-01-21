<?php

namespace Bitrix\Bizproc\Api\Response\TaskService;

use Bitrix\Bizproc\Result;

class GetUserTasksResponse extends Result
{
	public function getTasks(): ?array
	{
		return $this->data['tasks'] ?? null;
	}
}
