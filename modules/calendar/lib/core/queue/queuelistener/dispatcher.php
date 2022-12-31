<?php

namespace Bitrix\Calendar\Core\Queue\QueueListener;

use Bitrix;
use Bitrix\Calendar\Core\Queue;
use Bitrix\Calendar\Core\Queue\Rule\RuleMaster;

class Dispatcher
{
	public static function register()
	{
		Bitrix\Main\EventManager::getInstance()->addEventHandler(
			'calendar',
			RuleMaster::ON_QUEUE_PUSHED_EVENT_NAME,
			[
				self::class,
				'handle'
			]
		);
	}

	public static function handle(Bitrix\Main\Event $event)
	{
		if (
			($queue = $event->getParameter('queue'))
			&& ($queue instanceof Queue\Interfaces\Queue)
		)
		{
			(new self())->wakeUpQueue($queue);
		}
	}

	private function wakeUpQueue(Queue\Interfaces\Queue $queue)
	{
		if ($listener = $this->getRegistry()->getListenerByQueueId($queue->getQueueId()))
		{
			$listener->handle();
		}
	}

	/**
	 * @return Registry
	 */
	private function getRegistry(): Registry
	{
		return Registry::getInstance();
	}
}