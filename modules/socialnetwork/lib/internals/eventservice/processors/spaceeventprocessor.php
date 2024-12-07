<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Processors;

use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventCollection;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Queue\Queue;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;
use Bitrix\Socialnetwork\Internals\LiveFeed;
use Bitrix\Socialnetwork\Internals\Space;
use Bitrix\Socialnetwork\Space\List\RecentActivity;
use Bitrix\Socialnetwork\Space\Role;

class SpaceEventProcessor
{
	private Queue $queue;
	private Role\Event\Service $roleService;

	public function __construct()
	{
		$this->queue = Queue::getInstance();
		$this->roleService = new Role\Event\Service();
	}

	public function getStepLimit(): int
	{
		return (int) Option::get('socialnetwork', 'space_processor_step_limit', 100);
	}

	/**
	 * The logic of this process is to find out all recepients involved
	 * and re-calculate: live-feed(depends on event) and space counters
	 * and save recent activity for each of the recepients
	 * @return void
	 */
	public function process(): void
	{
		$isSpaceFeatureDisabled = !(\Bitrix\Socialnetwork\Space\Service::isAvailable());
		$isSpaceProcessorDisabled = Option::get('socialnetwork', 'space_processor_disabled', 'N') === 'Y';

		if ($isSpaceFeatureDisabled)
		{
			return;
		}

		if ($isSpaceProcessorDisabled)
		{
			return;
		}

		foreach (EventCollection::getInstance()->list() as $event)
		{
			/* @var Event $event */
			if (!in_array($event->getType(), EventDictionary::SPACE_EVENTS_SUPPORTED, true))
			{
				continue;
			}

			$this->processEvent($event);
		}
	}

	public function processEventForUser(Event $event, Recepient $recipient): void
	{
		// recount live-feed counters in case event is one of the live-feeds'
		(new LiveFeed\Counter\CounterController($recipient->getId()))->process($event);
		// recount space counters and push events for real-time
		(new Space\Counter\CounterController($recipient->getId()))->process($event, $recipient);
		// save space recent activity
		$this->roleService->processEvent($event, $recipient);
		(new RecentActivity\Event\Service())->processEvent($event, $recipient);
	}

	private function processEvent(Event $event, int $offset = 0, bool $processInQueue = false): void
	{
		$limit = $this->getStepLimit();
		$recipients = $event->getRecepients()->fetch($limit, $offset);

		foreach ($recipients as $recipient)
		{
			if ($processInQueue)
			{
				$this->queue->add($event, $recipient);

				continue;
			}

			$this->processEventForUser($event, $recipient);
		}

		$this->queue->save();

		if (count($recipients) >= $limit)
		{
			$processInQueue = true;
			$offset = $offset + $limit;
			$this->processEvent($event, $offset, $processInQueue);
		}
	}
}