<?php

namespace Bitrix\Calendar\Core\Queue\Rule\Rules;

use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Message\Dictionary;
use Bitrix\Calendar\Core\Queue\Queue;
use Bitrix\Calendar\Sync\Managers\PushManager;
use Bitrix\Calendar\Sync;

class PushDelayedRule extends DbRule
{
	private const MODE_SECTION = 1;
	private const MODE_CONNECTION = 2;

	/**
	 * @param Interfaces\Message $message
	 *
	 * @return Queue\Queue|null
	 */
    protected function getTargetQueue(Interfaces\Message $message): ?Queue\Queue
	{
		switch ($this->getMode($message))
		{
			case self::MODE_SECTION:
				return (new Queue\QueueFactory())
					->getById(Queue\QueueRegistry::QUEUE_LIST['DelayedSyncSection']);
			case self::MODE_CONNECTION:
				return (new Queue\QueueFactory())
					->getById(Queue\QueueRegistry::QUEUE_LIST['DelayedSyncConnection']);
			default:
				return null;
		}
    }

	/**
	 * @param Interfaces\Message $message
	 *
	 * @return string
	 */
    protected function getMessageHash(Interfaces\Message $message): string
    {
		$body = $message->getBody();
		switch ($this->getMode($message))
		{
			case self::MODE_SECTION:
				return 'section:' . $body[Sync\Push\Dictionary::PUSH_TYPE['sectionConnection']] ?? '';
			case self::MODE_CONNECTION:
				return 'connection:' . $body[Sync\Push\Dictionary::PUSH_TYPE['connection']] ?? '';
			default:
				return '';
		}
    }

	/**
	 * @param Interfaces\Message $message
	 *
	 * @return int|null
	 */
	private function getMode(Interfaces\Message $message): ?int
	{
		$routingKey = $message->getHeader(Dictionary::HEADER_KEYS['routingKey']);
		if ($routingKey === PushManager::QUEUE_ROUTE_KEY_SECTION)
		{
			return self::MODE_SECTION;
		}
		elseif ($routingKey === PushManager::QUEUE_ROUTE_KEY_CONNECTION)
		{
			return self::MODE_CONNECTION;
		}

		return null;
	}
}