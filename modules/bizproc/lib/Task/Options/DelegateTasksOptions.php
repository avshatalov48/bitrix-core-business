<?php

namespace Bitrix\Bizproc\Task\Options;

final class DelegateTasksOptions
{
	private array $taskIds;
	private int $fromUserId;
	private int $toUserId;
	private int $currentUserId;

	public function __construct(
		array $taskIds,
		int $fromUserId,
		int $toUserId,
		int $currentUserId
	)
	{
		$this->taskIds = $taskIds;
		$this->fromUserId = $fromUserId;
		$this->toUserId = $toUserId;
		$this->currentUserId = $currentUserId;
	}

	public function getTaskIds(): array
	{
		return $this->taskIds;
	}

	public function getFromUserId(): int
	{
		return $this->fromUserId;
	}

	public function getToUserId(): int
	{
		return $this->toUserId;
	}

	public function getCurrentUserId(): int
	{
		return $this->currentUserId;
	}
}