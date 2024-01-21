<?php

namespace Bitrix\Bizproc\Workflow;

use Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState;

class WorkflowState extends EO_WorkflowState
{
	public function getComplexDocumentId(): array
	{
		return [$this->getModuleId(), $this->getEntity(), $this->getDocumentId()];
	}

	public function getStateInfo(): array
	{
		return [
			'STATE' => $this->getState(),
			'TITLE' => $this->getStateTitle(),
			'PARAMETERS' => $this->getStateParameters(),
		];
	}

	public function getTasksInfo(): array
	{
		$info = [];

		$tasks = $this->getTasks();
		if ($tasks)
		{
			foreach ($this->getTasks() as $task)
			{
				$info[$task->getId()] = $task->getValues();
			}
		}

		return $info;
	}
}
