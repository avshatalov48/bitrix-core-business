<?php

namespace Bitrix\Calendar\Core\Queue\QueueListener;

use Bitrix\Calendar\Core\Queue\Agent\AgentEntity;
use Bitrix\Calendar\Core\Queue\Agent\EventAttendeesUpdateAgent;
use Bitrix\Calendar\Core\Queue\Agent\EventDelayedSyncAgent;
use Bitrix\Calendar\Core\Queue\Agent\EventsWithEntityAttendeesFindAgent;
use Bitrix\Calendar\Core\Queue\Examples\ConsumerClientExample;
use Bitrix\Calendar\Core\Queue\Queue\QueueRegistry;
use Bitrix\Calendar\Internals\SingletonTrait;
use Bitrix\Calendar\Core\Queue;

class Registry
{
	use SingletonTrait;

	/** @var Queue\Interfaces\Listener[] */
	private array $listeners = [];

	protected function __construct()
	{
		$this->init();
	}

	public function registerListener(int $queueId, Queue\Interfaces\Listener $listener)
	{
		$this->listeners[$queueId] = $listener;
	}

	public function getListenerByQueueId(int $queueId): ?Queue\Interfaces\Listener
	{
		return $this->listeners[$queueId] ?? null;
	}

	private function init()
	{
		$this->registerListener(
			QueueRegistry::QUEUE_LIST['EventDelayedSync'],
			new AgentListener(
				new AgentEntity(
					EventDelayedSyncAgent::class . '::runAgent();'
				)
			)

		);
		$this->registerListener(
			QueueRegistry::QUEUE_LIST['DelayedSyncSection'],
			new AgentListener(
				new AgentEntity(
					Queue\Agent\PushDelayedSectionAgent::class . '::runAgent();'
				)
			)
		);
		$this->registerListener(
			QueueRegistry::QUEUE_LIST['DelayedSyncConnection'],
			new AgentListener(
				new AgentEntity(
					Queue\Agent\PushDelayedConnectionAgent::class . '::runAgent();'
				)
			)
		);

		$this->registerListener(
			QueueRegistry::QUEUE_LIST['Example'],
			new AgentListener(
				new AgentEntity(
					ConsumerClientExample::class . '::runAgent();'
				)
			)
		);

		$this->registerListener(
			QueueRegistry::QUEUE_LIST['EventsWithEntityAttendeesFind'],
			new AgentListener(
				new AgentEntity(
					EventsWithEntityAttendeesFindAgent::class . '::runAgent();',
					'calendar',
					1,
				)
			)
		);

		$this->registerListener(
			QueueRegistry::QUEUE_LIST['EventAttendeesUpdate'],
			new AgentListener(
				new AgentEntity(
					EventAttendeesUpdateAgent::class . '::runAgent();',
					'calendar',
					1,
				)
			)
		);
	}
}