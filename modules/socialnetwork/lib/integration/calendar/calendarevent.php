<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2017 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Calendar;

use Bitrix\Socialnetwork\Livefeed\Provider;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CalendarEvent
{
	public static function onViewEvent(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'socialnetwork'
		);

		$calendarEventId = $event->getParameter('eventId');

		if (intval($calendarEventId) <= 0)
		{
			return $result;
		}

		if ($liveFeedEntity = Provider::init(array(
			'ENTITY_TYPE' => Provider::DATA_ENTITY_TYPE_CALENDAR_EVENT,
			'ENTITY_ID' => $calendarEventId
		)))
		{
			$liveFeedEntity->setContentView();
		}

		$result = new EventResult(
			EventResult::SUCCESS,
			array(),
			'socialnetwork'
		);

		return $result;
	}
}
?>