<?php

namespace Bitrix\Socialnetwork\Integration\Calendar\RecentActivity;

use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\AbstractProvider;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\Trait\EntityLoadTrait;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;

class CalendarProvider extends AbstractProvider
{
	use EntityLoadTrait;

	public function isAvailable(): bool
	{
		return Loader::includeModule('calendar');
	}

	public function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['calendar'];
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
			$event = $this->getEntity($this->getEntityIdFromRecentActivityItem($item));

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
