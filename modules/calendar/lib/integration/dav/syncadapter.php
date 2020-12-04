<?
/**
 * @access private
 */

namespace Bitrix\Calendar\Integration\Dav;

use Bitrix\Calendar\UserSettings;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/calendar/classes/general/calendar.php");

class SyncAdapter
{
	private static
		$sectionIndex = [];

	/**
	 * Returns collection of sections for user or other entity.
	 *
	 * @param string $entityType entity type (example: 'user' for user's calendars).
	 * @param int $entityId id of entity.
	 * @param array $params additional params.
	 * @return array list of calendar sections
	 */
	public static function getSectionList($entityType, $entityId, $params = [])
	{
		$sectionId = $params['sectionId'];
		$sectionList = [];

		if ($sectionId && self::$sectionIndex[$sectionId])
		{
			$sectionList[] = self::$sectionIndex[$sectionId];
		}
		else
		{
			if ($sectionId !== \CCalendar::TASK_SECTION_ID)
			{
				$filter = [
					'CAL_TYPE' => $entityType,
					'OWNER_ID' => $entityId
				];

				if (!is_array($params))
				{
					$params = [];
				}

				if ($sectionId > 0)
				{
					$filter['ID'] = $sectionId;
				}

				\CCalendar::SetSilentErrorMode(true);
				if (isset($params['active']))
				{
					$filter['ACTIVE'] = $params['active'] ? 'Y' : 'N';
				}

				$res = \CCalendarSect::GetList(['arFilter' => $filter]);
				\CCalendar::SetSilentErrorMode(false);

				foreach($res as $calendar)
				{
					if ($params['skipExchange'] == true && $calendar['DAV_EXCH_CAL'] <> '')
					{
						continue;
					}

					$section = [
						'ID' => $calendar['ID'],
						'~NAME' => $calendar['NAME'],
						'NAME' => htmlspecialcharsbx($calendar['NAME']),
						'COLOR' => htmlspecialcharsbx($calendar['COLOR'])
					];
					$sectionList[] = $section;
					self::$sectionIndex[$section['ID']] = $section;
				}

				// Temporary hide it, while waiting for new interface.
				if (!$sectionId && false)
				{
					$followedSectionIdList = UserSettings::getFollowedSectionIdList($entityId);
					if (count($followedSectionIdList) > 0)
					{
						$followedSectionList = \CCalendarSect::GetList(['arFilter' => [
							'CAL_TYPE' => $entityType,
							'OWNER_ID' => $entityId,
							'ACTIVE' => 'Y',
							'ADDITIONAL_IDS' => $followedSectionIdList
						]]);

						foreach($followedSectionList as $calendar)
						{
							$section = [
								'ID' => $calendar['ID'],
								'~NAME' => $calendar['NAME'],
								'NAME' => htmlspecialcharsbx($calendar['NAME']),
								'COLOR' => htmlspecialcharsbx($calendar['COLOR'])
							];
							$sectionList[] = $section;
							self::$sectionIndex[$section['ID']] = $section;
						}
					}
				}
			}

			if (\CCalendarSync::isTaskListSyncEnabled() && $entityType == 'user' && ($sectionId === \CCalendar::TASK_SECTION_ID || !$sectionId))
			{
				$sectionsUserData = UserSettings::getSectionCustomization($entityId);
				$taskSectionTitle = (isset($sectionsUserData['tasks']) && !empty($sectionsUserData['tasks']['name']))
					? $sectionsUserData['tasks']['name']
					: \Bitrix\Main\Localization\Loc::getMessage('EC_MY_TASKS');

				$section = [
					'ID' => \CCalendar::TASK_SECTION_ID,
					'~NAME' => $taskSectionTitle,
					'NAME' => htmlspecialcharsbx($taskSectionTitle),
					'COLOR' => (isset($sectionsUserData['tasks']) && !empty($sectionsUserData['tasks']['color'])) ?
						htmlspecialcharsbx($sectionsUserData['tasks']['color']) : \CCalendar::DEFAULT_TASK_COLOR
				];
				$sectionList[] = $section;
				self::$sectionIndex[$section['ID']] = $section;
			}
		}

		return $sectionList;
	}


	/**
	 * Returns calendar events for given section.
	 *
	 * @param string $sectionId id of section (collection) to select events from.
	 * @param array $params additional params.
	 * @return array list of calendar events
	 */
	public static function getEventList($sectionId = false, $params = [])
	{
		$entryList = [];

		if ($params['entityType'] === 'user')
		{
			$userId = $params['entityId'];
			if ($sectionId === \CCalendar::TASK_SECTION_ID)
			{
				$entryList = self::getTaskList($userId);
			}
			else
			{
				\CCalendar::SetOffset(false, 0);
				$filter = [
					'DELETED' => 'N'
				];

				if (isset($params['filter']['DAV_XML_ID']))
				{
					$filter['DAV_XML_ID'] = $params['filter']['DAV_XML_ID'];
				}
				else
				{
					if (isset($params['filter']['DATE_START']))
					{
						$filter['FROM_LIMIT'] = $params['filter']['DATE_START'];
					}
					if (isset($params['filter']['DATE_END']))
					{
						$filter['TO_LIMIT'] = $params['filter']['DATE_END'];
					}
				}

				if ($sectionId > 0)
				{
					$filter['SECTION'] = $sectionId;
				}

				$events = \CCalendarEvent::GetList(
					array(
						'arFilter' => $filter,
						'getUserfields' => false,
						'parseRecursion' => false,
						'fetchAttendees' => false,
						'fetchMeetings' => !$sectionId || \CCalendar::getMeetingSection($userId, true) == $sectionId,
						'userId' => $userId
					)
				);

				foreach ($events as $event)
				{
					// Skip events from where owner is host of the meeting and it's meeting from other section
					// or declined events
					if($event['IS_MEETING'] && ($event["MEETING_STATUS"] == 'N' || ($event['MEETING_HOST'] == $userId && $event['SECTION_ID'] != $sectionId)))
					{
						continue;
					}

					$event['XML_ID'] = $event['DAV_XML_ID'];
					$event['LOCATION'] = \CCalendar::GetTextLocation($event['LOCATION']);
					$event['RRULE'] = \CCalendarEvent::ParseRRULE($event['RRULE']);
					$entryList[] = $event;
				}
			}
		}

		return $entryList;
	}

	/**
	 * Returns calendar events.
	 *
	 * @param string $userId user id
	 * @param array $params additional params.
	 * @return array list of tasks prepared for viewing in calendar view
	 */
	public static function getTaskList($userId, $params = [])
	{
		$tasksEntries = [];
		if (\Bitrix\Main\Loader::includeModule('tasks'))
		{
			$tasksEntries = \CCalendar::getTaskList([
				'type' => 'user',
				'ownerId' => $userId
			]);

			for ($i = 0, $l = count($tasksEntries); $i < $l; $i++)
			{
				$tasksEntries[$i]['TIMESTAMP_X'] = \CCalendar::Date(round(time() / 180) * 180);
				$tasksEntries[$i]['DAV_XML_ID'] = 'task-'.$tasksEntries[$i]["ID"];
			}
		}

		return $tasksEntries;
	}

	/**
	 * Deletes calendar event
	 *
	 * @param integer $eventId - id of the event.
	 * @param array $params contains additional information.
	 * @return true or false - result of the operation
	 */
	public static function deleteEvent($eventId, $params = [])
	{
		return \CCalendar::DeleteEvent($eventId);
	}
}