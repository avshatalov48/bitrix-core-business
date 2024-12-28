<?php

namespace Bitrix\Calendar\Ui\Preview;

use Bitrix\Calendar\Core\EventOption\EventOption;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Event\Helper\EventHelper;
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
		/** @var \Bitrix\Calendar\Core\Event\Event $eventObject */
		$eventObject = $mapperFactory->getEvent()->getById($eventId);
		$eventOptions = $eventObject->getEventOption();
		$eventForView = \CCalendarEvent::getEventForViewInterface($eventId, [
			'userId' => \CCalendar::GetCurUserId(),
			'eventDate' => $params['eventDate'] ?? null,
		]);

		if (
			!$eventForView
			|| !self::shouldShowAttach($eventForView)
		)
		{
			return false;
		}

		$attach = new \CIMMessageParamAttach(1, $eventObject->getColor());
		$attach->AddLink([
			'NAME' => $eventForView['NAME'],
			'LINK' => EventHelper::getViewUrl($eventObject),
		]);

		$attach->AddDelimiter();
		$attach->AddGrid(self::getImAttachGrid($eventForView, $eventOptions));

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

	private static function getImAttachGrid(array $eventForView, ?EventOption $eventOption = null): array
	{
		$grid = [];
		$display = 'COLUMN';
		$columnWidth = 120;

		if ($categoryName = $eventOption?->getCategory()->getName())
		{
			$grid[] = [
				'NAME' => Loc::getMessage('CALENDAR_PREVIEW_ATTACH_CATEGORY'),
				'VALUE' => $categoryName,
				'DISPLAY' => $display,
				'WIDTH' => $columnWidth,
			];
		}

		$grid[] = [
			'NAME' => Loc::getMessage('CALENDAR_PREVIEW_ATTACH_TIME'),
			'VALUE' => self::getDateString($eventForView),
			'DISPLAY' => $display,
			'WIDTH' => $columnWidth,
		];

		if ($roomName = \CCalendar::GetTextLocation($eventForView['LOCATION'] ?? null))
		{
			$grid[] = [
				'NAME' => Loc::getMessage('CALENDAR_PREVIEW_ATTACH_ROOM'),
				'VALUE' => $roomName,
				'DISPLAY' => $display,
				'WIDTH' => $columnWidth,
			];
		}

		$creator = self::getMeetingCreator($eventForView);
		$grid[] = [
			'NAME' => Loc::getMessage('CALENDAR_PREVIEW_ATTACH_CREATOR'),
			'VALUE' => $creator['DISPLAY_NAME'],
			'USER_ID' => $creator['ID'],
			'DISPLAY' => $display,
			'WIDTH' => $columnWidth,
		];

		if ($description = ($eventForView['~DESCRIPTION'] ?? null))
		{
			$grid[] = [
				'NAME' => Loc::getMessage('CALENDAR_PREVIEW_ATTACH_DESCRIPTION'),
				'VALUE' => HTMLToTxt($description),
				'DISPLAY' => $display,
				'WIDTH' => $columnWidth,
			];
		}

		return $grid;
	}

	private static function shouldShowAttach(array $eventForView): bool
	{
		// previously this method test:
		// - event is open event
		// - event is collab event
		//
		// for now rich message should show for every event,
		// but if this will cause performance impact, we should go back to event type check
		return true;
	}

	private static function getDateString(array $eventForView): string
	{
		$skipTime = $eventForView['DT_SKIP_TIME'] === 'Y';
		$fromTs = \CCalendar::Timestamp($eventForView['DATE_FROM']);
		$toTs = \CCalendar::Timestamp($eventForView['DATE_TO']);
		if ($skipTime)
		{
			$toTs += \CCalendar::DAY_LENGTH;
		}
		else
		{
			$fromTs -= $eventForView['~USER_OFFSET_FROM'];
			$toTs -= $eventForView['~USER_OFFSET_TO'];
		}

		return \CCalendar::GetFromToHtml($fromTs, $toTs, $skipTime, $eventForView['DT_LENGTH']);
	}

	private static function getMeetingCreator(array $eventForView): ?array
	{
		$meetingCreator = [];

		if (
			$eventForView['IS_MEETING']
			&& $eventForView['MEETING']['MEETING_CREATOR']
			&& $eventForView['MEETING']['MEETING_CREATOR'] !== $eventForView['MEETING_HOST']
		)
		{
			$meetingCreator = \CCalendar::GetUser($eventForView['MEETING']['MEETING_CREATOR'], true);
			$meetingCreator['DISPLAY_NAME'] = \CCalendar::GetUserName($meetingCreator);
		}

		if (!$meetingCreator)
		{
			$meetingCreator = \CCalendar::GetUser($eventForView['MEETING_HOST'], true);
			$meetingCreator['DISPLAY_NAME'] = \CCalendar::GetUserName($meetingCreator);
		}

		return $meetingCreator;
	}
}
