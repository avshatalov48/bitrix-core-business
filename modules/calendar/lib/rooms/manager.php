<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Calendar\Internals\AccessTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\LocationTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use CCalendar;
use CCalendarEvent;
use CCalendarSect;

class Manager
{
	const TYPE = 'location';

	/**
	 * @param $params
	 *
	 * Creating Room in Location Calendar
	 * Returns id of created room
	 *
	 * @return int|null
	 */
	public static function createRoom($params): ?int
	{
		return (new Room())->create($params);
	}

	/**
	 * @param $params
	 *
	 * Updating data of room in Location calendar
	 * Returns id of updated room
	 *
	 * @return int|null
	 */
	public static function updateRoom($params): ?int
	{
		return (new Room())->update($params);
	}

	/**
	 * @param $id
	 *
	 * Deleting room by id in Location calendar
	 * Returns true if successful
	 *
	 * @return bool
	 */
	public static function deleteRoom($id): bool
	{
		return (new Room())->delete($id);
	}

	/**
	 * @return array of rooms in Location calendar
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getRoomsList(): ?array
	{
		$rooms = SectionTable::getList(
			[
				'select' => [
					'ID',
					'NAME',
					'COLOR',
					'OWNER_ID',
					'CAL_TYPE',
					'NECESSITY' => 'LOCATION.NECESSITY',
					'CAPACITY' => 'LOCATION.CAPACITY',
					'LOCATION_ID' => 'LOCATION.ID',
					'ACCESS_CODE' => 'ACCESS_TABLE.ACCESS_CODE',
					'TASK_ID' => 'ACCESS_TABLE.TASK_ID',
				],
				'runtime' => [
					new ReferenceField(
						'LOCATION',
						LocationTable::class, ['=this.ID' => 'ref.SECTION_ID'],
						['join_type' => 'INNER']
					),
					new ReferenceField(
						'ACCESS_TABLE',
						AccessTable::class, ['=this.ID' => 'ref.SECT_ID'],
						['join_type' => 'INNER']
					)
				],
				'order' => [
					'ID'
				],
		    ])->fetchAll();

		if(empty($rooms))
		{
			CCalendarSect::CreateDefault([
					'type' => self::TYPE,
					'ownerId' => 0
				]
			);
			return null;
		}
		else
		{
			$rooms = self::setAccess($rooms);
			foreach ($rooms as $item)
			{
				CCalendarSect::HandlePermission($item);
			}
			return CCalendarSect::GetSectionPermission($rooms);
		}
	}

	/**
	 * @param $id
	 *
	 * @return array room by section id
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getRoomById($id): array
	{
		$room = SectionTable::getList(
			[
				'filter' => [
					'=ID' => $id,
				],
				'select' => [
					'ID',
					'NAME',
					'COLOR',
					'OWNER_ID',
					'CAL_TYPE',
					'NECESSITY' => 'LOCATION.NECESSITY',
					'CAPACITY' => 'LOCATION.CAPACITY',
					'LOCATION_ID' => 'LOCATION.ID',
					'ACCESS_CODE' => 'ACCESS_TABLE.ACCESS_CODE',
					'TASK_ID' => 'ACCESS_TABLE.TASK_ID',
				],
				'runtime' => [
					new ReferenceField(
						'LOCATION',
						LocationTable::class, ['=this.ID' => 'ref.SECTION_ID'],
						['join_type' => 'INNER']
					),
					new ReferenceField(
						'ACCESS_TABLE',
						AccessTable::class, ['=this.ID' => 'ref.SECT_ID'],
						['join_type' => 'INNER']
					)
				],
				'order' => [
					'ID'
				],
		    ])->fetchAll();

		$room = self::setAccess($room);
		foreach ($room as $item)
		{
			CCalendarSect::HandlePermission($item);
		}
		return CCalendarSect::GetSectionPermission($room);
	}

	/**
	 * @param array $params
	 *
	 * @return bool|int|mixed id of new event
	 */
	public static function reserveRoom(array $params = []): ?int
	{
		$name = Manager::getRoomName($params['room_id']);
		if (empty($name['NAME']))
		{
			return null;
		}
		
		$createdBy = ($params['parentParams']['arFields']['CREATED_BY']
			?? $params['parentParams']['arFields']['MEETING_HOST']);
		$userId = $params['parentParams']['userId']
			??  $params['parentParams']['arFields']['userId'];

		return CCalendarEvent::Edit([
			'arFields' => [
				'ID' => $params['room_event_id'],
				'CAL_TYPE' => self::TYPE,
				'SECTIONS' => $params['room_id'],
				'DATE_FROM' => $params['parentParams']['arFields']['DATE_FROM'],
				'DATE_TO' => $params['parentParams']['arFields']['DATE_TO'],
				'TZ_FROM' => $params['parentParams']['arFields']['TZ_FROM'],
				'TZ_TO' => $params['parentParams']['arFields']['TZ_TO'],
				'SKIP_TIME' => $params['parentParams']['arFields']['SKIP_TIME'],
				'NAME' => CCalendar::GetUserName($userId),
				'RRULE' => $params['parentParams']['arFields']['RRULE'],
				'EXDATE' => $params['parentParams']['arFields']['EXDATE'],
				'CREATED_BY' => $createdBy
			],
		]);
	}

	/**
	 * @param string $location
	 * @param array $params
	 *
	 * Checks if room is accessible for meeting
	 *
	 * @return bool
	 */
	public static function checkAccessibility(string $location = '', array $params = []): bool
	{
		$location = Util::parseLocation($location);

		$res = true;
		if ($location['room_id'] || $location['mrid'])
		{
			$fromTs = CCalendar::Timestamp($params['fields']["DATE_FROM"]);
			$toTs = CCalendar::Timestamp($params['fields']["DATE_TO"]);
			if ($params['fields']['SKIP_TIME'])
			{
				$toTs += CCalendar::GetDayLen();
			}
			
			$eventLocation = Manager::getEventLocation($params['fields']['ID']);
			$currentLocationEventId = '';
			if ($eventLocation)
			{
				$eventLocation = Util::parseLocation($eventLocation['LOCATION']);
				$currentLocationEventId = $eventLocation['room_event_id'];
			}

			$from = CCalendar::Date($fromTs, false);
			$to = CCalendar::Date($fromTs, false);

			$curUserId = CCalendar::GetCurUserId();
			$deltaOffset = isset($params['timezone']) ? (CCalendar::GetTimezoneOffset($params['timezone'])
				- CCalendar::GetCurrentOffsetUTC($curUserId)) : 0;

			if ($location['mrid'])
			{
				$meetingRoomRes = CCalendar::GetAccessibilityForMeetingRoom([
					'allowReserveMeeting' => true,
					'id' => $location['mrid'],
					'from' => CCalendar::Date(
						$fromTs - CCalendar::DAY_LENGTH,
						false
					),
					'to' => CCalendar::Date(
						$toTs + CCalendar::DAY_LENGTH,
						false
					),
					'curEventId' => $location['mrevid'],
																			]);

				foreach ($meetingRoomRes as $entry)
				{
					if ($entry['ID'] != $location['mrevid'])
					{
						$entryfromTs = CCalendar::Timestamp($entry['DT_FROM']);
						$entrytoTs = CCalendar::Timestamp($entry['DT_TO']);

						if ($entryfromTs < $toTs && $entrytoTs > $fromTs)
						{
							$res = false;
							break;
						}
					}
				}
			}
			elseif ($location['room_id'])
			{
				$entries = Manager::getRoomAccessibility($location['room_id'], $from, $to);
				foreach ($entries as $entry)
				{
					if ((int)$entry['ID'] !== (int)$location['room_event_id']
						&& (int)$entry['ID'] !== $currentLocationEventId)
					{
						$entryfromTs = CCalendar::Timestamp($entry['DATE_FROM']);
						$entrytoTs = CCalendar::Timestamp($entry['DATE_TO']);
						if ($entry['DT_SKIP_TIME'] !== 'Y')
						{
							$entryfromTs -= $entry['~USER_OFFSET_FROM'];
							$entrytoTs -= $entry['~USER_OFFSET_TO'];
							$entryfromTs += $deltaOffset;
							$entrytoTs += $deltaOffset;
						}
						else
						{
							$entrytoTs += CCalendar::GetDayLen();
						}

						if ($entryfromTs < $toTs && $entrytoTs > $fromTs)
						{
							$res = false;
							break;
						}
					}
				}
			}
		}

		return $res;
	}

	/**
	 * @param $roomId
	 * @param $from
	 * @param $to
	 *
	 * @return array room accessibility for creating event
	 */
	public static function getRoomAccessibility($roomId, $from, $to): array
	{
		$accessibility = [];

		$roomEntries = CCalendarEvent::GetList([
		   'arFilter' => [
			   "FROM_LIMIT" => $from,
			   "TO_LIMIT" => $to,
			   "CAL_TYPE" => self::TYPE,
			   "ACTIVE_SECTION" => "Y",
			   "SECTION" => $roomId,
		   ],
		   'parseRecursion' => true,
		   'fetchSection' => true,
		   'setDefaultLimit' => false,
		]);

		foreach ($roomEntries as $roomEntry)
		{
			$accessibility[] = [
				"ID" => $roomEntry["ID"],
				"NAME" => $roomEntry["NAME"],
				"DATE_FROM" => $roomEntry["DATE_FROM"],
				"DATE_TO" => $roomEntry["DATE_TO"],
				"~USER_OFFSET_FROM" => $roomEntry["~USER_OFFSET_FROM"],
				"~USER_OFFSET_TO" => $roomEntry["~USER_OFFSET_TO"],
				"DT_SKIP_TIME" => $roomEntry["DT_SKIP_TIME"],
				"TZ_FROM" => $roomEntry["TZ_FROM"],
				"TZ_TO" => $roomEntry["TZ_TO"],
				"ACCESSIBILITY" => $roomEntry["ACCESSIBILITY"],
				"IMPORTANCE" => $roomEntry["IMPORTANCE"],
				"EVENT_TYPE" => $roomEntry["EVENT_TYPE"],
			];
		}

		return $accessibility;
	}

	/**
	 * @param array $params
	 *
	 * Deleting event from calendar location
	 *
	 * @return bool|string
	 */
	public static function releaseRoom(array $params = [])
	{
		return \CCalendar::deleteEvent(
			(int)$params['room_event_id'],
			false,
			[
				'checkPermissions' => false,
				'markDeleted' => false
			]
		);
	}

	/**
	 * Clears cache for updating list of rooms on the page
	 */
	public static function clearCache()
	{
		\CCalendar::clearCache(['section_list', 'event_list']);
	}

	/**
	 * @param $rooms
	 *  Creates the correct display of access field in rooms
	 *
	 *  If first making temperance array and adding access field
	 *  Else if next is not equal to past, pushing in result array and making new temperance
	 *  Else (if next is equal to past) pushing to existent access field
	 *  And at last checking if is last element and pushing to result
	 *
	 * @return array
	 */
	private static function setAccess($rooms): array
	{
		$length = count($rooms);
		$result = [];
		$tmp = [];

		for ($i = 0; $i < $length; $i++)
		{
			if ($i == 0)
			{
				$tmp = $rooms[$i];
				$tmp['ACCESS'] = [$rooms[$i]['ACCESS_CODE'] => $rooms[$i]['TASK_ID']];
			}
			elseif ($rooms[$i - 1]['ID'] !== $rooms[$i]['ID'])
			{
				unset($tmp['ACCESS_CODE'], $tmp['TASK_ID']);
				array_push($result, $tmp);
				$tmp = $rooms[$i];
				$tmp['ACCESS'] = [$rooms[$i]['ACCESS_CODE'] => $rooms[$i]['TASK_ID']];
			}
			else
			{
				$tmp['ACCESS'] += [$rooms[$i]['ACCESS_CODE'] => $rooms[$i]['TASK_ID']];
			}

			if ($i == $length - 1)
			{
				unset($tmp['ACCESS_CODE'], $tmp['TASK_ID']);
				array_push($result, $tmp);
			}
		}

		return $result;
	}

	/**
	 * @param $id
	 *
	 * Setting id of new event in user calendar
	 * for event in location calendar
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function setEventIdForLocation($id)
	{
		$event = EventTable::getList([
			'filter' => [
				'=ID' => $id,
			],
			'select' => [
				'LOCATION',
			],
		])->fetch();

		if (!empty($event['LOCATION']))
		{
			$location = Util::parseLocation($event['LOCATION']);
			if ($location['room_id'] && $location['room_event_id'])
			{
				EventTable::update(
					$location['room_event_id'],
					[
						'PARENT_ID' => $id,
					]
				);
			}
		}
	}

	/**
	 * @param $id
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getRoomName($id) : ?array
	{
		return SectionTable::getRow(
			[
				'filter' => [
					'=ID' => $id,
				],
				'select' => [
					'NAME',
				],
			]);
	}

	/**
	 * @param $name
	 * Validation for name of room
	 *
	 * @return string|null
	 */
	public static function checkRoomName(string $name): ?string
	{
		$name = trim($name);
		if(empty($name))
		{
			return null;
		}
		return $name;
	}

	/**
	 * Delete location value when deleting room
	 * @param int $id
	 * @param string $locationName
	 */
	public static function deleteLocationFromEvent(int $id, string $locationName)
	{
		global $DB;
		$guestsId = [];
		$idTemp = "(#ID#, ''),";
		$updateString = '';
		$locationId = 'calendar_' . $id;

		$events = $DB->Query("
			SELECT ID, PARENT_ID, OWNER_ID, LOCATION
			FROM b_calendar_event
			WHERE LOCATION LIKE '" . $locationId . "%';
		");

		while($event = $events->Fetch())
		{
			if($event['ID'] === $event['PARENT_ID'])
			{
				$guestsId[] = $event['OWNER_ID'];
			}
			$updateString .= str_replace('#ID#', $event['ID'], $idTemp);
		}

		if($updateString)
		{
			$updateString = substr($updateString, 0, -1);
			$DB->Query("
				INSERT INTO b_calendar_event (ID, LOCATION) 
				VALUES ".$updateString."
				ON DUPLICATE KEY UPDATE LOCATION = VALUES(LOCATION)
			");
			$guestsId = array_unique($guestsId);
			$userId = CCalendar::GetCurUserId();

			foreach ($guestsId as $guestId)
			{
				\CCalendarNotify::Send([
					'mode' => 'delete_location',
					"location" => $locationName,
					"locationId" => $id,
					"guestId" => (int)$guestId,
					"userId" => $userId,
				]);
			}
		}
	}
	
	public static function getEventLocation($id): ?array
	{
		return EventTable::getRow(
			[
				'filter' => [
					'=ID' => $id,
				],
				'select' => [
					'LOCATION',
				],
			]);
	}
}