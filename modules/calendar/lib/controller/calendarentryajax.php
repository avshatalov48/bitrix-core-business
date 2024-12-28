<?php
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Application\Command\CreateEventCommand;
use Bitrix\Calendar\Application\Command\CreateEventHandler;
use Bitrix\Calendar\Application\Command\UpdateEventCommand;
use Bitrix\Calendar\Application\Command\UpdateEventHandler;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Managers\Accessibility;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\FileUploader\EventController;
use Bitrix\Calendar\ICal\IcalIcsBuilder;
use Bitrix\Calendar\Integration\Disk\File;
use Bitrix\Calendar\Integration\Disk\FileUploader;
use Bitrix\Calendar\OpenEvents\Exception\MaxAttendeesReachedException;
use Bitrix\Calendar\Relation\Item\Relation;
use Bitrix\Calendar\Relation\RelationProvider;
use Bitrix\Calendar\Integration\Tasks\TaskQueryParameter;
use Bitrix\Calendar\Internals\Exception\InvalidDate;
use Bitrix\Calendar\Rooms;
use Bitrix\Calendar\Internals;
use Bitrix\Calendar\Ui\CalendarFilter;
use Bitrix\Calendar\UserSettings;
use Bitrix\Calendar\Util;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Intranet;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\MimeType;
use Bitrix\UI\FileUploader\Uploader;

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
		return [
			'getIcsFile' => [
				'-prefilters' => [
					ActionFilter\Csrf::class,
				],
				'+postfilters' => [
					new ActionFilter\Cors(),
				],
			],
			'getIcsFileMobile' => [
				'-prefilters' => [
					ActionFilter\Authentication::class,
					ActionFilter\Csrf::class,
				],
				'+postfilters' => [
					new ActionFilter\Cors(),
				],
			],
		];
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
				'maxAmount' => $maxEntryAmount,
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

		$userId = \CCalendar::GetUserId();

		$isExtranetUser = Loader::includeModule('intranet') && !Intranet\Util::isIntranetUser($userId);

		if (!empty($sectionIdList))
		{
			$sect = \CCalendarSect::GetList([
				'arFilter' => [
					'ID' => $sectionIdList,
					'ACTIVE' => 'Y',
				],
				'checkPermissions' => true,
			]);
			foreach($sect as $section)
			{
				if ($isExtranetUser && $section['CAL_TYPE'] === Dictionary::CALENDAR_TYPE['location'])
				{
					continue;
				}
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
				'edit_attendees' => $accessController->check(ActionDictionary::ACTION_EVENT_EDIT_ATTENDEES, $eventModel),
				'edit_location' => $accessController->check(ActionDictionary::ACTION_EVENT_EDIT_LOCATION, $eventModel),
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
				   '=ID' => $sectionId,
			   ],
			   'select' => [
				   'ID',
				   'CAL_TYPE',
				   'OWNER_ID',
				   'NAME',
			   ],
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
						"=SECTION_ID" => $sectionId,
					],
					"select" => ["ID", "CAL_TYPE"],
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
				"SKIP_TIME" => $skipTime,
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
							'requestUid' => $requestUid,
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
							'requestUid' => $requestUid,
						]
					);
				}
			}
		}

		return [
			'id' => $id,
			'reload' => $reload,
			'busy_warning' => $busyWarning,
			'location_busy_warning' => $locationBusyWarning,
		];
	}

	public function editEntryAction()
	{
		$response = [];
		$request = $this->getRequest();
		$id = (int)($request['id'] ?? null);
		$userId = \CCalendar::getCurUserId();
		$dates = $this->getDates($request);
		$reminderList = \CCalendarReminder::prepareReminder($request['reminder']);
		$newId = null;

		try
		{
			if ($id)
			{
				$command = $this->getUpdateCommand($request);
				$event = (new UpdateEventHandler())($command);
			}
			else
			{
				$command = $this->getCreateCommand($request);
				$event = (new CreateEventHandler())($command);
			}
			$newId = $event->getId();
			$response['reload'] = true;
		}
		catch (Internals\Exception\PermissionDenied)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'edit_entry_access_denied'));
		}
		catch (Internals\Exception\SectionNotFound)
		{
			$this->addError(new Error(Loc::getMessage('EC_SECTION_NOT_FOUND'), 'edit_entry_section_not_found'));
		}
		catch (Internals\Exception\ExtranetPermissionDenied)
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'edit_entry_extranet_access_denied'));
		}
		catch (Internals\Exception\InvalidDate)
		{
			$this->addError(new Error(Loc::getMessage('EC_JS_EV_FROM_ERR')));
		}
		catch (Internals\Exception\LocationBusy)
		{
			$this->addError(new Error(Loc::getMessage('EC_LOCATION_BUSY'), 'edit_entry_location_busy'));
		}
		catch (Internals\Exception\AttendeeBusy $e)
		{
			$this->addError(
				new Error(Loc::getMessage('EC_USER_BUSY', ['#USER#' => $e->getAttendeeName()]), 'edit_entry_user_busy')
			);
			$response['busyUsersList'] = $e->getBusyUsersList();
		}
		catch (Internals\Exception\EditException)
		{
			$this->addError(new Error(Loc::getMessage('EC_JS_EV_SAVE_ERR'), 'edit_entry_save'));
		}
		catch (Internals\Exception\EventNotFound)
		{
			$this->addError(new Error('Event not found', 'edit_entry_event_not_found'));
		}
		catch (Rooms\OccupancyCheckerException $e)
		{
			$this->addError(new Error(Loc::getMessage('EC_LOCATION_BUSY_RECURRENCE'), 'edit_entry_location_busy_recurrence'));
			$this->addError(new Error($e->getMessage(), 'edit_entry_location_repeat_busy'));
		}
		catch (MaxAttendeesReachedException)
		{
			$this->addError(new Error('MAX ATTENDEES_REACHED', 'edit_entry_max_attendees_reached'));
		}

		// Exit if any error
		if (!empty($this->getErrors()))
		{
			return $response;
		}

		$eventList = [];
		$eventIdList = [$newId];

		if ($newId)
		{
			$response['entryId'] = $newId;

			$filter = [
				'ID' => $newId,
				'FROM_LIMIT' => \CCalendar::Date(
					\CCalendar::Timestamp($dates['date_from']) -
					\CCalendar::DAY_LENGTH * 10, false
				),
				'TO_LIMIT' => \CCalendar::Date(
					\CCalendar::Timestamp($dates['date_to']) +
					\CCalendar::DAY_LENGTH * 90, false
				),
			];

			$eventList = \CCalendarEvent::GetList(
				[
					'arFilter' => $filter,
					'parseRecursion' => true,
					'fetchAttendees' => true,
					'userId' => \CCalendar::GetUserId(),
				]
			);

			if (isset($_REQUEST['rec_edit_mode']) && in_array($_REQUEST['rec_edit_mode'], ['this', 'next']))
			{
				unset($filter['ID']);
				$filter['RECURRENCE_ID'] = ($eventList && $eventList[0] && $eventList[0]['RECURRENCE_ID']) ? $eventList[0]['RECURRENCE_ID'] : $newId;

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

		$pathToCalendar = \CCalendar::GetPathForCalendarEx($userId);
		foreach($eventList as $ind => $event)
		{
			$eventList[$ind]['~URL'] = \CHTTP::urlAddParams($pathToCalendar, ['EVENT_ID' => $event['ID']]);
		}

		$response['eventList'] = $eventList;
		$response['eventIdList'] = $eventIdList;

		$userSettings = UserSettings::get($userId);
		$userSettings['defaultReminders'][$dates['skip_time'] ? 'fullDay' : 'withTime'] = $reminderList;
		UserSettings::set($userSettings, $userId);

		return $response;
	}

	/**
	 * @throws InvalidDate
	 */
	private function getCreateCommand(HttpRequest $request): CreateEventCommand
	{
		return new CreateEventCommand(...$this->getCommandFields($request));
	}

	/**
	 * @throws InvalidDate
	 */
	private function getUpdateCommand(HttpRequest $request): UpdateEventCommand
	{
		$id = (int)($request['id'] ?? null);

		return new UpdateEventCommand($id, ...$this->getCommandFields($request));
	}

	/**
	 * @throws InvalidDate
	 */
	private function getCommandFields(HttpRequest $request): array
	{
		$id = (int)($request['id'] ?? null);
		$sectionId = (int)($request['section'] ?? null);
		$requestUid = (int)($request['requestUid'] ?? null);
		$userId = \CCalendar::getCurUserId();
		$dates = $this->getDates($request);
		$timezone = $this->getTimeZones($request);
		$reminderList = \CCalendarReminder::prepareReminder($request['reminder']);

		return [
			$userId,
			$sectionId,
			$dates['date_from'],
			$dates['date_to'],
			$dates['skip_time'],
			$timezone['timezone_from'],
			$timezone['timezone_to'],
			$this->getName($request),
			trim($request['desc'] ?? ''),
			$request['color'] ?? null,
			$request['accessibility'] ?? null,
			$request['importance'] ?? 'normal',
			($request['private_event'] ?? 'N') === 'Y',
			$this->prepareRecurringRule($request['EVENT_RRULE'] ?? null, $request['rrule_endson'] ?? null),
			$request['location'] ?? '',
			$reminderList,
			(int)$request['meeting_host'] ?: $userId,
			(int)($request['chat_id'] ?? null),
			$request['attendeesEntityList'] ?? null,
			$request['exclude_users'] ?? null,
			($request['meeting_notify'] ?? 'N') === 'Y',
			($request['meeting_reinvite'] ?? 'N') === 'Y',
			($request['allow_invite'] ?? 'N') === 'Y',
			($request['hide_guests'] ?? 'N') === 'Y',
			Bitrix24Manager::isPlannerFeatureEnabled(),
			!$id || $request->getPost('checkCurrentUsersAccessibility') !== 'N',
			$request['newAttendeesList'] ?? null,
			!empty($request['rec_edit_mode']) ? $request['rec_edit_mode'] : null,
			$this->getCurrentDateFrom($request),
			$this->getUFfields($id, $request),
			($request['sendInvitesAgain'] ?? 'N') === 'Y',
			$requestUid,
			($request['doCheckOccupancy'] ?? 'N') === 'Y',
			(int)($request['max_attendees'] ?? 0),
			$request['category'] !== null ? (int)$request['category'] : null,
			$request['analyticsSubSection'] ?? null,
			(int)($request['analyticsChatId'] ?? 0),
		];
	}

	private function getUFfields(int $eventId, HttpRequest $request): array
	{
		$arUFFields = [];
		foreach($request as $field => $value)
		{
			if (mb_strpos($field, 'UF_') === 0)
			{
				$arUFFields[$field] = $value;
			}
		}

		$uploadedFiles = $this->handleUploadingFiles($eventId, $request);
		if (!empty($uploadedFiles))
		{
			if (isset($arUFFields['UF_WEBDAV_CAL_EVENT']) && is_array($arUFFields['UF_WEBDAV_CAL_EVENT']))
			{
				$arUFFields['UF_WEBDAV_CAL_EVENT'] = [...$arUFFields['UF_WEBDAV_CAL_EVENT'], ...$uploadedFiles];
			}
			else
			{
				$arUFFields['UF_WEBDAV_CAL_EVENT'] = ['', ...$uploadedFiles];
			}
		}

		return $arUFFields;
	}

	private function handleUploadingFiles(int $eventId, HttpRequest $request): array
	{
		$result = [];

		if (empty($request['uploaded_files']) || !is_array($request['uploaded_files']))
		{
			return $result;
		}

		$controller = new EventController(['eventId' => $eventId]);
		$uploader = new Uploader($controller);
		$fileUploader = new FileUploader(\CCalendar::getCurUserId());

		$pendingFiles = $uploader->getPendingFiles($request['uploaded_files']);

		foreach ($pendingFiles->getFileIds() as $fileId)
		{
			$addingResult = $fileUploader->addFile($fileId);
			if ($addingResult->isSuccess())
			{
				$result[] = $addingResult->getData()['ATTACHMENT_ID'];
			}
		}
		$pendingFiles->makePersistent();

		return $result;
	}

	private function getCurrentDateFrom(HttpRequest $request): ?string
	{
		return !empty($request['current_date_from'])
			? \CCalendar::Date(\CCalendar::Timestamp($request['current_date_from']), false)
			: null;
	}

	private function getBusyUsersIds(array $attendees, int $curEventId, int $fromTs, int $toTs): array
	{
		$usersToCheck = $this->getUsersToCheck($attendees);
		if (empty($usersToCheck))
		{
			return [];
		}

		return (new Accessibility())
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

	private function getName(HttpRequest $request): string
	{
		return (empty($request['name']))
			? Loc::getMessage('EC_DEFAULT_EVENT_NAME_V2')
			: trim($request['name']);
	}

	/**
	 * @throws InvalidDate
	 */
	private function getDates(HttpRequest $request): array
	{
		// Date & Time
		$dateFrom = $request['date_from'] ?? '';
		$dateTo = $request['date_to'] ?? '';
		$skipTime = isset($request['skip_time']) && $request['skip_time'] === 'Y';
		if (!$skipTime)
		{
			$dateFrom .= ' ' . $request['time_from'] ?? '';
			$dateTo .= ' ' . $request['time_to'] ?? '';
		}
		$dateFrom = trim($dateFrom);
		$dateTo = trim($dateTo);

		if (
			(int)(new \DateTime())->setTimestamp(\CCalendar::Timestamp($dateFrom))->format('Y') > 9999
			|| (int)(new \DateTime())->setTimestamp(\CCalendar::Timestamp($dateTo))->format('Y') > 9999
		)
		{
			throw new Internals\Exception\InvalidDate();
		}

		return [
			'date_from' => $dateFrom,
			'date_to' => $dateTo,
			'skip_time' => $skipTime,
		];
	}

	private function getTimeZones(HttpRequest $request): array
	{
		$tzFrom = $request['tz_from'] ?? '';
		$tzTo = $request['tz_to'] ?? '';
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

		return [
			'timezone_from' => $tzFrom,
			'timezone_to' => $tzTo,
		];
	}

	public function getEventEntityRelationAction(int $eventId): ?Relation
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return null;
		}

		$userId = \CCalendar::GetUserId();
		$accessController = new EventAccessController($userId);
		$hasAccess = $accessController->check(
			ActionDictionary::ACTION_EVENT_VIEW_FULL,
			\CCalendarEvent::getEventModelForPermissionCheck($eventId, [], $userId)
		);
		if (!$hasAccess)
		{
			$this->addError(new Error('Event access denied'));

			return null;
		}

		$getRelationResult = (new RelationProvider())->getEventRelation($userId, $eventId);
		if (!empty($getRelationResult->getErrors()))
		{
			$this->addErrors($getRelationResult->getErrors());

			return null;
		}

		return $getRelationResult->getRelation();
	}

	public function getIcsContentAction(CurrentUser $currentUser, int $eventId): ?string
	{
		$canAccess = EventAccessController::can(
			$currentUser->getId(),
			ActionDictionary::ACTION_EVENT_VIEW_FULL,
			$eventId
		);

		if (!$canAccess)
		{
			$this->addError(new Error('Event access denied'));

			return null;
		}

		$event = (new Mappers\Event())->getById($eventId);

		return IcalIcsBuilder::buildFromEvent($event)->getContent();
	}

	public function getIcsFileAction(CurrentUser $currentUser, int $eventId): string|HttpResponse
	{
		return $this->prepareIcsResponse($currentUser, $eventId);
	}

	public function getIcsFileMobileAction(CurrentUser $currentUser, string $hitHash, int $eventId): ?HttpResponse
	{
		$httpResponse = new HttpResponse();
		$httpResponse->addHeader('Location', 'bitrix24://');

		if (empty($eventId) || empty($hitHash))
		{
			return $httpResponse;
		}

		if (!$GLOBALS['USER']->LoginHitByHash($hitHash, false, true))
		{
			return $httpResponse;
		}

		HttpApplication::getInstance()->getSession()->set('MOBILE_OAUTH', true);

		return $this->prepareIcsResponse($currentUser, $eventId);
	}

	private function prepareIcsResponse(
		CurrentUser $currentUser,
		int $eventId,
	): ?HttpResponse
	{
		$canAccess = EventAccessController::can(
			$currentUser->getId(),
			ActionDictionary::ACTION_EVENT_VIEW_FULL,
			$eventId
		);

		// TODO: response with error should be here
		if (!$canAccess)
		{
			$this->addError(new Error('Event access denied'));

			return null;
		}

		$event = (new Mappers\Event())->getById($eventId);

		$fileContent = IcalIcsBuilder::buildFromEvent($event)->getContent();

		$fileType = 'ics';
		$mimeType = MimeType::getByFileExtension($fileType);

		$httpResponse = new HttpResponse();
		$httpResponse->addHeader('Content-Type', "{$mimeType}; charset=utf-8");
		$httpResponse->addHeader('Content-Disposition', "attachment;filename=event.{$fileType}");
		$httpResponse->setContent($fileContent);

		return $httpResponse;
	}
}
