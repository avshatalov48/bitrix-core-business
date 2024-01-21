<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Processors;

use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventCollection;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Push\SpaceListSender;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterController;
use Bitrix\Socialnetwork\Internals\Space;

class SpaceEventProcessor
{
	/**
	 * The logic of this process is to find out all recepients involved
	 * and re-calculate: live-feed(depends on event) and space counters
	 * and save recent activity for each of the recepients
	 * @return void
	 */
	public function process(): void
	{
		if (!\Bitrix\Socialnetwork\Space\Service::isAvailable())
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

			foreach ($event->getRecepients() as $recepient)
			{
				/* @var Recepient $recepient */
				// recount live-feed counters in case event is one of the live-feeds'
				(new CounterController($recepient->getId()))->process($event);

				// recount space counters and push events for real-time
				Space\Counter::getInstance($recepient->getId())->recount();

				// save space recent activity
				// .....
			}
		}
	}
}