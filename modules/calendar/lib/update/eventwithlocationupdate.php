<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Rooms;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Update\Stepper;
use CCalendar;
use CCalendarEvent;

final class EventWithLocationUpdate extends Stepper
{
	const PORTION = 100;
	const TIMESLICE = 5200000;

	protected static $moduleId = 'calendar';

	public static function className(): string
	{
		return get_called_class();
	}

	public function execute(array &$result)
	{
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		if (Loader::includeModule("calendar")
			&& (Option::get('calendar', 'eventWithLocationConverted', 'N') === 'Y')
		)
		{
			Rooms\Manager::createInstance()->clearCache();
			return self::FINISH_EXECUTION;
		}
		$status = $this->loadCurrentStatus();

		$newStatus = [
			'count' => $status['count'],
			'steps' => $status['steps'],
			'newFinished' => $status['newFinished'],
			'lastEventId' => $status['lastEventId']
		];

		// Update calendar room events
		if (!$status['newFinished'])
		{
			$res = $this->getLocationEvent($newStatus['lastEventId']);
			while ($event = $res->Fetch())
			{
				$eventId = (int)$event['ID'];
				$newStatus['lastEventId'] = $eventId;

				$parentRes = $this->getLocationParentEvent($eventId);
				if ($parentEvent = $parentRes->Fetch())
				{
					$ownerName = $sqlHelper->forSql(CCalendar::GetUserName($parentEvent['CREATED_BY']));
					$parentId = (int)$parentEvent['ID'];
					$this->updateLocationEvent($parentId, $ownerName, $eventId);
				}
				else
				{
					$this->deleteEvent($eventId);
				}

				$newStatus['steps']++;
			}

			if (isset($newStatus['lastEventId']) && $res->SelectedRowsCount() !== 0)
			{
				Option::set('calendar', 'eventWithLocationConvertedStatus', serialize($newStatus));
				$result = [
					'title' => Loc::getMessage("CALENDAR_UPDATE_EVENT_WITH_LOCATION"),
					'count' => $newStatus['count'],
					'steps' => $newStatus['steps'],
					'lastEventId' => $newStatus['lastEventId'],
					'newFinished' => $newStatus['newFinished']
				];

				return self::CONTINUE_EXECUTION;
			}

			$newStatus['newFinished'] = true;
			$newStatus['lastEventId'] = PHP_INT_MAX;
		}

		//update IBlock room events
		$meetingRoomArray = $this->getMeetingRoomArray();

		if ($meetingRoomArray !== null)
		{
			$res = $this->getIBlockEvent($newStatus['lastEventId']);
			while ($event = $res->Fetch())
			{
				$eventId = (int)$event['ID'];
				$newStatus['lastEventId'] = $eventId;
				$phrases = $this->prepareLocationEvent($event, $meetingRoomArray);

				if ($phrases !== null && isset($phrases['child']) && isset($phrases['parent']))
				{
					$this->updateLocationValue($phrases['parent'], $eventId);
					if ($event['IS_MEETING'])
					{
						$this->updateLocationValueForChildEvents($phrases['child'], $eventId);
					}
					Rooms\Manager::setEventIdForLocation($eventId);
				}

				$newStatus['steps']++;
			}

			if (isset($newStatus['lastEventId']) && $res->SelectedRowsCount() !== 0)
			{
				Option::set('calendar', 'eventWithLocationConvertedStatus', serialize($newStatus));
				$result = [
					'title' => Loc::getMessage("CALENDAR_UPDATE_EVENT_WITH_LOCATION"),
					'count' => $newStatus['count'],
					'steps' => $newStatus['steps'],
					'lastEventId' => $newStatus['lastEventId'],
					'newFinished' => $newStatus['newFinished']
				];

				return self::CONTINUE_EXECUTION;
			}
		}

		Option::set('calendar', 'eventWithLocationConverted', 'Y');
		Option::delete('calendar', ['name' => 'eventWithLocationConvertedStatus']);
		Rooms\Manager::createInstance()->clearCache();

		return self::FINISH_EXECUTION;
	}

	/**
	 * @return array|mixed
	 */
	private function loadCurrentStatus()
	{
		$status = Option::get('calendar', 'eventWithLocationConvertedStatus', 'default');
		$status = ($status !== 'default' ? @unserialize($status, ['allowed_classes' => false]) : []);
		$status = (is_array($status) ? $status : []);

		if (empty($status))
		{
			$status = [
				'count' => $this->getTotalCount(),
				'steps' => 0,
				'lastEventId' => PHP_INT_MAX,
				'newFinished' => false
			];
		}

		return $status;
	}

	/**
	 * @return int
	 */
	private function getTotalCount(): int
	{
		return $this->getTotalCountLocation() + $this->getTotalCountIBlock();
	}

	/**
	 * @return int
	 */
	private function getTotalCountLocation(): int
	{
		global $DB;
		$count = 0;
		$result = $DB->Query("
			SELECT COUNT(*) AS cnt
			FROM b_calendar_event
			WHERE ID = PARENT_ID 
			AND CAL_TYPE = 'location' 
		    AND DELETED = 'N';"
		);
		if ($res = $result->Fetch())
		{
			$count = (int)$res['cnt'];
		}

		return $count;
	}

	/**
	 * @return int
	 */
	private function getTotalCountIBlock(): int
	{
		global $DB;
		$timestamp = time() - self::TIMESLICE;
		$count = 0;
		$result = $DB->Query("
			SELECT COUNT(*) AS cnt
			FROM b_calendar_event
			WHERE DELETED = 'N' 
		    AND DATE_TO_TS_UTC > " . $timestamp . "
		    AND PARENT_ID = ID
			AND LOCATION LIKE 'ECMR%'"
		);
		if ($res = $result->Fetch())
		{
			$count = (int)$res['cnt'];
		}

		return $count;
	}

	/**
	 * @return mixed
	 */
	private function getLocationEvent(int $lastEventId)
	{
		global $DB;
		return $DB->Query("
			SELECT ID, PARENT_ID, CAL_TYPE
			FROM b_calendar_event
			WHERE ID = PARENT_ID 
			AND CAL_TYPE = 'location' 
			AND DELETED = 'N'
			AND ID < ".$lastEventId."
			ORDER BY ID DESC
			LIMIT ".self::PORTION.";"
		);
	}

	/**
	 * @param int $eventId
	 * @return mixed
	 */
	private function getLocationParentEvent(int $eventId)
	{
		global $DB;
		return $DB->Query("
			SELECT ID, CREATED_BY, LOCATION
			FROM b_calendar_event
			WHERE LOCATION LIKE 'calendar_%_".$eventId."'
			AND DELETED = 'N'
			LIMIT 1"
		);
	}

	/**
	 * @param $DB
	 * @param int $parentId
	 * @param string $ownerName
	 * @param int $id
	 */
	private function updateLocationEvent(int $parentId, string $ownerName, int $eventId): void
	{
		global $DB;
		$DB->Query("
			UPDATE b_calendar_event
			SET PARENT_ID = " . $parentId . ", NAME = '" . $ownerName . "'
			WHERE ID = " . $eventId . ";"
		);
	}

	/**
	 * @return mixed|null
	 */
	private function getMeetingRoomArray()
	{
		$newMeetingRooms = Option::get('calendar', 'converted_meeting_rooms');
		$newMeetingRooms =  json_decode($newMeetingRooms, true);

		if (!empty($newMeetingRooms) && is_array($newMeetingRooms))
		{
			return $newMeetingRooms;
		}

		return null;
	}

	/**
	 * @return mixed
	 */
	private function getIBlockEvent(int $lastEventId)
	{
		global $DB;
		$timestamp = time() - self::TIMESLICE;

		return $DB->Query("
			SELECT ID, PARENT_ID, DATE_FROM,
		    DATE_TO, TZ_FROM, TZ_TO, IS_MEETING,
		    RRULE, EXDATE, CREATED_BY, DT_SKIP_TIME
			FROM b_calendar_event
			WHERE DELETED = 'N' 
			AND DATE_TO_TS_UTC > " . $timestamp . "
			AND LOCATION LIKE 'ECMR%'
			AND PARENT_ID = ID
			AND ID < ".$lastEventId."
			ORDER BY ID DESC
			LIMIT " . self::PORTION . ";"
		);
	}

	/**
	 * @param $event
	 * @param $meetingRoomArray
	 * @return array|null phrases for updating the location value
	 */
	private function prepareLocationEvent($event, $meetingRoomArray): ?array
	{
		global $DB;
		$id = (int)$event['ID'];
		$dateToRaw = strtotime($event['DATE_TO']);
		$dateFromRaw = strtotime($event['DATE_FROM']);
		$dateTo = CCalendar::Date($dateToRaw);
		$dateFrom = CCalendar::Date($dateFromRaw);

		$RRule = CCalendarEvent::ParseRRULE($event['RRULE']);
		if (isset($RRule['~UNTIL']))
		{
			unset($RRule['~UNTIL']);
		}
		if ($RRule['FREQ'] === 'WEEKLY' && !isset($RRule['BYDAY']))
		{
			return null;
		}

		$skipTime = $event['DT_SKIP_TIME'] === 'Y';

		$phraseLocationParent = 'calendar_#ROOMID#_#EVENTID#';
		$phraseLocationChild = 'calendar_#ROOMID#';
		$result = [];

		$res = $DB->Query("
			SELECT LOCATION 
			FROM b_calendar_event
			WHERE ID = " . $id . ";"
		);
		if ($location = $res->Fetch())
		{
			$location = explode("_", $location['LOCATION']);
			$mrId = $location[1];
			$roomId = $meetingRoomArray[$mrId];

			$locationEventId = Rooms\Manager::reserveRoom([
				'parentParams' => [
					'arFields' => [
						'DATE_FROM' => $dateFrom,
						'DATE_TO' => $dateTo,
						'TZ_FROM' => $event['TZ_FROM'],
						'TZ_TO' => $event['TZ_TO'],
						'SKIP_TIME' => $skipTime,
						'RRULE' => $RRule,
						'EXDATE' => $event['EXDATE'],
						'CREATED_BY' => (int)$event['CREATED_BY']
					],
					'userId' => (int)$event['CREATED_BY']
				],
				'room_event_id' => false,
				'room_id' => (int)$roomId
			]);

			if ($locationEventId && $roomId)
			{
				$result['parent'] = str_replace(
					['#ROOMID#', '#EVENTID#'],
					[$roomId, $locationEventId],
					$phraseLocationParent
				);
				$result['child'] = str_replace(
					['#ROOMID#'],
					[$roomId],
					$phraseLocationChild
				);
				return $result;
			}
		}

		return null;
	}

	/**
	 * @param string $phraseLocation
	 * @param int $id
	 */
	private function updateLocationValue(string $phraseLocation, int $id) : void
	{
		global $DB;
		$DB->Query("
			UPDATE b_calendar_event
			SET LOCATION = '" . $phraseLocation . "'
			WHERE ID = ".$id.";"
		);
	}

	/**
	 * @param string $phraseLocation
	 * @param int $id
	 */
	private function updateLocationValueForChildEvents(string $phraseLocation, int $id) : void
	{
		global $DB;
		$DB->Query("
			UPDATE b_calendar_event
			SET LOCATION = '" . $phraseLocation . "'
			WHERE PARENT_ID = " . $id. "
			AND PARENT_ID <> ID
			AND CAL_TYPE <> 'location'
			AND DELETED = 'N';"
		);
	}

	/**
	 * @param int $id
	 */
	private function deleteEvent(int $id)
	{
		global $DB;
		$DB->Query("
			UPDATE b_calendar_event
			SET DELETED = 'Y'
			WHERE ID = " . $id . ";"
		);
	}
}