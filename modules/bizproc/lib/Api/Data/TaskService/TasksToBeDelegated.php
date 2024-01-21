<?php

namespace Bitrix\Bizproc\Api\Data\TaskService;

use Bitrix\Bizproc\Api\Request\TaskService\DelegateTasksRequest;
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
	public static function createFromRequest(DelegateTasksRequest $request): self
	{
		$taskIds = self::validateTaskIds($request->taskIds);
		if (!$taskIds)
		{
			throw new ArgumentException('taskIds');
		}

		$fromUserId = self::validateUserId($request->fromUserId);
		if (!$fromUserId)
		{
			throw new ArgumentOutOfRangeException('fromUserId', 1, null);
		}

		$toUserId = self::validateUserId($request->toUserId);
		if (!$toUserId)
		{
			throw new ArgumentOutOfRangeException('toUserId', 1, null);
		}

		$currentUserId = self::validateUserId($request->currentUserId);

		return new self($taskIds, $fromUserId, $toUserId, $currentUserId);
	}

	/**
	 * @param array $taskIds
	 * @return array
	 */
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

	/**
	 * @param int $userId
	 * @return int
	 */
	private static function validateUserId(int $userId): int
	{
		return max($userId, 0);
	}

	/**
	 * @return array
	 */
	public function getTaskIds(): array
	{
		return $this->taskIds;
	}

	/**
	 * @return int
	 */
	public function getFromUserId(): int
	{
		return $this->fromUserId;
	}

	/**
	 * @return int
	 */
	public function getToUserId(): int
	{
		return $this->toUserId;
	}

	/**
	 * @return int
	 */
	public function getCurrentUserId(): int
	{
		return $this->currentUserId;
	}
}
