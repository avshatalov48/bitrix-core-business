<?php

namespace Bitrix\Bizproc\Task;

use Bitrix\Bizproc\Task\Data\TaskData;

final class Manager
{
	public static function hasTask(string $associatedActivity): bool
	{
		return array_key_exists($associatedActivity, self::getSupportedTasks());
	}

	public static function getTask(string $associatedActivity, array $task, int $userId): ?Task
	{
		if (self::hasTask($associatedActivity))
		{
			$class = self::getSupportedTasks()[$associatedActivity];
			if (class_exists($class))
			{
				$taskData = TaskData::createFromArray($task);
				if ($taskData)
				{
					return new $class($taskData, $userId);
				}
			}
		}

		return null;
	}

	private static function getSupportedTasks(): array
	{
		return [
			ExternalEventTask::getAssociatedActivity() => ExternalEventTask::class,
		];
	}
}
