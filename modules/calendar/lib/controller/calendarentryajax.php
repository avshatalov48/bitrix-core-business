<?
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Internals;
use Bitrix\Calendar\Sync\Google;
use Bitrix\Calendar\UserSettings;
use Bitrix\Calendar\Util;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Calendar\Integration\Bitrix24Manager;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/calendar/lib/controller/calendarajax.php');

/**
 * Class CalendarEntryAjax
 */
class CalendarEntryAjax extends \Bitrix\Main\Engine\Controller
{
	public function getNearestEventsAction()
	{
		$request = $this->getRequest();
		$calendarType = $request->getPost('type');
		$ownerId = (int)$request->getPost('ownerId');
		$futureDaysAmount = (int)$request->getPost('futureDaysAmount');
		$maxEntryAmount = (int)$request->getPost('maxEntryAmount');

		$entries = \CCalendar::getNearestEventsList([
				'bCurUserList' => true,
				'fromLimit' => \CCalendar::Date(time(), false),
				'toLimit' => \CCalendar::Date(time() + \CCalendar::DAY_LENGTH * $futureDaysAmount, false),
				'type' => $calendarType,
				'maxAmount' => $maxEntryAmount
			]
		);

		return [
			'entries' => $entries,
		];
	}

	public function loadEntriesAction()
	{
		$request = $this->getRequest();
		$finish = false;
		$monthFrom = (int)$request->getPost('month_from');
		$yearFrom = (int)$request->getPost('year_from');
		$monthTo = (int)$request->getPost('month_to');
		$yearTo = (int)$request->getPost('year_to');
		$loadLimit = (int)$request->getPost('loadLimit');
		$ownerId = (int)$request->getPost('ownerId');
		$calendarType = $request->getPost('type');

		$loadNext = $request->getPost('loadNext') === 'Y';
		$loadPrevious = $request->getPost('loadPrevious') === 'Y';

		$parseRecursion = true;
		$activeSectionIds = is_array($request->getPost('active_sect'))
			? $request->getPost('active_sect')
			: [];
		$additionalSectionIds = is_array($request->getPost('sup_sect'))
			? $request->getPost('sup_sect')
			: [];

		$params = [
			'type' => $calendarType,
			'section' => [],
			'fromLimit' => $monthFrom ? \CCalendar::Date(mktime(0, 0, 0, $monthFrom, 1, $yearFrom), false) : false,
			'toLimit' => $monthTo ? \CCalendar::Date(mktime(0, 0, 0, $monthTo, 1, $yearTo), false) : false,
		];

		$connections = false;
		if ($loadNext|| $loadPrevious)
		{
			$params['limit'] = $loadLimit;
			$parseRecursion = false;
		}

		if ($request->getPost('cal_dav_data_sync') === 'Y' && \CCalendar::IsCalDAVEnabled())
		{
			$isGoogleApiEnabled = \CCalendar::isGoogleApiEnabled();
			$config = [];
			\CCalendar::InitExternalCalendarsSyncParams($config);
			\CDavGroupdavClientCalendar::DataSync("user", $ownerId);

			if ($isGoogleApiEnabled)
			{
				$dbConnections = \CDavConnection::GetList(
					[
						"SYNCHRONIZED" => "ASC"
					],
					[
						'ACCOUNT_TYPE' => Google\Helper::GOOGLE_ACCOUNT_TYPE_API,
						'ENTITY_TYPE' => 'user',
						'ENTITY_ID' => $ownerId
					],
					false,
					false,
					['ID', 'ENTITY_TYPE', 'ENTITY_ID', 'ACCOUNT_TYPE', 'SERVER_SCHEME', 'SERVER_HOST', 'SERVER_PORT', 'SERVER_USERNAME', 'SERVER_PASSWORD', 'SERVER_PATH', 'SYNCHRONIZED', 'SYNC_TOKEN']
				);

				if ($connection = $dbConnections->Fetch())
				{
					$connection['forceSync'] = true;
					\CCalendarSync::dataSync($connection);
				}

				\CCalendar::InitExternalCalendarsSyncParams($config);
			}
			if ($config['connections'])
			{
				$connections = $config['connections'];
			}
		}

		$fetchTasks = false;
		$sectionIdList = [];
		$entries = [];

		foreach(array_unique(array_merge($activeSectionIds, $additionalSectionIds)) as $sectId)
		{
			if ($sectId == 'tasks')
			{
				$fetchTasks = true;
			}
			elseif ((int)$sectId > 0)
			{
				$sectionIdList[] = (int)$sectId;
			}
		}

		if (count($sectionIdList) > 0)
		{
			$sect = \CCalendarSect::GetList([
				'arFilter' => [
					'ID'=> $sectionIdList,
					'ACTIVE' => 'Y'
				],
				'checkPermissions' => true
			]);
			foreach($sect as $section)
			{
				$params['section'][] = (int)$section['ID'];
			}
		}

		if (count($params['section']) > 0)
		{
			$arFilter = [
				'SECTION' => $params['section']
			];

			if (isset($params['fromLimit']))
			{
				$arFilter["FROM_LIMIT"] = $params['fromLimit'];
			}
			if (isset($params['toLimit']))
			{
				$arFilter["TO_LIMIT"] = $params['toLimit'];
			}

			if ($params['type'] === 'user')
			{
				$fetchMeetings = in_array(\CCalendar::GetMeetingSection($arFilter['OWNER_ID']), $params['section']);
			}
			else
			{
				$fetchMeetings = in_array(\CCalendar::GetCurUserMeetingSection(), $params['section']);
			}

			$res = \CCalendarEvent::GetList(
				[
					'arFilter' => $arFilter,
					'parseRecursion' => $parseRecursion,
					'fetchAttendees' => true,
					'userId' => \CCalendar::GetCurUserId(),
					'fetchMeetings' => $fetchMeetings,
					'setDefaultLimit' => false,
					'limit' => $params['limit']
				]
			);

			$finish = $params['limit'] && count($res) < $params['limit'];
			$entries = [];
			$lastDateTimestamp = 0;
			$firstDateTimestamp = INF;
			foreach($res as $entry)
			{
				if(in_array($entry['SECT_ID'], $params['section']))
				{
					$entries[] = $entry;

					if ($loadNext && !\CCalendarEvent::CheckRecurcion($entry) && $entry['DATE_TO_TS_UTC'] > $lastDateTimestamp)
					{
						$lastDateTimestamp = $entry['DATE_TO_TS_UTC'];
					}
					elseif($loadPrevious && !\CCalendarEvent::CheckRecurcion($entry) && $entry['DATE_FROM_TS_UTC'] < $lastDateTimestamp)
					{
						$firstDateTimestamp = $entry['DATE_FROM_TS_UTC'];
					}
				}
			}

			if ($loadNext)
			{
				$params['toLimit'] = \CCalendar::Date($lastDateTimestamp);
			}
			if ($loadPrevious)
			{
				$params['fromLimit'] = \CCalendar::Date($firstDateTimestamp);
			}

			if(!$parseRecursion)
			{
				foreach($entries as $entry)
				{
					if (in_array($entry['SECT_ID'], $params['section']))
					{
						if (\CCalendarEvent::CheckRecurcion($entry))
						{
							\CCalendarEvent::ParseRecursion($entries, $entry, [
								'fromLimit' => $params['fromLimit'],
								'toLimit' => $params['toLimit'],
								'instanceCount' => false,
								'preciseLimits' => true
							]);
						}
					}
				}
			}
		}

		//  **** GET TASKS ****
		if ($fetchTasks)
		{
			$tasksEntries = \CCalendar::getTaskList(
				[
					'type' => $calendarType,
					'ownerId' => $ownerId
				]
			);

			if(count($tasksEntries) > 0)
			{
				$entries = array_merge($entries, $tasksEntries);
			}
		}

		$response = [
			'entries' => $entries,
			'userIndex' => \CCalendarEvent::getUserIndex(),
		];
		if (is_array($connections))
		{
			$response['connections'] = $connections;
		}
		if ($params['limit'])
		{
			$response['finish'] = $finish;
		}

		return $response;
	}

	public function moveEventAction()
	{
		$request = $this->getRequest();
		$userId = \CCalendar::getCurUserId();
		$id = (int)$request->getPost('id');
		$sectionId = (int)$request->getPost('section');

		if (!$id && !\CCalendarSect::CanDo('calendar_add', $sectionId, $userId)
			||
			$id && !\CCalendarSect::CanDo('calendar_edit', $sectionId, $userId))
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'move_entry_access_denied'));
		}

		$requestUid = (int)$request->getPost('requestUid');
		$reload = $request->getPost('recursive') === 'Y';
		$sendInvitesToDeclined = $request->getPost('sendInvitesAgain') === 'Y';
		$skipTime = $request->getPost('skip_time') === 'Y';
		$dateFrom = $request->getPost('date_from');
		$dateTo = $request->getPost('date_to');
		$timezone = $request->getPost('timezone');
		$attendees = $request->getPost('attendees');
		$location = trim((string) $request->getPost('location'));
		$isPlannerFeatureEnabled = Bitrix24Manager::isPlannerFeatureEnabled();

		$locationBusyWarning = false;
		$busyWarning = false;

		if(empty($this->getErrors()))
		{
			$arFields = [
				"ID" => $id,
				"DATE_FROM" => \CCalendar::Date(\CCalendar::Timestamp($dateFrom), !$skipTime),
				"SKIP_TIME" => $skipTime
			];

			if (!empty($dateTo))
			{
				$arFields["DATE_TO"] = \CCalendar::Date(\CCalendar::Timestamp($dateTo), !$skipTime);
			}

			if (!$skipTime && $request->getPost('set_timezone') === 'Y' && $timezone)
			{
				$arFields["TZ_FROM"] = $timezone;
				$arFields["TZ_TO"] = $timezone;
			}



			if (
				$isPlannerFeatureEnabled
				&& !empty($location)
				&& !\CCalendarLocation::checkAccessibility($location, ['fields' => $arFields])
			)
			{
				$locationBusyWarning = true;
				$reload = true;
			}

			if (
				$isPlannerFeatureEnabled
				&& $request->getPost('is_meeting') === 'Y'
				&& is_array($attendees)
			)
			{
				$usersToCheck = [];
				foreach ($attendees as $attId)
				{
					if ($attId !== \CCalendar::GetUserId())
					{
						$userSettings = UserSettings::get(intval($attId));
						if ($userSettings && $userSettings['denyBusyInvitation'])
						{
							$usersToCheck[] = intval($attId);
						}
					}
				}

				if (count($usersToCheck) > 0)
				{
					$fromTs = \CCalendar::Timestamp($arFields["DATE_FROM"]);
					$toTs = \CCalendar::Timestamp($arFields["DATE_TO"]);
					$fromTs = $fromTs - \CCalendar::GetTimezoneOffset($timezone, $fromTs);
					$toTs = $toTs - \CCalendar::GetTimezoneOffset($timezone, $toTs);
					$dateFromUtc = \CCalendar::Date($fromTs);
					$dateToUtc = \CCalendar::Date($toTs);

					$accessibility = \CCalendar::GetAccessibilityForUsers(
						[
							'users' => $usersToCheck,
							'from' => $dateFromUtc, // date or datetime in UTC
							'to' => $dateToUtc, // date or datetime in UTC
							'curEventId' => $id,
							'getFromHR' => true,
							'checkPermissions' => false
						]
					);

					foreach ($accessibility as $userId => $entries)
					{
						foreach ($entries as $entry)
						{
							$entFromTs = \CCalendar::Timestamp($entry["DATE_FROM"]);
							$entToTs = \CCalendar::Timestamp($entry["DATE_TO"]);
							$entFromTs -= \CCalendar::GetTimezoneOffset($entry['TZ_FROM'], $entFromTs);
							$entToTs -= \CCalendar::GetTimezoneOffset($entry['TZ_TO'], $entToTs);

							if ($entFromTs < $toTs && $entToTs > $fromTs)
							{
								$busyWarning = true;
								$reload = true;
								break;
							}
						}

						if ($busyWarning)
						{
							break;
						}
					}
				}
			}

			if (!$busyWarning && !$locationBusyWarning)
			{
				if ($request->getPost('recursive') === 'Y')
				{
					\CCalendar::SaveEventEx(
						[
							'arFields' => $arFields,
							'silentErrorMode' => false,
							'recursionEditMode' => 'this',
							'currentEventDateFrom' => \CCalendar::Date(
								\CCalendar::Timestamp($request->getPost('current_date_from')),
								false
							),
							'sendInvitesToDeclined' => $sendInvitesToDeclined,
							'requestUid' => $requestUid
						]
					);
				}
				else
				{
					$id = \CCalendar::SaveEvent(
						[
							'arFields' => $arFields,
							'silentErrorMode' => false,
							'sendInvitesToDeclined' => $sendInvitesToDeclined,
							'requestUid' => $requestUid
						]
					);
				}
			}
		}

		return [
			'id' => $id,
			'reload' => $reload,
			'busy_warning' => $busyWarning,
			'location_busy_warning' => $locationBusyWarning
		];
	}

	public function editEntryAction()
	{
		$response = [];
		$request = $this->getRequest();

		$id = (int)$request['id'];
		$sectionId = (int)$request['section'];
		$requestUid = (int)$request['requestUid'];
		$userId = \CCalendar::getCurUserId();
		$isPlannerFeatureEnabled = Bitrix24Manager::isPlannerFeatureEnabled();
		$checkCurrentUsersAccessibility = !$id || $request->getPost('checkCurrentUsersAccessibility') !== 'N';

		if (!$id && !\CCalendarSect::CanDo('calendar_add', $sectionId, $userId)
			||
			$id && !\CCalendarSect::CanDo('calendar_edit', $sectionId, $userId))
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'edit_entry_access_denied'));
		}

		if(empty($this->getErrors()))
		{
			$sectionList = Internals\SectionTable::getList(
				array(
					"filter" => array(
						"=ACTIVE" => 'Y',
						"=ID" => $sectionId
					),
					"select" => array("ID", "CAL_TYPE", "OWNER_ID", "NAME")
				)
			);

			if (!($section = $sectionList->fetch()))
			{
				$this->addError(new Error(Loc::getMessage('EC_SECTION_NOT_FOUND'), 'edit_entry_section_not_found'));
			}

			if(empty($this->getErrors()))
			{
				// Default name for events
				$name = trim($request['name']);
				if(empty($name))
				{
					$name = Loc::getMessage('EC_DEFAULT_EVENT_NAME');
				}
				$reminderList = \CCalendarReminder::prepareReminder($request['reminder']);

				$rrule = $request['EVENT_RRULE'];
				if (isset($rrule) && !isset($rrule['INTERVAL']) && $rrule['FREQ'] !== 'NONE')
				{
					$rrule['INTERVAL'] = 1;
				}
				if($request['rrule_endson'] === 'never')
				{
					unset($rrule['COUNT']);
					unset($rrule['UNTIL']);
				}
				elseif($request['rrule_endson'] === 'count')
				{
					if(intval($rrule['COUNT']) <= 0)
						$rrule['COUNT'] = 10;
					unset($rrule['UNTIL']);
				}
				elseif($request['rrule_endson'] === 'until')
				{
					unset($rrule['COUNT']);
				}

				// Date & Time
				$dateFrom = $request['date_from'];
				$dateTo = $request['date_to'];
				$skipTime = isset($request['skip_time']) && $request['skip_time'] == 'Y';
				if(!$skipTime)
				{
					$dateFrom .= ' '.$request['time_from'];
					$dateTo .= ' '.$request['time_to'];
				}
				$dateFrom = trim($dateFrom);
				$dateTo = trim($dateTo);

				// Timezone
				$tzFrom = $request['tz_from'];
				$tzTo = $request['tz_to'];
				if(!$tzFrom && isset($request['default_tz']))
				{
					$tzFrom = $request['default_tz'];
				}
				if(!$tzTo && isset($request['default_tz']))
				{
					$tzTo = $request['default_tz'];
				}

				if(isset($request['default_tz']) && $request['default_tz'] != '')
				{
					\CCalendar::SaveUserTimezoneName(\CCalendar::GetUserId(), $request['default_tz']);
				}

				$entryFields = [
					"ID" => $id,
					"DATE_FROM" => $dateFrom,
					"DATE_TO" => $dateTo,
					"SKIP_TIME" => $skipTime,
					'TZ_FROM' => $tzFrom,
					'TZ_TO' => $tzTo,
					'NAME' => $name,
					'DESCRIPTION' => trim($request['desc']),
					'SECTIONS' => [$sectionId],
					'COLOR' => $request['color'],
					'ACCESSIBILITY' => $request['accessibility'],
					'IMPORTANCE' => isset($request['importance']) ? $request['importance'] : 'normal',
					'PRIVATE_EVENT' => $request['private_event'] === 'Y',
					'RRULE' => $rrule,
					'LOCATION' => $request['location'],
					"REMIND" => $reminderList,
					"IS_MEETING" => !!$request['is_meeting'],
					"SECTION_CAL_TYPE" => $section['CAL_TYPE'],
					"SECTION_OWNER_ID" => $section['OWNER_ID']
				];

				$codes = [];
				if (isset($request['attendeesEntityList']) && is_array($request['attendeesEntityList']))
				{
					$codes = Util::convertEntitiesToCodes($request['attendeesEntityList']);
				}

				$accessCodes = \CCalendarEvent::handleAccessCodes($codes, ['userId' => $userId]);

				$entryFields['IS_MEETING'] = $accessCodes != ['U'.$userId];

				if($entryFields['IS_MEETING'])
				{
					$entryFields['ATTENDEES_CODES'] = $accessCodes;
					$entryFields['ATTENDEES'] = \CCalendar::GetDestinationUsers($accessCodes);
					$response['reload'] = true;
				}

				if($request['exclude_users'] && count($entryFields['ATTENDEES']) > 0)
				{
					$excludeUsers = explode(",", $request['exclude_users']);
					$entryFields['ATTENDEES_CODES'] = [];

					if(count($excludeUsers) > 0)
					{
						$entryFields['ATTENDEES'] = array_diff($entryFields['ATTENDEES'], $excludeUsers);
						foreach($entryFields['ATTENDEES'] as $attendee)
						{
							$entryFields['ATTENDEES_CODES'][] = 'U'.intval($attendee);
						}
					}
				}
				else
				{
					$excludeUsers = [];
				}

				if(\CCalendar::GetType() == 'user' && \CCalendar::GetOwnerId() != \CCalendar::GetUserId())
				{
					$entryFields['MEETING_HOST'] = \CCalendar::GetOwnerId();
				}
				else
				{
					$entryFields['MEETING_HOST'] = \CCalendar::GetUserId();
				}

				$entryFields['MEETING'] = array(
					'HOST_NAME' => \CCalendar::GetUserName($entryFields['MEETING_HOST']),
					'NOTIFY' => $request['meeting_notify'] === 'Y',
					'REINVITE' => $request['meeting_reinvite'] === 'Y',
					'ALLOW_INVITE' => $request['allow_invite'] === 'Y',
					'MEETING_CREATOR' => \CCalendar::GetUserId(),
					'HIDE_GUESTS' => $request['hide_guests'] === 'Y'
				);

				if (!\CCalendarLocation::checkAccessibility($entryFields['LOCATION'], ['fields' => $entryFields]))
				{
					$this->addError(new Error(Loc::getMessage('EC_LOCATION_BUSY'), 'edit_entry_location_busy'));
				}

				if($entryFields['IS_MEETING'] && $isPlannerFeatureEnabled)
				{
					$usersToCheck = [];
					if ($checkCurrentUsersAccessibility)
					{
						foreach ($entryFields['ATTENDEES'] as $attId)
						{
							$attId = (int)$attId;
							if ($attId !== \CCalendar::GetUserId())
							{
								$userSettings = UserSettings::get($attId);
								if($userSettings && $userSettings['denyBusyInvitation'])
								{
									$usersToCheck[] = $attId;
								}
							}
						}
					}
					else
					{
						if (is_array($request['newAttendeesList']))
						{
							$newAttendeesList = array_diff($request['newAttendeesList'], $excludeUsers);
							foreach ($newAttendeesList as $attId)
							{
								$attId = (int)$attId;
								if ($attId !== \CCalendar::GetUserId())
								{
									$userSettings = UserSettings::get($attId);
									if($userSettings && $userSettings['denyBusyInvitation'])
									{
										$usersToCheck[] = $attId;
									}
								}
							}
						}
					}

					if (count($usersToCheck) > 0)
					{
						$fromTs = \CCalendar::Timestamp($dateFrom);
						$toTs = \CCalendar::Timestamp($dateTo);
						$fromTs = $fromTs - \CCalendar::GetTimezoneOffset($tzFrom, $fromTs);
						$toTs = $toTs - \CCalendar::GetTimezoneOffset($tzTo, $toTs);

						$accessibility = \CCalendar::GetAccessibilityForUsers([
								'users' => $usersToCheck,
								'from' => \CCalendar::Date($fromTs, false), // date or datetime in UTC
								'to' => \CCalendar::Date($toTs, false), // date or datetime in UTC
								'curEventId' => $id,
								'getFromHR' => true,
								'checkPermissions' => false
							]
						);

						$busyUsersList = [];
						foreach($accessibility as $accUserId => $entries)
						{
							foreach($entries as $entry)
							{
								$entFromTs = \CCalendar::Timestamp($entry["DATE_FROM"]);
								$entToTs = \CCalendar::Timestamp($entry["DATE_TO"]);

								if ($entry["DT_SKIP_TIME"] === 'Y')
								{
									$entToTs += \CCalendar::GetDayLen();
								}

								$entFromTs -= \CCalendar::GetTimezoneOffset($entry['TZ_FROM'], $entFromTs);
								$entToTs -= \CCalendar::GetTimezoneOffset($entry['TZ_TO'], $entToTs);

								if ($entFromTs < $toTs && $entToTs > $fromTs)
								{
									$busyUsersList[] = $accUserId;
									$this->addError(new Error(Loc::getMessage('EC_USER_BUSY', ["#USER#" => \CCalendar::GetUserName($accUserId)]), 'edit_entry_user_busy'));
									break;
								}
							}
						}

						if (count($busyUsersList) > 0)
						{
							$response['busyUsersList'] = \CCalendarEvent::getUsersDetails($busyUsersList);
						}
					}
				}

				// Userfields for event
				$arUFFields = [];
				foreach($request as $field => $value)
				{
					if(mb_substr($field, 0, 3) == "UF_")
					{
						$arUFFields[$field] = $value;
					}
				}

				if(empty($this->getErrors()))
				{
					$newId = \CCalendar::SaveEvent(
						[
						   'arFields' => $entryFields,
						   'UF' => $arUFFields,
						   'silentErrorMode' => false,
						   'recursionEditMode' => $request['rec_edit_mode'],
						   'currentEventDateFrom' => \CCalendar::Date(\CCalendar::Timestamp($request['current_date_from']), false),
						   'sendInvitesToDeclined' => $request['sendInvitesAgain'] === 'Y',
						   'requestUid' => $requestUid
				 	  	]
					);

					$errors = \CCalendar::GetErrors();
					$eventList = [];
					$eventIdList = [$newId];

					if($newId && !count($errors))
					{
						$response['entryId'] = $newId;

						$filter = [
							"ID" => $newId,
							"FROM_LIMIT" => \CCalendar::Date(
								\CCalendar::Timestamp($entryFields["DATE_FROM"]) -
								\CCalendar::DAY_LENGTH * 10, false),
							"TO_LIMIT" => \CCalendar::Date(
								\CCalendar::Timestamp($entryFields["DATE_TO"]) +
								\CCalendar::DAY_LENGTH * 90, false)
						];

						$eventList = \CCalendarEvent::GetList(
							[
								'arFilter' => $filter,
								'parseRecursion' => true,
								'fetchAttendees' => true,
								'userId' => \CCalendar::GetUserId(),
							]
						);

						if($entryFields['IS_MEETING'])
						{
							\Bitrix\Main\FinderDestTable::merge(
								[
									"CONTEXT" => Util::getUserSelectorContext(),
									"CODE" => \Bitrix\Main\FinderDestTable::convertRights(
										$accessCodes,
										['U'.\CCalendar::GetUserId()]
									)
								]
							);
						}

						if(in_array($_REQUEST['rec_edit_mode'], ['this', 'next']))
						{
							unset($filter['ID']);
							$filter['RECURRENCE_ID'] = ($eventList && $eventList[0] && $eventList[0]['RECURRENCE_ID']) ? $eventList[0]['RECURRENCE_ID'] : $newId;

							$resRelatedEvents = \CCalendarEvent::GetList(
								[
									'arFilter' => $filter,
									'parseRecursion' => true,
									'fetchAttendees' => true,
									'userId' => \CCalendar::GetUserId()
								]
							);

							foreach($resRelatedEvents as $ev)
							{
								$eventIdList[] = $ev['ID'];
							}
							$eventList = array_merge($eventList, $resRelatedEvents);
						}
						elseif($id && $eventList && $eventList[0] && \CCalendarEvent::CheckRecurcion($eventList[0]))
						{
							$recId = $eventList[0]['RECURRENCE_ID']
								? $eventList[0]['RECURRENCE_ID']
								: $eventList[0]['ID'];

							if($eventList[0]['RECURRENCE_ID'] && $eventList[0]['RECURRENCE_ID'] !== $eventList[0]['ID'])
							{
								unset($filter['RECURRENCE_ID']);
								$filter['ID'] = $eventList[0]['RECURRENCE_ID'];
								$resRelatedEvents = \CCalendarEvent::GetList(
									[
										'arFilter' => $filter,
										'parseRecursion' => true,
										'fetchAttendees' => true,
										'userId' => \CCalendar::GetUserId(),
									]
								);
								$eventIdList[] = $eventList[0]['RECURRENCE_ID'];
								$eventList = array_merge($eventList, $resRelatedEvents);
							}
							$name = trim($request['name']);

							if($recId)
							{
								unset($filter['ID']);
								$filter['RECURRENCE_ID'] = $recId;
								$resRelatedEvents = \CCalendarEvent::GetList(
									[
										'arFilter' => $filter,
										'parseRecursion' => true,
										'fetchAttendees' => true,
										'userId' => \CCalendar::GetUserId(),
									]
								);

								foreach($resRelatedEvents as $ev)
								{
									$eventIdList[] = $ev['ID'];
								}
								$eventList = array_merge($eventList, $resRelatedEvents);
							}
						}
					}
					else
					{
						if (is_iterable($errors))
						{
							foreach ($errors as $error)
							{
								if (is_string($error))
									$this->addError(new Error($error, 'send_invite_failed'));
							}
						}
					}

					$pathToCalendar = \CCalendar::GetPathForCalendarEx($userId);
					foreach($eventList as $ind => $event)
					{
						$eventList[$ind]['~URL'] = \CHTTP::urlAddParams($pathToCalendar, ['EVENT_ID' => $event['ID']]);
					}

					$response['eventList'] = $eventList;
					$response['eventIdList'] = $eventIdList;
					$response['displayMobileBanner'] = Util::isShowDailyBanner();
					$response['countEventWithEmailGuestAmount'] = Bitrix24Manager::getCountEventWithEmailGuestAmount();

					$userSettings = UserSettings::get($userId);
					$userSettings['defaultReminders'][$skipTime ? 'fullDay' : 'withTime'] = $reminderList;
					UserSettings::set($userSettings, $userId);
				}
			}
		}

		return $response;
	}
}
