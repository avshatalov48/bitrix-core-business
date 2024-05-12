<?php

namespace Bitrix\Bizproc\Api\Response\TaskService;

use Bitrix\Bizproc\Result;

final class GetUserTaskResponse extends Result
{
	public function setTask(array $task): self
	{
		$this->data['task'] = $task;

		return $this;
	}

	public function getTask(): ?array
	{
		$task = $this->data['task'] ?? null;

		return is_array($task) ? $task : null;
	}
}
