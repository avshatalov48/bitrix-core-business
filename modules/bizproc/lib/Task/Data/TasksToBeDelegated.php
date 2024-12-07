<?php

namespace Bitrix\Bizproc\Task\Data;

use Bitrix\Bizproc\Task\Options\DelegateTasksOptions;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;

final class TasksToBeDelegated
{
	private array $taskIds;
	private int $fromUserId;
	private int $toUserId;
	private int $currentUserId;

	private function __construct(
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

	/**
	 * @throws ArgumentException
	 * @throws ArgumentOutOfRangeException
	 */
	public static function createFromOptions(DelegateTasksOptions $options): TasksToBeDelegated
	{
		$taskIds = self::validateTaskIds($options->getTaskIds());
		if (!$taskIds)
		{
			throw new ArgumentException('taskIds');
		}

		$fromUserId = self::validateUserId($options->getFromUserId());
		if (!$fromUserId)
		{
			throw new ArgumentOutOfRangeException('fromUserId', 1, null);
		}

		$toUserId = self::validateUserId($options->getToUserId());
		if (!$toUserId)
		{
			throw new ArgumentOutOfRangeException('toUserId', 1, null);
		}

		$currentUserId = self::validateUserId($options->getCurrentUserId());

		return new self($taskIds, $fromUserId, $toUserId, $currentUserId);
	}

	private static function validateTaskIds(array $taskIds): array
	{
		$ids = [];
		foreach ($taskIds as $taskId)
		{
			if (is_numeric($taskId))
			{
				$taskId = (int)$taskId;
				$ids[$taskId] = $taskId;
			}
		}

		return $ids ? array_keys($ids) : [];
	}

	private static function validateUserId(int $userId): int
	{
		return max($userId, 0);
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
