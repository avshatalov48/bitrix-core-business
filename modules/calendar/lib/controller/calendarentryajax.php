<?php
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Core\Managers\Accessibility;
use Bitrix\Calendar\Integration\Tasks\TaskQueryParameter;
use Bitrix\Calendar\Rooms;
use Bitrix\Calendar\Internals;
use Bitrix\Calendar\Ui\CalendarFilter;
use Bitrix\Calendar\UserSettings;
use Bitrix\Calendar\Util;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Intranet;
use Bitrix\Main\Loader;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/calendar/lib/controller/calendarajax.php');

/**
 * Class CalendarEntryAjax
 */
class CalendarEntryAjax extends \Bitrix\Main\Engine\Controller
{
	protected const DIRECTION_PREVIOUS = 'previous';
	protected const DIRECTION_NEXT = 'next';
	protected const DIRECTION_BOTH = 'both';

	public function configureActions(): array
	{
		return [];
	}

	public function getNearestEventsAction()
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
		}

		$request = $this->getRequest();
		$calendarType = $request->getPost('type');
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
		$monthFrom = (int)$request->getPost('month_from');
		$yearFrom = (int)$request->getPost('year_from');
		$monthTo = (int)$request->getPost('month_to');
		$yearTo = (int)$request->getPost('year_to');
		$ownerId = (int)$request->getPost('ownerId');
		$calendarType = $request->getPost('type');

		$direction = $request->getPost('direction');
		if (!in_array($direction, [self::DIRECTION_PREVIOUS, self::DIRECTION_NEXT, self::DIRECTION_BOTH], true))
		{
			$direction = null;
		}

		$activeSectionIds = is_array($request->getPost('active_sect'))
			? $request->getPost('active_sect')
			: [];
		$additionalSectionIds = is_array($request->getPost('sup_sect'))
			? $request->getPost('sup_sect')
			: [];

		$sections = [];
		$limits = \CCalendarEvent::getLimitDates($yearFrom, $monthFrom, $yearTo, $monthTo);

		$connections = false;
		$fetchTasks = false;
		$sectionIdList = [];

		foreach(array_unique(array_merge($activeSectionIds, $additionalSectionIds)) as $sectId)
		{
			if ($sectId === 'tasks')
			{
				$fetchTasks = true;
			}
			elseif ((int)$sectId > 0)
			{
				$sectionIdList[] = (int)$sectId;
			}
		}

		if (!empty($sectionIdList))
		{
			$sect = \CCalendarSect::GetList([
				'arFilter' => [
					'ID' => $sectionIdList,
					'ACTIVE' => 'Y'
				],
				'checkPermissions' => true
			]);
			foreach($sect as $section)
			{
				$sections[] = (int)$section['ID'];
			}
		}

		$isBoundaryOfPastReached = false;
		$isBoundaryOfFutureReached = false;
		$entries = [];
		if (!empty($sections))
		{
			$entries = $this->getEntries($sections, $limits);

			if (
				$direction === self::DIRECTION_BOTH
				&& count($this->getShownEntries($entries)) < 5
			)
			{
				$isBoundaryOfPastReached = true;
				$isBoundaryOfFutureReached = true;
				//Load all events
				$limits = [
					'from' => false,
					'to' => false,
				];
				$entries = $this->getEntries($sections, $limits);

				if (!empty($entries))
				{
					$earliestEvent = $this->getEarliestEvent($entries);
					$timestamp = strtotime($earliestEvent['DATE_FROM']);
					if($timestamp < strtotime("01.$monthFrom.$yearFrom"))
					{
						$yearFrom = (int)date('Y', $timestamp);
						$monthFrom = (int)date('m', $timestamp);
					}

					$latestEvent = $this->getLatestEvent($entries);
					$timestamp = strtotime($latestEvent['DATE_FROM']);
					if($timestamp > strtotime("01.$monthTo.$yearTo"))
					{
						$yearTo = (int)date('Y', $timestamp);
						$monthTo = (int)date('m', $timestamp);
						[$yearTo, $monthTo] = $this->getValidYearAndMonth($yearTo, $monthTo + 1);
					}
				}
			}

			if (
				($direction === self::DIRECTION_PREVIOUS)
				&& !$this->hasArrayEntriesInMonth($entries, $yearFrom, $monthFrom)
			)
			{
				//Load one month further
				[$yearFrom, $monthFrom] = $this->getValidYearAndMonth($yearFrom, $monthFrom - 1);
				$entries = $this->getEntries($sections, \CCalendarEvent::getLimitDates($yearFrom, $monthFrom, $yearTo, $monthTo));

				if (!$this->hasArrayEntriesInMonth($entries, $yearFrom, $monthFrom))
				{
					//Load half year further
					[$yearFrom, $monthFrom] = $this->getValidYearAndMonth($yearFrom, $monthFrom - 5);
					$limits = \CCalendarEvent::getLimitDates($yearFrom, $monthFrom, $yearTo, $monthTo);
					$entries = $this->getEntries($sections, $limits);

					if (!$this->hasArrayEntriesInRange($entries, $yearFrom, $monthFrom, (int)$request->getPost('year_from'), (int)$request->getPost('month_from')))
					{
						$isBoundaryOfPastReached = true;
						//Load all events
						$limits['from'] = false;
						$entries = $this->getEntries($sections, $limits);

						if (!empty($entries))
						{
							$earliestEvent = $this->getEarliestEvent($entries);
							$timestamp = strtotime($earliestEvent['DATE_FROM']);
							$yearFrom = (int)date('Y', $timestamp);
							$monthFrom = (int)date('m', $timestamp);
						}
					}
				}
			}

			if (
				($direction === self::DIRECTION_NEXT)
				&& !$this->hasArrayEntriesInMonth($entries, $yearTo, $monthTo - 1)
			)
			{
				//Load one month further
				[$yearTo, $monthTo] = $this->getValidYearAndMonth($yearTo, $monthTo + 1);
				$entries = $this->getEntries($sections, \CCalendarEvent::getLimitDates($yearFrom, $monthFrom, $yearTo, $monthTo));

				if (!$this->hasArrayEntriesInMonth($entries, $yearTo, $monthTo - 1))
				{
					//Load half year further
					[$yearTo, $monthTo] = $this->getValidYearAndMonth($yearTo, $monthTo + 5);
					$limits = \CCalendarEvent::getLimitDates($yearFrom, $monthFrom, $yearTo, $monthTo);
					$entries = $this->getEntries($sections, $limits);

					if (!$this->hasArrayEntriesInRange($entries, (int)$request->getPost('year_to'), (int)$request->getPost('month_to') - 1, $yearTo, $monthTo - 1))
					{
						$isBoundaryOfFutureReached = true;
						//Load all events
						$limits['to'] = false;
						$entries = $this->getEntries($sections, $limits);

						if (!empty($entries))
						{
							$latestEvent = $this->getLatestEvent($entries);
							$timestamp = strtotime($latestEvent['DATE_FROM']);
							$yearTo = (int)date('Y', $timestamp);
							$monthTo = (int)date('m', $timestamp);
							[$yearTo, $monthTo] = $this->getValidYearAndMonth($yearTo, $monthTo + 1);
						}
					}
				}
			}
		}

		$userId = \CCalendar::GetUserId();
		$accessController = new EventAccessController($userId);
		foreach ($entries as $key => $entry)
		{
			$eventModel = EventModel::createFromArray($entry);
			$canEditEventInParentSection = $accessController->check(ActionDictionary::ACTION_EVENT_EDIT, $eventModel);
			$canEditEventInCurrentSection = $accessController->check(ActionDictionary::ACTION_EVENT_EDIT, $eventModel, [
				'checkCurrentEvent' => 'Y',
			]);
			$entries[$key]['permissions'] = [
				'edit' => $canEditEventInParentSection && $canEditEventInCurrentSection,
				'edit_attendees' => false, //$accessController->check(ActionDictionary::ACTION_EVENT_EDIT_ATTENDEES, $eventModel),
				'edit_location' => false, //$accessController->check(ActionDictionary::ACTION_EVENT_EDIT_LOCATION, $eventModel),
			];
		}

		//  **** GET TASKS ****
		if ($fetchTasks)
		{
			$tasksEntries = \CCalendar::getTaskList(
				(new TaskQueryParameter($this->getCurrentUser()->getId()))
					->setType($calendarType)
					->setOwnerId($ownerId)
			);

			if (!empty($tasksEntries))
			{
				$entries = array_merge($entries, $tasksEntries);
			}
		}

		$response = [
			'entries' => $entries,
			'userIndex' => \CCalendarEvent::getUserIndex(),
			'isBoundaryOfPastReached' => $isBoundaryOfPastReached,
			'isBoundaryOfFutureReached' => $isBoundaryOfFutureReached,
		];
		if (is_array($connections))
		{
			$response['connections'] = $connections;
		}

		if (
			(int)$request->getPost('month_from') !== $monthFrom
			|| (int)$request->getPost('year_from') !== $yearFrom
		)
		{
			$response['newYearFrom'] = $yearFrom;
			$response['newMonthFrom'] = $monthFrom;
		}

		if (
			(int)$request->getPost('month_to') !== $monthTo
			|| (int)$request->getPost('year_to') !== $yearTo
		)
		{
			$response['newYearTo'] = $yearTo;
			$response['newMonthTo'] = $monthTo;
		}

		return $response;
	}

	protected function getShownEntries(array $entries): array
	{
		return CalendarFilter::filterByShowDeclined($entries);
	}

	protected function getEntries(array $sections, array $limits): array
	{
		return \CCalendarEvent::GetList(
			[
				'arFilter' => [
					'SECTION' => $sections,
					'FROM_LIMIT' => $limits['from'],
					'TO_LIMIT' => $limits['to'],
				],
				'parseRecursion' => true,
				'fetchAttendees' => true,
				'userId' => \CCalendar::GetCurUserId(),
				'setDefaultLimit' => false,
			]
		);
	}

	protected function getValidYearAndMonth(int $year, int $month): array
	{
		if ($month <= 0)
		{
			return [$year - 1, $month + 12];
		}

		if ($month > 12)
		{
			return [$year + 1, $month - 12];
		}

		return [$year, $month];
	}

	protected function hasArrayEntriesInMonth(array $entries, int $yearFrom, int $monthFrom): bool
	{
		return $this->hasArrayEntriesInRange($entries, $yearFrom, $monthFrom, $yearFrom, $monthFrom);
	}

	protected function hasArrayEntriesInRange(array $entries, int $yearFrom, int $monthFrom, int $yearTo, int $monthTo): bool
	{
		$monthsFrom = $yearFrom * 12 + $monthFrom;
		$monthsTo = $yearTo * 12 + $monthTo;
		$settings = UserSettings::get();
		$showDeclined = $settings['showDeclined'];
		foreach ($entries as $entry)
		{
			if (!$showDeclined && $entry['MEETING_STATUS'] === 'N')
			{
				continue;
			}

			$timestamp = strtotime($entry['DATE_FROM']);
			$entryYear = (int)date('Y', $timestamp);
			$entryMonth = (int)date('m', $timestamp);
			$entryMonths = $entryYear * 12 + $entryMonth;

			if ($entryMonths >= $monthsFrom && $entryMonths <= $monthsTo)
			{
				return true;
			}
		}
		return false;
	}

	protected function getEarliestEvent(array $entries): array
	{
		return array_reduce($entries, static function($firstEntry, $entry) {
			if (!$firstEntry)
			{
				return $entry;
			}
			if (strtotime($entry['DATE_FROM']) < strtotime($firstEntry['DATE_FROM']))
			{
				return $entry;
			}
			return $firstEntry;
		});
	}

	protected function getLatestEvent(array $entries): array
	{
		return array_reduce($entries, static function($lastEntry, $entry) {
			if (!$lastEntry)
			{
				return $entry;
			}
			if (strtotime($entry['DATE_FROM']) > strtotime($lastEntry['DATE_FROM']))
			{
				return $entry;
			}
			return $lastEntry;
		});
	}

	public function moveEventAction()
	{
		$request = $this->getRequest();
		$userId = \CCalendar::getCurUserId();
		$id = (int)$request->getPost('id');
		$sectionId = (int)$request->getPost('section');

		if ($id)
		{
			$eventModel = \CCalendarEvent::getEventModelForPermissionCheck($id, [], $userId);
		}
		else
		{
			$section = \CCalendarSect::GetById($sectionId);

			$eventModel =
				EventModel::createNew()
					->setOwnerId((int)($section['OWNER_ID'] ?? 0))
					->setSectionId($sectionId ?? 0)
					->setSectionType($section['TYPE'] ?? '')
			;
		}
		$accessController = new EventAccessController($userId);
		if (
			(!$id && !$accessController->check(ActionDictionary::ACTION_EVENT_ADD, $eventModel))
			|| ($id && !$accessController->check(ActionDictionary::ACTION_EVENT_EDIT, $eventModel))
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'move_entry_access_denied'));
		}

		$sectionList = Internals\SectionTable::getList([
			   'filter' => [
				   '=ACTIVE' => 'Y',
				   '=ID' => $sectionId
			   ],
			   'select' => [
				   'ID',
				   'CAL_TYPE',
				   'OWNER_ID',
				   'NAME'
			   ]
		   ]
		);

		if (!($section = $sectionList->fetch()))
		{
			$this->addError(new Error(Loc::getMessage('EC_SECTION_NOT_FOUND'), 'edit_entry_section_not_found'));
		}

		if (
			$section['CAL_TYPE'] !== 'group'
			&& Loader::includeModule('intranet') && !Intranet\Util::isIntranetUser($userId)
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'edit_entry_extranet_access_denied'));
		}


		if (empty($this->getErrors()))
		{
			$entry = Internals\EventTable::getList(
				[
					"filter" => [
						"=ID" => $id,
						"=DELETED" => 'N',
						"=SECTION_ID" => $sectionId
					],
					"select" => ["ID", "CAL_TYPE"]
				]
			)->fetch();

			if (Loader::includeModule('intranet'))
			{
				if ($entry['CAL_TYPE'] !== 'group' && !Intranet\Util::isIntranetUser($userId))
				{
					$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'edit_entry_extranet_access_denied'));
				}
			}
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

		if (empty($this->getErrors()))
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
				&& !Rooms\AccessibilityManager::checkAccessibility($location, ['fields' => $arFields])
			)
			{
				$locationBusyWarning = true;
				$reload = true;
			}

			if (
				$isPlannerFeatureEnabled
				&& is_array($attendees)
				&& $request->getPost('is_meeting') === 'Y'
			)
			{
				$timezoneName = \CCalendar::GetUserTimezoneName(\CCalendar::GetUserId());
				$timezoneOffset = Util::getTimezoneOffsetUTC($timezoneName);
				$timestampFrom = \CCalendar::TimestampUTC($arFields["DATE_FROM"]) - $timezoneOffset;
				$timestampTo = \CCalendar::TimestampUTC($arFields["DATE_TO"]) - $timezoneOffset;
				if (!empty($this->getBusyUsersIds($attendees, $id, $timestampFrom, $timestampTo)))
				{
					$busyWarning = true;
					$reload = true;
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

		if ($id)
		{
			$eventModel = \CCalendarEvent::getEventModelForPermissionCheck($id, [], $userId);
		}
		else
		{
			$section = \CCalendarSect::GetById($sectionId, false);

			$eventModel =
				EventModel::createNew()
					->setOwnerId((int)($section['OWNER_ID'] ?? 0))
					->setSectionId($sectionId)
					->setSectionType($section['CAL_TYPE'] ?? '')
			;
		}
		$accessController = new EventAccessController($userId);
		if (
			(!$id && !$accessController->check(ActionDictionary::ACTION_EVENT_ADD, $eventModel))
			|| ($id && !$accessController->check(ActionDictionary::ACTION_EVENT_EDIT, $eventModel))
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'edit_entry_access_denied'));
		}

		if (!empty($this->getErrors()))
		{
			return $response;
		}

		$sectionList = Internals\SectionTable::getList([
				'filter' => [
					'=ACTIVE' => 'Y',
					'=ID' => $sectionId
				],
				'select' => [
					'ID',
					'CAL_TYPE',
					'OWNER_ID',
					'NAME'
				]
			]
		);

		if (!($section = $sectionList->fetch()))
		{
			$this->addError(new Error(Loc::getMessage('EC_SECTION_NOT_FOUND'), 'edit_entry_section_not_found'));
		}

		if (
			$section['CAL_TYPE'] !== 'group'
			&& Loader::includeModule('intranet') && !Intranet\Util::isIntranetUser($userId)
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'edit_entry_extranet_access_denied'));
		}

		if (!empty($this->getErrors()))
		{
			return $response;
		}

		// Default name for events
		$name = trim($request['name']);
		if (empty($name))
		{
			$name = Loc::getMessage('EC_DEFAULT_EVENT_NAME_V2');
		}
		$reminderList = \CCalendarReminder::prepareReminder($request['reminder']);

		$rrule = $this->prepareRecurringRule($request['EVENT_RRULE'], $request['rrule_endson']);

		// Date & Time
		$dateFrom = $request['date_from'];
		$dateTo = $request['date_to'];
		$skipTime = isset($request['skip_time']) && $request['skip_time'] === 'Y';
		if (!$skipTime)
		{
			$dateFrom .= ' '.$request['time_from'];
			$dateTo .= ' '.$request['time_to'];
		}
		$dateFrom = trim($dateFrom);
		$dateTo = trim($dateTo);

		if (
			(int)(new \DateTime())->setTimestamp(\CCalendar::Timestamp($dateFrom))->format('Y') > 9999
			|| (int)(new \DateTime())->setTimestamp(\CCalendar::Timestamp($dateTo))->format('Y') > 9999
		)
		{
			$this->addError(new Error(Loc::getMessage('EC_JS_EV_FROM_ERR')));
			return false;
		}

		// Timezone
		$tzFrom = $request['tz_from'];
		$tzTo = $request['tz_to'];
		if (!$tzFrom && isset($request['default_tz']))
		{
			$tzFrom = $request['default_tz'];
		}
		if (!$tzTo && isset($request['default_tz']))
		{
			$tzTo = $request['default_tz'];
		}

		if (isset($request['default_tz']) && (string)$request['default_tz'] !== '')
		{
			\CCalendar::SaveUserTimezoneName(\CCalendar::GetUserId(), $request['default_tz']);
		}

		$entryFields = [
			'ID' => $id,
			'DATE_FROM' => $dateFrom,
			'DATE_TO' => $dateTo,
			'SKIP_TIME' => $skipTime,
			'TZ_FROM' => $tzFrom,
			'TZ_TO' => $tzTo,
			'NAME' => $name,
			'DESCRIPTION' => trim($request['desc']),
			'SECTIONS' => [$sectionId],
			'COLOR' => $request['color'],
			'ACCESSIBILITY' => $request['accessibility'],
			'IMPORTANCE' => $request['importance'] ?? 'normal',
			'PRIVATE_EVENT' => $request['private_event'] === 'Y',
			'RRULE' => $rrule,
			'LOCATION' => $request['location'],
			'REMIND' => $reminderList,
			'SECTION_CAL_TYPE' => $section['CAL_TYPE'],
			'SECTION_OWNER_ID' => $section['OWNER_ID']
		];

		$codes = [];
		if (isset($request['attendeesEntityList']) && is_array($request['attendeesEntityList']))
		{
			$codes = Util::convertEntitiesToCodes($request['attendeesEntityList']);
		}

		$accessCodes = \CCalendarEvent::handleAccessCodes($codes, ['userId' => $userId]);

		$entryFields['IS_MEETING'] =
			$accessCodes !== ['U'.$userId] || in_array($entryFields['SECTION_CAL_TYPE'], ['group', 'company_calendar'], true)
		;

		$entryFields['ATTENDEES_CODES'] = $accessCodes;
		$entryFields['ATTENDEES'] = \CCalendar::GetDestinationUsers($accessCodes);
		$response['reload'] = true;

		if ($request['exclude_users'] && !empty($entryFields['ATTENDEES']))
		{
			$excludeUsers = explode(',', $request['exclude_users']);
			$entryFields['ATTENDEES_CODES'] = [];

			if (!empty($excludeUsers))
			{
				$entryFields['ATTENDEES'] = array_diff($entryFields['ATTENDEES'], $excludeUsers);
				foreach($entryFields['ATTENDEES'] as $attendee)
				{
					$entryFields['ATTENDEES_CODES'][] = 'U'. (int)$attendee;
				}
			}
		}
		else
		{
			$excludeUsers = [];
		}

		$meetingHost = (int)$request['meeting_host'];
		$chatId = (int)$request['chat_id'];

		if ($meetingHost)
		{
			$entryFields['MEETING_HOST'] = $meetingHost;
		}
		else
		{
			$entryFields['MEETING_HOST'] = \CCalendar::GetUserId();
		}

		$entryFields['MEETING'] = [
			'HOST_NAME' => \CCalendar::GetUserName($entryFields['MEETING_HOST']),
			'NOTIFY' => $request['meeting_notify'] === 'Y',
			'REINVITE' => $request['meeting_reinvite'] === 'Y',
			'ALLOW_INVITE' => $request['allow_invite'] === 'Y',
			'MEETING_CREATOR' => $entryFields['MEETING_HOST'],
			'HIDE_GUESTS' => $request['hide_guests'] === 'Y'
		];

		$recurrenceEventMode = !empty($request['rec_edit_mode']) ? $request['rec_edit_mode'] : null;
		$currentEventDate = !empty($request['current_date_from'])
			? \CCalendar::Date(\CCalendar::Timestamp($request['current_date_from']), false)
			: null
		;


		if ($chatId)
		{
			$entryFields['MEETING']['CHAT_ID'] = $chatId;
		}

		if (!Rooms\AccessibilityManager::checkAccessibility($entryFields['LOCATION'], ['fields' => $entryFields]))
		{
			$this->addError(new Error(Loc::getMessage('EC_LOCATION_BUSY'), 'edit_entry_location_busy'));
		}

		if ($entryFields['IS_MEETING'] && $isPlannerFeatureEnabled)
		{
			$attendees = [];
			if ($checkCurrentUsersAccessibility)
			{
				$attendees = $entryFields['ATTENDEES'];
			}
			else if (is_array($request['newAttendeesList']))
			{
				$attendees = array_diff($request['newAttendeesList'], $excludeUsers);
			}

			$timezoneFrom = !empty($tzFrom) ? $tzFrom : \CCalendar::GetUserTimezoneName(\CCalendar::GetUserId());
			$timezoneTo = !empty($tzTo) ? $tzTo : $timezoneFrom;
			$timezoneOffsetFrom = Util::getTimezoneOffsetUTC($timezoneFrom);
			$timezoneOffsetTo = Util::getTimezoneOffsetUTC($timezoneTo);
			$timestampFrom = \CCalendar::TimestampUTC($dateFrom) - $timezoneOffsetFrom;
			$timestampTo = \CCalendar::TimestampUTC($dateTo) - $timezoneOffsetTo;
			if ($skipTime)
			{
				$timestampTo += \CCalendar::GetDayLen();
			}
			$busyUsers = $this->getBusyUsersIds($attendees, $id, $timestampFrom, $timestampTo);
			if (!empty($busyUsers))
			{
				$response['busyUsersList'] = \CCalendarEvent::getUsersDetails($busyUsers);
				$busyUserName = current($response['busyUsersList'])['DISPLAY_NAME'];
				$this->addError(new Error(Loc::getMessage('EC_USER_BUSY', ['#USER#' => $busyUserName]), 'edit_entry_user_busy'));
			}
		}

		// Userfields for event
		$arUFFields = [];
		foreach($request as $field => $value)
		{
			if (mb_strpos($field, 'UF_') === 0)
			{
				$arUFFields[$field] = $value;
			}
		}

		if (!empty($this->getErrors()))
		{
			return $response;
		}

		$newId = false;
		try
		{
			$newId = \CCalendar::SaveEvent([
				'arFields' => $entryFields,
				'UF' => $arUFFields,
				'silentErrorMode' => false,
				'recursionEditMode' => $recurrenceEventMode,
				'currentEventDateFrom' => $currentEventDate,
				'sendInvitesToDeclined' => $request['sendInvitesAgain'] === 'Y',
				'requestUid' => $requestUid,
				'checkLocationOccupancy' => ($request['doCheckOccupancy'] ?? 'N') === 'Y',
			]);
		}
		catch (Rooms\OccupancyCheckerException $e)
		{
			$this->addError(new Error(Loc::getMessage('EC_LOCATION_BUSY_RECURRENCE'), 'edit_entry_location_busy_recurrence'));
			$this->addError(new Error($e->getMessage(), 'edit_entry_location_repeat_busy'));
		}

		$errors = \CCalendar::GetErrors();
		$eventList = [];
		$eventIdList = [$newId];

		if ($newId && empty($errors))
		{
			$response['entryId'] = $newId;

			$filter = [
				'ID' => $newId,
				'FROM_LIMIT' => \CCalendar::Date(
					\CCalendar::Timestamp($entryFields['DATE_FROM']) -
					\CCalendar::DAY_LENGTH * 10, false
				),
				'TO_LIMIT' => \CCalendar::Date(
					\CCalendar::Timestamp($entryFields['DATE_TO']) +
					\CCalendar::DAY_LENGTH * 90, false
				)
			];

			$eventList = \CCalendarEvent::GetList(
				[
					'arFilter' => $filter,
					'parseRecursion' => true,
					'fetchAttendees' => true,
					'userId' => \CCalendar::GetUserId(),
				]
			);

			if ($entryFields['IS_MEETING'])
			{
				\Bitrix\Main\FinderDestTable::merge(
					[
						'CONTEXT' => Util::getUserSelectorContext(),
						'CODE' => \Bitrix\Main\FinderDestTable::convertRights(
							$accessCodes,
							['U'. \CCalendar::GetUserId()]
						)
					]
				);
			}

			if (isset($_REQUEST['rec_edit_mode']) && in_array($_REQUEST['rec_edit_mode'], ['this', 'next']))
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
			else if ($id && $eventList && $eventList[0] && \CCalendarEvent::CheckRecurcion($eventList[0]))
			{
				$recId = $eventList[0]['RECURRENCE_ID'] ?? $eventList[0]['ID'];

				if ($eventList[0]['RECURRENCE_ID'] && $eventList[0]['RECURRENCE_ID'] !== $eventList[0]['ID'])
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

				if ($recId)
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
		else if (is_iterable($errors))
		{
			foreach ($errors as $error)
			{
				if (is_string($error))
				{
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
		$response['countEventWithEmailGuestAmount'] = Bitrix24Manager::getCountEventWithEmailGuestAmount();

		$userSettings = UserSettings::get($userId);
		$userSettings['defaultReminders'][$skipTime ? 'fullDay' : 'withTime'] = $reminderList;
		UserSettings::set($userSettings, $userId);

		return $response;
	}

	private function getBusyUsersIds(array $attendees, int $curEventId, int $fromTs, int $toTs): array
	{
		$usersToCheck = $this->getUsersToCheck($attendees);
		if (empty($usersToCheck))
		{
			return [];
		}

		return (new Accessibility())
			->setCheckPermissions(false)
			->setSkipEventId($curEventId)
			->getBusyUsersIds($usersToCheck, $fromTs, $toTs);
	}

	private function getUsersToCheck(array $attendees): array
	{
		$usersToCheck = [];
		foreach ($attendees as $attId)
		{
			if ((int)$attId !== \CCalendar::GetUserId())
			{
				$userSettings = UserSettings::get((int)$attId);
				if ($userSettings && $userSettings['denyBusyInvitation'])
				{
					$usersToCheck[] = (int)$attId;
				}
			}
		}
		return $usersToCheck;
	}

	private function prepareRecurringRule(array $rrule = null, ?string $endMode = 'never'): ?array
	{
		if (empty($rrule) || !is_array($rrule))
		{
			return null;
		}
		if (!isset($rrule['INTERVAL']) && $rrule['FREQ'] !== 'NONE')
		{
			$rrule['INTERVAL'] = 1;
		}
		if ($endMode === 'never')
		{
			unset($rrule['COUNT'], $rrule['UNTIL']);
		}
		elseif ($endMode === 'count')
		{
			if ((int)$rrule['COUNT'] <= 0)
			{
				$rrule['COUNT'] = 10;
			}
			unset($rrule['UNTIL']);
		}
		elseif ($endMode === 'until')
		{
			unset($rrule['COUNT']);
		}

		return $rrule;
	}
}
