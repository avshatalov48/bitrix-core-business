<?php

namespace Bitrix\Calendar\Core\Queue\Queue;

class QueueFactory
{
	private static array $cache = [];

	public function getById(int $queueId): ?Queue
	{
		if (!array_key_exists($queueId, self::$cache))
		{
			if ($queueName = QueueRegistry::getNameById($queueId))
			{
				self::$cache[$queueId] = new Queue($queueId, $queueName);
			}
			else
			{
				self::$cache[$queueId] = null;
			}
		}

		return self::$cache[$queueId];
	}

	public function getByName(string $queueName): ?Queue
	{
		if ($queueId = QueueRegistry::getIdByName($queueName))
		{
			if (!array_key_exists($queueId, self::$cache))
			{
				self::$cache[$queueId] = new Queue($queueId, $queueName);
			}
		}
		else
		{
			self::$cache[$queueId] = null;
		}

		return self::$cache[$queueId];
	}
}