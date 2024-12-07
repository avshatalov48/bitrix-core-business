<?php

namespace Bitrix\Calendar\Internals\Counter\Processor;

use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Internals\Counter\CounterDictionary;
use Bitrix\Calendar\Internals\Counter\Event\Event;
use Bitrix\Calendar\Internals\Counter\Event\EventCollection;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;
use Bitrix\Calendar\Sync\Caldav;
use Bitrix\Calendar\Sync\Icloud;
use Bitrix\Calendar\Sync\Google;
use Bitrix\Calendar\Sync\Office365;


class Sync implements Base
{
	private const SUPPORTED_EVENTS = [
		EventDictionary::SYNC_CHANGED,
		EventDictionary::COUNTERS_UPDATE,
	];

	private const PROVIDERS = [
		Icloud\Helper::ACCOUNT_TYPE,
		Google\Helper::GOOGLE_ACCOUNT_TYPE_API,
		Office365\Helper::ACCOUNT_TYPE,
		Caldav\Helper::CALDAV_TYPE,
	];

	public function process(): void
	{
		$events = (EventCollection::getInstance())->list();
		foreach ($events as $event)
		{
			/* @var $event Event */
			$eventType = $event->getType();

			if (in_array($eventType, self::SUPPORTED_EVENTS, true))
			{
				$affectedUsers = $event->getData()['user_ids'] ?? [];
				$this->recountSyncErrors($affectedUsers);
			}
		}
	}

	private function recountSyncErrors(array $users): void
	{
		if (empty($users))
		{
			return;
		}

		$davConnectionProvider = new ConnectionProvider();

		foreach ($users as $userId)
		{
			$syncErrors = 0;
			$connections = $davConnectionProvider->getSyncConnections($userId, 'user', self::PROVIDERS);

			foreach ($connections as $connection)
			{
				$isSuccess = \CCalendarSync::isConnectionSuccess($connection->getStatus());

				if (!$isSuccess)
				{
					$syncErrors++;
				}
			}

			\CUserCounter::Set($userId, CounterDictionary::COUNTER_SYNC_ERRORS, $syncErrors, '**', '', false);
		}
	}
}