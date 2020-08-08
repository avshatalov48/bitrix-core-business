<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class TaskSuite implements TolokaTransferObject
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int
	 */
	private $poolId;

	/**
	 * @var Task[]
	 */
	private $tasks;

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 *
	 * @return TaskSuite
	 */
	public function setId(int $id): TaskSuite
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPoolId(): int
	{
		return $this->poolId;
	}

	/**
	 * @param int $poolId
	 *
	 * @return TaskSuite
	 */
	public function setPoolId(int $poolId): TaskSuite
	{
		$this->poolId = $poolId;

		return $this;
	}

	/**
	 * @return Task[]
	 */
	public function getTasks(): array
	{
		return $this->tasks;
	}

	/**
	 * @param Task[] $tasks
	 *
	 * @return TaskSuite
	 */
	public function setTasks(array $tasks): TaskSuite
	{
		$this->tasks = $tasks;

		return $this;
	}


	public function toArray(): array
	{
		$tasks = [];
		foreach ($this->tasks as $task)
		{
			$tasks[] = $task->toArray();
		}

		return [
			'pool_id'    => $this->poolId,
			'tasks'      => $tasks
		];
	}
}