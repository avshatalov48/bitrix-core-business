<?php

namespace Bitrix\Bizproc\Api\Response\TaskService;

use Bitrix\Bizproc\Result;
use Bitrix\Bizproc\Controller\Response\RenderControlCollectionContent;

class GetUserTaskByWorkflowIdResponse extends Result
{
	public function setContent(RenderControlCollectionContent $content): self
	{
		$this->data['content'] = $content;

		return $this;
	}

	public function getContent(): RenderControlCollectionContent
	{
		return $this->data['content'];
	}

	public function setTask(array $task): self
	{
		$this->data['task'] = $task;

		return $this;
	}

	public function getTask(): array
	{
		return $this->data['task'] ?? [];
	}
}