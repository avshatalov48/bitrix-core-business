<?php

namespace Bitrix\Calendar\Core\Queue\Rule\Rules;

use Bitrix;
use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Message\Dictionary;
use Bitrix\Calendar\Core\Queue\Queue\Queue;
use Bitrix\Calendar\Core\Queue\Queue\QueueFactory;
use Bitrix\Calendar\Core\Queue\Queue\QueueRegistry;

class ExampleRule extends DbRule
{

	/**
	 * @param Interfaces\Message $message
	 *
	 * @return Queue|null
	 */
	protected function getTargetQueue(Interfaces\Message $message): ?Queue
	{
		$routingKey = $message->getHeaders()[Dictionary::HEADER_KEYS['routingKey']] ?? null;
		if ($routingKey === 'example.test')
		{
			$queue = (new QueueFactory())->getById(QueueRegistry::QUEUE_LIST['EventDelayedSync']);
			return $queue;
		}

		return null;
	}

	/**
	 * @param Interfaces\Message $message
	 *
	 * @return string
	 */
	protected function getMessageHash(Interfaces\Message $message): string
	{
		return 'ExamplePrefix_' . $message->getBody()['exampleField'];
	}
}