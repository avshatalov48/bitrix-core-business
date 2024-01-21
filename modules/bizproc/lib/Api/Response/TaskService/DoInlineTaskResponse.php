<?php

namespace Bitrix\Bizproc\Api\Response\TaskService;

use Bitrix\Bizproc\Result;

final class DoInlineTaskResponse extends Result
{
	public function getCompletedTasks(): array
	{
		return $this->data['completedTasks'] ?? [];
	}
}