<?php

namespace Bitrix\Calendar\Core\Queue\Agent;

use Bitrix\Calendar\Core\Queue\Consumer;
use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Core\Queue\Processor;
use Bitrix\Calendar\Core\Queue\Queue\QueueFactory;
use Bitrix\Calendar\Core\Queue\Queue\QueueRegistry;

class EventAttendeesUpdateAgent extends BaseAgent
{
	protected function getConsumer(): Interfaces\Consumer
	{
		$queue = (new QueueFactory())->getById(QueueRegistry::QUEUE_LIST['EventAttendeesUpdate']);

		return new Consumer\GroupHash($queue);
	}

	protected function getProcessor(): Interfaces\Processor
	{
		return new Processor\EventAttendeesUpdate();
	}

	protected function getEscalatedInterval(): int
	{
		return 5;
	}
}