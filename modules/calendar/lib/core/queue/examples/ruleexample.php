<?php

namespace Bitrix\Calendar\Core\Queue\Examples;

use Bitrix;
use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Interfaces\Message;
use Bitrix\Calendar\Core\Queue\Message\Dictionary;
use Bitrix\Calendar\Core\Queue\Queue\Queue;
use Bitrix\Calendar\Core\Queue\Queue\QueueFactory;
use Bitrix\Calendar\Core\Queue\Queue\QueueRegistry;
use Bitrix\Calendar\Core\Queue\Rule\Rules\DbRule;

class RuleExample extends DbRule implements Interfaces\RouteRule
{
	/**
	 * @param Message $message
	 *
	 * @return Queue|null
	 */
	protected function getTargetQueue(Message $message): ?Queue
	{
		$routingKey = $message->getHeaders()[Dictionary::HEADER_KEYS['routingKey']] ?? null;
		if ($routingKey === 'example')
		{
			$queue = (new QueueFactory())->getById(QueueRegistry::QUEUE_LIST['Example']);
			return $queue;
		}

		return null;
	}

	/**
	 * @param Message $message
	 *
	 * @return string
	 */
	protected function getMessageHash(Message $message): string
	{
		return 'ExamplePrefix_' . $message->getBody()['exampleField'];
	}
}