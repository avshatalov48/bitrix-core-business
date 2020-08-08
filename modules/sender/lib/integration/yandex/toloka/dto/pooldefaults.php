<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class PoolDefaults implements TolokaTransferObject
{
	private $overlapForNewTaskSuites;
	private $overlapForNewTasks;

	/**
	 * @return mixed
	 */
	public function getOverlapForNewTaskSuites()
	{
		return $this->overlapForNewTaskSuites;
	}

	/**
	 * @param mixed $overlapForNewTaskSuites
	 *
	 * @return PoolDefaults
	 */
	public function setOverlapForNewTaskSuites($overlapForNewTaskSuites)
	{
		$this->overlapForNewTaskSuites = $overlapForNewTaskSuites;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOverlapForNewTasks()
	{
		return $this->overlapForNewTasks;
	}

	/**
	 * @param mixed $overlapForNewTasks
	 *
	 * @return PoolDefaults
	 */
	public function setOverlapForNewTasks($overlapForNewTasks)
	{
		$this->overlapForNewTasks = $overlapForNewTasks;

		return $this;
	}



	public function toArray(): array
	{
		return [
			"default_overlap_for_new_task_suites" => $this->overlapForNewTaskSuites,
			"default_overlap_for_new_tasks"       => $this->overlapForNewTasks
		];
	}
}