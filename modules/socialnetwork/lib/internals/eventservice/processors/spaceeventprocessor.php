<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Processors;

use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventCollection;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Push\SpaceListSender;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;
use Bitrix\Socialnetwork\Internals\LiveFeed;
use Bitrix\Socialnetwork\Internals\Space;
use Bitrix\Socialnetwork\Space\List\RecentActivity;

class SpaceEventProcessor
{
	public const STEP_LIMIT = 500;
	private $spaceEnabledForUsers = null;

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

	private function processEvent(Event $event, int $offset = 0): void
	{
		$recipients = $event->getRecepients()->fetch(self::STEP_LIMIT, $offset);

		foreach ($recipients as $recipient)
		{
			/* @var Recepient $recipient */

			// TODO: replace it with functionality that checks if user is watching spaces
			if (!$this->isFeatureEnabledForUser($recipient->getId()))
			{
				continue;
			}

			// recount live-feed counters in case event is one of the live-feeds'
			(new LiveFeed\Counter\CounterController($recipient->getId()))->process($event);
			// recount space counters and push events for real-time
			(new Space\Counter\CounterController($recipient->getId()))->process($event);
			// save space recent activity
			(new SpaceListSender())->send($event, $recipient);
			(new RecentActivity\Event\Service())->processEvent($event, $recipient);
		}

		if (count($recipients) >= self::STEP_LIMIT)
		{
			$offset = $offset + self::STEP_LIMIT;
			$this->processEvent($event, $offset);
		}
	}

	/**
	 * TODO: temporary solution
	 * @param int $userId
	 * @return bool
	 */
	private function isFeatureEnabledForUser(int $userId): bool
	{
		if (is_null($this->spaceEnabledForUsers))
		{
			$this->spaceEnabledForUsers = [];

			$filter = [
				'CATEGORY' => 'socialnetwork.space',
				'NAME' => 'space_enabled',
			];
			$dbRes = \CUserOptions::GetList([], $filter);

			if ($dbRes)
			{
				while ($option = $dbRes->fetch())
				{
					$userIdFromOption = (int)$option['USER_ID'];
					$this->spaceEnabledForUsers[$userIdFromOption] = $userIdFromOption;
				}
			}
		}

		return isset($this->spaceEnabledForUsers[$userId]);
	}
}