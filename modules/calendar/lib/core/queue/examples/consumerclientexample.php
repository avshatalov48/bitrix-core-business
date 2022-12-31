<?php

namespace Bitrix\Calendar\Core\Queue\Examples;

use Bitrix\Calendar\Core\Queue\Consumer\Simple;
use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Queue\QueueFactory;
use Bitrix\Calendar\Core\Queue\Queue\QueueRegistry;
use Bitrix\Calendar\Core\Queue;
use Bitrix;

class ConsumerClientExample extends Queue\Agent\BaseAgent
{
	protected function getConsumer(): Interfaces\Consumer
	{
		$queue = (new QueueFactory())->getById(QueueRegistry::QUEUE_LIST['Example']);
		$consumer = new Simple($queue);
		$consumer->setPackSize(100);
		return $consumer;
	}

	protected function getProcessor(): Interfaces\Processor
	{
		return new ProcessorExample();
	}
}