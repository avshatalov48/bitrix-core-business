<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Processors;

use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventCollection;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Service;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Option\EventPreProcessingOption;

class SpaceEventPreProcessor
{
	private const SUPPORTED_EVENTS = [
		EventDictionary::EVENT_SPACE_LIVEFEED_POST_UPD,
		EventDictionary::EVENT_SPACE_TASK_UPDATE,
		EventDictionary::EVENT_SPACE_CALENDAR_EVENT_UPD,
	];

	/**
	 * This method processes incoming space events and,
	 * if necessary, creates new events based on them before processing in SpaceEventProcessor
	 * @return void
	 */
	public function process(): void
	{
		// TODO spaces stub
		return;

		if (!EventPreProcessingOption::isEnabled())
		{
			return;
		}

		$recentActivityService = new Service();
		foreach (EventCollection::getInstance()->list() as $event)
		{
			/* @var Event $event */
			if (!in_array($event->getType(), self::SUPPORTED_EVENTS, true))
			{
				continue;
			}

			$recentActivityService->preProcessEvent($event);
		}
	}
}