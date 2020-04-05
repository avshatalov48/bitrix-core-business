<?
/**
 * @access private
 */

namespace Bitrix\Calendar\Integration\SocialNetwork;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Item\LogIndex;

class Log
{
	const EVENT_ID_CALENDAR = 'calendar';

	/**
	 * Returns set EVENT_ID processed by handler to generate content for full index.
	 *
	 * @param void
	 * @return array
	 */
	public static function getEventIdList()
	{
		return array(
			self::EVENT_ID_CALENDAR
		);
	}

	/**
	 * Returns content for LogIndex.
	 *
	 * @param Event $event Event from LogIndex::setIndex().
	 * @return EventResult
	 */
	public static function onIndexGetContent(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'calendar'
		);

		$eventId = $event->getParameter('eventId');
		$sourceId = $event->getParameter('sourceId');

		if (!in_array($eventId, self::getEventIdList()))
		{
			return $result;
		}

		$result = new EventResult(
			EventResult::SUCCESS,
			array(
				'content' => intval($sourceId) > 0 ? \CCalendarEvent::getSearchIndexContent($sourceId) : "",
			),
			'calendar'
		);

		return $result;
	}


}