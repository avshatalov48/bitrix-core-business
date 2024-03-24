<?php

namespace Bitrix\Socialnetwork\Integration\Calendar\RecentActivity;

use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\AbstractProvider;

final class CalendarProvider extends AbstractProvider
{

	public function isAvailable(): bool
	{
		return Loader::includeModule('calendar');
	}

	public function getTypeId(): string
	{
		return 'calendar';
	}

	protected function fill(): void
	{
		$eventIds = $this->getEntityIdsFromRecentActivityItems();
		$events = [];
		if (!empty($eventIds))
		{
			$events = EventTable::query()
				->setSelect(['ID', 'NAME'])
				->whereIn('ID', $eventIds)
				->fetchAll()
			;
		}

		foreach ($events as $event)
		{
			$this->addEntity((int)$event['ID'], $event);
		}

		foreach ($this->recentActivityDataItems as $item)
		{
			$event = $this->getEntity($item->getEntityId());

			if (empty($event))
			{
				continue;
			}

			$message = Loc::getMessage(
				'SONET_CALENDAR_RECENT_ACTIVITY_DESCRIPTION',
				['#CONTENT#' => Emoji::decode($event['NAME'])],
			);
			$item->setDescription($message);
		}
	}
}
