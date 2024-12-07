<?php

namespace Bitrix\Calendar\Ui\Preview;

use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Event\Helper\EventHelper;
use Bitrix\Calendar\Rooms;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

class Event
{
	public static function buildPreview(array $params)
	{
		global $APPLICATION;
		$eventId = (int)$params['eventId'];
		if(!$eventId)
		{
			return '';
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:calendar.event.preview',
			'',
			$params
		);
		return ob_get_clean();
	}

	public static function checkUserReadAccess(array $params): bool
	{
		$eventId = (int)$params['eventId'];
		if(
			!$eventId
			|| !Loader::includeModule('calendar')
		)
		{
			return false;
		}

		$events = \CCalendarEvent::getList(
			[
				'arFilter' => [
					'ID' => $eventId,
					'DELETED' => false,
				],
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false
			]
		);

		return ($events && is_array($events[0]));
	}

	public static function getImAttach(array $params): false|\CIMMessageParamAttach
	{
		if (!Loader::includeModule('im'))
		{
			return false;
		}

		if (!($params['eventId'] ?? null) || !($eventId = (int)$params['eventId']))
		{
			return false;
		}

		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		/** @var \Bitrix\Calendar\Core\Event\Event $event */
		$event = $mapperFactory->getEvent()->getById($eventId);
		if (!$event)
		{
			return false;
		}

		// return attach only for open events to prevent old logic affect
		if (!$event->isOpenEvent())
		{
			return false;
		}

		$attach = new \CIMMessageParamAttach(1, $event->getColor());
		$attach->AddLink([
			'NAME' => $event->getName(),
			'LINK' => EventHelper::getViewUrl($event),
		]);

		$attach->AddDelimiter();
		$attach->AddGrid(self::getImAttachGrid($event));

		return $attach;
	}

	public static function getImRich(array $params)
	{
		if (!Loader::includeModule('im'))
		{
			return false;
		}

		if (!class_exists('\Bitrix\Im\V2\Entity\Url\RichData'))
		{
			return false;
		}

		return (new \Bitrix\Im\V2\Entity\Url\RichData())->setType(\Bitrix\Im\V2\Entity\Url\RichData::CALENDAR_TYPE);
	}

	protected static function getUser()
	{
		global $USER;

		return $USER;
	}

	private static function getImAttachGrid(\Bitrix\Calendar\Core\Event\Event $event): array
	{
		$grid = [];
		$display = 'COLUMN';
		$columnWidth = 120;
		if ($categoryName = $event->getEventOption()?->getCategory()->getName())
		{
			$grid[] = [
				'NAME' => Loc::getMessage('CALENDAR_PREVIEW_ATTACH_CATEGORY'),
				'VALUE' => $categoryName,
				'DISPLAY' => $display,
				'WIDTH' => $columnWidth,
			];
		}

		if ($location = $event->getLocation())
		{
			$parsedLocation = Rooms\Util::parseLocation($location);
			$roomId = $parsedLocation['room_id'] ?? null;
			if ($roomId)
			{
				$rooms = Rooms\Manager::getRoomById($roomId);
				$room = $rooms ? $rooms[0] : null;
				$roomName = $room['NAME'] ?? null;
				if ($roomName)
				{
					$grid[] = [
						'NAME' => Loc::getMessage('CALENDAR_PREVIEW_ATTACH_ROOM'),
						'VALUE' => $roomName,
						'DISPLAY' => $display,
						'WIDTH' => $columnWidth,
					];
				}
			}
		}

		$creator = $event->getCreator();
		$grid[] = [
			'NAME' => Loc::getMessage('CALENDAR_PREVIEW_ATTACH_CREATOR'),
			'VALUE' => $creator->getFullName(),
			'USER_ID' => $creator->getId(),
			'DISPLAY' => $display,
			'WIDTH' => $columnWidth,
		];

		return $grid;
	}
}
