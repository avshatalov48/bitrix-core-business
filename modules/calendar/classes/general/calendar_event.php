<?php
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/calendar/classes/general/calendar.php");

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Core\Queue\Processor\SendingEmailNotification;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Integration\Bitrix24\FeatureDictionary;
use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Integration\SocialNetwork\Collab;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Sync\Factories\FactoriesCollection;
use Bitrix\Calendar\Core\Event\Tools\UidGenerator;
use Bitrix\Calendar\Sync\Managers\Synchronization;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\UserSettings;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Text\Emoji;
use Bitrix\Calendar\ICal\IncomingEventManager;
use Bitrix\Calendar\ICal\MailInvitation\InvitationInfo;
use Bitrix\Calendar\ICal\Basic\ICalUtil;
use Bitrix\Calendar\Internals;
use Bitrix\Calendar\Util;
use Bitrix\Calendar\Rooms;
use Bitrix\Disk\AttachedObject;
use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\OpenEvents;

class CCalendarEvent
{
	public static
		$TextParser,
		$sendPush = true;

	private static
		$fields = [],
		$userIndex = [],
		$eventUserFields = [],
		$attendeeBelongingToEvent = [],
		$collabIdByParent = [],
		$isAddIcalFailEmailError = false
	;

	public static $defaultSelectEvent = [
		'ID',
		'PARENT_ID',
		'CREATED_BY',
		'OWNER_ID',
		'EVENT_TYPE',
		'NAME',
		'DATE_FROM',
		'DATE_TO',
		'TZ_FROM',
		'TZ_TO',
		'TZ_OFFSET_FROM',
		'TZ_OFFSET_TO',
		'DATE_FROM_TS_UTC',
		'DATE_TO_TS_UTC',
		'DT_SKIP_TIME',
		'ACCESSIBILITY',
		'IMPORTANCE',
		'RRULE',
		'EXDATE',
		'SECTION_ID',
		'CAL_TYPE',
		'MEETING_STATUS',
		'IS_MEETING',
		'DT_LENGTH',
		'PRIVATE_EVENT',
	];

	public static function CheckRRULE($recRule = [])
	{
		if (is_array($recRule) && isset($recRule['FREQ']) && $recRule['FREQ'] !== 'WEEKLY' && isset($recRule['BYDAY']))
		{
			unset($recRule['BYDAY']);
		}

		return $recRule;
	}

	/**
	 * @param array $params
	 * params['arFields'] event fields
	 * params['userId'] user id
	 * params['saveAttendeesStatus'] sending notification flag
	 *
	 * @return bool|mixed
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Calendar\Rooms\OccupancyCheckerException
	 */
	public static function Edit($params = [])
	{
		global $DB, $CACHE_MANAGER;
		$entryFields = $params['arFields'] ?? [];
		$arAffectedSections = [];
		$entryChanges = [];
		$changedFields = [];
		$sendInvitations = ($params['sendInvitations'] ?? null) !== false;
		$sendEditNotification = ($params['sendEditNotification'] ?? null) !== false;
		$checkLocationOccupancy = ($params['checkLocationOccupancy'] ?? null) === true;
		$result = false;

		// Get current user id
		$userId = (isset($params['userId']) && (int)$params['userId'] > 0)
			? (int)$params['userId']
			: CCalendar::GetCurUserId();

		if (!$userId && isset($entryFields['CREATED_BY']))
		{
			$userId = (int)$entryFields['CREATED_BY'];
		}

		$isNewEvent = !isset($entryFields['ID']) || !$entryFields['ID'];
		$entryFields['TIMESTAMP_X'] = CCalendar::Date(time(), true, false);

		// Current event
		$currentEvent = [];
		if (!empty($entryFields['IS_MEETING']) && !isset($entryFields['ATTENDEES']) && isset($entryFields['ATTENDEES_CODES']))
		{
			$entryFields['ATTENDEES'] = \CCalendar::getDestinationUsers($entryFields['ATTENDEES_CODES']);
		}

		if (!$isNewEvent)
		{
			$currentEvent = $params['currentEvent'] ?? self::GetById($entryFields['ID'], $params['checkPermission'] ?? true);

			if (!isset($entryFields['LOCATION']) || !is_array($entryFields['LOCATION']))
			{
				$entryFields['LOCATION'] = [
					'NEW' => $entryFields['LOCATION'] ?? null,
				];
			}

			if (
				isset($entryFields['MEETING'])
				&& is_array($entryFields['MEETING'])
				&& is_array($currentEvent['MEETING'])
                && !isset($entryFields['MEETING']['CHAT_ID'])
                && isset($currentEvent['MEETING']['CHAT_ID'])
			)
			{
				$entryFields['MEETING']['CHAT_ID'] = $currentEvent['MEETING']['CHAT_ID'];
			}

			if (empty($entryFields['LOCATION']['OLD']))
			{
				$entryFields['LOCATION']['OLD'] = $currentEvent['LOCATION'] ?? null;
			}

			if (
				!empty($currentEvent['IS_MEETING']) && !isset($entryFields['ATTENDEES'])
				&& $currentEvent['PARENT_ID'] === $currentEvent['ID']
				&& !empty($entryFields['IS_MEETING'])
			)
			{
				$entryFields['ATTENDEES'] = [];
				$attendees = self::GetAttendees($currentEvent['PARENT_ID']);
				if (!empty($attendees[$currentEvent['PARENT_ID']]))
				{
					foreach ($attendees[$currentEvent['PARENT_ID']] as $attendee)
					{
						$entryFields['ATTENDEES'][] = $attendee['USER_ID'];
					}
				}
			}

			if (!empty($currentEvent['PARENT_ID']))
			{
				$entryFields['PARENT_ID'] = (int)$currentEvent['PARENT_ID'];
			}
		}

		if (self::CheckFields($entryFields, $currentEvent, $userId))
		{
			$attendees = (isset($entryFields['ATTENDEES']) && is_array($entryFields['ATTENDEES']))
                ? $entryFields['ATTENDEES']
                : [];

			if (
                ($entryFields['CAL_TYPE'] ?? null) !== Rooms\Manager::TYPE
				&& (empty($entryFields['PARENT_ID']) || $entryFields['PARENT_ID'] === $entryFields['ID'])
			)
			{
				$fromTs = $entryFields['DATE_FROM_TS_UTC'] ?? null;
				$toTs = $entryFields['DATE_TO_TS_UTC'] ?? null;
				if (($entryFields['DT_SKIP_TIME'] ?? null) !== "Y")
				{
					$fromTs += (int)date('Z', $fromTs);
					$toTs += (int)date('Z', $toTs);
				}

				$entryFields['LOCATION'] = self::checkLocationField($entryFields['LOCATION'] ?? null, $isNewEvent);

				if ($checkLocationOccupancy)
				{
					self::checkLocationOccupancy($entryFields, $params, $currentEvent, $userId);
				}

				$entryFields['LOCATION'] = Bitrix\Calendar\Rooms\Util::setLocation(
					$entryFields['LOCATION']['OLD'],
					$entryFields['LOCATION']['NEW'],
					[
						// UTC timestamp + date('Z', $timestamp) /*offset of the server*/
						'dateFrom' => CCalendar::Date($fromTs, $entryFields['DT_SKIP_TIME'] !== "Y"),
						'dateTo' => CCalendar::Date($toTs, $entryFields['DT_SKIP_TIME'] !== "Y"),
						'parentParams' => $params,
						'name' => $entryFields['NAME'],
						'persons' => count($attendees),
						'attendees' => $attendees,
						'bRecreateReserveMeetings' => ($entryFields['LOCATION']['RE_RESERVE'] ?? null) !== 'N',
						'checkPermission' => $params['checkPermission'] ?? null,
					]
				);
			}
			else
			{
				$entryFields['LOCATION'] = self::checkLocationField($entryFields['LOCATION'], $isNewEvent);
				$entryFields['LOCATION'] = $entryFields['LOCATION']['NEW'];
			}

			// Section
			if (isset($entryFields['SECTION_ID']))
			{
				$sectionId = (int)$entryFields['SECTION_ID'];
			}
			else
			{
				$sectionId = !empty($entryFields['SECTIONS'][0])
                    ? (int)$entryFields['SECTIONS'][0]
					: false;
			}

			if (!$sectionId)
			{
				$sectionId = self::tryingToFindEventSection($isNewEvent, $entryFields, $userId, $currentEvent);
			}

			$entryFields['SECTION_ID'] = $sectionId;
			$arAffectedSections[] = $sectionId;

			$section = CCalendarSect::GetList(['arFilter' => ['ID' => $sectionId],
				'checkPermissions' => false,
				'getPermissions' => false,
			])[0] ?? null;

			// Here we take type and owner parameters from section data
			if ($section)
			{
				$entryFields['CAL_TYPE'] = $section['CAL_TYPE'];
				$entryFields['OWNER_ID'] = $section['OWNER_ID'] ?? '';

				if (
					$section['CAL_TYPE'] === Dictionary::CALENDAR_TYPE['group']
					&& Collab\Entity\SectionEntityHelper::getIfCollab($sectionId)
					&& $entryFields['EVENT_TYPE'] !== Dictionary::EVENT_TYPE['shared_collab']
				)
				{
					$entryFields['EVENT_TYPE'] = Dictionary::EVENT_TYPE['collab'];
				}
			}

			if (($entryFields['CAL_TYPE'] ?? null) === 'user')
			{
				$CACHE_MANAGER->ClearByTag('calendar_user_'.$entryFields['OWNER_ID']);
			}

			if ($isNewEvent)
			{
				if (!isset($entryFields['CREATED_BY']))
				{
					$entryFields['CREATED_BY'] = (
                        !empty($entryFields['IS_MEETING'])
						&& ($entryFields['CAL_TYPE'] ?? null) === 'user'
						&& !empty($entryFields['OWNER_ID'])
                    ) ? $entryFields['OWNER_ID'] : $userId;
				}

				if (!isset($entryFields['DATE_CREATE']))
				{
					$entryFields['DATE_CREATE'] = $entryFields['TIMESTAMP_X'];
				}
			}
			else
			{
				$arAffectedSections[] = $currentEvent['SECTION_ID'] ?? $currentEvent['SECT_ID'];
			}

			if (
				!isset($entryFields['IS_MEETING'])
				&& isset($entryFields['ATTENDEES'])
				&& is_array($entryFields['ATTENDEES'])
			&& empty($entryFields['ATTENDEES'])
			)
			{
				$entryFields['IS_MEETING'] = false;
			}
			if (!empty($entryFields['IS_MEETING']) && !$isNewEvent)
			{
				$entryChanges = self::CheckEntryChanges($entryFields, $currentEvent);
				$changedFields = array_column($entryChanges, 'fieldKey');
			}
			else if (
				!empty($entryFields['IS_MEETING'])
				&& !empty($params['editInstance'])
				&& !empty($params['instanceChanges'])
			)
			{
				$entryChanges = $params['instanceChanges'];
				$changedFields = array_column($entryChanges, 'fieldKey');
			}

			$attendeesCodes = $entryFields['ATTENDEES_CODES'] ?? null;
			if (is_array($attendeesCodes))
			{
				$entryFields['ATTENDEES_CODES'] = implode(',', $attendeesCodes);
			}

			if ($entryFields['CAL_TYPE'] === Dictionary::CALENDAR_TYPE['open_event'])
			{
				// for open events meeting status stored for each attendee in event_attendee table
				$entryFields['MEETING_STATUS'] = 'N';
			}
			else if (
                !isset($entryFields['MEETING_STATUS'])
				&& !empty($entryFields['MEETING_HOST'])
                && (int)$entryFields['MEETING_HOST'] === (int)($entryFields['CREATED_BY'] ?? null)
			)
			{
				$entryFields['MEETING_STATUS'] = 'H';
			}
			else if (!isset($entryFields['MEETING_STATUS']) && !$currentEvent)
			{
				$entryFields['MEETING_STATUS'] = 'Y';
			}

			if (isset($entryFields['MEETING']) && is_array($entryFields['MEETING']))
			{
				$entryFields['~MEETING'] = $entryFields['MEETING'];
				$entryFields['MEETING']['REINVITE'] = false;
				$meetingHostSettings = UserSettings::get($entryFields['MEETING_HOST'] ?? null);
				$entryFields['MEETING']['MAIL_FROM'] =
					$entryFields['MEETING']['MAIL_FROM']
					?? $meetingHostSettings['sendFromEmail']
					?? null
				;
				$entryFields['MEETING'] = serialize($entryFields['MEETING']);
			}

			if (isset($entryFields['RELATIONS']) && is_array($entryFields['RELATIONS']))
			{
				$entryFields['~RELATIONS'] = $entryFields['RELATIONS'];
				$entryFields['RELATIONS'] = serialize($entryFields['RELATIONS']);
			}

			if (
				isset($entryFields['REMIND'])
				&& (
					$isNewEvent
					|| !$entryFields['IS_MEETING']
					|| (int)$entryFields['CREATED_BY'] === $userId
					|| ($params['updateReminders'] ?? null) === true
				)
			)
			{
				$reminderList = CCalendarReminder::prepareReminder($entryFields['REMIND']);
			}
			elseif (!empty($currentEvent['REMIND']))
			{
				$reminderList = CCalendarReminder::prepareReminder($currentEvent['REMIND']);
			}
			else
			{
				$reminderList = [];
			}
			$entryFields['REMIND'] = serialize($reminderList);

			if (
				isset($entryFields['SYNC_STATUS'])
				&& !in_array($entryFields['SYNC_STATUS'],Bitrix\Calendar\Sync\Google\Dictionary::SYNC_STATUS, true)
			)
			{
				$entryFields['SYNC_STATUS'] = null;
			}

			if (isset($entryFields['EXDATE']) && is_array($entryFields['EXDATE']))
			{
				$entryFields['EXDATE'] = implode(';', $entryFields['EXDATE']);
			}
			$entryFields['EXDATE'] = !empty($entryFields['EXDATE'])
				? self::convertExDatesToInternalFormat($entryFields['EXDATE'])
				: ''
			;

			$entryFields['RRULE'] = self::convertRuleUntilToInternalFormat($entryFields['RRULE'] ?? null);

			$AllFields = self::GetFields();
			$dbFields = [];

			foreach($entryFields as $field => $val)
			{
				if (
					isset($AllFields[$field])
					&& $field !== "ID"
					&& is_scalar($val)
				)
				{
					$dbFields[$field] = $val;
				}
			}

			if (!empty($dbFields['NAME']))
			{
				$dbFields['NAME'] = Emoji::encode($dbFields['NAME']);
			}
			if (!empty($dbFields['DESCRIPTION']))
			{
				$dbFields['DESCRIPTION'] = Emoji::encode($dbFields['DESCRIPTION']);
			}
			if (!empty($dbFields['LOCATION']))
			{
				$dbFields['LOCATION'] = Emoji::encode($dbFields['LOCATION']);
			}

			CTimeZone::Disable();

			$parentEventId = $entryFields['PARENT_ID'];
			$isOpenEvent = ($entryFields['CAL_TYPE'] ?? null) === Dictionary::CALENDAR_TYPE['open_event'];

			if ($isNewEvent) // Add
			{
				$eventId = $DB->Add("b_calendar_event", $dbFields, ['DESCRIPTION', 'MEETING', 'EXDATE']);

				if ($dbFields['CAL_TYPE'] === 'user' || $parentEventId)
				{
					self::storeEventAttendee((int)($eventId), $dbFields);
				}
			}
			else // Update
			{
				$eventId = $entryFields['ID'];
				$strUpdate = $DB->PrepareUpdate("b_calendar_event", $dbFields);
				$strSql =
					"UPDATE b_calendar_event SET ".
						$strUpdate.
						" WHERE ID=". (int)$eventId;

				$DB->QueryBind($strSql, array(
					'DESCRIPTION' => Emoji::encode($entryFields['DESCRIPTION'] ?? ''),
					'MEETING' => $entryFields['MEETING'] ?? null,
					'EXDATE' => $entryFields['EXDATE'] ?? null,
				));
				// update separated attendee fields
				self::updateEventAttendee($eventId, $userId, $changedFields, $dbFields);
			}

			CTimeZone::Enable();

			if (
				$userId
				&& $params
				&& ($params['overSaving'] ?? null) !== true
				&& \Bitrix\Calendar\Sync\Util\RequestLogger::isEnabled()
			)
			{
				$loggerParams = $params;
				$loggerParams['arFields'] = $entryFields;
				$loggerParams['loggerUuid'] = $eventId;

				(new \Bitrix\Calendar\Sync\Util\RequestLogger($userId, 'portal_edit'))->write($loggerParams);
			}

			/**
			 * @deprecated
			 */
//			if ($isNewEvent && !isset($dbFields['DAV_XML_ID']))
//			{
//				$strSql =
//					"UPDATE b_calendar_event SET ".
//						$DB->PrepareUpdate("b_calendar_event", ['DAV_XML_ID' => (int)$eventId]).
//						" WHERE ID = ". (int)$eventId;
//				$DB->Query($strSql);
//			}
			/**
			 * @deprecated Now connection saved in the table
			 */
//			if (
//				!Util::isSectionStructureConverted()
//				&& ($isNewEvent || $sectionId !== $currentEvent['SECTION_ID']))
//			{
//				self::ConnectEventToSection($eventId, $sectionId);
//			}

			if (!empty($arAffectedSections))
			{
				CCalendarSect::UpdateModificationLabel($arAffectedSections);
			}

			if (
				!empty($entryFields['IS_MEETING'])
				|| (!$isNewEvent && !empty($currentEvent['IS_MEETING']))
			)
			{
				if (empty($entryFields['PARENT_ID']))
				{
					Internals\EventTable::update((int)$eventId, [
						'PARENT_ID' => (int)$eventId,
					]);
				}

				if (
					(empty($entryFields['PARENT_ID']) || $entryFields['PARENT_ID'] === $eventId)
					&& !$isOpenEvent
				)
				{
					self::CreateChildEvents($eventId, $entryFields, $params, $entryChanges);
				}

				if ((empty($entryFields['PARENT_ID']) || $entryFields['PARENT_ID'] === $eventId) && !empty($entryFields['RECURRENCE_ID']))
				{
					self::UpdateParentEventExDate($entryFields['RECURRENCE_ID'], $entryFields['ORIGINAL_DATE_FROM'], $entryFields['ATTENDEES']);
				}

				if (empty($entryFields['PARENT_ID']))
				{
					$entryFields['PARENT_ID'] = (int)$eventId;
				}
			}
			else if (($isNewEvent && empty($entryFields['PARENT_ID'])) || (!$isNewEvent && empty($currentEvent['PARENT_ID'])))
			{
				Internals\EventTable::update((int)$eventId, [
					'PARENT_ID' => (int)$eventId,
				]);

				if (empty($entryFields['PARENT_ID']))
				{
					$entryFields['PARENT_ID'] = (int)$eventId;
				}
			}

			// Update reminders for event
			CCalendarReminder::updateReminders([
				'id' => $eventId,
				'reminders' => $reminderList,
				'arFields' => $entryFields,
				'userId' => $userId,
			]);

			if ((empty($entryFields['PARENT_ID']) || $entryFields['PARENT_ID'] === $eventId))
			{
				self::updateSearchIndex((int)$eventId, [
					'userId' => $userId,
					'updateAllByParent' => true,
					'isNew' => $isNewEvent,
					'changedFields' => $changedFields,
				]);
			}

			$nowUtc = time() - date('Z');
			// Send invitations and notifications
			if (
				!empty($entryFields['IS_MEETING'])
				&& ($params['overSaving'] ?? null) !== true
			)
			{
				$fromTo = self::GetEventFromToForUser($entryFields, $entryFields['OWNER_ID'] ?? null);

				// If it's event in the past we're skipping notifications.
				// The past is the past...
				if (isset($entryFields['DATE_TO_TS_UTC']) && $entryFields['DATE_TO_TS_UTC'] > $nowUtc)
				{
					if (
						$sendEditNotification
						&& (int)($entryFields['PARENT_ID'] ?? null) !== (int)$eventId
						&& !empty($entryChanges)
						&& (
							($entryFields['MEETING_STATUS'] ?? null) === 'Y'
							|| ($entryFields['MEETING_STATUS'] ?? null) === 'H'
						)
					)
					{
						// third problematic place

						if (
							(!empty($entryFields['MEETING_HOST']) && (int)$entryFields['MEETING_HOST'] === (int)$userId)
							|| self::checkAttendeeBelongsToEvent($entryFields['PARENT_ID'] ?? null, $userId)
						)
						{
							$CACHE_MANAGER->ClearByTag('calendar_user_'.$entryFields['OWNER_ID']);
							CCalendarNotify::Send([
								'mode' => 'change_notify',
								'name' => $entryFields['NAME'] ?? null,
								"from" => $fromTo['DATE_FROM'] ?? null,
								"to" => $fromTo['DATE_TO'] ?? null,
								"location" => CCalendar::GetTextLocation($entryFields["LOCATION"] ?? null),
								"guestId" => $entryFields['OWNER_ID'] ?? null,
								"eventId" => $entryFields['PARENT_ID'] ?? null,
								"userId" => \CCalendar::GetUserId(),
								"fields" => $entryFields,
								"isSharing" => ($entryFields['EVENT_TYPE'] ?? null) === Dictionary::EVENT_TYPE['shared'],
								"entryChanges" => $entryChanges,
							]);
						}
					}
					elseif (
						(int)($entryFields['PARENT_ID'] ?? null) !== $eventId
						&& ($entryFields['MEETING_STATUS'] ?? null) === 'Q'
						&& $sendInvitations
					)
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$entryFields['OWNER_ID'] ?? '');
						CCalendarNotify::Send(array(
							"mode" => 'invite',
							"name" => $entryFields['NAME'] ?? null,
							"from" => $fromTo['DATE_FROM'] ?? null,
							"to" => $fromTo['DATE_TO'] ?? null,
							"location" => CCalendar::GetTextLocation($entryFields["LOCATION"] ?? null),
							"guestId" => $entryFields['OWNER_ID'] ?? null,
							"eventId" => $entryFields['PARENT_ID'] ?? null,
							"userId" => \CCalendar::GetUserId(),
							"isSharing" => ($entryFields['EVENT_TYPE'] ?? null) === Dictionary::EVENT_TYPE['shared'],
							"fields" => $entryFields,
						));
					}
				}
			}

			if (
				!empty($entryFields['IS_MEETING'])
				&& !empty($entryFields['ATTENDEES_CODES'])
				&& (int)($entryFields['PARENT_ID'] ?? null) === (int)$eventId
				&& ($params['overSaving'] ?? null) !== true
				&& isset($entryFields['DATE_TO_TS_UTC'])
				&& $entryFields['DATE_TO_TS_UTC'] > $nowUtc
			)
			{
				CCalendarLiveFeed::OnEditCalendarEventEntry([
					'eventId' => $eventId,
					'arFields' => $entryFields,
					'attendeesCodes' => $attendeesCodes,
				]);
			}

			CCalendar::ClearCache('event_list');

			if (($entryFields['ACCESSIBILITY'] ?? '') === 'absent')
			{
				(new \Bitrix\Calendar\Integration\Intranet\Absence())->cleanCache();
			}

			$result = $eventId;

			if (!empty($entryFields['LOCATION']))
			{
				Rooms\Manager::setEventIdForLocation($eventId, $entryFields['LOCATION']);
			}

			if ($isNewEvent)
			{
				foreach(EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarEntryAdd") as $event)
				{
					ExecuteModuleEventEx(
						$event,
						[
							$eventId,
							$entryFields,
							[],
						]
					);
				}

				if (($entryFields['PARENT_ID'] ?? null) === $eventId && $entryFields['CAL_TYPE'] !== 'location')
				{
					Bitrix24Manager::increaseEventsAmount();
				}
			}
			else
			{
				foreach(EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarEntryUpdate") as $event)
				{
					ExecuteModuleEventEx(
						$event,
						[
							$eventId,
							$entryFields,
							$currentEvent['ATTENDEE_LIST'] ?? null,
						]
					);
				}

				if (($entryFields['PARENT_ID'] ?? null) === $eventId)
				{
					$attendeeListBeforeUpdate =
						!empty($currentEvent['ATTENDEE_LIST'])
							? array_map(fn($attendee) => $attendee['id'] ?? 0, $currentEvent['ATTENDEE_LIST'])
							: []
					;
					$attendeesAfterUpdate = !empty($attendees) ? $attendees : [(int)($entryFields['MEETING_HOST'] ?? null)];
					(new \Bitrix\Calendar\Integration\SocialNetwork\SpaceService())->addEvent(
						'onAfterCalendarEventUpdate',
						[
							'ATTENDEES_CODES_BEFORE_UPDATE' => $currentEvent['ATTENDEES_CODES'] ?? [],
							'ATTENDEES_CODES_AFTER_UPDATE' => $attendeesCodes,
							'ATTENDEES_BEFORE_UPDATE' => $attendeeListBeforeUpdate,
							'ATTENDEES_AFTER_UPDATE' => $attendeesAfterUpdate ?? [],
							'ID' => $eventId,
						],
					);
				}
			}

			$pullUserId = (isset($entryFields['OWNER_ID']) && (int)$entryFields['OWNER_ID'] > 0)
                ? (int)$entryFields['OWNER_ID']
				: $userId
			;

			if (
				$pullUserId > 0
				&& ($params['overSaving'] ?? null) !== true
				&& self::$sendPush
			)
			{
				$entryFields['ID'] = $result;

				$entryFields = self::calculateUserOffset($pullUserId, $entryFields);
				$attendeeListResult = self::getAttendeeList([$result]);
				$attendeeList = $attendeeListResult['attendeeList'][$entryFields['PARENT_ID']] ?? [];
				$entryFields  = self::PreHandleEvent($entryFields);
				$skipTime = $entryFields['DT_SKIP_TIME'] === 'Y';
				$dateFromFormatted = self::getDateInJsFormat(
					CCalendar::createDateTimeObjectFromString($entryFields['DATE_FROM']),
					$skipTime,
				);
				$dateToFormatted = self::getDateInJsFormat(
					CCalendar::createDateTimeObjectFromString($entryFields['DATE_TO']),
					$skipTime,
				);

				$collabId = 0;

				if (
					!empty($entryFields['EVENT_TYPE'])
					&& in_array(
						$entryFields['EVENT_TYPE'],
						[Dictionary::EVENT_TYPE['collab'], Dictionary::EVENT_TYPE['shared_collab']],
						true
					)
				)
				{
					$collabId = self::getCollabIdByParentId($entryFields['PARENT_ID']);
				}

				Util::addPullEvent(
					PushCommand::EditEvent,
					$pullUserId,
					[
						'fields' => array_merge($entryFields, [
							'ATTENDEE_LIST' => $attendeeList,
							'DATE_FROM_FORMATTED' => $dateFromFormatted,
							'DATE_TO_FORMATTED' => $dateToFormatted,
							'COLLAB_ID' => $collabId,
							'permissions' => self::getEventPermissions($entryFields, $pullUserId),
						]),
						'newEvent' => $isNewEvent,
						'requestUid' => $params['requestUid'] ?? null,
					]
				);
			}
		}

		return $result;
	}

	/**
	 * @param $id
	 * @param bool $checkPermissions
	 * @param bool $loadOriginalRecursion
	 * @return array|false
	 */
	public static function GetById($id, bool $checkPermissions = true, $loadOriginalRecursion = false)
	{
		if ($id > 0)
		{
			$event = self::GetList([
				'arFilter' => [
					'ID' => $id,
					'DELETED' => 'N',
				],
				'parseRecursion' => false,
				'fetchAttendees' => $checkPermissions,
				'checkPermissions' => $checkPermissions,
				'setDefaultLimit' => false,
				'loadOriginalRecursion' => $loadOriginalRecursion,
			]);
			if (!empty($event[0]) && is_array($event[0]))
			{
				return $event[0];
			}
		}

		return false;
	}

	public static function GetList($params = [])
	{
		$isIntranetEnabled = CCalendar::IsIntranetEnabled();
		$checkPermissions = ($params['checkPermissions'] ?? null) !== false;
		$bCache = CCalendar::CacheTime() > 0;
		$params['setDefaultLimit'] = ($params['setDefaultLimit'] ?? null) === true;
		$userId = (isset($params['userId']) && $params['userId']) ? (int)$params['userId'] : CCalendar::GetCurUserId();
		$params['parseDescription'] = $params['parseDescription'] ?? true;
		$params['fetchAttendees'] = ($params['fetchAttendees'] ?? null) !== false;
		$resultEntryList = null;
		$userIndex = null;

		CTimeZone::Disable();
		if ($bCache)
		{
			$cache = new CPHPCache;
			$cacheId = 'eventlist'.md5(serialize($params)).CCalendar::GetOffset();
			if ($checkPermissions)
			{
				$cacheId .= 'perm' . CCalendar::GetCurUserId() . '|';
			}
			if (CCalendar::IsSocNet() && CCalendar::IsSocnetAdmin())
			{
				$cacheId .= 'socnetAdmin|';
			}
			$cachePath = CCalendar::CachePath().'event_list';

			if ($cache->InitCache(CCalendar::CacheTime(), $cacheId, $cachePath))
			{
				$cachedData = $cache->GetVars();
				if (isset($cachedData['dateTimeFormat']) && $cachedData['dateTimeFormat'] === FORMAT_DATETIME)
				{
					$resultEntryList = $cachedData["resultEntryList"];
					$userIndex = $cachedData["userIndex"];
				}
			}
		}

		if (!$bCache || !isset($resultEntryList))
		{
			$arFilter = $params['arFilter'];
			$resultEntryList = [];

			[$eventList, $parentMeetingIdList, $involvedUsersIdList] = self::getListOrm($params);

			if (!empty($params['fetchAttendees']) && !empty($parentMeetingIdList))
			{
				$attendeeListData = self::getAttendeeList($parentMeetingIdList);
				$attendeeList = $attendeeListData['attendeeList'];
				$involvedUsersIdList = array_unique(array_merge($involvedUsersIdList, $attendeeListData['userIdList']));
			}
			$userIndex = self::getUsersDetails($involvedUsersIdList);

			$parentCollabConnections = self::getParentCollabConnections($eventList);

			foreach ($eventList as $event)
			{
				$isOpenEvent = $event['CAL_TYPE'] === Dictionary::CALENDAR_TYPE['open_event'];
				if (
					$event['IS_MEETING']
					&& isset($attendeeList[$event['PARENT_ID']])
					&& $isIntranetEnabled
				)
				{
					if ($isOpenEvent)
					{
						// keep only STATUS=Y attendees for open events
						// cause there is no logic for disline user of open event
						$event['ATTENDEE_LIST'] = array_filter(
							$attendeeList[$event['PARENT_ID']],
							static fn (array $a) => $a['status'] === 'Y'
						);
					}
					else
					{
						$event['ATTENDEE_LIST'] = $attendeeList[$event['PARENT_ID']];
					}
				}
				else if (!empty($params['fetchAttendees']))
				{
					// for open event we not need to HOST user presented in attendee list
					if (!$isOpenEvent)
					{
						$event['ATTENDEE_LIST'] = [
							[
								'id' => (int)$event['MEETING_HOST'],
								'entryId' => $event['ID'],
								'status' => in_array($event['MEETING_STATUS'], ['Y', 'N', 'Q', 'H'])
									? $event['MEETING_STATUS']
									: 'H'
								,
							],
						];
					}
				}
				else
				{
					$event['ATTENDEE_LIST'] = [];
				}

				if ($checkPermissions)
				{
					$checkPermissionsForEvent = $userId !== (int)($event['CREATED_BY'] ?? null); // It's creator

					// It's event in user's calendar
					if (
						$checkPermissionsForEvent
						&& ($event['CAL_TYPE'] ?? null) === 'user'
						&& $userId === (int)$event['OWNER_ID']
					)
					{
						$checkPermissionsForEvent = false;
					}
					if (
						$checkPermissionsForEvent
						&& $event['IS_MEETING']
						&& ($event['USER_MEETING'] ?? null)
						&& (int)$event['USER_MEETING']['ATTENDEE_ID'] === $userId
					)
					{
						$checkPermissionsForEvent = false;
					}

					if (
						$checkPermissionsForEvent
						&& $event['IS_MEETING']
						&& is_array($event['ATTENDEE_LIST'] ?? null)
					)
					{
						foreach($event['ATTENDEE_LIST'] as $attendee)
						{
							if ((int)$attendee['id'] === $userId)
							{
								$checkPermissionsForEvent = false;
								break;
							}
						}
					}

					if ($checkPermissionsForEvent)
					{
						$event = self::ApplyAccessRestrictions($event, $userId);
					}
				}

				if ($event !== false)
				{
					$event['COLLAB_ID'] = self::getCollabIdByEvent($event, $parentCollabConnections);

					$event = self::PreHandleEvent($event, [
						'parseDescription' => $params['parseDescription'],
					]);

					if (!empty($params['parseRecursion']) && self::CheckRecurcion($event))
					{
						self::ParseRecursion($resultEntryList, $event, [
							'userId' => $userId,
							'fromLimit' => $arFilter["FROM_LIMIT"] ?? null,
							'toLimit' => $arFilter["TO_LIMIT"] ?? null,
							'loadLimit' => $params["limit"] ?? null,
							'instanceCount' => $params['maxInstanceCount'] ?? false,
							'preciseLimits' => $params['preciseLimits'] ?? false,
						]);
					}
					else
					{
						self::HandleEvent($resultEntryList, $event, $userId);
					}
				}
			}

			if ($bCache)
			{
				$cache->StartDataCache(CCalendar::CacheTime(), $cacheId, $cachePath);
				$cache->EndDataCache([
					"resultEntryList" => $resultEntryList,
					"userIndex" => $userIndex,
					"dateTimeFormat" => FORMAT_DATETIME,
				]);
			}
		}

		if (is_array($userIndex))
		{
			foreach($userIndex as $userIndexId => $userIndexDetails)
			{
				self::$userIndex[$userIndexId] = $userIndexDetails;
			}
		}

		CTimeZone::Enable();

		return $resultEntryList;
	}

	private static function getListOrm($params = [])
	{
		$eventList = [];

		$userId = (isset($params['userId']) && $params['userId']) ? (int)$params['userId'] : CCalendar::GetCurUserId();
		$fetchSection = $params['fetchSection'] ?? null;
		$orderFields = $params['arOrder'] ?? [];
		$filterFields = $params['arFilter'] ?? [];
		$selectFields = $params['arSelect'] ?? [];
		$getUf = ($params['getUserfields'] ?? null) !== false;
		$eventFields = self::getEventFields();

		if (isset($filterFields["DELETED"]) && ($filterFields["DELETED"] === false))
		{
			unset($filterFields["DELETED"]);
		}
		elseif (!isset($filterFields["DELETED"]))
		{
			$filterFields["DELETED"] = "N";
		}

		if (($params['setDefaultLimit'] ?? null) !== false) // Deprecated
		{
			if (!isset($filterFields['FROM_LIMIT'])) // default 3 month back
			{
				$filterFields['FROM_LIMIT'] = CCalendar::Date(time() - 31 * 3 * 24 * 3600, false);
			}

			if (!isset($filterFields['TO_LIMIT'])) // default one year into the future
			{
				$filterFields['TO_LIMIT'] = CCalendar::Date(time() + 365 * 24 * 3600, false);
			}
		}

		$query = Internals\EventTable::query();
		$attendeesQuery = Internals\EventTable::query();
		$attendeeIds = [$userId];
		$openEventSection = self::getOpenEventSection();
		$joinOpenEvents = !empty(
			array_intersect(['ID', 'PARENT_ID', 'RECURRENCE_ID', 'CAL_TYPE', 'SECTION'], array_keys($filterFields))
		);

		if (!empty($filterFields) && is_array($filterFields))
		{
			foreach ($filterFields as $key => $value)
			{
				if (is_string($value) && !$value)
				{
					continue;
				}

				switch ($key)
				{
					case 'FROM_LIMIT':
						$timestamp = (int)CCalendar::Timestamp($value, false);
						if ($timestamp)
						{
							$query->where('DATE_TO_TS_UTC', '>=', $timestamp);
							$attendeesQuery->where('DATE_TO_TS_UTC', '>=', $timestamp);
						}
						break;
					case 'TO_LIMIT':
						$timestamp = (int)CCalendar::Timestamp($value, false);
						if ($timestamp)
						{
							$toTimestamp = $timestamp + CCalendar::GetDayLen() - 1;
							$query->where('DATE_FROM_TS_UTC', '<=', $toTimestamp);
							$attendeesQuery->where('DATE_FROM_TS_UTC', '<=', $toTimestamp);
						}
						break;
					case 'MEETING_HOST':
					case 'CREATED_BY':
						if (is_array($value))
						{
							$value = array_map(static function($item) {
								return (int)$item;
							}, $value);

							if (empty($value))
							{
								$value = [''];
							}

							$query->whereIn($key, $value);
						}
						else if ((int)$value)
						{
							$query->where($key, $value);
						}
						break;
					case 'ID':
					case 'PARENT_ID':
					case 'RECURRENCE_ID':
						if (!is_array($value))
						{
							$value = [$value];
						}

						$value = array_map('intval', $value);

						if (empty($value))
						{
							$value = [''];
						}

						$query->whereIn($key, $value);
						$attendeesQuery->whereIn($key, $value);

						break;
					case 'OWNER_ID':
						if (!is_array($value))
						{
							$value = [$value];
						}

						$value = array_map('intval', $value);

						if (empty($value))
						{
							$value = [''];
						}
						else
						{
							$attendeeIds = $value;
						}

						$query->whereIn('OWNER_ID', $value);

						break;
					case '>ID':
						if ((int)$value)
						{
							$query->where('ID', '>', $value);
						}
						break;
					case 'CAL_TYPE':
						if (!is_array($value))
						{
							$value = [$value];
						}

						$joinOpenEvents = $joinOpenEvents && in_array(Dictionary::CALENDAR_TYPE['open_event'], $value, true);

						$calTypes = array_diff($value, [Dictionary::CALENDAR_TYPE['open_event']]);
						if (!empty($calTypes))
						{
							$query->whereIn('CAL_TYPE', $calTypes);
						}

						break;
					case 'SECTION':
						if (!is_array($value))
						{
							$value = [$value];
						}

						if (is_array($value))
						{
							$sections = [];
							foreach ($value as $item)
							{
								if ((int)$item)
								{
									$sections[] = (int)$item;
								}
							}

							if (!empty($sections))
							{
								$joinOpenEvents = $joinOpenEvents && in_array($openEventSection?->getId(), $sections, true);
								$sections = array_diff($sections, [$openEventSection?->getId()]);

								if (Util::isSectionStructureConverted())
								{
									$query->whereIn('SECTION_ID', $sections);
								}
								else
								{
									$query->whereIn('EVENT_SECT.SECT_ID', $sections);
								}
							}
						}
						break;
					case 'ACTIVE_SECTION':
						if ($value === 'Y' && Util::isSectionStructureConverted())
						{
							$query->where('SECTION.ACTIVE', $value);
						}
						break;
					case '*SEARCHABLE_CONTENT':
						$searchText = \Bitrix\Main\ORM\Query\Filter\Helper::matchAgainstWildcard($value);
						$query->whereMatch('SEARCHABLE_CONTENT', $searchText);
						break;
					case '*%SEARCHABLE_CONTENT':
						$query->whereLike('SEARCHABLE_CONTENT', '%' . $value . '%');
						break;
					case '=UF_CRM_CAL_EVENT':
						$query->where('UF_CRM_CAL_EVENT', $value);
						break;
					default:
						if (in_array($key, $eventFields, true))
						{
							if (is_array($value))
							{
								$query->whereIn($key, $value);
							}
							else
							{
								$query->where($key, $value);
							}
						}
						break;
				}
			}
		}

		if (empty($selectFields))
		{
			$selectFields = ['*'];
		}

		$attendeesQuery->setSelect([
			...$selectFields,
			'ATTENDEES.*',
			'EVENT_OPTIONS.*',
		]);

		$joinSection = $fetchSection
			&& ($filterFields['ACTIVE_SECTION'] ?? null) === 'Y'
			&& Util::isSectionStructureConverted()
		;

		if ($joinSection)
		{
			$selectFields['SECTION_DAV_XML_ID'] = 'SECTION.CAL_DAV_CAL';
		}

		if ($getUf)
		{
			$selectFields[] = 'UF_*';
		}

		$query
			->registerRuntimeField(
				new ReferenceField(
					'EVENT_OPTIONS',
					OpenEvents\Internals\OpenEventOptionTable::getEntity(),
					Join::on('this.ID', 'ref.EVENT_ID'),
				),
			);
		$selectFields[] = 'EVENT_OPTIONS.*';

		$query->setSelect($selectFields);

		$orderList = [];
		foreach ($orderFields as $key => $order)
		{
			if (in_array($key, $eventFields, true))
			{
				$orderList[$key] = (mb_strtoupper($order) === 'DESC') ? 'DESC' : 'ASC';
			}
		}

		if (!empty($orderList))
		{
			$query->setOrder($orderList);
			$attendeesQuery->setOrder($orderList);
		}

		if (isset($params['limit']) && (int)$params['limit'] > 0)
		{
			$query->setLimit((int)$params['limit']);
			$attendeesQuery->setLimit((int)$params['limit']);
		}

		if (($params['loadOriginalRecursion'] ?? null) === true)
		{
			self::applyLoadOriginalRecursionLogic($query);
		}

		$queryResult = $query->exec();
		$hasAttendees = $query->getEntity()->hasField('ATTENDEES');

		$parentMeetingIdList = [];
		$involvedUsersIdList = [];

		$openEventList = [];

		while ($eventObject = $queryResult->fetchObject())
		{
			[$event, $parentMeetings, $involvedUsers] = self::prepareEventObject($userId, $eventObject, $hasAttendees);

			$parentMeetingIdList = array_merge($parentMeetingIdList, $parentMeetings);
			$involvedUsersIdList = array_merge($involvedUsersIdList, $involvedUsers);

			$eventList[] = $event;

			// if open event presented in query results
			// add it in separate array to check in next query processing
			if (!empty($event['CAL_TYPE']) && $event['CAL_TYPE'] === Dictionary::CALENDAR_TYPE['open_event'])
			{
				$openEventList[] = (int)$event['ID'];
			}
		}

		if (!$joinOpenEvents)
		{
			return [$eventList, array_unique($parentMeetingIdList), array_unique($involvedUsersIdList)];
		}

		$attendeesQuery
			->registerRuntimeField(
				(new ReferenceField(
					'ATTENDEES',
					Internals\EventAttendeeTable::getEntity(),
					Join::on('this.ID', 'ref.EVENT_ID')
						->whereIn('ref.OWNER_ID', $attendeeIds)
						->where('ref.MEETING_STATUS', 'Y')
						->where('ref.DELETED', 'N')
					,
				))
					->configureJoinType(Join::TYPE_INNER)
				,
			)
			->registerRuntimeField(
				new ReferenceField(
					'EVENT_OPTIONS',
					OpenEvents\Internals\OpenEventOptionTable::getEntity(),
					Join::on('this.ID', 'ref.EVENT_ID'),
				),
			)
			->where('DELETED', 'N')
			->where('CAL_TYPE', Dictionary::CALENDAR_TYPE['open_event'])
		;

		$attendeesQueryResult = $attendeesQuery->exec();
		$hasAttendees = $attendeesQuery->getEntity()->hasField('ATTENDEES');

		while ($eventObject = $attendeesQueryResult->fetchObject())
		{
			[$event, $parentMeetings, $involvedUsers] = self::prepareEventObject($userId, $eventObject, $hasAttendees);

			$parentMeetingIdList = array_merge($parentMeetingIdList, $parentMeetings);
			$involvedUsersIdList = array_merge($involvedUsersIdList, $involvedUsers);

			// replace same open_event from previous query with attendee field from current query to prevent duplicates
			// or add as new
			if (($key = array_search((int)$event['ID'], $openEventList, true)) !== false)
			{
				$eventList[$key] = $event;
			}
			else
			{
				$eventList[] = $event;
			}
		}

		return [$eventList, array_unique($parentMeetingIdList), array_unique($involvedUsersIdList)];
	}

	private static function prepareEventObject(int $userId, Internals\EO_Event $eventObject, bool $hasAttendees): array
	{
		$event = $eventObject->collectValues();

		//we don't need it here
		unset($event['UTS_OBJECT']);

		// transform types from fetchObject-style to fetch-style
		// next booleans and strings fields sensitive for frontend
		// and also we need to support BC for rest api
		$stringFields = [
			'ID',
			'OWNER_ID',
			'CREATED_BY',
			'SECTION_ID',
			'TZ_OFFSET_FROM',
			'TZ_OFFSET_TO',
			'DATE_FROM_TS_UTC',
			'DATE_TO_TS_UTC',
			'MEETING_HOST',
		];
		foreach ($stringFields as $sField)
		{
			if (!isset($event[$sField]))
			{
				continue;
			}
			$event[$sField] = (string)$event[$sField];
		}

		$boolFields = [
			'ACTIVE',
			'DELETED',
			'DT_SKIP_TIME',
		];
		foreach ($boolFields as $bField)
		{
			if (!isset($event[$bField]))
			{
				continue;
			}
			$event[$bField] = $event[$bField] === true ? 'Y' : 'N';
		}

		// to prevent rest api method fails when try to on-fly create depth fields of MEETING array (like 0194353)
		$notEmptyFields = [
			'SYNC_STATUS' => '',
			'MEETING' => '',
			'EVENT_TYPE' => '',
			'ATTENDEES_CODES' => '',
			'RECURRENCE_ID' => 0,
		];
		foreach ($notEmptyFields as $nesField => $emptyValue)
		{
			if (!isset($event[$nesField]))
			{
				continue;
			}
			$event[$nesField] = $event[$nesField] === $emptyValue ? null : $event[$nesField];
		}

		$event['SECTION_DAV_XML_ID'] = $eventObject->getSection()?->getCalDavCal();

		if (isset($event['PARENT_ID']))
		{
			$event['PARENT_ID'] = (string)$event['PARENT_ID'];
		}

		$isFullDay = ($event['DT_SKIP_TIME'] ?? null) === 'Y';

		if (!empty($event['DATE_FROM']))
		{
			$event['DATE_FROM_FORMATTED'] = self::getDateInJsFormat($event['DATE_FROM'], $isFullDay);
			$event['DATE_FROM'] = (string)$event['DATE_FROM'];
		}

		if (!empty($event['DATE_TO']))
		{
			$event['DATE_TO_FORMATTED'] = self::getDateInJsFormat($event['DATE_TO'], $isFullDay);
			$event['DATE_TO'] = (string)$event['DATE_TO'];
		}

		if (!empty($event['ORIGINAL_DATE_FROM']))
		{
			$event['ORIGINAL_DATE_FROM'] = (string)$event['ORIGINAL_DATE_FROM'];
		}

		if (!empty($event['TIMESTAMP_X']))
		{
			$event['TIMESTAMP_X'] = (string)$event['TIMESTAMP_X'];
		}

		if (!empty($event['DATE_CREATE']))
		{
			$event['DATE_CREATE'] = (string)$event['DATE_CREATE'];
		}

		$event['SECT_ID'] = $event['SECTION_ID'] ?? null;
		if (
			(int)($event['IS_MEETING'] ?? 0) > 0
			|| ($event['CAL_TYPE'] ?? null) === Dictionary::CALENDAR_TYPE['open_event']
		)
		{
			$event['IS_MEETING'] = true;
		}
		else
		{
			$event['IS_MEETING'] = false;
		}

		if (empty($event['NAME']))
		{
			$event['NAME'] = Loc::getMessage('EC_T_NEW_EVENT');
		}
		else
		{
			$event['NAME'] = Emoji::decode($event['NAME']);
		}

		if (!empty($event['DESCRIPTION']))
		{
			$event['DESCRIPTION'] = Emoji::decode($event['DESCRIPTION']);
		}

		if (!empty($event['LOCATION']))
		{
			$event['LOCATION'] = Emoji::decode($event['LOCATION']);
		}

		if (!empty($event['DT_LENGTH']) && is_numeric($event['DT_LENGTH']))
		{
			$event['DT_LENGTH'] = (int)$event['DT_LENGTH'];
		}

		$parentMeetingIdList = [];
		$involvedUsersIdList = [];

		if (!empty($event['IS_MEETING']) && !empty($event['PARENT_ID']) && CCalendar::IsIntranetEnabled())
		{
			$parentMeetingIdList[] = $event['PARENT_ID'];
		}

		if (!empty($event['CREATED_BY']))
		{
			$involvedUsersIdList[] = $event['CREATED_BY'];
		}

		if (
			!empty($event['IS_MEETING'])
			&& $event['CAL_TYPE'] === Dictionary::CALENDAR_TYPE['user']
			&& (int)$event['OWNER_ID'] === $userId
			&& !$event['SECTION_ID']
		)
		{
			$defaultMeetingSection = CCalendar::GetMeetingSection($userId);
			if (!$defaultMeetingSection || !CCalendarSect::GetById($defaultMeetingSection, false))
			{
				$sectRes = CCalendarSect::GetSectionForOwner($event['CAL_TYPE'], $userId);
				$defaultMeetingSection = $sectRes['sectionId'];
			}

			// to support rest api BC cast to string
			$event['SECT_ID'] = (string)$defaultMeetingSection;
			$event['SECTION_ID'] = (string)$defaultMeetingSection;
		}

		$eventOptions = $eventObject->get('EVENT_OPTIONS');
		$event['OPTIONS'] = $eventOptions ? $eventOptions->collectValues() : null;

		// replace fields at host event from external attendee
		/** @var Internals\EO_EventAttendee $currentAttendee */
		if ($hasAttendees && ($currentAttendee = $eventObject->get('ATTENDEES')))
		{
			//TODO: for test purposes keep it for a while
			// this rows should stored only on actual event table
//			if (isset($event['OWNER_ID']))
//			{
//				$event['OWNER_ID'] = $currentAttendee->getOwnerId();
//			}
//			if (isset($event['CREATED_BY']))
//			{
//				$event['CREATED_BY'] = $currentAttendee->getCreatedBy();
//			}
//			$event['~TYPE'] = 'open_event';
			if (isset($event['MEETING_STATUS']))
			{
				$event['MEETING_STATUS'] = $currentAttendee->getMeetingStatus();
			}
			if (isset($event['SECTION_ID']))
			{
				// to support rest api BC cast to string
				$event['SECTION_ID'] = (string)$currentAttendee->getSectionId();
				$event['SECT_ID'] = (string)$currentAttendee->getSectionId();
			}
			if (!empty($currentAttendee->getColor()))
			{
				$event['COLOR'] = $currentAttendee->getColor();
			}
			if (isset($event['REMIND']))
			{
				$event['REMIND'] = $currentAttendee->getRemind();
			}
			if (isset($event['SYNC_STATUS']))
			{
				$event['SYNC_STATUS'] = $currentAttendee->getSyncStatus();
			}
			$event['CURRENT_ATTENDEE_ID'] = $currentAttendee->getId();
			$parentMeetingIdList[] = $event['ID'];
		}

		return [$event, $parentMeetingIdList, $involvedUsersIdList];
	}

	private static function applyLoadOriginalRecursionLogic(Query $query): void
	{
		$query->registerRuntimeField(
			(new Reference(
				'ORIGINAL_RECURSION',
				Internals\EventOriginalRecursionTable::class,
				Join::on('this.PARENT_ID', 'ref.PARENT_EVENT_ID'),
			))
				->configureJoinType(Join::TYPE_LEFT)
		);
		$query->setSelect(
			array_merge(
				$query->getSelect(),
				['ORIGINAL_RECURSION_ID' => 'ORIGINAL_RECURSION.ORIGINAL_RECURSION_EVENT_ID']
			)
		);
	}


	private static function getDateInJsFormat(DateTime|Bitrix\Main\Type\DateTime $date, $isFullDay): string
	{
		if ($isFullDay)
		{
			return $date->format('D M d Y');
		}

		return $date->format('D M d Y H:i:s');
	}

	private static function GetFields()
	{
		global $DB;
		if (!count(self::$fields))
		{
			CTimeZone::Disable();
			self::$fields = array(
				"ID" => Array("FIELD_NAME" => "CE.ID", "FIELD_TYPE" => "int"),
				"PARENT_ID" => Array("FIELD_NAME" => "CE.PARENT_ID", "FIELD_TYPE" => "int"),
				"DELETED" => Array("FIELD_NAME" => "CE.DELETED", "FIELD_TYPE" => "string"),
				"CAL_TYPE" => Array("FIELD_NAME" => "CE.CAL_TYPE", "FIELD_TYPE" => "string"),
				"SYNC_STATUS" => Array("FIELD_NAME" => "CE.SYNC_STATUS", "FIELD_TYPE" => "string"),
				"OWNER_ID" => Array("FIELD_NAME" => "CE.OWNER_ID", "FIELD_TYPE" => "int"),
				"EVENT_TYPE" => Array("FIELD_NAME" => "CE.EVENT_TYPE", "FIELD_TYPE" => "string"),
				"CREATED_BY" => Array("FIELD_NAME" => "CE.CREATED_BY", "FIELD_TYPE" => "int"),
				"NAME" => Array("FIELD_NAME" => "CE.NAME", "FIELD_TYPE" => "string"),
				"DATE_FROM" => Array("FIELD_NAME" => $DB->DateToCharFunction("CE.DATE_FROM").' as DATE_FROM', "FIELD_TYPE" => "date"),
				"DATE_TO" => Array("FIELD_NAME" => $DB->DateToCharFunction("CE.DATE_TO").' as DATE_TO', "FIELD_TYPE" => "date"),
				"TZ_FROM" => Array("FIELD_NAME" => "CE.TZ_FROM", "FIELD_TYPE" => "string"),
				"TZ_TO" => Array("FIELD_NAME" => "CE.TZ_TO", "FIELD_TYPE" => "string"),
				"ORIGINAL_DATE_FROM" => Array("FIELD_NAME" => $DB->DateToCharFunction("CE.ORIGINAL_DATE_FROM").' as ORIGINAL_DATE_FROM', "FIELD_TYPE" => "date"),
				"TZ_OFFSET_FROM" => Array("FIELD_NAME" => "CE.TZ_OFFSET_FROM", "FIELD_TYPE" => "int"),
				"TZ_OFFSET_TO" => Array("FIELD_NAME" => "CE.TZ_OFFSET_TO", "FIELD_TYPE" => "int"),
				"DATE_FROM_TS_UTC" => Array("FIELD_NAME" => "CE.DATE_FROM_TS_UTC", "FIELD_TYPE" => "int"),
				"DATE_TO_TS_UTC" => Array("FIELD_NAME" => "CE.DATE_TO_TS_UTC", "FIELD_TYPE" => "int"),

				"TIMESTAMP_X" => Array("FIELD_NAME" => $DB->DateToCharFunction("CE.TIMESTAMP_X").' as TIMESTAMP_X', "FIELD_TYPE" => "date"),
				"DATE_CREATE" => Array("FIELD_NAME" => $DB->DateToCharFunction("CE.DATE_CREATE").' as DATE_CREATE', "FIELD_TYPE" => "date"),
				"DESCRIPTION" => Array("FIELD_NAME" => "CE.DESCRIPTION", "FIELD_TYPE" => "string"),
				"DT_SKIP_TIME" => Array("FIELD_NAME" => "CE.DT_SKIP_TIME", "FIELD_TYPE" => "string"),
				"DT_LENGTH" => Array("FIELD_NAME" => "CE.DT_LENGTH", "FIELD_TYPE" => "int"),
				"PRIVATE_EVENT" => Array("FIELD_NAME" => "CE.PRIVATE_EVENT", "FIELD_TYPE" => "string"),
				"ACCESSIBILITY" => Array("FIELD_NAME" => "CE.ACCESSIBILITY", "FIELD_TYPE" => "string"),
				"IMPORTANCE" => Array("FIELD_NAME" => "CE.IMPORTANCE", "FIELD_TYPE" => "string"),
				"IS_MEETING" => Array("FIELD_NAME" => "CE.IS_MEETING", "FIELD_TYPE" => "string"),
				"MEETING_HOST" => Array("FIELD_NAME" => "CE.MEETING_HOST", "FIELD_TYPE" => "int"),
				"MEETING_STATUS" => Array("FIELD_NAME" => "CE.MEETING_STATUS", "FIELD_TYPE" => "string"),
				"MEETING" => Array("FIELD_NAME" => "CE.MEETING", "FIELD_TYPE" => "string"),
				"LOCATION" => Array("FIELD_NAME" => "CE.LOCATION", "FIELD_TYPE" => "string"),
				"REMIND" => Array("FIELD_NAME" => "CE.REMIND", "FIELD_TYPE" => "string"),
				"COLOR" => Array("FIELD_NAME" => "CE.COLOR", "FIELD_TYPE" => "string"),
				"RRULE" => Array("FIELD_NAME" => "CE.RRULE", "FIELD_TYPE" => "string"),
				"EXDATE" => Array("FIELD_NAME" => "CE.EXDATE", "FIELD_TYPE" => "string"),
				"ATTENDEES_CODES" => Array("FIELD_NAME" => "CE.ATTENDEES_CODES", "FIELD_TYPE" => "string"),
				"DAV_XML_ID" => Array("FIELD_NAME" => "CE.DAV_XML_ID", "FIELD_TYPE" => "string"), //
				"DAV_EXCH_LABEL" => Array("FIELD_NAME" => "CE.DAV_EXCH_LABEL", "FIELD_TYPE" => "string"), // Exchange sync label
				"G_EVENT_ID" => Array("FIELD_NAME" => "CE.G_EVENT_ID", "FIELD_TYPE" => "string"), // Google event id
				"CAL_DAV_LABEL" => Array("FIELD_NAME" => "CE.CAL_DAV_LABEL", "FIELD_TYPE" => "string"), // CalDAV sync label
				"VERSION" => Array("FIELD_NAME" => "CE.VERSION", "FIELD_TYPE" => "string"), // Version used for outlook sync
				"RECURRENCE_ID" => Array("FIELD_NAME" => "CE.RECURRENCE_ID", "FIELD_TYPE" => "int"),
				"RELATIONS" => Array("FIELD_NAME" => "CE.RELATIONS", "FIELD_TYPE" => "int"),
				"SEARCHABLE_CONTENT" => Array("FIELD_NAME" => "CE.SEARCHABLE_CONTENT", "FIELD_TYPE" => "string"),
				"SECTION_ID" => Array("FIELD_NAME" => "CE.SECTION_ID", "FIELD_TYPE" => "int"),
			);
			CTimeZone::Enable();
		}
		return self::$fields;
	}

	private static function getEventFields(): array
	{
		return [
			'ID',
			'PARENT_ID',
			'DELETED',
			'CAL_TYPE',
			'SYNC_STATUS',
			'OWNER_ID',
			'EVENT_TYPE',
			'CREATED_BY',
			'NAME',
			'DATE_FROM',
			'DATE_TO',
			'TZ_FROM',
			'TZ_TO',
			'ORIGINAL_DATE_FROM',
			'TZ_OFFSET_FROM',
			'TZ_OFFSET_TO',
			'DATE_FROM_TS_UTC',
			'DATE_TO_TS_UTC',
			'TIMESTAMP_X',
			'DATE_CREATE',
			'DESCRIPTION',
			'DT_SKIP_TIME',
			'DT_LENGTH',
			'PRIVATE_EVENT',
			'ACCESSIBILITY',
			'IMPORTANCE',
			'IS_MEETING',
			'MEETING_HOST',
			'MEETING_STATUS',
			'MEETING',
			'LOCATION',
			'REMIND',
			'COLOR',
			'RRULE',
			'EXDATE',
			'ATTENDEES_CODES',
			'DAV_XML_ID',
			'DAV_EXCH_LABEL',
			'G_EVENT_ID',
			'CAL_DAV_LABEL',
			'VERSION',
			'RECURRENCE_ID',
			'RELATIONS',
			'SECTION_ID',
		];
	}

	/**
	 * @deprecated
	 */
	public static function ConnectEventToSection($eventId, $sectionId)
	{
		global $DB;
		$DB->Query(
			"DELETE FROM b_calendar_event_sect WHERE EVENT_ID=". (int)$eventId);

		$DB->Query(
			"INSERT INTO b_calendar_event_sect(EVENT_ID, SECT_ID) ".
			"SELECT ". (int)$eventId .", ID ".
			"FROM b_calendar_section ".
			"WHERE ID=". (int)$sectionId);
	}

	/**
	 * selects all participants of the event by parentId
	 * @param array $entryIdList
	 * @return array
	 */
	public static function getAttendeeList($entryIdList = []): array
	{
		global $DB;
		$attendeeList = [];
		$userIdList = [];

		if (CCalendar::IsSocNet())
		{
			$entryIdList =
				is_array($entryIdList)
					? array_map(
					function($entryId) {return (int)$entryId;} ,
					array_unique($entryIdList)
				)
					: [(int)$entryIdList]
			;

			if (!empty($entryIdList))
			{
				$strSql = "
					SELECT
						CE.OWNER_ID AS USER_ID,
						CE.ID, CE.PARENT_ID, CE.MEETING_STATUS, CE.MEETING_HOST
					FROM
						b_calendar_event CE
					WHERE
						CE.ACTIVE = 'Y' AND
						CE.CAL_TYPE = 'user' AND
						CE.DELETED = 'N' AND
						CE.PARENT_ID in (".implode(',', $entryIdList).")"
				;

				$res = $DB->Query($strSql);

				// for event which stored with external attendees in b_calendar_event_attendee
				$strSqlExternalAttendees = "
					SELECT
						CEA.OWNER_ID AS USER_ID,
						CE.ID,
						CE.PARENT_ID,
						CEA.MEETING_STATUS,
						CE.MEETING_HOST,
						CEA.ID AS ATTENDEE_ID
					FROM
						b_calendar_event CE
					INNER JOIN b_calendar_event_attendee CEA ON CEA.EVENT_ID = CE.ID
					WHERE
						CE.ACTIVE = 'Y' AND
						CE.DELETED = 'N' AND
						CE.PARENT_ID in (".implode(',', $entryIdList).") AND
						(CEA.MEETING_STATUS = 'Y' OR CE.CAL_TYPE != '" . Dictionary::CALENDAR_TYPE['open_event'] . "')"
				;
				$resExternalAttendees = $DB->Query($strSqlExternalAttendees);
				// generator for union-line joining two queries
				$queryCombineGenerator = function ($queryResults): \Generator
				{
					foreach ($queryResults as $queryResult)
					{
						while($item = $queryResult->Fetch())
						{
							yield $item;
						}
					}
				};

				// combine query result in one virtual set
				$combinedQuery = $queryCombineGenerator([$res, $resExternalAttendees]);

				while($entry = $combinedQuery->current())
				{
					$combinedQuery->next();
					$entry['USER_ID'] = (int)$entry['USER_ID'];
					if (!isset($attendeeList[$entry['PARENT_ID']]))
					{
						$attendeeList[$entry['PARENT_ID']] = [];
					}
					$entry["STATUS"] = trim($entry["MEETING_STATUS"]);
					if (
						($entry['PARENT_ID'] === $entry['ID'] || $entry['USER_ID'] === $entry['MEETING_HOST'])
						&& !isset($entry['ATTENDEE_ID'])
					)
					{
						$entry["STATUS"] = "H";
					}
					if (!isset($attendeeList[$entry['PARENT_ID']][$entry['USER_ID']])) {
						$attendeeList[$entry['PARENT_ID']][$entry['USER_ID']] = [
							'id' => $entry['USER_ID'],
							'entryId' => $entry['ID'],
							'status' => $entry["STATUS"],
						];
					}

					if (!in_array($entry['USER_ID'], $userIdList, true))
					{
						$userIdList[] = $entry['USER_ID'];
					}
				}
				// remove doubles attendees from events
				// which stored in both b_calendar_event & b_calendar_event_attendee tables
				$attendeeList = array_map(static fn($itemList) => array_values($itemList), $attendeeList);
			}
		}

		return [
			'attendeeList' => $attendeeList,
			'userIdList' => $userIdList,
		];
	}

	public static function getUsersDetails($userIdList = [], $params = [])
	{
		$users = [];
		$userList = [];
		if ($userIdList)
		{
			$userIdList = array_unique($userIdList);
			$userList = UserTable::getList([
				'select' => [
					'ID',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME',
					'LOGIN',
					'PERSONAL_PHOTO',
					'EMAIL',
					'EXTERNAL_AUTH_ID',
				],
				'filter' => [
					'=ID' => $userIdList,
				],
			]);
		}

		foreach ($userList as $userData)
		{
			$id = (int)$userData['ID'];
			if (!in_array($id, $userIdList))
			{
				continue;
			}

			$users[$userData['ID']] = [
				'ID' => $userData['ID'],
				'DISPLAY_NAME' => CCalendar::GetUserName($userData),
				'URL' => CCalendar::GetUserUrl($userData['ID']),
				'AVATAR' => CCalendar::GetUserAvatarSrc($userData, $params),
				'EMAIL_USER' => $userData['EXTERNAL_AUTH_ID'] === 'email',
				'SHARING_USER' => $userData['EXTERNAL_AUTH_ID'] === Sharing\SharingUser::EXTERNAL_AUTH_ID,
				'COLLAB_USER' => Util::isCollabUser($userData['ID']),
			];
		}

		return $users;
	}

	public static function GetAttendees($eventIdList = [], $checkDeleted = true)
	{
		global $DB;
		$attendees = [];

		if (CCalendar::IsSocNet())
		{
			$eventIdList = is_array($eventIdList) ? array_map('intval', array_unique($eventIdList)) : [(int)$eventIdList];

			if (!empty($eventIdList))
			{
				$deletedCondition = $checkDeleted ? "CE.DELETED = 'N' AND" : '';
				$strSql = "
				SELECT
					CE.OWNER_ID AS USER_ID,
					CE.ID, CE.PARENT_ID, CE.MEETING_STATUS, CE.MEETING_HOST,
					U.LOGIN, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.EMAIL, U.PERSONAL_PHOTO, U.WORK_POSITION, U.EXTERNAL_AUTH_ID,
					BUF.UF_DEPARTMENT
				FROM
					b_calendar_event CE
					LEFT JOIN b_user U ON (U.ID=CE.OWNER_ID)
					LEFT JOIN b_uts_user BUF ON (BUF.VALUE_ID = CE.OWNER_ID)
				WHERE
					CE.ACTIVE = 'Y' AND
					CE.CAL_TYPE = 'user' AND
					{$deletedCondition}
					CE.PARENT_ID in (".implode(',', $eventIdList).")";

				$res = $DB->Query($strSql);
				while($entry = $res->Fetch())
				{
					$parentId = (int)$entry['PARENT_ID'];
					$attendeeId = (int)$entry['USER_ID'];
					if (!isset($attendees[$parentId]))
					{
						$attendees[$parentId] = [];
					}
					$entry["STATUS"] = trim($entry["MEETING_STATUS"]);
					if ($parentId === (int)$entry['ID'])
					{
						$entry["STATUS"] = "H";
					}

					CCalendar::SetUserDepartment($attendeeId, (empty($entry['UF_DEPARTMENT'])
						? []
						: unserialize($entry['UF_DEPARTMENT'], ['allowed_classes' => false])));
					$entry['DISPLAY_NAME'] = CCalendar::GetUserName($entry);
					$entry['URL'] = CCalendar::GetUserUrl($attendeeId);
					$entry['AVATAR'] = CCalendar::GetUserAvatarSrc($entry);
					$entry['EVENT_ID'] = $entry['ID'];

					unset($entry['ID'], $entry['PARENT_ID'], $entry['UF_DEPARTMENT'], $entry['LOGIN']);
					$attendees[$parentId][] = $entry;
				}
			}
		}

		return $attendees;
	}

	public static function GetAttendeeIds(array $eventIdList): array
	{
		$eventIdList = array_map('intval', array_unique($eventIdList));

		if (empty($eventIdList))
		{
			return [];
		}

		$attendeeIds = [];
		$entries = Internals\EventTable::query()
			->setSelect(['PARENT_ID', 'CAL_TYPE', 'OWNER_ID', 'ACTIVE', 'DELETED'])
			->whereIn('PARENT_ID', $eventIdList)
			->where('CAL_TYPE', Dictionary::CALENDAR_TYPE['user'])
			->where('ACTIVE', 'Y')
			->where('DELETED', 'N')
			->exec()
			->fetchAll()
		;

		foreach ($entries as $entry)
		{
			$parentId = $entry['PARENT_ID'];
			$attendeeIds[$parentId] ??= [];
			$attendeeIds[$parentId][] = (int)$entry['OWNER_ID'];
		}

		return $attendeeIds;
	}

	public static function ApplyAccessRestrictions($event, $userId = false)
	{
		$sectId = $event['SECT_ID'];
		if (empty($event['ACCESSIBILITY']))
		{
			$event['ACCESSIBILITY'] = 'busy';
		}

		$private = !empty($event['PRIVATE_EVENT']) && ($event['CAL_TYPE'] ?? null) === 'user';
		$isAttendee = false;

		if (is_array($event['ATTENDEE_LIST'] ?? null))
		{
			foreach($event['ATTENDEE_LIST'] as $attendee)
			{
				if ((int)$attendee['id'] === (int)$userId)
				{
					$isAttendee = true;
					break;
				}
			}
		}

		if (!$userId)
		{
			$userId = CCalendar::GetUserId();
		}


		if (
            ($event['CAL_TYPE'] ?? null) === 'user'
			&& !empty($event['IS_MEETING'])
			&& (int)$event['OWNER_ID'] !== (int)$userId
		)
		{
			if ($isAttendee)
			{
				$sectId = CCalendar::GetMeetingSection($userId);
			}
			elseif (
				isset($event['USER_MEETING']['ATTENDEE_ID'])
				&& $event['USER_MEETING']['ATTENDEE_ID'] !== $userId
			)
			{
				$sectId = CCalendar::GetMeetingSection($event['USER_MEETING']['ATTENDEE_ID']);
				$event['SECT_ID'] = $sectId;
				$event['OWNER_ID'] = $event['USER_MEETING']['ATTENDEE_ID'];
			}
		}

		$accessController = new EventAccessController($userId);
		$eventModel = self::getEventModelForPermissionCheck((int)($event['ID'] ?? 0), $event, $userId);
		$eventModel->setSectionId((int)$sectId);
		$request = [
			ActionDictionary::ACTION_EVENT_VIEW_FULL => [],
			ActionDictionary::ACTION_EVENT_VIEW_TIME => [],
			ActionDictionary::ACTION_EVENT_VIEW_TITLE => [],
		];
		$accessResult = $accessController->batchCheck($request, $eventModel);

		if ($private || (!$accessResult[ActionDictionary::ACTION_EVENT_VIEW_FULL] && !$isAttendee))
		{
			if ($private)
			{
				$event['NAME'] = '['.GetMessage('EC_ACCESSIBILITY_'.mb_strtoupper($event['ACCESSIBILITY'])).']';
				$event['IS_ACCESSIBLE_TO_USER'] = false;
				if (!$accessResult[ActionDictionary::ACTION_EVENT_VIEW_TIME])
				{
					return false;
				}
			}
			else if (!$accessResult[ActionDictionary::ACTION_EVENT_VIEW_TITLE])
			{
				if ($accessResult[ActionDictionary::ACTION_EVENT_VIEW_TIME])
				{
					$event['NAME'] = '['.GetMessage('EC_ACCESSIBILITY_'.mb_strtoupper($event['ACCESSIBILITY'])).']';
					$event['IS_ACCESSIBLE_TO_USER'] = false;
				}
				else
				{
					return false;
				}
			}
			else
			{
				$event['NAME'] .= ' ['.GetMessage('EC_ACCESSIBILITY_'.mb_strtoupper($event['ACCESSIBILITY'])).']';
			}

			// Clear information about
			unset(
				$event['DESCRIPTION'],
				$event['LOCATION'],
				$event['REMIND'],
				$event['USER_MEETING'],
				$event['ATTENDEE_LIST'],
				$event['ATTENDEES_CODES'],
                $event['UF_CRM_CAL_EVENT'],
                $event['UF_WEBDAV_CAL_EVENT'],
			);
		}

		return $event;
	}

	public static function convertDateToCulture(string $str): string
	{
		if (CCalendar::DFormat(false) !== ExcludedDatesCollection::EXCLUDED_DATE_FORMAT)
		{
			if (preg_match_all("/(\d{2})\.(\d{2})\.(\d{4})/", $str, $matches))
			{
				foreach ($matches[0] as $index => $match)
				{
					$newValue = CCalendar::Date(
						mktime(
							0,
							0,
							0,
							$matches[2][$index],
							$matches[1][$index],
							$matches[3][$index]
						),
						false
					);
					$str = str_replace($match, $newValue, $str);
				}
			}
		}

		return $str;
	}

	private static function convertExDatesToInternalFormat(string $exDateString): string
	{
		if (!empty($exDateString))
		{
			$exDates = explode(';', $exDateString);
			$result = [];
			foreach ($exDates as $exDate)
			{
				$result[] = self::convertDateToRecurrenceFormat($exDate);
			}
			$exDateString = implode(';', $result);
		}

		return $exDateString;
	}
	private static function convertRuleUntilToInternalFormat(?string $untilString): ?string
	{
		if (!empty($untilString) && preg_match('/UNTIL=(.+)[;$]/U', $untilString, $matches))
		{
			$internalFormatedDate = self::convertDateToRecurrenceFormat($matches[1]);
			$untilString = str_replace($matches[1], $internalFormatedDate, $untilString);
		}

		return $untilString;
	}

	private static function convertDateToRecurrenceFormat(string $date = ''): string
	{
		if (CCalendar::DFormat(false) !== ExcludedDatesCollection::EXCLUDED_DATE_FORMAT)
		{
			$date = date(
				ExcludedDatesCollection::EXCLUDED_DATE_FORMAT,
				CCalendar::Timestamp($date)
			);
		}

		return $date;
	}

	private static function PreHandleEvent($item, $params = [])
	{
		if (!empty($item['LOCATION']))
		{
			$item['LOCATION'] = trim($item['LOCATION']);
		}

		if (!empty($item['MEETING']))
		{
			$item['MEETING'] = unserialize($item['MEETING'], ['allowed_classes' => false]);

			if (!is_array($item['MEETING']))
			{
				$item['MEETING'] = [];
			}
		}

		if (!empty($item['RELATIONS']))
		{
			$item['RELATIONS'] = unserialize($item['RELATIONS'], ['allowed_classes' => false]);

			if (!is_array($item['RELATIONS']))
			{
				$item['RELATIONS'] = [];
			}
		}

		if (!empty($item['REMIND']))
		{
			$item['REMIND'] = unserialize($item['REMIND'], ['allowed_classes' => false]);

			if (!is_array($item['REMIND']))
			{
				$item['REMIND'] = [];
			}
		}

		if (!empty($item['IS_MEETING']) && !empty($item['MEETING']) && !is_array($item['MEETING']))
		{
			$item['MEETING'] = unserialize($item['MEETING'], ['allowed_classes' => false]);

			if (!is_array($item['MEETING']))
			{
				$item['MEETING'] = [];
			}
		}

		if (self::CheckRecurcion($item))
		{
			$item['EXDATE'] = !empty($item['EXDATE']) ? self::convertDateToCulture($item['EXDATE']) : '';
			$item['RRULE'] = self::ParseRRULE(self::convertDateToCulture($item['RRULE']));
			$item['~RRULE_DESCRIPTION'] = self::GetRRULEDescription($item);
			$tsFrom = CCalendar::Timestamp($item['DATE_FROM']);
			$tsTo = CCalendar::Timestamp($item['DATE_TO']);
			if (($tsTo - $tsFrom) > $item['DT_LENGTH'] + CCalendar::DAY_LENGTH)
			{
				$toTS = $tsFrom + $item['DT_LENGTH'];
				if (isset($item['DT_SKIP_TIME']) && $item['DT_SKIP_TIME'] === 'Y')
				{
					$toTS -= CCalendar::GetDayLen();
				}
				$item['DATE_TO'] = CCalendar::Date($toTS);
			}
		}

		if (!empty($item['ATTENDEES_CODES']) && is_string($item['ATTENDEES_CODES']))
		{
			$item['ATTENDEES_CODES'] = explode(',', $item['ATTENDEES_CODES']);
			$item['attendeesEntityList'] = Util::convertCodesToEntities($item['ATTENDEES_CODES'] ?? null);
		}

		if (
			!empty($item['IS_MEETING'])
			&& (int)$item['ID'] === (int)$item['PARENT_ID']
			&& !($item['CURRENT_ATTENDEE_ID'] ?? null)
		)
		{
			$item['MEETING_STATUS'] = 'H';
		}

		$item['DT_SKIP_TIME'] = ($item['DT_SKIP_TIME'] ?? null) === 'Y' ? 'Y' : 'N';


		if (empty($item['IMPORTANCE']))
		{
			$item['IMPORTANCE'] = 'normal';
		}

		$item['PRIVATE_EVENT'] = trim((string)($item['PRIVATE_EVENT'] ?? null));

		$item['DESCRIPTION'] = trim((string)($item['DESCRIPTION'] ?? null));

		if (!empty($params['parseDescription']))
		{
			$item['~DESCRIPTION'] = self::ParseText(
				$item['DESCRIPTION'],
				!empty($item['PARENT_ID']) ? $item['PARENT_ID'] : $item['ID'],
				$item['UF_WEBDAV_CAL_EVENT'] ?? null
			);
		}

		if (isset($item['UF_CRM_CAL_EVENT']) && is_array($item['UF_CRM_CAL_EVENT']) && empty($item['UF_CRM_CAL_EVENT']))
		{
			$item['UF_CRM_CAL_EVENT'] = '';
		}

		unset($item['SEARCHABLE_CONTENT']);

		return $item;
	}

	public static function CheckRecurcion($event)
	{
		return !empty($event['RRULE']);
	}

	public static function ParseText($text = "", $eventId = 0, $arUFWDValue = [])
	{
		if ($text)
		{
			if (!is_object(self::$TextParser))
			{
				self::$TextParser = new CTextParser();
				self::$TextParser->allow = array(
					"HTML" => "N",
					"ANCHOR" => "Y",
					"BIU" => "Y",
					"IMG" => "Y",
					"QUOTE" => "Y",
					"CODE" => "Y",
					"FONT" => "Y",
					"LIST" => "Y",
					"SMILES" => "Y",
					"NL2BR" => "Y",
					"VIDEO" => "Y",
					"TABLE" => "Y",
					"CUT_ANCHOR" => "N",
					"ALIGN" => "Y",
					"USER" => "Y",
				);
			}

			self::$TextParser->allow["USERFIELDS"] = self::getUFForParseText($eventId, $arUFWDValue);
			$text = self::$TextParser->convertText($text);
			$text = preg_replace("/<br \/>/i", "<br>", $text);
		}
		return $text;
	}

	public static function getUFForParseText($eventId = 0, $arUFWDValue = []): array
	{
		$userFields = self::GetEventUserFields(['ID' => $eventId]);
		$userFields = [
			'UF_WEBDAV_CAL_EVENT' => $userFields['UF_WEBDAV_CAL_EVENT'] ?? [],
		];

		if (empty($arUFWDValue))
		{
			$arUFWDValue = $userFields['UF_WEBDAV_CAL_EVENT']['VALUE'] ?? [];
		}

		$userFields['UF_WEBDAV_CAL_EVENT']['VALUE'] = $arUFWDValue;
		$userFields['UF_WEBDAV_CAL_EVENT']['ENTITY_VALUE_ID'] = $eventId;

		return $userFields;
	}

	public static function ParseRecursion(&$res, $event, $params = [])
	{
		$event['DT_LENGTH'] = (int)$event['DT_LENGTH'];// length in seconds
		$event['RRULE'] = self::ParseRRULE($event['RRULE']);
		$length = $event['DT_LENGTH'];
		$skipTime = $event['DT_SKIP_TIME'] === 'Y';

		$rrule = $event['RRULE'];
		$exDate = self::GetExDate($event['EXDATE'] ?? null);
		$tsFrom = CCalendar::Timestamp($event['DATE_FROM']);
		$tsTo = CCalendar::Timestamp($event['DATE_TO']);

		if (($tsTo - $tsFrom) > $event['DT_LENGTH'] + CCalendar::DAY_LENGTH)
		{
			$toTS = $tsFrom + $length;
			if ($skipTime)
			{
				$toTS -= CCalendar::GetDayLen();
			}

			$event['DATE_TO'] = CCalendar::Date($toTS);
		}

		$h24 = CCalendar::GetDayLen();
		$instanceCount = ($params['instanceCount'] && $params['instanceCount'] > 0) ? $params['instanceCount'] : false;
		$loadLimit = ($params['loadLimit'] && $params['loadLimit'] > 0) ? $params['loadLimit'] : false;

		$preciseLimits = $params['preciseLimits'];

		if ($length < 0) // Protection from infinite recursion
		{
			$length = $h24;
		}

		// Time boundaries
		if (isset($params['fromLimitTs']))
		{
			$limitFromTS = (int)$params['fromLimitTs'];
		}
		else if (!empty($params['fromLimit']))
		{
			$limitFromTS = CCalendar::Timestamp($params['fromLimit']);
		}
		else
		{
			$limitFromTS = CCalendar::Timestamp(CCalendar::GetMinDate());
		}

		if (isset($params['toLimitTs']))
		{
			$limitToTS = (int)$params['toLimitTs'];
		}
		else if (!empty($params['toLimit']))
		{
			$limitToTS = CCalendar::Timestamp($params['toLimit']);
		}
		else
		{
			$limitToTS = CCalendar::Timestamp(CCalendar::GetMaxDate());
		}

		$evFromTS = CCalendar::Timestamp($event['DATE_FROM']);

		$limitFromTS += $event['TZ_OFFSET_FROM'];
		$limitToTS += $event['TZ_OFFSET_TO'] + CCalendar::GetDayLen();
		$limitFromTSReal = $limitFromTS;


		if ($skipTime && $length > CCalendar::GetDayLen())
		{
			$limitFromTSReal += $length - CCalendar::GetDayLen();
		}

		if ($limitFromTS < $event['DATE_FROM_TS_UTC'])
		{
			$limitFromTS = $event['DATE_FROM_TS_UTC'];
		}

		$eventDateToTsUtc = $event['DATE_TO_TS_UTC'];
		if (isset($rrule['COUNT']))
		{
			$eventDateToTsUtc = self::calculateUntilForCountRRule($event);
		}

		if ($limitToTS > $eventDateToTsUtc)
		{
			$limitToTS = $eventDateToTsUtc;
		}

		$fromTS = self::getClosestRepetitionTs($limitFromTS, $evFromTS, $rrule);

		$countOffset = 0;
		if (isset($rrule['COUNT']) && in_array($rrule['FREQ'], ['DAILY', 'MONTHLY', 'YEARLY'], true))
		{
			$lastRepetitionTs = $eventDateToTsUtc;
			if (!$skipTime)
			{
				$lastRepetitionTs -= $length;
			}

			$untilDiff = self::calculateUntilForCountRRule($event, $fromTS - $evFromTS) - $lastRepetitionTs;
			$freqDuration = self::getFreqDuration($rrule['FREQ']);
			$countOffset = (int)($untilDiff / ($freqDuration * $rrule['INTERVAL']));
		}

		if ($skipTime)
		{
			$event['~DATE_FROM'] = CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false);
			$event['~DATE_TO'] = CCalendar::Date(CCalendar::Timestamp($event['DATE_TO']), false);
		}
		else
		{
			$event['~DATE_FROM'] = $event['DATE_FROM'];
			$event['~DATE_TO'] = $event['DATE_TO'];
		}

		$hour = (int)date('H', $fromTS);
		$min = (int)date('i', $fromTS);
		$sec = (int)date('s', $fromTS);

		$orig_d = (int)date('d', $fromTS);
		$orig_m = (int)date('m', $fromTS);
		$orig_y = (int)date('Y', $fromTS);

		$realCount = 0;
		$dispCount = 0;

		while(true)
		{
			$d = (int)date('d', $fromTS);
			$m = (int)date('m', $fromTS);
			$y = (int)date('Y', $fromTS);
			$toTS = mktime($hour, $min, $sec + $length, $m, $d, $y);

			if (
				(isset($rrule['COUNT']) && $rrule['COUNT'] > 0 && ($realCount + $countOffset) >= $rrule['COUNT'])
				|| ($loadLimit && $dispCount >= $loadLimit)
				|| ($fromTS > $limitToTS)
				|| ($instanceCount && $dispCount >= $instanceCount)
				|| (!$fromTS || $fromTS < $evFromTS - CCalendar::GetDayLen()) // Emergency exit (mantis: 56981)
			)
			{
				break;
			}

			// Common handling
			$event['DATE_FROM'] = CCalendar::Date($fromTS, !$skipTime, false);
			$event['DATE_FROM_FORMATTED'] = self::getDateInJsFormat(
				CCalendar::createDateTimeObjectFromString($event['DATE_FROM']),
				$skipTime
			);

			$event['RRULE'] = $rrule;
			$event['RINDEX'] = $realCount > 0 || $countOffset > 0
				? $realCount + $countOffset
				: self::getFirstInstanceIndex($fromTS, $event['~DATE_FROM'])
			;

			$exclude = false;

			if (!empty($exDate))
			{
				$fromDate = CCalendar::Date($fromTS, false);
				$exclude = in_array($fromDate, $exDate, true);
			}

			if ($rrule['FREQ'] === 'WEEKLY')
			{
				$weekDay = CCalendar::WeekDayByInd(date("w", $fromTS));

				if (!empty($rrule['BYDAY'][$weekDay]))
				{
					$realCount++;

					if (($preciseLimits && $toTS >= $limitFromTSReal) || (!$preciseLimits && $toTS > $limitFromTS - $h24))
					{
						if (($event['DT_SKIP_TIME'] ?? null) === 'Y')
						{
							$toTS -= CCalendar::GetDayLen();
						}

						$event['DATE_TO'] = CCalendar::Date($toTS - ($event['TZ_OFFSET_FROM'] - $event['TZ_OFFSET_TO']), !$skipTime, false);
						$event['DATE_TO_FORMATTED'] = self::getDateInJsFormat(
							CCalendar::createDateTimeObjectFromString($event['DATE_TO']),
							$skipTime
						);

						if (!$exclude)
						{
							self::HandleEvent($res, $event, $params['userId']);
							$dispCount++;
						}
					}
				}

				if (isset($weekDay) && $weekDay === 'SU')
				{
					$delta = ($rrule['INTERVAL'] - 1) * 7 + 1;
				}
				else
				{
					$delta = 1;
				}

				$fromTS = mktime($hour, $min, $sec, $m, $d + $delta, $y);
			}
			else // DAILY, MONTHLY, YEARLY
			{
				$realCount++;

				if (($event['DT_SKIP_TIME'] ?? null) === 'Y')
				{
					$toTS -= CCalendar::GetDayLen();
				}

				if (
					($preciseLimits && $toTS >= $limitFromTSReal)
					|| (!$preciseLimits && $toTS > $limitFromTS - $h24)
				)
				{
					$event['DATE_TO'] = CCalendar::Date($toTS - ($event['TZ_OFFSET_FROM'] - $event['TZ_OFFSET_TO']), !$skipTime, false);
					$event['DATE_TO_FORMATTED'] = self::getDateInJsFormat(
						CCalendar::createDateTimeObjectFromString($event['DATE_TO']),
						$skipTime
					);

					//$event['DATE_TO'] = CCalendar::Date($toTS, !$skipTime, false);
					if (!$exclude)
					{
						self::HandleEvent($res, $event, $params['userId']);
						$dispCount++;
					}
				}

				switch ($rrule['FREQ'])
				{
					case 'DAILY':
						$fromTS = mktime($hour, $min, $sec, $m, $d + $rrule['INTERVAL'], $y);
						break;
					case 'MONTHLY':
						$durOffset = $realCount * $rrule['INTERVAL'];

						$day = $orig_d;
						$month = $orig_m + $durOffset;
						$year = $orig_y;

						if ($month > 12)
						{
							$delta_y = floor($month / 12);
							$delta_m = $month - $delta_y * 12;

							$month = $delta_m;
							$year = $orig_y + $delta_y;
						}

						// 1. Check only for 29-31 dates. 2.We are out of range in this month
						if ($orig_d > 28 && $orig_d > date("t", mktime($hour, $min, $sec, $month, 1, $year)))
						{
							$month++;
							$day = 1;
						}

						$fromTS = mktime($hour, $min, $sec, $month, $day, $year);
						break;
					case 'YEARLY':
						$fromTS = mktime($hour, $min, $sec, $orig_m, $orig_d, $y + $rrule['INTERVAL']);
						break;
				}
			}
		}
	}

	private static function getFirstInstanceIndex($fromTs, $eventFromDate): int
	{
		$eventTs = (int)CCalendar::Timestamp($eventFromDate);

		return (($fromTs - $eventTs) / 86400) > 1 ? 1 : 0;
	}

	public static function getClosestRepetitionTs(int $limitFromTS, int $evFromTS, array $rrule): int
	{
		$interval = (int)$rrule['INTERVAL'];

		$hour = (int)date('H', $evFromTS);
		$min = (int)date('i', $evFromTS);
		$sec = (int)date('s', $evFromTS);

		$orig_d = (int)date('d', $evFromTS);
		$orig_m = (int)date('m', $evFromTS);
		$orig_y = (int)date('Y', $evFromTS);

		$closestDateTs = $limitFromTS;

		$freqDuration = self::getFreqDuration($rrule['FREQ']);

		if ($rrule['FREQ'] === 'DAILY')
		{
			$dayDifference = round(($limitFromTS - $evFromTS) / $freqDuration);
			$dayDifference -= $dayDifference % $interval;
			$closestDateTs = mktime($hour, $min, $sec, $orig_m, $orig_d + $dayDifference, $orig_y);
		}

		if ($rrule['FREQ'] === 'MONTHLY')
		{
			$monthDifference = round(($limitFromTS - $evFromTS) / $freqDuration);
			$monthDifference -= $monthDifference % $interval;
			$closestDateTs = mktime($hour, $min, $sec, $orig_m + $monthDifference, $orig_d, $orig_y);
		}

		if ($rrule['FREQ'] === 'YEARLY')
		{
			$yearDifference = round(($limitFromTS - $evFromTS) / $freqDuration);
			$yearDifference -= $yearDifference % $interval;
			$closestDateTs = mktime($hour, $min, $sec, $orig_m, $orig_d, $orig_y + $yearDifference);
		}

		if ($rrule['FREQ'] === 'WEEKLY')
		{
			$dayDifference = round(($limitFromTS - $evFromTS) / $freqDuration);
			$count = round($dayDifference / (count($rrule['BYDAY']) * 7 * $interval) + 1);
			$daysCount = round(($count - 1) / count($rrule['BYDAY']) * 7 * $interval);
			$closestDateTs = mktime($hour, $min, $sec, $orig_m, $orig_d + $daysCount, $orig_y);
		}

		$closestRepetitionDate = (int)date('d', $closestDateTs);
		$closestRepetitionMonth = (int)date('m', $closestDateTs);
		$closestRepetitionYear = (int)date('Y', $closestDateTs);

		return mktime($hour, $min, $sec, $closestRepetitionMonth, $closestRepetitionDate, $closestRepetitionYear);
	}

	public static function getFreqDuration(string $freq): int
	{
		if ($freq === 'DAILY' || $freq === 'WEEKLY')
		{
			return 60 * 60 * 24;
		}

		if ($freq === 'MONTHLY')
		{
			return 60 * 60 * 24 * 30;
		}

		if ($freq === 'YEARLY')
		{
			return 60 * 60 * 24 * 365;
		}

		return 0;
	}

	public static function ParseRRULE($rule = null)
	{
		$res = [];
		if (!$rule)
		{
			return $res;
		}

		if (is_array($rule))
		{
			return isset($rule['FREQ'])
				? $rule
				: $res;
		}

		$arRule = explode(";", $rule);
		if (!is_array($arRule))
		{
			return $res;
		}

		foreach($arRule as $par)
		{
			$arPar = explode("=", $par);
			if (!empty($arPar[0]))
			{
				switch($arPar[0])
				{
					case 'FREQ':
						if (in_array($arPar[1], ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY']))
						{
							$res['FREQ'] = $arPar[1];
						}

						break;
					case 'COUNT':
					case 'INTERVAL':
						if ((int)$arPar[1] > 0)
						{
							$res[$arPar[0]] = (int)$arPar[1];
						}

						break;
					case 'UNTIL':
						if (
							CCalendar::DFormat(false) !== ExcludedDatesCollection::EXCLUDED_DATE_FORMAT
							&& $arPar[1][2] === '.'
							&& $arPar[1][5] === '.'
						)
						{
							$arPar[1] = self::convertDateToCulture($arPar[1]);
						}
						$res['UNTIL'] = CCalendar::Timestamp($arPar[1])
							? $arPar[1]
							: CCalendar::Date((int)$arPar[1], false, false)
						;

						break;
					case 'BYDAY':
						$res[$arPar[0]] = [];
						foreach(explode(',', $arPar[1]) as $day)
						{
							$matches = [];
							if (preg_match('/((\-|\+)?\d+)?(MO|TU|WE|TH|FR|SA|SU)/', $day, $matches))
							{
								$res[$arPar[0]][$matches[3]] =
									$matches[1] === ''
										? $matches[3]
										: $matches[1];
							}
						}
						if (empty($res[$arPar[0]]))
						{
							unset($res[$arPar[0]]);
						}

						break;
					case 'BYMONTHDAY':
						$res[$arPar[0]] = [];
						foreach(explode(',', $arPar[1]) as $day)
						{
							if (abs($day) > 0 && abs($day) <= 31)
							{
								$res[$arPar[0]][(int)$day] = (int)$day;
							}
						}
						if (empty($res[$arPar[0]]))
						{
							unset($res[$arPar[0]]);
						}

						break;
					case 'BYYEARDAY':
					case 'BYSETPOS':
						$res[$arPar[0]] = [];
						foreach(explode(',', $arPar[1]) as $day)
						{
							if (abs($day) > 0 && abs($day) <= 366)
							{
								$res[$arPar[0]][(int)$day] = (int)$day;
							}
						}
						if (empty($res[$arPar[0]]))
						{
							unset($res[$arPar[0]]);
						}

						break;
					case 'BYWEEKNO':
						$res[$arPar[0]] = [];
						foreach(explode(',', $arPar[1]) as $day)
						{
							if (abs($day) > 0 && abs($day) <= 53)
							{
								$res[$arPar[0]][(int)$day] = (int)$day;
							}
						}
						if (empty($res[$arPar[0]]))
						{
							unset($res[$arPar[0]]);
						}

						break;
					case 'BYMONTH':
						$res[$arPar[0]] = [];
						foreach(explode(',', $arPar[1]) as $m)
						{
							if ($m > 0 && $m <= 12)
							{
								$res[$arPar[0]][(int)$m] = (int)$m;
							}
						}
						if (empty($res[$arPar[0]]))
						{
							unset($res[$arPar[0]]);
						}

						break;
				}
			}
		}

		if (
			$res['FREQ'] === 'WEEKLY'
			&& (
				empty($res['BYDAY'])
				|| !is_array($res['BYDAY'])
			)
		)
		{
			$res['BYDAY'] = ['MO' => 'MO'];
		}

		if ($res['FREQ'] !== 'WEEKLY' && isset($res['BYDAY']))
		{
			unset($res['BYDAY']);
		}

		$res['INTERVAL'] = (int)($res['INTERVAL'] ?? null);
		if ($res['INTERVAL'] <= 1)
		{
			$res['INTERVAL'] = 1;
		}

		$res['~UNTIL'] = $res['UNTIL'] ?? null;
		if (($res['UNTIL'] ?? null) === CCalendar::GetMaxDate())
		{
			$res['~UNTIL'] = '';
		}

		$res['UNTIL_TS'] = CCalendar::TimestampUTC($res['UNTIL']);

		return $res;
	}

	/**
	 * @param $exDate
	 * @return array|false|string[]
	 */
	public static function GetExDate($exDate = '')
	{
		$result = [];
		if (is_string($exDate))
		{
			$result = $exDate === '' ? [] : explode(';', $exDate);
		}

		return $result ?: [];
	}

	private static function HandleEvent(&$res, $event = [], $userId = null)
	{
		$userId = $userId ?: CCalendar::GetCurUserId();

		$res[] = self::calculateUserOffset($userId, $event);
	}

	private static function calculateUserOffset($userId, $event = [])
	{
		if (($event['DT_SKIP_TIME'] ?? null) === 'N')
		{
			$currentUserTimezone = \CCalendar::GetUserTimezoneName($userId);

			$fromTs = \CCalendar::Timestamp($event['DATE_FROM']);
			$toTs = $fromTs + ($event['DT_LENGTH'] ?? null);

			$event['~USER_OFFSET_FROM'] = CCalendar::GetTimezoneOffset(($event['TZ_FROM'] ?? null), $fromTs)
				- \CCalendar::GetTimezoneOffset($currentUserTimezone, $fromTs);

			$event['~USER_OFFSET_TO'] = CCalendar::GetTimezoneOffset(($event['TZ_TO'] ?? null), $toTs)
				- \CCalendar::GetTimezoneOffset($currentUserTimezone, $toTs);
		}
		else
		{
			$event['~USER_OFFSET_FROM'] = 0;
			$event['~USER_OFFSET_TO'] = 0;
		}

		return $event;
	}

	public static function CheckFields(&$arFields, $currentEvent = [], $userId = false)
	{
		$arFields['ID'] = (int)($arFields['ID'] ?? null);
		$arFields['PARENT_ID'] = (int)($arFields['PARENT_ID'] ?? 0);
		$arFields['OWNER_ID'] = (int)($arFields['OWNER_ID'] ?? 0);

		if (!isset($arFields['TIMESTAMP_X']))
		{
			$arFields['TIMESTAMP_X'] = CCalendar::Date(time(), true, false);
		}

		if (!$userId)
		{
			$userId = CCalendar::GetCurUserId();
		}

		if (!isset($arFields['DT_SKIP_TIME']) && isset($currentEvent['DT_SKIP_TIME']))
		{
			$arFields['DT_SKIP_TIME'] = $currentEvent['DT_SKIP_TIME'];
		}
		if (!isset($arFields['DATE_FROM']) && isset($currentEvent['DATE_FROM']))
		{
			$arFields['DATE_FROM'] = $currentEvent['DATE_FROM'];
		}
		if (!isset($arFields['DATE_TO']) && isset($currentEvent['DATE_TO']))
		{
			$arFields['DATE_TO'] = $currentEvent['DATE_TO'];
		}

		$isNewEvent = !isset($arFields['ID']) || $arFields['ID'] <= 0;
		if (!isset($arFields['DATE_CREATE']) && $isNewEvent)
		{
			$arFields['DATE_CREATE'] = $arFields['TIMESTAMP_X'];
		}

		// Skip time
		if (isset($arFields['SKIP_TIME']))
		{
			$arFields['DT_SKIP_TIME'] = $arFields['SKIP_TIME'] ? 'Y' : 'N';
			unset($arFields['SKIP_TIME']);
		}
		elseif (isset($arFields['DT_SKIP_TIME']) && $arFields['DT_SKIP_TIME'] !== 'Y' && $arFields['DT_SKIP_TIME'] !== 'N')
		{
			unset($arFields['DT_SKIP_TIME']);
		}

		unset($arFields['DT_FROM'], $arFields['DT_TO']);

		$arFields['DT_SKIP_TIME'] = ($arFields['DT_SKIP_TIME'] ?? null) !== 'Y' ? 'N' : 'Y';
		$fromTs = CCalendar::Timestamp($arFields['DATE_FROM'], false, $arFields['DT_SKIP_TIME'] !== 'Y');
		$toTs = CCalendar::Timestamp($arFields['DATE_TO'], false, $arFields['DT_SKIP_TIME'] !== 'Y');

		$arFields['DATE_FROM'] = CCalendar::Date($fromTs);
		$arFields['DATE_TO'] = CCalendar::Date($toTs);

		if (!$fromTs)
		{
			$arFields['DATE_FROM'] = FormatDate("SHORT", time());
			$fromTs = CCalendar::Timestamp($arFields['DATE_FROM'], false, false);
			if (!$toTs)
			{
				$arFields['DATE_TO'] = $arFields['DATE_FROM'];
				$toTs = $fromTs;
				$arFields['DT_SKIP_TIME'] = 'Y';
			}
		}
		elseif (!$toTs)
		{
			$arFields['DATE_TO'] = $arFields['DATE_FROM'];
			$toTs = $fromTs;
		}

		if (($arFields['DT_SKIP_TIME'] ?? null) !== 'Y')
		{
			$arFields['DT_SKIP_TIME'] = 'N';
			if (!isset($arFields['TZ_FROM']) && isset($currentEvent['TZ_FROM']))
			{
				$arFields['TZ_FROM'] = $currentEvent['TZ_FROM'];
			}
			if (!isset($arFields['TZ_TO']) && isset($currentEvent['TZ_TO']))
			{
				$arFields['TZ_TO'] = $currentEvent['TZ_TO'];
			}

			if (!isset($arFields['TZ_FROM']) && !isset($arFields['TZ_TO']))
			{
				$userTimezoneName = CCalendar::GetUserTimezoneName($userId, true);
				$arFields['TZ_FROM'] = $userTimezoneName;
				$arFields['TZ_TO'] = $userTimezoneName;
			}

			if (!isset($arFields['TZ_OFFSET_FROM']))
			{
				$arFields['TZ_OFFSET_FROM'] = CCalendar::GetTimezoneOffset($arFields['TZ_FROM'], $fromTs);
			}
			if (!isset($arFields['TZ_OFFSET_TO']))
			{
				$arFields['TZ_OFFSET_TO'] = CCalendar::GetTimezoneOffset($arFields['TZ_TO'], $toTs);
			}
		}

		if (!isset($arFields['TZ_OFFSET_FROM']))
		{
			$arFields['TZ_OFFSET_FROM'] = 0;
		}
		if (!isset($arFields['TZ_OFFSET_TO']))
		{
			$arFields['TZ_OFFSET_TO'] = 0;
		}

		if (!isset($arFields['DATE_FROM_TS_UTC']))
		{
			$arFields['DATE_FROM_TS_UTC'] = $fromTs - $arFields['TZ_OFFSET_FROM'];
		}
		if (!isset($arFields['DATE_TO_TS_UTC']))
		{
			$arFields['DATE_TO_TS_UTC'] = $toTs - $arFields['TZ_OFFSET_TO'];
		}

		if ($arFields['DATE_FROM_TS_UTC'] > $arFields['DATE_TO_TS_UTC'])
		{
			$arFields['DATE_TO'] = $arFields['DATE_FROM'];
			$arFields['DATE_TO_TS_UTC'] = $arFields['DATE_FROM_TS_UTC'];
			$arFields['TZ_OFFSET_TO'] = $arFields['TZ_OFFSET_FROM'];
			$arFields['TZ_TO'] = $arFields['TZ_FROM'];
		}

		$h24 = 60 * 60 * 24; // 24 hours
		if (($arFields['DT_SKIP_TIME'] ?? null) === 'Y')
		{
			unset($arFields['TZ_FROM'], $arFields['TZ_TO'], $arFields['TZ_OFFSET_FROM'], $arFields['TZ_OFFSET_TO']);
		}

		// Event length in seconds
		if ((int)$fromTs === (int)$toTs && date('H:i', $fromTs) === '00:00' && $arFields['DT_SKIP_TIME'] === 'Y') // One day
		{
			$arFields['DT_LENGTH'] = $h24;
		}
		else
		{
			$arFields['DT_LENGTH'] = (int)($arFields['DATE_TO_TS_UTC'] - $arFields['DATE_FROM_TS_UTC']);
			if (($arFields['DT_SKIP_TIME'] ?? null) === "Y") // We have dates without times
			{
				$arFields['DT_LENGTH'] += $h24;
			}
		}

		if (empty($arFields['VERSION']))
		{
			$arFields['VERSION'] = 1;
		}

		// Accessibility
		$arFields['ACCESSIBILITY'] = mb_strtolower(trim($arFields['ACCESSIBILITY'] ?? ''));
		if (!in_array($arFields['ACCESSIBILITY'], ['busy', 'quest', 'free', 'absent'], true))
		{
			$arFields['ACCESSIBILITY'] = 'busy';
		}

		// Importance
		$arFields['IMPORTANCE'] = mb_strtolower(trim($arFields['IMPORTANCE'] ?? ''));
		if (!in_array($arFields['IMPORTANCE'], ['high', 'normal', 'low']))
		{
			$arFields['IMPORTANCE'] = 'normal';
		}

		// Color
		$arFields['COLOR'] = CCalendar::Color($arFields['COLOR'] ?? null, false);

		// Section
		if (
            isset($arFields['SECTIONS'])
            && !is_array($arFields['SECTIONS'])
            && (int)$arFields['SECTIONS'] > 0
        )
		{
			$arFields['SECTIONS'] = (array)((int)($arFields['SECTIONS'] ?? null));
		}

		self::checkRecurringRuleField($arFields, $toTs, ($currentEvent['EXDATE'] ?? null));

		// Location
		if (!isset($arFields['LOCATION']) || !is_array($arFields['LOCATION']))
		{
			$arFields['LOCATION'] = [
                "NEW" => is_string($arFields['LOCATION'] ?? null)
                    ? $arFields['LOCATION']
                    : "",
            ];
		}

		// Private
		$arFields['PRIVATE_EVENT'] = isset($arFields['PRIVATE_EVENT']) && $arFields['PRIVATE_EVENT'];

		return true;
	}

	public static function CheckEntryChanges($newFields = [], $currentFields = [])
	{
		$changes = [];
		$fieldList = [
			'NAME',
			'DATE_FROM',
			'DATE_TO',
			'RRULE',
			'DESCRIPTION',
			'LOCATION',
			'IMPORTANCE',
		];

		foreach ($fieldList as $fieldKey)
		{
			if ($fieldKey === 'LOCATION')
			{
				if (
					is_array($newFields[$fieldKey] ?? null)
					&& ($newFields[$fieldKey]['NEW'] ?? null) !== ($currentFields[$fieldKey] ?? null)
					&& (CCalendar::GetTextLocation($newFields[$fieldKey]['NEW'] ?? '')) !== (CCalendar::GetTextLocation($currentFields[$fieldKey] ?? ''))
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey] ?? null,
						'newValue' => $newFields[$fieldKey]['NEW'] ?? null,
					];
				}
				else if (
					!is_array($newFields[$fieldKey] ?? null)
					&& ($newFields[$fieldKey] ?? null) !== ($currentFields[$fieldKey] ?? null)
					&& (CCalendar::GetTextLocation($newFields[$fieldKey] ?? '')) !== (CCalendar::GetTextLocation($currentFields[$fieldKey] ?? ''))
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey],
					];
				}
			}
			else if ($fieldKey === 'DATE_FROM')
			{
				if (
					$newFields[$fieldKey] !== $currentFields[$fieldKey]
					|| ($newFields['TZ_FROM'] ?? null) !== ($currentFields['TZ_FROM'] ?? null)
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey],
					];
				}
			}
			else if ($fieldKey === 'DATE_TO')
			{
				if (
					(
						$newFields['DATE_FROM'] === $currentFields['DATE_FROM']
						&& ($newFields['TZ_FROM'] ?? null) === ($currentFields['TZ_FROM'] ?? null)
					)
					&&
					(
						$newFields[$fieldKey] !== $currentFields[$fieldKey]
						|| ($newFields['TZ_TO'] ?? null) !== ($currentFields['TZ_TO'] ?? null)
					)
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey],
					];
				}
			}
			else if ($fieldKey === 'IMPORTANCE')
			{
				if (
					$newFields[$fieldKey] !== $currentFields[$fieldKey]
					&& $newFields[$fieldKey] === 'high'
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey],
					];
				}
			}
			else if ($fieldKey === 'DESCRIPTION')
			{
				if (mb_strtolower(trim($newFields[$fieldKey])) !== mb_strtolower(trim($currentFields[$fieldKey])))
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey],
					];
				}
			}
			else if ($fieldKey === 'RRULE')
			{
				$newRule = self::ParseRRULE($newFields[$fieldKey] ?? null);
				$oldRule = self::ParseRRULE($currentFields[$fieldKey] ?? null);

				if (
                    (($newRule['FREQ'] ?? null) !== ($oldRule['FREQ'] ?? null))
					|| (($newRule['INTERVAL'] ?? null) !== ($oldRule['INTERVAL'] ?? null))
					|| (($newRule['BYDAY'] ?? null) !== ($oldRule['BYDAY'] ?? null))
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $oldRule,
						'newValue' => $newRule,
					];
				}
			}

			else if (($newFields[$fieldKey] ?? null) !== ($currentFields[$fieldKey] ?? null))
			{
				$changes[] = [
					'fieldKey' => $fieldKey,
					'oldValue' => $currentFields[$fieldKey],
					'newValue' => $newFields[$fieldKey],
				];
			}
		}

		if (is_array($newFields['ATTENDEES_CODES']) && is_array($currentFields['ATTENDEES_CODES'])
			&& (count(array_diff($newFields['ATTENDEES_CODES'], $currentFields['ATTENDEES_CODES']))
				|| count(array_diff($currentFields['ATTENDEES_CODES'], $newFields['ATTENDEES_CODES'])))
		)
		{
			$changes[] = [
				'fieldKey' => 'ATTENDEES',
				'oldValue' => $currentFields['ATTENDEES_CODES'],
				'newValue' => $newFields['ATTENDEES_CODES'],
			];
		}

		return $changes;
	}

	private static function PackRRule($RRule = [])
	{
		$strRes = "";
		if (is_array($RRule))
		{
			foreach($RRule as $key => $val)
			{
				$strRes .= $key . '=' . $val . ';';
			}
		}

		return trim($strRes, ', ');
	}

	//

	/**
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private static function CreateChildEvents($parentId, $arFields, $params, $changeFields)
	{
		global $CACHE_MANAGER;
		$parentId = (int)$parentId;
		$isNewEvent = !isset($arFields['ID']) || $arFields['ID'] <= 0;
		$chatId = (int) ($arFields['~MEETING']['CHAT_ID'] ?? null) ;
		$involvedAttendees = []; // List of all attendees to invite or to exclude from event
		$isCalDavEnabled = CCalendar::IsCalDAVEnabled();
		$isPastEvent = ($arFields['DATE_TO_TS_UTC'] ?? null) < (time() - (int)date('Z'));

		$userId = $params['userId'];
		$attendees = is_array($arFields['ATTENDEES'] ?? null) ? $arFields['ATTENDEES'] : []; // List of attendees for event
		$chat = null;
		$eventWithEmailGuestEnabled = Bitrix24Manager::isFeatureEnabled(FeatureDictionary::CALENDAR_EVENTS_WITH_EMAIL_GUESTS);

		unset($params['dontSyncParent']);

		if ($chatId > 0 && Loader::includeModule('im'))
		{
			$chat = new \CIMChat($userId);
		}

		if (empty($attendees) && !($arFields['CAL_TYPE'] === 'user' && $arFields['OWNER_ID'] === $userId))
		{
			$attendees[] = (int)$arFields['CREATED_BY'];
		}

		foreach($attendees as $userKey)
		{
			$involvedAttendees[] = (int)$userKey;
		}

		$currentAttendeesIndex = [];
		$deletedAttendees = [];
		$affectedEventIds = [$parentId];
		if (!$isNewEvent)
		{
			$curAttendees = self::GetAttendees($parentId);
			$curAttendees = is_array(($curAttendees[$parentId] ?? null)) ? $curAttendees[$parentId] : [];
			foreach($curAttendees as $user)
			{
				$currentAttendeesIndex[$user['USER_ID']] = $user;
				if (
					(int)$user['USER_ID'] !== $arFields['MEETING_HOST']
					&& (
						(int)$user['USER_ID'] !== (int)$arFields['OWNER_ID']
						|| $arFields['CAL_TYPE'] !== 'user'
					)
				)
				{
					$deletedAttendees[$user['USER_ID']] = (int)$user['USER_ID'];
					$involvedAttendees[] = (int)$user['USER_ID'];
				}
			}
		}
		$involvedAttendees = array_unique($involvedAttendees);
		$userIndex = self::generateUserIndex($involvedAttendees, $arFields['MEETING_HOST']);

		foreach($attendees as $userKey)
		{
			$clonedParams = $params;
			$attendeeId = (int)$userKey;
			$isNewAttendee = !empty($clonedParams['currentEvent']['ATTENDEE_LIST'])
				&& is_array($clonedParams['currentEvent']['ATTENDEE_LIST'])
				&& self::isNewAttendee($clonedParams['currentEvent']['ATTENDEE_LIST'], $attendeeId)
			;
			$CACHE_MANAGER->ClearByTag('calendar_user_'.$attendeeId);

			// Skip creation of child event if it's event inside his own user calendar
			if (
				$attendeeId
				&& (($arFields['CAL_TYPE'] ?? null) !== 'user' || (int)($arFields['OWNER_ID'] ?? null) !== $attendeeId)
			)
			{
				$childParams = $clonedParams;
				$childParams['arFields']['CAL_TYPE'] = 'user';
				$childParams['arFields']['PARENT_ID'] = $parentId;
				$childParams['arFields']['OWNER_ID'] = $attendeeId;
				$childParams['arFields']['CREATED_BY'] = $attendeeId;
				$childParams['arFields']['CREATED'] = $arFields['DATE_CREATE'] ?? null;
				$childParams['arFields']['MODIFIED'] = $arFields['TIMESTAMP_X'] ?? null;
				$childParams['arFields']['ACCESSIBILITY'] = $arFields['ACCESSIBILITY'] ?? null;
				$childParams['arFields']['MEETING'] = $arFields['~MEETING'] ?? null;
				$childParams['arFields']['TEXT_LOCATION'] = CCalendar::GetTextLocation($arFields["LOCATION"] ?? null);
				$childParams['arFields']['MEETING_STATUS'] = 'Q';
				$childParams['arFields']['EVENT_TYPE'] = $arFields['EVENT_TYPE'] ?? null;
				$childParams['sendInvitations'] = $clonedParams['sendInvitations'] ?? null;

				if ((int)$arFields['CREATED_BY'] === $attendeeId)
				{
					$childParams['arFields']['MEETING_STATUS'] = 'Y';
				}
				elseif ($isNewEvent && (int)($arFields['~MEETING']['MEETING_CREATOR'] ?? null) === $attendeeId)
				{
					$childParams['arFields']['MEETING_STATUS'] = 'Y';
				}
				elseif (
					!empty($clonedParams['saveAttendeesStatus'])
					&& !empty($clonedParams['currentEvent']['ATTENDEE_LIST'])
					&& is_array($clonedParams['currentEvent']['ATTENDEE_LIST'])
				)
				{
					foreach($clonedParams['currentEvent']['ATTENDEE_LIST'] as $currentAttendee)
					{
						if ((int)$currentAttendee['id'] === $attendeeId)
						{
							$childParams['arFields']['MEETING_STATUS'] = $currentAttendee['status'];
							break;
						}
					}
				}
				else
				{
					$childParams['arFields']['MEETING_STATUS'] = 'Q';
				}

				unset(
					$childParams['arFields']['SECTIONS'],
					$childParams['arFields']['SECTION_ID'],
					$childParams['currentEvent'],
					$childParams['updateReminders'],
					$childParams['arFields']['ID'],
					$childParams['arFields']['DAV_XML_ID'],
					$childParams['arFields']['G_EVENT_ID'],
					$childParams['arFields']['SYNC_STATUS']
				);

				$isExchangeEnabled = CCalendar::IsExchangeEnabled($attendeeId);

				if (
					isset($userIndex[$attendeeId])
					&& $userIndex[$attendeeId]['EXTERNAL_AUTH_ID'] === 'email'
					&& $isNewEvent
					&& !$eventWithEmailGuestEnabled
				)
				{
					// Just skip external emil users if they are not allowed
					// We will show warning on the client's side
					continue;
				}

				if (!empty($currentAttendeesIndex[$attendeeId]))
				{
					$childParams['arFields']['ID'] = $currentAttendeesIndex[$attendeeId]['EVENT_ID'];

					if (empty($arFields['~MEETING']['REINVITE']))
					{
						$childParams['arFields']['MEETING_STATUS'] = $currentAttendeesIndex[$attendeeId]['STATUS'];

						$childParams['sendInvitations'] = $childParams['sendInvitations'] &&  $currentAttendeesIndex[$attendeeId]['STATUS'] !== 'Q';
					}

					if (
						$clonedParams['sendInvitesToDeclined']
						&& $childParams['arFields']['MEETING_STATUS'] === 'N'
					)
					{
						$childParams['arFields']['MEETING_STATUS'] = 'Q';
						$childParams['sendInvitations'] = true;
					}

					if (
						($isExchangeEnabled || $isCalDavEnabled)
						&& ($childParams['overSaving'] ?? false) !== true
					)
					{
						self::prepareArFieldBeforeSyncEvent($childParams);
						$childParams['currentEvent'] = self::GetById($childParams['arFields']['ID'], false);

						$davParams = [
							'bCalDav' => $isCalDavEnabled,
							'bExchange' => $isExchangeEnabled,
							'sectionId' => (int)$childParams['currentEvent']['SECTION_ID'],
							'modeSync' => $clonedParams['modeSync'],
							'editInstance' => $clonedParams['editInstance'],
							'originalDavXmlId' => $childParams['currentEvent']['G_EVENT_ID'],
							'instanceTz' => $childParams['currentEvent']['TZ_FROM'],
							'editParentEvents' => $clonedParams['editParentEvents'],
							'editNextEvents' => $clonedParams['editNextEvents'],
							'syncCaldav' => $clonedParams['syncCaldav'],
							'parentDateFrom' => $childParams['currentEvent']['DATE_FROM'],
							'parentDateTo' => $childParams['currentEvent']['DATE_TO'],
						];
						CCalendarSync::DoSaveToDav($childParams['arFields'], $davParams, $childParams['currentEvent']);
					}
				}
				else
				{
					$childSectId = CCalendar::GetMeetingSection($attendeeId, true);
					if ($childSectId)
					{
						$childParams['arFields']['SECTIONS'] = [$childSectId];
					}

					if (!$clonedParams['editInstance'])
					{
						$childParams['arFields']['DAV_XML_ID'] = self::getUidForChildEvent($childParams['arFields']);
					}

					$parentEvent = [];
					if (!empty($childParams['arFields']['RECURRENCE_ID']))
					{
						$parentEvent = Internals\EventTable::query()
							->where('PARENT_ID' , (int)$childParams['arFields']['RECURRENCE_ID'])
							->where('OWNER_ID' , (int)($childParams['arFields']['OWNER_ID'] ?? 0))
							->setSelect([
								'DAV_XML_ID',
								'TZ_FROM',
								'DATE_FROM',
								'DATE_TO',
							])
							->setLimit(1)
							->exec()
							->fetch() ?: [];
					}

					if ($parentEvent)
					{
						$childParams['arFields']['DAV_XML_ID'] = $parentEvent['DAV_XML_ID'] ?? null;
					}
					else
					{
						unset(
							$childParams['arFields']['ORIGINAL_DATE_FROM'],
							$childParams['arFields']['RECURRENCE_ID'],
							$clonedParams['recursionEditMode']
						);

						$childParams['arFields']['DAV_XML_ID'] = UidGenerator::createInstance()
							->setPortalName(Util::getServerName())
							->setDate(new Date(Util::getDateObject(
								$childParams['arFields']['DATE_FROM'] ?? null,
								false,
								($childParams['arFields']['TZ_FROM'] ?? null) ?: null
							)))
							->setUserId((int)($childParams['arFields']['OWNER_ID'] ?? null))
							->getUidWithDate();
					}


					// CalDav & Exchange
					if (
						($isExchangeEnabled || $isCalDavEnabled)
						&& ($childParams['overSaving'] ?? false) !== true
					)
					{
						$davParams = [
							'bCalDav' => $isCalDavEnabled,
							'bExchange' => $isExchangeEnabled,
							'sectionId' => $childSectId,
							'modeSync' => $clonedParams['modeSync'] ?? null,
							'editInstance' => $clonedParams['editInstance'] ?? null,
							'instanceTz' => $parentEvent['TZ_FROM'] ?? null,
							'editParentEvents' => $clonedParams['editParentEvents'] ?? null,
							'editNextEvents' => $clonedParams['editNextEvents'] ?? null,
							'syncCaldav' => $clonedParams['syncCaldav'] ?? null,
							'parentDateFrom' => $parentEvent['DATE_FROM'] ?? null,
							'parentDateTo' => $parentEvent['DATE_TO'] ?? null,
						];
						CCalendarSync::DoSaveToDav($childParams['arFields'], $davParams);
					}
				}

				$curEvent = null;
				if (!empty($childParams['arFields']['ID']))
				{
					$curEvent = self::GetList([
						'arFilter' => [
							"ID" => (int)$childParams['arFields']['ID'],
							"DELETED" => 'N',
						],
						'checkPermissions' => false,
						'parseRecursion' => false,
						'fetchAttendees' => true,
						'fetchMeetings' => false,
						'userId' => $userId,
					]);
				}
				if ($curEvent)
				{
					$curEvent = $curEvent[0];
				}

				if (!empty($curEvent['COLOR']))
				{
					if (
						empty($childParams['arFields']['COLOR'])
						|| ($curEvent['COLOR'] !== $childParams['arFields']['COLOR'])
					)
					{
						$childParams['arFields']['COLOR'] = $curEvent['COLOR'];
					}
				}

				$id = self::Edit($childParams);
				$affectedEventIds[] = $id;

				if (
					$userIndex[$attendeeId]
					&& $userIndex[$attendeeId]['EXTERNAL_AUTH_ID'] === 'email'
					&& ((!($clonedParams['fromWebservice'] ?? false)) || !empty($changeFields))
					&& !$isPastEvent
					&& ($childParams['overSaving'] ?? false) !== true
				)
				{

					$sender = self::getSenderForIcal($userIndex, $childParams['arFields']['MEETING_HOST']);

					if (empty($sender) || !$sender['ID'])
					{
						continue;
					}

					if (!empty($email = self::getSenderEmailForIcal($arFields['MEETING'])) && !self::$isAddIcalFailEmailError)
					{
						$sender['EMAIL'] = $email;
					}
					else
					{
						CCalendar::ThrowError(GetMessage("EC_ICAL_NOTICE_DO_NOT_SET_EMAIL"));
						self::$isAddIcalFailEmailError = true;
						continue;
					}

					$arFields['ID'] = $id;
					$invitationInfo = [];

					if (!empty($currentAttendeesIndex[$attendeeId]))
					{
						$mailChangeFields = array_filter($changeFields,
							static fn (array $field) => !in_array(
								$field['fieldKey'],
								['ATTENDEES', 'IMPORTANCE'],
								true,
							),
						);
						if (!empty($mailChangeFields))
						{
							$invitationInfo = (new InvitationInfo(
								(int)$arFields['ID'],
								(int)$sender['ID'],
								(int)$attendeeId,
								InvitationInfo::TYPE_EDIT,
								$mailChangeFields,
							))->toArray();
						}
					}
					else
					{
						$invitationInfo = (new InvitationInfo(
							(int)$arFields['ID'],
							(int)$sender['ID'],
							(int)$attendeeId,
							InvitationInfo::TYPE_REQUEST
						))->toArray();
					}

					SendingEmailNotification::sendMessageToQueue($invitationInfo);
				}

				if (
					$chatId > 0
					&& $chat
					&& $isNewAttendee
					&& $userIndex[$attendeeId]
					&& $userIndex[$attendeeId]['EXTERNAL_AUTH_ID'] !== 'email'
					&& $userIndex[$attendeeId]['EXTERNAL_AUTH_ID'] !== Sharing\SharingUser::EXTERNAL_AUTH_ID
					&& $childParams['arFields']['MEETING_STATUS'] !== 'N'
				)
				{
					$chat->AddUser($chatId, $attendeeId, $hideHistory = true, $skipMessage = false);
				}

				if ($id)
				{
					CCalendar::syncChange($id, $childParams['arFields'], $clonedParams, $curEvent);
				}

				unset($deletedAttendees[$attendeeId]);
			}
		}

		// Delete
		$eventIdToDelete = [];
		if (!$isNewEvent && !empty($deletedAttendees))
		{
			$isSharing = in_array(
				$arFields['EVENT_TYPE'] ?? '', Sharing\SharingEventManager::getSharingEventTypes(), true
			);
			if ($isSharing)
			{
				$notifyUserId = $userId;
			}
			else
			{
				$notifyUserId = $arFields['MEETING_HOST'] ?? null;
			}
			foreach($deletedAttendees as $attendeeId)
			{
				if ($chatId > 0 && $chat)
				{
					$chat->DeleteUser($chatId, $attendeeId, false);
				}

				$att = $currentAttendeesIndex[$attendeeId];
				if (
					($params['sendInvitations'] ?? null) !== false
					&& ($att['STATUS'] ?? null) === 'Y'
					&& !$isPastEvent
				)
				{
					$CACHE_MANAGER->ClearByTag('calendar_user_'.$att["USER_ID"]);
					$fromTo = self::GetEventFromToForUser($arFields, $att["USER_ID"]);
					CCalendarNotify::Send([
						'mode' => 'cancel',
						"name" => $arFields['NAME'] ?? null,
						"from" => $fromTo['DATE_FROM'] ?? null,
						"to" => $fromTo['DATE_TO'] ?? null,
						"location" => CCalendar::GetTextLocation($arFields["LOCATION"] ?? null),
						"guestId" => $att["USER_ID"] ?? null,
						"eventId" => $parentId,
						"userId" => $notifyUserId,
						"fields" => $arFields,
					]);
				}
				//add pull event to update calendar grid after event delete
				$pullUserId = (int)$attendeeId;
				if (
					$pullUserId > 0
					&& self::$sendPush
				)
				{
					Util::addPullEvent(
						PushCommand::DeleteEvent,
						$pullUserId,
						[
							'fields' => $arFields,
							'requestUid' => $params['userId'],
						]
					);
				}
				CCalendarNotify::ClearNotifications($arFields['PARENT_ID'], $pullUserId);
				$affectedEventIds[] = $att['EVENT_ID'] ?? 0;

				if ((int)($att['EVENT_ID'] ?? null))
				{
					$eventIdToDelete[] = (int)$att['EVENT_ID'];
				}

				$currentEvent = self::GetList([
					'arFilter' => [
						"PARENT_ID" => $parentId,
						"OWNER_ID" => $attendeeId,
						"IS_MEETING" => 1,
						"DELETED" => "N",
					],
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'fetchMeetings' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false,
				]);
				$currentEvent = $currentEvent[0];

				$isExchangeEnabled = CCalendar::IsExchangeEnabled($attendeeId);
				if (($isExchangeEnabled || $isCalDavEnabled) && $currentEvent)
				{
					CCalendarSync::DoDeleteToDav([
						'bCalDav' => $isCalDavEnabled,
						'bExchangeEnabled' => $isExchangeEnabled,
						'sectionId' => $currentEvent['SECT_ID'] ?? null,
					], $currentEvent);
				}

				if ($currentEvent)
				{
					self::onEventDelete($currentEvent, $params);
				}

				if (isset($att['EXTERNAL_AUTH_ID']) && $att['EXTERNAL_AUTH_ID'] === 'email' && !$isPastEvent)
				{
					if (empty($receiver['EMAIL']))
					{
						continue;
					}

					$sender = self::getSenderForIcal($currentAttendeesIndex, $arFields['MEETING_HOST']);
					if ($email = self::getSenderEmailForIcal($arFields['MEETING']))
					{
						$sender['EMAIL'] = $email;
					}
					else
					{
						$meetingHostSettings = UserSettings::get($arFields['MEETING_HOST']);
						$sender['EMAIL'] = $meetingHostSettings['sendFromEmail'];
					}
					if (empty($sender['ID']) && isset($sender['USER_ID']))
					{
						$sender['ID'] = (int)$sender['USER_ID'];
					}

					$invitationInfo = (new InvitationInfo(
						(int)$arFields['ID'],
						(int)$sender['ID'],
						(int)$receiver['ID'],
						InvitationInfo::TYPE_CANCEL
					))->toArray();

					SendingEmailNotification::sendMessageToQueue($invitationInfo);
				}
			}
		}


		if (!empty($eventIdToDelete))
		{
			Internals\EventTable::updateByFilter(
				[
					'ID' => $eventIdToDelete,
					'=PARENT_ID' => (int)$parentId,
				],
				['DELETED' => 'Y'],
			);

			self::safeDeleteEventAttendees($parentId, $deletedAttendees);
		}

		if (!empty($involvedAttendees))
		{
			$involvedAttendees = array_unique($involvedAttendees);
			CCalendar::UpdateCounter($involvedAttendees, array_unique($affectedEventIds));
		}
	}

	public static function UpdateParentEventExDate($recurrenceId, $exDate, $attendeeIds)
	{
		global $CACHE_MANAGER;

		$event = Internals\EventTable::query()
			->setSelect(['PARENT_ID', 'EXDATE'])
			->where('PARENT_ID', $recurrenceId)
			->setLimit(1)
			->exec()->fetch()
		;
		$exDates = self::GetExDate($event['EXDATE']);
		$exDates[] = date(
			ExcludedDatesCollection::EXCLUDED_DATE_FORMAT,
			\CCalendar::Timestamp($exDate)
		);
		$exDates = array_unique($exDates);
		$strExDates = implode(';', $exDates);

		Internals\EventTable::updateByFilter(
			['=PARENT_ID' => (int)$recurrenceId],
			['EXDATE' => $strExDates],
		);

		if (is_array($attendeeIds))
		{
			foreach ($attendeeIds as $id)
			{
				$CACHE_MANAGER->ClearByTag('calendar_user_' . $id);
			}
		}

		return true;
	}

	public static function GetEventFromToForUser($params, $userId)
	{
		$skipTime = $params['DT_SKIP_TIME'] !== 'N';

		$fromTs = CCalendar::Timestamp($params['DATE_FROM'], false, !$skipTime);
		$toTs = CCalendar::Timestamp($params['DATE_TO'], false, !$skipTime);

		if (!$skipTime)
		{
			$fromTs -= (CCalendar::GetTimezoneOffset($params['TZ_FROM']) - CCalendar::GetCurrentOffsetUTC($userId));
			$toTs -= (CCalendar::GetTimezoneOffset($params['TZ_TO']) - CCalendar::GetCurrentOffsetUTC($userId));
		}

		$dateFrom = CCalendar::Date($fromTs, !$skipTime);
		$dateTo = CCalendar::Date($toTs, !$skipTime);

		return array(
			"DATE_FROM" => $dateFrom,
			"DATE_TO" => $dateTo,
			"TS_FROM" => $fromTs,
			"TS_TO" => $toTs,
		);
	}

	public static function OnPullPrepareArFields($arFields = [])
	{
		$arFields['~DESCRIPTION'] = self::ParseText($arFields['DESCRIPTION']);

		$arFields['~LOCATION'] = '';
		if (($arFields['LOCATION'] ?? null) !== '')
		{
			$arFields['~LOCATION'] = $arFields['LOCATION'];
			$arFields['LOCATION'] = CCalendar::GetTextLocation($arFields["LOCATION"]);
		}

		if (isset($arFields['~MEETING']))
		{
			$arFields['MEETING'] = $arFields['~MEETING'];
		}

		if (!empty($arFields['REMIND']) && !is_array($arFields['REMIND']))
		{
			$arFields['REMIND'] = unserialize($arFields['REMIND'], ['allowed_classes' => false]);
		}
		if (!is_array($arFields['REMIND'] ?? null))
		{
			$arFields['REMIND'] = [];
		}

		$arFields['RRULE'] = self::ParseRRULE($arFields['RRULE']);

		return $arFields;
	}

	public static function UpdateUserFields($eventId, $arFields = [], $updateSearchIndex = true)
	{
		$eventId = (int)$eventId;
		if (!is_array($arFields) || empty($arFields) || $eventId <= 0)
		{
			return false;
		}

		global $USER_FIELD_MANAGER;
		if ($USER_FIELD_MANAGER->CheckFields("CALENDAR_EVENT", $eventId, $arFields))
		{
			$USER_FIELD_MANAGER->Update("CALENDAR_EVENT", $eventId, $arFields);
		}

		foreach(GetModuleEvents("calendar", "OnAfterCalendarEventUserFieldsUpdate", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($eventId, $arFields));
		}

		if ($updateSearchIndex)
		{
			self::updateSearchIndex($eventId);
		}

		return true;
	}

	public static function Delete($params)
	{
		global $CACHE_MANAGER;
		$bCalDav = CCalendar::IsCalDAVEnabled();
		$id = (int)$params['id'];
		$sendNotification = ($params['sendNotification'] ?? null) !== false;
		$params['requestUid'] = $params['requestUid'] ?? null;

		if ($id)
		{
			$userId = (isset($params['userId']) && (int)$params['userId'] > 0)
				? (int)$params['userId']
				: CCalendar::GetCurUserId()
			;

			$arAffectedSections = [];
			$entry = $params['Event'] ?? null;

			if (!isset($entry) || !is_array($entry))
			{
				CCalendar::SetOffset();
				$res = self::GetList([
					'arFilter' => [
						'ID' => $id,
					],
					'parseRecursion' => false,
				]);
				$entry = $res[0] ?? null;
			}

			if ($entry)
			{
				$entry['PARENT_ID'] = $entry['PARENT_ID'] ?? null;
				if (!empty($entry['IS_MEETING']) && $entry['PARENT_ID'] !== $entry['ID'])
				{
					$parentEvent = self::GetList([
						'arFilter' => [
							"ID" => $entry['PARENT_ID'],
						],
						'parseRecursion' => false,
                    ]);
					$parentEvent = $parentEvent[0];
					if ($parentEvent)
					{
						$accessController = new EventAccessController($userId);
						$eventModel = self::getEventModelForPermissionCheck(
							(int)($entry['ID'] ?? 0),
							$entry,
							$userId
						);

						$perm = $accessController->check(ActionDictionary::ACTION_EVENT_DELETE, $eventModel);
						if (!$perm)
						{
							if (in_array($entry['MEETING_STATUS'] ?? null,
								[
									Dictionary::MEETING_STATUS['Yes'],
									Dictionary::MEETING_STATUS['Question'],
								], true)
							)
							{
								self::SetMeetingStatus([
									'userId' => $userId,
									'eventId' => $entry['ID'],
									'status' => 'N',
									'doSendMail' => false,
								]);
							}

							return true;
						}

						return CCalendar::DeleteEvent($parentEvent['ID']);
					}

					return false;
				}
				foreach(GetModuleEvents("calendar", "OnBeforeCalendarEventDelete", true) as $arEvent)
				{
					ExecuteModuleEventEx($arEvent, array($id, $entry));
				}

				if (!empty($entry['PARENT_ID']))
				{
					CCalendarLiveFeed::OnDeleteCalendarEventEntry($entry['PARENT_ID']);
				}
				else
				{
					CCalendarLiveFeed::OnDeleteCalendarEventEntry($entry['ID']);
				}

				$sharingOwnerId = -1;
				$eventLink = null;
				if (in_array($entry['EVENT_TYPE'] ?? '', Sharing\SharingEventManager::getSharingEventTypes(), true))
				{
					Sharing\SharingEventManager::onSharingEventDeleted((int)($entry['ID'] ?? 0), $entry['EVENT_TYPE']);
					/** @var Sharing\Link\EventLink $eventLink */
					$eventLink = (new Sharing\Link\Factory())->getEventLinkByEventId((int)($entry['PARENT_ID'] ?? $entry['ID'] ?? 0));
					if ($eventLink)
					{
						$sharingOwnerId = $eventLink->getOwnerId();
					}
				}

				$arAffectedSections[] = $entry['SECT_ID'];
				// Check location: if reserve meeting was reserved - clean reservation
				if (!empty($entry['LOCATION']))
				{
					$loc = Rooms\Util::parseLocation($entry['LOCATION']);
					if ($loc['mrevid'] || $loc['room_event_id'])
					{
						Rooms\Util::releaseLocation($loc);
					}
				}

				if ($entry['CAL_TYPE'] === 'user')
				{
					$CACHE_MANAGER->ClearByTag('calendar_user_'.$entry['OWNER_ID']);
				}

				if (!empty($entry['IS_MEETING']))
				{
					$isPastEvent = (int)$entry['DATE_TO_TS_UTC'] < (time() - (int)$entry['TZ_OFFSET_TO']);;
					CCalendarNotify::ClearNotifications($entry['PARENT_ID']);

					if (Loader::includeModule("im"))
					{
						CIMNotify::DeleteBySubTag("CALENDAR|INVITE|".$entry['PARENT_ID']);
						CIMNotify::DeleteBySubTag("CALENDAR|STATUS|".$entry['PARENT_ID']);
					}

					$involvedAttendees = [];

					$CACHE_MANAGER->ClearByTag('calendar_user_'.$userId);
					$childEvents = self::GetList([
						'arFilter' => [
							"PARENT_ID" => $id,
						],
						'parseRecursion' => false,
						'checkPermissions' => false,
						'setDefaultLimit' => false,
					]);

					foreach($childEvents as $chEvent)
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$chEvent["OWNER_ID"]);
						if (
							$chEvent["MEETING_STATUS"] !== "N"
							&& $sendNotification
							&& !$isPastEvent
							&& $sharingOwnerId !== (int)$chEvent["OWNER_ID"]
						)
						{
							$fromTo = self::GetEventFromToForUser($entry, $chEvent["OWNER_ID"]);
							$sendCancelUserId = $userId;
							if ($userId === 0 && ($eventLink instanceof Sharing\Link\EventLink))
							{
								$sendCancelUserId = $eventLink->getHostId();
							}
							if (
								(!empty($chEvent['MEETING_HOST']) && (int)$chEvent['MEETING_HOST'] === $sendCancelUserId)
								|| self::checkAttendeeBelongsToEvent($id, $sendCancelUserId)
							)
							{
								if (($eventLink instanceof Sharing\Link\EventLink))
								{
									\CCalendarNotify::Send([
										'mode' => 'cancel_sharing',
										'userId' => $sendCancelUserId,
										'guestId' => $chEvent['OWNER_ID'],
										'eventId' => $id,
										'name' => $chEvent['NAME'],
										'from' => $fromTo['DATE_FROM'],
										'to' => $fromTo['DATE_TO'],
										'isSharing' => true,
									]);
								}
								else
								{
									// first problematic place
									\CCalendarNotify::Send([
										'mode' => 'cancel',
										'name' => $chEvent['NAME'],
										"from" => $fromTo["DATE_FROM"],
										"to" => $fromTo["DATE_TO"],
										"location" => CCalendar::GetTextLocation($chEvent["LOCATION"]),
										"guestId" => $chEvent["OWNER_ID"],
										"eventId" => $id,
										"userId" => $sendCancelUserId,
									]);
								}
							}
						}

						if ($chEvent["MEETING_STATUS"] === "Q")
						{
							$involvedAttendees[] = $chEvent["OWNER_ID"];
						}

						$bExchange = CCalendar::IsExchangeEnabled($chEvent["OWNER_ID"]);
						if ($bExchange || $bCalDav)
						{
							CCalendarSync::DoDeleteToDav(
								[
									'bCalDav' => $bCalDav,
									'bExchangeEnabled' => $bExchange,
									'sectionId' => $chEvent['SECT_ID'],
								],
								$chEvent
							);
						}

						self::onEventDelete($chEvent, $params);

						$isParent = $chEvent['ID'] === $chEvent['PARENT_ID'];
						if (
							!$isParent
							&& !$isPastEvent
							&& ICalUtil::isMailUser($chEvent['OWNER_ID'])
						)
						{
							if (!empty($chEvent['ATTENDEE_LIST']) && is_array($chEvent['ATTENDEE_LIST']))
							{
								$attendeeIds = [];
								foreach ($chEvent['ATTENDEE_LIST'] as $attendee)
								{
									$attendeeIds[] = $attendee['id'];
								}
							}
							$attendees = null;
							if (!empty($attendeeIds))
							{
								$attendees = ICalUtil::getIndexUsersById($attendeeIds);
							}

							$sender = self::getSenderForIcal($attendees, $chEvent['MEETING_HOST']);
							if (!empty($chEvent['MEETING']['MAIL_FROM']))
							{
								$sender['EMAIL'] = $chEvent['MEETING']['MAIL_FROM'];
								$sender['MAIL_FROM'] = $chEvent['MEETING']['MAIL_FROM'];
							}
							else
							{
								continue;
							}

							/** increment version to delete event in outside service */
							$chEvent['VERSION'] = (int)$chEvent['VERSION'] + 1;

							$invitationInfo = (new InvitationInfo(
								(int)$chEvent['ID'],
								(int)$sender['ID'],
								(int)$chEvent['OWNER_ID'],
								InvitationInfo::TYPE_CANCEL
							))->toArray();

							SendingEmailNotification::sendMessageToQueue($invitationInfo);
						}

						$pullUserId = (int)$chEvent['OWNER_ID'] > 0 ? (int)$chEvent['OWNER_ID'] : $userId;
						if (
							$pullUserId
							&& self::$sendPush
						)
						{
							Util::addPullEvent(
								PushCommand::DeleteEvent,
								$pullUserId,
								[
									'fields' => $chEvent,
									'requestUid' => $params['requestUid'] ?? null,
								]
							);
						}
					}

					// Set flag
					if (!empty($params['bMarkDeleted']))
					{
						Internals\EventTable::updateByFilter(
							['=PARENT_ID' => $id],
							['DELETED' => 'Y'],
						);
					}
					else // Actual deleting
					{
						Internals\EventTable::deleteByFilter(['PARENT_ID' => $id]);
					}

					if (!empty($involvedAttendees))
					{
						CCalendar::UpdateCounter($involvedAttendees, [$id]);
					}
				}

				if (!$entry['IS_MEETING'] && $entry['CAL_TYPE'] === 'user')
				{
					self::onEventDelete($entry, $params);
				}

				if (
					$params
					&& is_array($params)
					&& \Bitrix\Calendar\Sync\Util\RequestLogger::isEnabled()
				)
				{
					$loggerData = $params;
					unset($loggerData['Event']);
					$loggerData['loggerUuid'] = $id;
					(new \Bitrix\Calendar\Sync\Util\RequestLogger($userId, 'portal_delete'))->write($loggerData);
				}

				if (!empty($params['bMarkDeleted']))
				{
					Internals\EventTable::update($id, ['DELETED' => 'Y']);
				}
				else
				{
					// Real deleting
					Internals\EventTable::delete($id);
				}

				if (!empty($arAffectedSections))
				{
					CCalendarSect::UpdateModificationLabel($arAffectedSections);
				}

				foreach(EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarEventDelete") as $event)
				{
					ExecuteModuleEventEx($event, [$id, $entry, $userId]);
				}

				CCalendar::ClearCache('event_list');

				if (($entry['ACCESSIBILITY'] ?? '') === 'absent')
				{
					(new \Bitrix\Calendar\Integration\Intranet\Absence())->cleanCache();
				}

				(new \Bitrix\Calendar\Integration\SocialNetwork\SpaceService())->addEvent(
					'onAfterCalendarEventDelete',
					[
						'ID' => $id,
						'ATTENDEE_LIST' => $entry['ATTENDEE_LIST'] ?? null,
					],
				);

				$pullUserId = (int)$entry['OWNER_ID'] > 0 ? (int)$entry['OWNER_ID'] : $userId;
				if (
					$pullUserId
					&& self::$sendPush
				)
				{
					Util::addPullEvent(
						PushCommand::DeleteEvent,
						$pullUserId,
						[
							'fields' => $entry,
							'requestUid' => $params['requestUid'] ?? null,
						]
					);
				}

				(new \Bitrix\Calendar\Event\Event\AfterCalendarEventDeleted($id))->emit();

				return true;
			}
		}

		return false;
	}

	public static function SetMeetingStatusEx($params)
	{
		$doSendMail = $params['doSendMail'] ?? true;
		$reccurentMode = isset($params['reccurentMode'])
			&& in_array($params['reccurentMode'], ['this', 'next', 'all'])
				? $params['reccurentMode']
				: false;

		$currentDateFrom = CCalendar::Date(CCalendar::Timestamp($params['currentDateFrom']), false);
		if ($reccurentMode && $currentDateFrom)
		{
			$event = self::GetById($params['parentId'], false);
			$recurrenceId = $event['RECURRENCE_ID'] ?? $event['ID'];

			if ($reccurentMode !== 'all')
			{
				$res = CCalendar::SaveEventEx([
					'arFields' => [
						'ID' => $params['parentId'],
					],
					'silentErrorMode' => false,
					'recursionEditMode' => $reccurentMode,
					'userId' => $event['MEETING_HOST'],
					'checkPermission' => false,
					'currentEventDateFrom' => $currentDateFrom,
					'sendEditNotification' => false,
				]);

				if (
					$res
					&& isset($res['recEventId'])
					&& $res['recEventId']
				)
				{
					self::SetMeetingStatus([
						'userId' => $params['attendeeId'],
						'eventId' => $res['recEventId'],
						'status' => $params['status'],
						'personalNotification' => true,
						'doSendMail' => $doSendMail,
					]);
				}
			}

			if ($reccurentMode === 'all' || $reccurentMode === 'next')
			{
				$recRelatedEvents = self::GetEventsByRecId($recurrenceId, false);

				if ($reccurentMode === 'next')
				{
					$untilTimestamp = CCalendar::Timestamp($currentDateFrom);
				}
				else
				{
					$untilTimestamp = false;
					self::SetMeetingStatus([
						'userId' => $params['attendeeId'],
						'eventId' => $params['eventId'],
						'status' => $params['status'],
						'personalNotification' => true,
						'doSendMail' => $doSendMail,
					]);
				}

				foreach($recRelatedEvents as $ev)
				{
					if ((int)$ev['ID'] === (int)($params['eventId'] ?? 0))
					{
						continue;
					}

					if ($reccurentMode === 'all'
						|| (
							$untilTimestamp
							&& CCalendar::Timestamp($ev['DATE_FROM']) > $untilTimestamp
						)
					)
					{
						self::SetMeetingStatus([
							'userId' => $params['attendeeId'],
							'eventId' => $ev['ID'],
							'status' => $params['status'],
							'doSendMail' => $doSendMail,
						]);
					}
				}
			}
		}
		else
		{
			self::SetMeetingStatus([
				'userId' => $params['attendeeId'] ?? null,
				'eventId' => $params['eventId'] ?? null,
				'status' => $params['status'] ?? null,
				'doSendMail' => $doSendMail,
			]);
		}
	}

	public static function SetMeetingStatus($params)
	{
		$eventId = $params['eventId'] = (int)($params['eventId'] ?? 0);
		if (!$eventId)
		{
			return;
		}

		CTimeZone::Disable();
		global $CACHE_MANAGER;
		$userId = $params['userId'] = (int)$params['userId'];
		$status = mb_strtoupper($params['status']);
		$doSendMail = $params['doSendMail'] ?? true;
		$prevStatus = null;
		if (!in_array($status, ["Q", "Y", "N", "H", "M"], true))
		{
			$status = $params['status'] = "Q";
		}

		$event = self::GetList([
			'arFilter' => [
				"ID" => $eventId,
				"IS_MEETING" => 1,
				"DELETED" => "N",
			],
			'parseRecursion' => false,
			'fetchAttendees' => true,
			'fetchMeetings' => true,
			'checkPermissions' => false,
			'setDefaultLimit' => false,
		]);

		if (!empty($event))
		{
			$event = $event[0];
			$prevStatus = $event['MEETING_STATUS'];
		}

		if ($event && $event['IS_MEETING'] && (int)$event['PARENT_ID'] > 0)
		{
			if (in_array($event['EVENT_TYPE'], Sharing\SharingEventManager::getSharingEventTypes(), true))
			{
				$userEventForSharing = self::GetList([
					'arFilter' => [
						'PARENT_ID' => $event['PARENT_ID'],
						'OWNER_ID' => $userId,
						'IS_MEETING' => 1,
						'DELETED' => 'N',
					],
					'checkPermissions' => false,
				]);

				if (!empty($userEventForSharing))
				{
					$userEventForSharing = $userEventForSharing[0];
				}
			}

			if (ICalUtil::isMailUser($event['MEETING_HOST']))
			{
				if (\Bitrix\Main\Config\Option::get('calendar', 'log_mail_send_meeting_status', 'N') === 'Y')
				{
					(new Internals\Log\Logger('DEBUG_CALENDAR_MAIL_SEND_MEETING_STATUS'))
						->log(['eventId' => $event['ID'], 'userId' => $userId, 'status' => $status], 10)
					;
				}
				if ($doSendMail && $prevStatus !== $status)
				{
					IncomingEventManager::rehandleRequest([
						'event' => $event,
						'userId' => $userId,
						'answer' => $status === 'Y',
					]);
				}
			}

			if ($event['CURRENT_ATTENDEE_ID'] ?? null)
			{
				self::updateEventAttendeeMeetingStatus((int)$event['CURRENT_ATTENDEE_ID'], $status);
			}

			Internals\EventTable::updateByFilter(
				[
					'=PARENT_ID' => (int)$event['PARENT_ID'],
					'=OWNER_ID' => $userId,
					'=CAL_TYPE' => Dictionary::CALENDAR_TYPE['user'],
				],
				['MEETING_STATUS' => $status],
			);

			if ($status === 'Y')
			{
				self::ShowEventSection((int)$event['PARENT_ID'], $userId);
			}

			CCalendarSect::UpdateModificationLabel($event['SECT_ID']);

			// Clear invitation in messenger
			CCalendarNotify::ClearNotifications($event['PARENT_ID'], $userId);

			// Add new notification in messenger
			if (!empty($params['personalNotification']) && CCalendar::getCurUserId() === $userId)
			{
				$fromTo = self::GetEventFromToForUser($event, $userId);
				CCalendarNotify::Send([
					'mode' => $status === "Y" ? 'status_accept' : 'status_decline',
					'name' => $event['NAME'],
					"from" => $fromTo["DATE_FROM"],
					"guestId" => $userId,
					"eventId" => $event['PARENT_ID'],
					"userId" => $userId,
					"markRead" => true,
					"fields" => $event,
				]);
			}

			$shouldNotifyExtranetInCollab = $status === 'Y'
				&& $event['EVENT_TYPE'] === Dictionary::EVENT_TYPE['collab']
				&& Util::isCollabUser($userId)
			;

			if ($shouldNotifyExtranetInCollab)
			{
				$fromTo = self::GetEventFromToForUser($event, $userId);
				CCalendarNotify::Send([
					'mode' => 'ics_link',
					'name' => $event['NAME'],
					'from' => $fromTo['DATE_FROM'],
					'guestId' => $userId,
					'eventId' => $event['PARENT_ID'],
					'userId' => $userId,
					'markRead' => true,
					'fields' => $event,
				]);
			}

			if (
				$status === 'Y'
				&& ($params['sharingAutoAccept'] ?? null) === true
				&& in_array($event['EVENT_TYPE'], Sharing\SharingEventManager::getSharingEventTypes(), true)
			)
			{
				$fromTo = self::GetEventFromToForUser($event, $userId);
				CCalendarNotify::Send([
					'mode' => 'status_accept',
					'name' => $event['NAME'],
					"from" => $fromTo["DATE_FROM"],
					"guestId" => (int)($event['MEETING_HOST'] ?? null),
					"eventId" => $event['PARENT_ID'],
					"userId" => $userId,
					"fields" => $event,
					'isSharing' => true,
				]);
			}

			$addedPullUserList = [];
			if (isset($event['ATTENDEE_LIST']) && is_array($event['ATTENDEE_LIST']))
			{
				foreach ($event['ATTENDEE_LIST'] as $attendee)
				{
					$fields = [
						'MEETING_STATUS' => $status,
						'USER_ID' => $userId,
						'PARENT_ID' => $event['PARENT_ID'] ?? $eventId,
						'ATTENDEES' => $event['ATTENDEES'] ?? null,
						'CAL_TYPE' => $event['CAL_TYPE'] ?? null,
					];

					Util::addPullEvent(
						PushCommand::SetMeetingStatus,
						$attendee['id'],
						[
							'fields' => $fields,
							'requestUid' => $params['requestUid'] ?? null,
						]
					);
					$addedPullUserList[] = (int)$attendee['id'];
				}
			}

			$pullUserId = (int)($event['OWNER_ID'] ?? $userId);
			if ($pullUserId && !in_array($pullUserId, $addedPullUserList, true))
			{
				$fields = [
					'MEETING_STATUS' => $status,
					'USER_ID' => $userId,
					'PARENT_ID' => $event['PARENT_ID'] ?? $eventId,
					'ATTENDEES' => $event['ATTENDEES'] ?? null,
					'CAL_TYPE' => $event['CAL_TYPE'] ?? null,
				];

				Util::addPullEvent(
					PushCommand::SetMeetingStatus,
					$pullUserId,
					[
						'fields' => $fields,
						'requestUid' => $params['requestUid'] ?? null,
					]
				);
			}

			// Notify author of event
			if (
				$event['MEETING']['NOTIFY']
				&& (int)$event['MEETING_HOST']
				&& $userId !== (int)$event['MEETING_HOST']
				&& ($params['hostNotification'] ?? null) !== false
			)
			{
				if (self::checkAttendeeBelongsToEvent($event['PARENT_ID'], $userId))
				{
					// Send message to the author
					$fromTo = self::GetEventFromToForUser($event, $event['MEETING_HOST']);
					CCalendarNotify::Send([
						'mode' => $status === "Y" ? 'accept' : 'decline',
						'name' => $event['NAME'],
						"from" => $fromTo["DATE_FROM"],
						"to" => $fromTo["DATE_TO"],
						"location" => CCalendar::GetTextLocation($event["LOCATION"] ?? null),
						"guestId" => $userId,
						"eventId" => $event['PARENT_ID'],
						"userId" => $event['MEETING']['MEETING_CREATOR'] ?? $event['MEETING_HOST'],
						"fields" => $event,
					]);
				}

				if (
					!empty($userEventForSharing)
					&& in_array($event['EVENT_TYPE'], Sharing\SharingEventManager::getSharingEventTypes(), true)
				)
				{
					Sharing\SharingEventManager::onSharingEventMeetingStatusChange(
						$userId,
						$status,
						$userEventForSharing,
						$params['sharingAutoAccept'] ?? false
					);
				}

			}
			CCalendarSect::UpdateModificationLabel([$event['SECTIONS'][0] ?? null]);

			if ($status === "N")
			{
				$childEvent = self::GetList([
					'arFilter' => [
						"PARENT_ID" => $event['PARENT_ID'],
						"CREATED_BY" => $userId,
						"IS_MEETING" => 1,
						"DELETED" => "N",
					],
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false,
				]);

				if ($childEvent && $childEvent[0])
				{
					$childEvent = $childEvent[0];
					$bCalDav = CCalendar::IsCalDAVEnabled();
					$bExchange = CCalendar::IsExchangeEnabled($userId);

					if ($bExchange || $bCalDav)
					{
						CCalendarSync::DoDeleteToDav([
							'bCalDav' => $bCalDav,
							'bExchangeEnabled' => $bExchange,
							'sectionId' => $childEvent['SECT_ID'],
						], $childEvent);
					}

					self::onEventDelete($childEvent);
				}
			}

			if ($status === "Y")
			{
				if (($params['affectRecRelatedEvents'] ?? null) !== false)
				{
					$event = self::GetList([
						'arFilter' => [
							"ID" => $eventId,
							"IS_MEETING" => 1,
							"DELETED" => "N",
						],
						'parseRecursion' => false,
						'fetchAttendees' => true,
						'fetchMeetings' => true,
						'checkPermissions' => false,
						'setDefaultLimit' => false,
					]);

					if (!empty($event))
					{
						$event = $event[0];
					}

					if (!empty($event['RECURRENCE_ID']))
					{
						$masterEvent = self::GetList([
							'arFilter' => [
								'PARENT_ID' => $event['RECURRENCE_ID'],
								'DELETED' => 'N',
								'OWNER_ID' => $userId,
							],
							'parseRecursion' => false,
							'fetchAttendees' => false,
							'checkPermissions' => false,
							'setDefaultLimit' => false,
						]);
						if (!empty($masterEvent))
						{
							$masterEvent = $masterEvent[0];
						}

						if (($masterEvent['MEETING_STATUS'] ?? null) !== 'Y')
						{
							self::SetMeetingStatus([
								'userId' => $userId,
								'eventId' => $masterEvent['ID'],
								'status' => $status,
								'personalNotification' => true,
								'hostNotification' => true,
								'affectRecRelatedEvents' => false,
								'updateDescription' => $params['updateDescription'] ?? null,
							]);

							self::SetMeetingStatusForRecurrenceEvents(
								$event['RECURRENCE_ID'],
								$userId,
								$params['eventId'],
								$status,
								$params['updateDescription'] ?? null,
							);
						}
					}

					if (!empty($event['RRULE']) && in_array($prevStatus, ['N', 'Q', 'H']))
					{
						self::SetMeetingStatusForRecurrenceEvents(
							$event['PARENT_ID'],
							$userId,
							$params['eventId'],
							$status,
							$params['updateDescription'] ?? null,
						);
					}
				}

				if ($prevStatus === 'N')
				{
					CCalendar::syncChange(
						$eventId,
						[
							"MEETING_STATUS" => $status,
						],
						[
							'userId' => $userId,
							'originalFrom' => null,
						],
						null //$event
					);
				}
			}

			if (($params['updateDescription'] ?? null) !== false)
			{
				if (!empty($event['RECURRENCE_ID']))
				{
					self::pushUpdateDescriptionToQueue($event['RECURRENCE_ID'], $userId, $status);
				}
				if (!empty($event['PARENT_ID']) && (int)$event['PARENT_ID'] !== (int)$event['RECURRENCE_ID'])
				{
					self::pushUpdateDescriptionToQueue($event['PARENT_ID'], $userId, $status);
				}
			}

			CCalendarLiveFeed::OnChangeMeetingStatusEventEntry([
				'userId' => $userId,
				'eventId' => $eventId,
				'status' => $status,
				'event' => $event,
			]);

			CCalendar::UpdateCounter([$userId], [$eventId]);

			$CACHE_MANAGER->ClearByTag('calendar_user_'.$userId);
			$CACHE_MANAGER->ClearByTag('calendar_user_'.$event['CREATED_BY']);

			if (($event['ACCESSIBILITY'] ?? '') === 'absent')
			{
				(new \Bitrix\Calendar\Integration\Intranet\Absence())->cleanCache();
			}
		}
		else
		{
			CCalendarNotify::ClearNotifications($eventId);
		}

		CTimeZone::Enable();
		CCalendar::ClearCache(['attendees_list', 'event_list']);
	}

	private static function storeEventAttendee(int $parentEventId, array $dbFields): void
	{
		$addResult = Internals\EventAttendeeTable::add([
			'OWNER_ID' => $dbFields['OWNER_ID'],
			'CREATED_BY' => $dbFields['CREATED_BY'],
			'MEETING_STATUS' => $dbFields['MEETING_STATUS'],
			'SECTION_ID' => $dbFields['SECTION_ID'],
			'COLOR' => $dbFields['COLOR'],
			'REMIND' => $dbFields['REMIND'],
			'DAV_EXCH_LABEL' => $dbFields['DAV_EXCH_LABEL'] ?? null,
			'SYNC_STATUS' => $dbFields['SYNC_STATUS'] ?? null,
			'EVENT_ID' => $parentEventId,
		]);
		if ($addResult->isSuccess())
		{
			$eventAttendeeId = $addResult->getId();
		}
	}

	private static function updateEventAttendeeMeetingStatus(int $eventAttendeeId, string $meetingStatus): void
	{
		$updateResult = Internals\EventAttendeeTable::update(
			$eventAttendeeId,
			[
				'MEETING_STATUS' => $meetingStatus,
			]
		);
		if ($updateResult->isSuccess())
		{
		}
	}

	private static function updateEventAttendeeColor(int $eventAttendeeId, string $color): void
	{
		$updateResult = Internals\EventAttendeeTable::update(
			$eventAttendeeId,
			[
				'COLOR' => $color,
			]
		);
		if ($updateResult->isSuccess())
		{
		}
	}

	private static function getEventAttendeeIdByEventIdAndUserId(int $eventId, int $userId): ?int
	{
		$eventAttendee = Internals\EventAttendeeTable::getRow([
			'select' => ['ID'],
			'filter' => [
				'EVENT_ID' => $eventId,
				'OWNER_ID' => $userId,
			],
		]);

		return $eventAttendee ? (int)$eventAttendee['ID'] : null;
	}

	private static function safeDeleteEventAttendees(int $eventId, array $attendeeUserIds): void
	{
		$eventAttendeesResult = Internals\EventAttendeeTable::getList([
			'select' => ['ID'],
			'filter' => [
				'EVENT_ID' => $eventId,
				'OWNER_ID' => $attendeeUserIds,
			],
		]);

		$eventAttendees = $eventAttendeesResult->fetchAll();
		if (empty($eventAttendees))
		{
			return;
		}

		$eventAttendeeIds = array_column($eventAttendees, 'ID');

		$updateResult = Internals\EventAttendeeTable::updateMulti($eventAttendeeIds, [
			'DELETED' => 'Y',
		]);

		if ($updateResult->isSuccess())
		{
		}
	}

	private static function updateEventAttendee(
		int $parentEventId,
		int $userId,
		array $changedFields,
		array $dbFields
	): void
	{
		if (in_array('COLOR', $changedFields, true) && isset($dbFields['COLOR']))
		{
			// color changed in all child events
			$allChildEventAttendees = $eventAttendeesResult = Internals\EventAttendeeTable::getList([
				'select' => ['ID'],
				'filter' => [
					'EVENT_ID' => $parentEventId,
				],
			]);
			$eventAttendeeIds = array_column($allChildEventAttendees->fetchAll(), 'ID');
			// for event not stored in b_calendar_event_attendees
			if (!$eventAttendeeIds)
			{
				return;
			}
			$colorUpdateResult = Internals\EventAttendeeTable::updateMulti(
				$eventAttendeeIds,
				[
					'COLOR' => $dbFields['COLOR'],
				],
			);
			if ($colorUpdateResult->isSuccess())
			{
			}
		}

		// remind changed only for given user
		if (in_array('REMIND', $changedFields, true) && isset($dbFields['REMIND']))
		{
			$eventAttendeeId = self::getEventAttendeeIdByEventIdAndUserId($parentEventId, $userId);
			// for event not stored in b_calendar_event_attendees
			if (!$eventAttendeeId)
			{
				return;
			}

			$remindUpdateResult = Internals\EventAttendeeTable::update(
				$eventAttendeeId,
				[
					'REMIND' => $dbFields['REMIND'],
				],
			);
			if ($remindUpdateResult->isSuccess())
			{
			}
		}
	}

	protected static function ShowEventSection(int $parentId, int $userId): void
	{
		$eventEO = Internals\EventTable::query()
			->setSelect(['*'])
			->where('PARENT_ID', $parentId)
			->where('OWNER_ID', $userId)
			->exec()->fetchObject();

		if (is_null($eventEO))
		{
			return;
		}

		/** @var Event $acceptedEvent */
		$acceptedEvent = (new Mappers\Event())->getByEntityObject($eventEO);

		if (is_null($acceptedEvent))
		{
			return;
		}

		$acceptedSectionId = $acceptedEvent->getSection()->getId();
		$hiddenSectionIds = UserSettings::getHiddenSections($userId, [ 'isPersonalCalendarContext' => true ]);
		$newHiddenSectionIds = array_filter($hiddenSectionIds, static function($hiddenSectionId) use ($acceptedSectionId) {
			return !is_numeric($hiddenSectionId) || (int)$hiddenSectionId !== $acceptedSectionId;
		});

		if (count($hiddenSectionIds) === count($newHiddenSectionIds))
		{
			return;
		}

		UserSettings::saveHiddenSections($userId, $newHiddenSectionIds);
		Util::addPullEvent(
			PushCommand::HiddenSectionsUpdated,
			$userId,
			[
				'hiddenSections' => $newHiddenSectionIds,
			]
		);
	}

	public static function SetMeetingStatusForRecurrenceEvents(
		int $recurrenceId,
		int $userId,
		int $eventId,
		string $status,
		?bool $updateDescription = null,
	): void
	{
		$recRelatedEvents = self::GetEventsByRecId($recurrenceId, false, $userId);
		foreach ($recRelatedEvents as $ev)
		{
			if ((int)$ev['ID'] === $eventId)
			{
				continue;
			}

			self::SetMeetingStatus([
				'userId' => $userId,
				'eventId' => $ev['ID'],
				'status' => $status,
				'personalNotification' => false,
				'hostNotification' => false,
				'affectRecRelatedEvents' => false,
				'updateDescription' => $updateDescription,
			]);
		}
	}

	/*
	 * $params['dateFrom']
	 * $params['dateTo']
	 *
	 * */

	public static function GetMeetingStatus($userId, $eventId)
	{
		$eventId = (int)$eventId;
		$userId = (int)$userId;
		$status = false;
		$event = self::GetById($eventId, false);
		if ($event && $event['IS_MEETING'] && (int)$event['PARENT_ID'] > 0)
		{
			if ((int)$event['OWNER_ID'] !== $userId)
			{
				$event = Internals\EventTable::query()
					->setSelect(['MEETING_STATUS'])
					->where('PARENT_ID', (int)$event['PARENT_ID'])
					->where('OWNER_ID', $userId)
					->setLimit(1)
					->exec()->fetch()
				;
			}

			$status = $event['MEETING_STATUS'];
		}
		return $status;
	}

	/**
	 * @param array $params
	 * $params = [
	 *      'curEventId' => (int)
	 *      'userId' => (int)
	 *      'checkPermissions' => (bool)
	 * 		users => int[]
	 * 		from => string Time start period
	 * 		to => string Time finish period
	 *    ]
	 *
	 *
	 * @return array
	 */
	public static function GetAccessibilityForUsers($params = []): array
	{
		$curEventId = (int)$params['curEventId'];
		$curUserId = isset($params['userId']) ? (int)$params['userId'] : CCalendar::GetCurUserId();
		if (!is_array($params['users']) || !count($params['users']))
		{
			return [];
		}

		if (!isset($params['checkPermissions']))
		{
			$params['checkPermissions'] = true;
		}

		$users = [];
		$accessibility = [];
		foreach($params['users'] as $userId)
		{
			$userId = (int)$userId;
			if ($userId)
			{
				$users[] = $userId;
				$accessibility[$userId] = [];
			}
		}

		if (empty($users))
		{
			return [];
		}

		$events = self::GetList([
			'arFilter' => [
				"FROM_LIMIT" => $params['from'],
				"TO_LIMIT" => $params['to'],
				"CAL_TYPE" => 'user',
				"OWNER_ID" => $users,
				"ACTIVE_SECTION" => "Y",
			],
			'arSelect' => self::$defaultSelectEvent,
			'parseRecursion' => true,
			'fetchAttendees' => false,
			'fetchSection' => true,
			'parseDescription' => false,
			'setDefaultLimit' => false,
			'checkPermissions' => $params['checkPermissions'],
		]);

		foreach ($events as $event)
		{
			if ($curEventId && ((int)$event["ID"] === $curEventId || (int)$event["PARENT_ID"] === $curEventId))
			{
				continue;
			}
			if ($event["ACCESSIBILITY"] === 'free')
			{
				continue;
			}
			if ($event["IS_MEETING"] && $event["MEETING_STATUS"] === "N")
			{
				continue;
			}
			if (CCalendarSect::CheckGoogleVirtualSection($event['SECTION_DAV_XML_ID']))
			{
				continue;
			}

			$name = $event["NAME"];
			$accessController = new EventAccessController($curUserId);
			$eventModel = EventModel::createFromArray($event);

			if (
				($event['PRIVATE_EVENT'] && $event['CAL_TYPE'] === 'user' && $event['OWNER_ID'] !== $curUserId)
				|| !$accessController->check(ActionDictionary::ACTION_EVENT_VIEW_TITLE, $eventModel)
			)
			{
				$name = '['.GetMessage('EC_ACCESSIBILITY_'.mb_strtoupper($event['ACCESSIBILITY'])).']';
			}

			$accessibility[$event['OWNER_ID']][] = [
				"ID" => $event["ID"],
				"NAME" => $name,
				"DATE_FROM" => $event["DATE_FROM"],
				"DATE_TO" => $event["DATE_TO"],
				"DATE_FROM_TS_UTC" => $event["DATE_FROM_TS_UTC"],
				"DATE_TO_TS_UTC" => $event["DATE_TO_TS_UTC"],
				"~USER_OFFSET_FROM" => $event["~USER_OFFSET_FROM"],
				"~USER_OFFSET_TO" => $event["~USER_OFFSET_TO"],
				"DT_SKIP_TIME" => $event["DT_SKIP_TIME"],
				"TZ_FROM" => $event["TZ_FROM"],
				"TZ_TO" => $event["TZ_TO"],
				"ACCESSIBILITY" => $event["ACCESSIBILITY"],
				"IMPORTANCE" => $event["IMPORTANCE"],
				"EVENT_TYPE" => $event["EVENT_TYPE"],
			];
		}

		// foreach ($users as $userId)
		// {
		// 	$userSettings = UserSettings::get($userId);
		// 	$enableLunchTime = $userSettings['enableLunchTime'] === 'Y';
		//
		// 	if (!$enableLunchTime)
		// 	{
		// 		continue;
		// 	}
		//
		// 	$lunchStart = CCalendar::Timestamp("{$params['from']} {$userSettings['lunchStart']}");
		// 	$lunchEnd = CCalendar::Timestamp("{$params['from']} {$userSettings['lunchEnd']}");
		// 	$lunchLength = $lunchEnd - $lunchStart;
		//
		// 	$from = $lunchStart;
		// 	$to = CCalendar::Timestamp("{$params['to']} {$userSettings['lunchStart']}");
		// 	while ($from <= $to)
		// 	{
		// 		$dateFrom = (new \DateTime())->setTimestamp($from);
		// 		$dateTo = (new \DateTime())->setTimestamp($from + $lunchLength);
		// 		$lunchStart = DateTime::createFromPhp($dateFrom);
		// 		$lunchEnd = DateTime::createFromPhp($dateTo);
		// 		$eventTsFromUTC = Sharing\Helper::getEventTimestampUTC($lunchStart);
		// 		$eventTsToUTC = Sharing\Helper::getEventTimestampUTC($lunchEnd);
		// 		$accessibility[$userId][] = array(
		// 			'ID' => -1,
		// 			'NAME' => 'Lunch',
		// 			'DATE_FROM' => $lunchStart,
		// 			'DATE_TO' => $lunchEnd,
		// 			'TZ_FROM' => $dateFrom->getTimezone()->getName(),
		// 			'TZ_TO' => $dateTo->getTimezone()->getName(),
		// 			'DATE_FROM_TS_UTC' => $eventTsFromUTC,
		// 			'DATE_TO_TS_UTC' => $eventTsToUTC,
		// 			'~USER_OFFSET_FROM' => 0,
		// 			'~USER_OFFSET_TO' => 0,
		// 			'ACCESSIBILITY' => 'busy',
		// 			'IMPORTANCE' => 'normal',
		// 			'DT_SKIP_TIME' => false,
		// 		);
		//
		// 		$from += \CCalendar::DAY_LENGTH;
		// 	}
		// }

		return $accessibility;
	}

	public static function GetAbsent($users = null, $params = []): array
	{
		// Can be called from agent... So we have to create $USER if it is not exists
		$tempUser = CCalendar::TempUser(false, true);
		$checkPermissions = $params['checkPermissions'] !== false;
		$curUserId = isset($params['userId']) ? (int)$params['userId'] : CCalendar::GetCurUserId();
		$arUsers = [];

		if (is_array($users) && !empty($users))
		{
			foreach($users as $id)
			{
				if ($id > 0)
				{
					$arUsers[] = (int)$id;
				}
			}
			if (empty($arUsers))
			{
				$users = false;
			}
		}

		$arFilter = [
			'DELETED' => 'N',
			'ACCESSIBILITY' => 'absent',
			'CAL_TYPE' => Dictionary::CALENDAR_TYPE['user'],
		];

		if ($users)
		{
			$arFilter['CREATED_BY'] = $users;
		}

		if (isset($params['fromLimit']))
		{
			$arFilter['FROM_LIMIT'] = CCalendar::Date(CCalendar::Timestamp($params['fromLimit'], false), true, false);
		}

		if (isset($params['toLimit']))
		{
			$arFilter['TO_LIMIT'] = CCalendar::Date(CCalendar::Timestamp($params['toLimit'], false), true, false);
		}

		$eventList = self::GetList([
			'arFilter' => $arFilter,
			'arSelect' => self::$defaultSelectEvent,
			'parseRecursion' => true,
			'getUserfields' => false,
			'fetchAttendees' => false,
			'userId' => $curUserId,
			'preciseLimits' => true,
			'checkPermissions' => false,
			'parseDescription' => false,
			'skipDeclined' => true,
		]);

		$result = [];
		$settings = false;

		foreach($eventList as $event)
		{
			$userId = (int)$event['CREATED_BY'];
			if (!empty($users) && !in_array($userId, $arUsers, true))
			{
				continue;
			}

			if ($event['IS_MEETING'] && $event["MEETING_STATUS"] === 'N')
			{
				continue;
			}

			if (
				$checkPermissions
				&& ($event['CAL_TYPE'] !== 'user' || $curUserId !== (int)$event['OWNER_ID'])
				&& $curUserId !== (int)$event['CREATED_BY']
			)
			{
				$sectId = (int)$event['SECTION_ID'];
				if (empty($event['ACCESSIBILITY']))
				{
					$event['ACCESSIBILITY'] = 'busy';
				}

				if ($settings === false)
				{
					$settings = CCalendar::GetSettings(array('request' => false));
				}
				$private = $event['PRIVATE_EVENT'] && $event['CAL_TYPE'] === 'user';

				$accessController = new EventAccessController($userId);
				$eventModel = EventModel::createFromArray($event);
				$eventModel->setSectionId((int)$sectId);

				if ($private || !$accessController->check(ActionDictionary::ACTION_EVENT_VIEW_FULL, $eventModel))
				{
					$event = self::ApplyAccessRestrictions($event, $userId);
				}
			}

			$skipTime = $event['DT_SKIP_TIME'] === 'Y';
			$fromTs = CCalendar::Timestamp($event['DATE_FROM'], false, !$skipTime);
			$toTs = CCalendar::Timestamp($event['DATE_TO'], false, !$skipTime);
			if ($event['DT_SKIP_TIME'] !== 'Y')
			{
				$fromTs -= $event['~USER_OFFSET_FROM'];
				$toTs -= $event['~USER_OFFSET_TO'];
			}

			$result[] = [
				'ID' => $event['ID'],
				'NAME' => $event['NAME'],
				'DATE_FROM' => CCalendar::Date($fromTs, !$skipTime, false),
				'DATE_TO' => CCalendar::Date($toTs, !$skipTime, false),
				'DT_FROM_TS' => $fromTs,
				'DT_TO_TS' => $toTs,
				'CREATED_BY' => $userId,
				'DETAIL_TEXT' => '',
				'USER_ID' => $userId,
			];
		}

		// Sort by DATE_FROM_TS_UTC
		usort($result, static function($a, $b){
			if ($a['DT_FROM_TS'] === $b['DT_FROM_TS'])
			{
				return 0;
			}
			return $a['DT_FROM_TS'] < $b['DT_FROM_TS'] ? -1 : 1;
		});

		CCalendar::TempUser($tempUser, false);

		return $result;
	}

	public static function DeleteEmpty(int $sectionId)
	{
		if (!$sectionId)
		{
			return;
		}

		$query = Internals\EventTable::query()
			->setSelect(['ID', 'LOCATION', 'SECTION_ID'])
			->where('SECTION_ID', $sectionId)
			->exec()
		;

		while ($event = $query->fetch())
		{
			$loc = $event['LOCATION'] ?? null;
			if ($loc && mb_strlen($loc) > 5 && mb_strpos($loc, 'ECMR_') === 0)
			{
				$loc = Rooms\Util::parseLocation($loc);
				if ($loc['mrid'] !== false && $loc['mrevid'] !== false) // Release MR
				{
					Rooms\Util::releaseLocation($loc);
				}
			}
			else if ($loc && mb_strlen($loc) > 9 && mb_strpos($loc, 'calendar_') === 0)
			{
				$loc = Rooms\Util::parseLocation($loc);
				if ($loc['room_id'] !== false && $loc['room_event_id'] !== false) // Release calendar_room
				{
					Rooms\Util::releaseLocation($loc);
				}
			}
			$itemIds[] = (int)$event['ID'];
		}

		// Clean from 'b_calendar_event'
		if (!empty($itemIds))
		{
			Internals\EventTable::deleteByFilter([
				'ID' => $itemIds,
			]);
		}

		CCalendar::ClearCache([
			'section_list',
			'event_list',
		]);
	}

	public static function CleanEventsWithDeadParents()
	{
		global $DB;
		$strSql = "SELECT PARENT_ID from b_calendar_event where PARENT_ID in (SELECT ID from b_calendar_event where MEETING_STATUS='H' and DELETED='Y') AND DELETED='N'";
		$res = $DB->Query($strSql);

		$strItems = "0";
		while($result = $res->Fetch())
		{
			$strItems .= ",". (int)$result['ID'];
		}

		if ($strItems != "0")
		{
			$strSql =
				"UPDATE b_calendar_event SET ".
				$DB->PrepareUpdate("b_calendar_event", array("DELETED" => "Y")).
				" WHERE PARENT_ID in (".$strItems.")";
			$DB->Query($strSql);
		}
		CCalendar::ClearCache(['section_list', 'event_list']);
	}

	public static function CanView($eventId, $userId)
	{
		if ((int)$eventId > 0)
		{
			Loader::includeModule("calendar");
			$event = self::GetList(
				array(
					'arFilter' => array(
						"ID" => $eventId,
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'userId' => $userId,
				)
			);

			if (!$event || !is_array($event[0]))
			{
				$event = self::GetList(
					array(
						'arFilter' => array(
							"PARENT_ID" => $eventId,
							"CREATED_BY" => $userId,
						),
						'parseRecursion' => false,
						'fetchAttendees' => true,
						'checkPermissions' => true,
						'userId' => $userId,
					)
				);
			}

			// Event is not partly accessible - so it was not cleaned before by ApplyAccessRestrictions
			if (
				$event
				&& is_array($event[0])
				&& ($event[0]['IS_ACCESSIBLE_TO_USER'] ?? null) !== false
				&& (
					isset($event[0]['DESCRIPTION'])
					|| isset($event[0]['IS_MEETING'])
					|| isset($event[0]['LOCATION'])
				)
			)
			{
				return true;
			}

		}

		return false;
	}

	public static function GetEventUserFields($event)
	{
		global $USER_FIELD_MANAGER;

		$eventId = !empty($event['PARENT_ID']) ? (int)$event['PARENT_ID'] : (int)($event['ID'] ?? null);

		if (isset(self::$eventUserFields[$eventId]))
		{
			return self::$eventUserFields[$eventId];
		}

		self::$eventUserFields[$eventId] = $USER_FIELD_MANAGER->GetUserFields('CALENDAR_EVENT', $eventId, LANGUAGE_ID);

		return self::$eventUserFields[$eventId];
	}

	public static function SetExDate($exDate = [], $untilTimestamp = false)
	{
		if ($untilTimestamp && !empty($exDate) && is_array($exDate))
		{
			$exDateRes = [];

			foreach($exDate as $date)
			{
				if (CCalendar::Timestamp($date) <= $untilTimestamp)
				{
					$exDateRes[] = $date;
				}
			}

			$exDate = $exDateRes;
		}

		$exDate = array_unique($exDate);

		return implode(';', $exDate);
	}

	public static function GetEventsByRecId($recurrenceId, $checkPermissions = true, $userId = null)
	{
		if ($recurrenceId > 0)
		{
			$filter = [
				'RECURRENCE_ID' => $recurrenceId,
				'DELETED' => 'N',
			];
			if ($userId)
			{
				$filter['OWNER_ID'] = $userId;
			}

			return self::GetList([
				'arFilter' => $filter,
				'parseRecursion' => false,
				'fetchAttendees' => false,
				'checkPermissions' => $checkPermissions,
				'setDefaultLimit' => false,
			]);
		}
		return [];
	}

	/**
	 * Which events are broken:
	 * 1) events of a series created from the exclusion of another series
	 * 2) exclusions of a series created from the exclusion of another series
	 * 3) events of a series created from the exclusion of another subseries
	 * 4) the exclusions of a series created from the exclusion of another subseries
	 *
	 * this method corrects event comments only when a recurring event is opened, but not when an exception is opened.
	 */
	public static function FixCommentsIfEventIsBroken(array $event): array
	{
		if (self::IsBrokenEventOfSeries($event))
		{
			$commentXmlId = $event['RELATIONS']['COMMENT_XML_ID'];
			$doesntHaveCommentsOrWereCommentsMoved = self::MoveCommentsToFirstRecurrence($event, $commentXmlId);
			if ($doesntHaveCommentsOrWereCommentsMoved)
			{
				self::CleanUpBrokenRecursiveExclusion($commentXmlId);
			}

			$xmlId = $event['RECURRENCE_ID'] ?? null;
			preg_match('/EVENT_\d+_(.*)/', $commentXmlId, $matchesDate);
			$xmlDate = $matchesDate[1] ?? null;

			$doesntHaveCommentsOrWereCommentsMovedAnother = false;
			if (!is_null($xmlId) && !is_null($xmlDate))
			{
				$anotherCommentXmlId = "EVENT_{$xmlId}_{$xmlDate}";
				$doesntHaveCommentsOrWereCommentsMovedAnother = self::MoveCommentsToFirstRecurrence($event, $anotherCommentXmlId);
				if ($doesntHaveCommentsOrWereCommentsMovedAnother)
				{
					self::CleanUpBrokenRecursiveExclusion($anotherCommentXmlId);
				}
			}

			if ($doesntHaveCommentsOrWereCommentsMoved && $doesntHaveCommentsOrWereCommentsMovedAnother)
			{
				self::CleanUpBrokenRecursiveEvent($event);
			}

			unset($event['ORIGINAL_DATE_FROM'], $event['RELATIONS']);

			\CCalendar::ClearCache('event_list');
		}


		return $event;
	}

	public static function IsBrokenEventOfSeries(array $event): bool
	{
		return !empty($event['RRULE']) && !empty($event['RELATIONS']['COMMENT_XML_ID']);
	}

	public static function CleanUpBrokenRecursiveEvent(array $event): void
	{
		$rows = Internals\EventTable::query()
			->setSelect(['ID'])
			->whereNot('RRULE', '')
			->where('PARENT_ID', $event['PARENT_ID'])
		;

		$events = $rows->fetchAll();
		if (empty($events))
		{
			return;
		}

		$eventIds = array_map('intval', array_column($events, 'ID'));
		Internals\EventTable::updateMulti($eventIds, [
			'ORIGINAL_DATE_FROM' => null,
			'RELATIONS' => null,
		]);
	}

	public static function CleanUpBrokenRecursiveExclusion(string $commentXmlId): void
	{
		$rows = Internals\EventTable::query()
			->setSelect(['ID', 'PARENT_ID', 'RECURRENCE_ID', 'ORIGINAL_DATE_FROM'])
			->where('RRULE', '')
			->where('RELATIONS', serialize([
				'COMMENT_XML_ID' => $commentXmlId,
			]))
		;

		$events = $rows->fetchAll();
		foreach ($events as $brokenEvent)
		{
			Internals\EventTable::update($brokenEvent['ID'], [
				'RELATIONS' => serialize([
					'COMMENT_XML_ID' => self::GetEventCommentXmlId($brokenEvent),
				]),
			]);
		}
	}

	public static function MoveCommentsToFirstRecurrence(array $event, string $currentEntityXmlId): bool
	{
		if (!Loader::includeModule('forum'))
		{
			return false;
		}

		$forumId = \CCalendar::GetSettings()['forum_id'];
		$eventEntityId = $event['PARENT_ID'] ?: $event['ID'];
		$newEntityXmlId = "EVENT_$eventEntityId";

		$tsFrom = $event['DATE_FROM_TS_UTC'];
		if (!empty($event['~DATE_FROM']))
		{
			$tsFrom = \CCalendar::TimestampUTC($event['~DATE_FROM']);
		}

		$firstRecurrenceDate = Util::formatDateTimestampUTC($tsFrom);
		if (str_contains($event['EXDATE'], $firstRecurrenceDate))
		{
			$newEntityXmlId = "EVENT_{$event['RECURRENCE_ID']}_{$firstRecurrenceDate}";
		}

		$currentFeed = new \Bitrix\Forum\Comments\Feed($forumId, [
			'type' => 'EV',
			'id' => $eventEntityId,
			'xml_id' => $currentEntityXmlId,
		]);

		return $currentFeed->moveEventCommentsToNewXmlId($newEntityXmlId);
	}

	public static function GetEventCommentXmlId($event)
	{
		if (isset($event['RELATIONS']['ORIGINAL_RECURSION_ID']))
		{
			$date = CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false);
			return "EVENT_{$event['RELATIONS']['ORIGINAL_RECURSION_ID']}_$date";
		}
		if (isset($event['ORIGINAL_DATE_FROM'], $event['RECURRENCE_ID']))
		{
			$date = CCalendar::Date(CCalendar::Timestamp($event['ORIGINAL_DATE_FROM']), false);
			return "EVENT_{$event['RECURRENCE_ID']}_$date";
		}

		$commentXmlId = "EVENT_" . ($event['PARENT_ID'] ?? $event['ID']);

		if (
			self::CheckRecurcion($event)
			&& (!isset($event['RINDEX']) || $event['RINDEX'] > 0)
			&& (CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false)
				!== CCalendar::Date(CCalendar::Timestamp($event['~DATE_FROM'] ?? null), false))
		)
		{
			$commentXmlId .= '_'.CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false);
		}

		return $commentXmlId;
	}

	public static function ExtractDateFromCommentXmlId($xmlId = '')
	{
		$date = false;
		if ($xmlId)
		{
			$xmlAr = explode('_', $xmlId);
			if (is_array($xmlAr) && isset($xmlAr[2]) && $xmlAr[2])
			{
				$date = CCalendar::Date(CCalendar::Timestamp($xmlAr[2]), false);
			}
		}
		return $date;
	}

	public static function GetRRULEDescription($event, $html = false, $showUntil = true, $languageId = null)
	{
		$res = '';
		if (!empty($event['RRULE']))
		{
			$event['RRULE'] = self::ParseRRULE($event['RRULE']);

			if (!empty($event['RRULE']['BYDAY']))
			{
				$event['RRULE']['BYDAY'] = self::sortByDay($event['RRULE']['BYDAY']);
			}

			switch($event['RRULE']['FREQ'])
			{
				case 'DAILY':
					if ((int)$event['RRULE']['INTERVAL'] === 1)
					{
						$res = Loc::getMessage('EC_RRULE_EVERY_DAY', null, $languageId);
					}
					else
					{
						$res = Loc::getMessage(
							'EC_RRULE_EVERY_DAY_1',
							['#DAY#' => $event['RRULE']['INTERVAL']],
							$languageId
						);
					}
					break;
				case 'WEEKLY':
					$daysList = [];
					foreach ($event['RRULE']['BYDAY'] as $day)
					{
						$daysList[] = Loc::getMessage('EC_'.$day, null, $languageId);
					}

					$daysList = implode(', ', $daysList);
					if ((int)$event['RRULE']['INTERVAL'] === 1)
					{
						$res = Loc::getMessage(
							'EC_RRULE_EVERY_WEEK',
							['#DAYS_LIST#' => $daysList],
							$languageId
						);
					}
					else
					{
						$res = Loc::getMessage(
							'EC_RRULE_EVERY_WEEK_1',
							[
								'#WEEK#' => $event['RRULE']['INTERVAL'],
								'#DAYS_LIST#' => $daysList,
							],
							$languageId
						);
					}
					break;
				case 'MONTHLY':
					if ((int)$event['RRULE']['INTERVAL'] === 1)
					{
						$res = Loc::getMessage('EC_RRULE_EVERY_MONTH', null, $languageId);
					}
					else
					{
						$res = Loc::getMessage(
							'EC_RRULE_EVERY_MONTH_1',
							[
								'#MONTH#' => $event['RRULE']['INTERVAL'],
							],
							$languageId
						);
					}
					break;
				case 'YEARLY':
					$fromTs = CCalendar::Timestamp($event['DATE_FROM']);
					if ($event['DT_SKIP_TIME'] !== "Y")
					{
						$fromTs -= $event['~USER_OFFSET_FROM'] ?? 0;
					}

					if ((int)$event['RRULE']['INTERVAL'] === 1)
					{
						$res = Loc::getMessage(
							'EC_RRULE_EVERY_YEAR',
							[
								'#DAY#' => FormatDate('j', $fromTs, false, $languageId), // day
								'#MONTH#' => FormatDate('n', $fromTs, false, $languageId), // month
							],
							$languageId
						);
					}
					else
					{
						$res = Loc::getMessage(
							'EC_RRULE_EVERY_YEAR_1',
							[
								'#YEAR#' => $event['RRULE']['INTERVAL'],
								'#DAY#' => FormatDate('j', $fromTs, false, $languageId), // day
								'#MONTH#' => FormatDate('n', $fromTs, false, $languageId), // month
							],
							$languageId
						);
					}
					break;
			}

			if ($html)
			{
				$res .= '<br>';
			}
			else
			{
				$res .= ', ';
			}

			if (isset($event['~DATE_FROM']))
			{
				$res .= Loc::getMessage(
					'EC_RRULE_FROM',
					['#FROM_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['~DATE_FROM']), false)],
					$languageId
				);
			}
			else
			{
				$res .= Loc::getMessage(
					'EC_RRULE_FROM',
					['#FROM_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false)],
					$languageId
				);
			}

			if ($showUntil && ($event['RRULE']['UNTIL'] ?? null) != CCalendar::GetMaxDate())
			{
				$res .= ' ' . Loc::getMessage(
					'EC_RRULE_UNTIL',
					['#UNTIL_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['RRULE']['UNTIL']), false)],
					$languageId
				);
			}
			elseif ($showUntil && (($event['RRULE']['COUNT'] ?? null) > 0))
			{
				$res .= ', ' . Loc::getMessage(
					'EC_RRULE_COUNT',
					['#COUNT#' => $event['RRULE']['COUNT']],
					$languageId
				);
			}
		}

		return $res;
	}

	public static function ExcludeInstance($eventId, $excludeDate)
	{
		global $CACHE_MANAGER;

		$userId = \CCalendar::getCurUserId();
		$eventId = (int)$eventId;
		$excludeDateTs = CCalendar::Timestamp($excludeDate);
		$excludeDate = CCalendar::Date($excludeDateTs, false);

		$event = self::GetList([
			'arFilter' => [
				"ID" => $eventId,
				"DELETED" => "N",
			],
			'parseRecursion' => false,
			'fetchAttendees' => true,
			'setDefaultLimit' => false,
		]);
		if ($event && is_array($event[0]))
		{
			$event = $event[0];
		}

		if ($event && $excludeDate && self::CheckRecurcion($event))
		{
			$excludeDates = self::GetExDate($event['EXDATE']);
			$excludeDates[] = $excludeDate;

			$id = CCalendar::SaveEvent([
				'arFields' => [
					'ID' => $event['ID'],
					'DATE_FROM' => $event['DATE_FROM'],
					'DATE_TO' => $event['DATE_TO'],
					'EXDATE' => self::SetExDate($excludeDates),
				],
				'silentErrorMode' => false,
				'recursionEditMode' => 'skip',
				'editParentEvents' => true,
			]);

			if (!empty($event['ATTENDEE_LIST']) && is_array($event['ATTENDEE_LIST']))
			{
				foreach($event['ATTENDEE_LIST'] as $attendee)
				{
					if ($attendee['status'] === 'Y')
					{
						if ($event['DT_SKIP_TIME'] !== 'Y')
						{
							$excludeDate = CCalendar::Date(CCalendar::DateWithNewTime(CCalendar::Timestamp($event['DATE_FROM']), $excludeDateTs));
						}

						$CACHE_MANAGER->ClearByTag('calendar_user_'.$attendee["id"]);

						$meetingHost = $event['MEETING']['MEETING_CREATOR'] ?? $event['MEETING_HOST'];
						CCalendarNotify::Send([
							"mode" => 'cancel_this',
							"name" => $event['NAME'],
							"from" => $excludeDate,
							"guestId" => $attendee["id"],
							"eventId" => $event['PARENT_ID'],
							"userId" => $userId > 0 ? $userId : $meetingHost,
							"fields" => $event,
						]);
					}
				}
			}
		}
	}

	public static function getDiskUFFileNameList($valueList = [])
	{
		$result = [];

		if (
			!empty($valueList)
			&& is_array($valueList)
			&& Loader::includeModule('disk')
		)
		{
			$attachedIdList = [];
			foreach($valueList as $value)
			{
				[$type, $realValue] = FileUserType::detectType($value);
				if ($type === FileUserType::TYPE_NEW_OBJECT)
				{
					$file = \Bitrix\Disk\File::loadById($realValue, array('STORAGE'));
					$result[] = strip_tags($file->getName());
				}
				else
				{
					$attachedIdList[] = $realValue;
				}
			}

			if (!empty($attachedIdList))
			{
				$attachedObjects = AttachedObject::getModelList(array(
					'with' => array('OBJECT'),
					'filter' => array(
						'ID' => $attachedIdList,
					),
				));
				foreach($attachedObjects as $attachedObject)
				{
					$file = $attachedObject->getFile();
					$result[] = strip_tags($file->getName());
				}
			}
		}

		return $result;
	}

	public static function getSearchIndexContent($eventId)
	{
		$res = '';
		if ((int)$eventId > 0)
		{
			$event = Internals\EventTable::query()
				->setSelect(['ID', 'DELETED', 'SEARCHABLE_CONTENT'])
				->where('ID', (int)$eventId)
				->where('DELETED', 'N')
				->exec()->fetch()
			;

			$res = !empty($event['SEARCHABLE_CONTENT']) ? $event['SEARCHABLE_CONTENT'] : '';
		}

		return $res;
	}

	public static function updateSearchIndex($eventIdList = [], $params = []): void
	{
		if (isset($params['isNew']) && $params['isNew'] === false)
		{
			$targetChangedFields = ['NAME', 'DESCRIPTION', 'LOCATION'];

			if (
				isset($params['changedFields'])
				&& empty(array_intersect($params['changedFields'], $targetChangedFields))
			)
			{
				return;
			}
		}

		if (isset($params['events']))
		{
			$events = $params['events'];
		}
		else
		{
			$events = self::getList([
				'arFilter' => [
					'ID' => $eventIdList,
					'DELETED' => 'N',
				],
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'checkPermissions' => false,
				'setDefaultLimit' => false,
				'userId' => $params['userId'] ?? null,
			]);
		}

		if (is_array($events))
		{
			$event = current($events);
			$eventId = (int)$event['ID'];
			$content = self::formatSearchIndexContent($event);

			if (!empty($params['updateAllByParent']) && $eventId === (int)$event['PARENT_ID'])
			{
				Internals\EventTable::updateByFilter(
					['=PARENT_ID' => $eventId],
					['SEARCHABLE_CONTENT' => $content]
				);
			}
			else
			{
				Internals\EventTable::update($eventId, [
					['SEARCHABLE_CONTENT' => $content],
				]);
			}
		}
	}

	public static function formatSearchIndexContent($entry = []): string
	{
		$content = '';
		if (!empty($entry))
		{
			$content = static::prepareToken(
				Emoji::encode($entry['NAME'])
				. ' '
				. Emoji::encode($entry['DESCRIPTION'])
				. ' '
				. Emoji::encode(\CCalendar::GetTextLocation($entry['LOCATION'] ?? ''))
			);

			if ($entry['IS_MEETING'])
			{
				$attendeesWereHandled = false;
				if (!empty($entry['ATTENDEE_LIST']) && is_array($entry['ATTENDEE_LIST']))
				{
					foreach($entry['ATTENDEE_LIST'] as $attendee)
					{
						if (isset(self::$userIndex[$attendee['id']]))
						{
							$content .= ' '.static::prepareToken(self::$userIndex[$attendee['id']]['DISPLAY_NAME']);
						}
					}
					$attendeesWereHandled = true;
				}

				if (!empty($entry['ATTENDEES_CODES']))
				{
					if ($attendeesWereHandled)
					{
						$attendeesCodes = [];
						foreach($entry['ATTENDEES_CODES'] as $code)
						{
							if (!str_starts_with($code, 'U'))
							{
								$attendeesCodes[] = $code;
							}
						}
					}
					else
					{
						$attendeesCodes = $entry['ATTENDEES_CODES'];
					}
					$content .= ' ' . static::prepareToken(
						implode(
							' ',
							Bitrix\Socialnetwork\Item\LogIndex::getEntitiesName($attendeesCodes)
						)
					);
				}
			}
			else
			{
				$content .= ' ' . static::prepareToken(CCalendar::GetUserName($entry['CREATED_BY']));
			}

			try
			{
				if (!empty($entry['UF_WEBDAV_CAL_EVENT']) && Loader::includeModule('disk'))
				{
					$fileNameList = self::getDiskUFFileNameList($entry['UF_WEBDAV_CAL_EVENT']);
					if (!empty($fileNameList))
					{
						$content .= ' '.static::prepareToken(implode(' ', $fileNameList));
					}
				}
			}
			catch (RuntimeException $e)
			{
			}

			try
			{
				if (!empty($entry['UF_CRM_CAL_EVENT']) && Loader::includeModule('crm'))
				{
					$uf = $entry['UF_CRM_CAL_EVENT'];

					foreach ($uf as $item)
					{
						$crmElement = explode('_', $item);
						$type = $crmElement[ 0 ];

						$typeId = \CCrmOwnerType::ResolveID(\CCrmOwnerTypeAbbr::ResolveName($type));
						$title = \CCrmOwnerType::GetCaption($typeId, $crmElement[ 1 ]);

						$content .= ' '.static::prepareToken($title);
					}
				}
			}
			catch (RuntimeException $e)
			{
			}
		}

		return $content;
	}

	public static function GetCount()
	{
		global $DB;
		$count = 0;
		$res = $DB->Query('select count(*) as c from b_calendar_event');

		if ($res = $res->Fetch())
		{
			$count = $res['c'];
		}

		return $count;
	}

	public static function updateColor($eventId, $color = '')
	{
		Internals\EventTable::update((int)$eventId, ['COLOR' => $color]);

		$eventAttendeeId = self::getEventAttendeeIdByEventIdAndUserId($eventId, CCalendar::GetCurUserId());
		if ($eventAttendeeId)
		{
			self::updateEventAttendeeColor($eventAttendeeId, $color);
		}
	}

	/**
	 * Applies ROT13 transform to search token, in order to bypass default mysql search blacklist.
	 * @param string $token Search token.
	 * @return string
	 */
	public static function prepareToken($token)
	{
		return str_rot13($token);
	}

	public static function isFullTextIndexEnabled()
	{
		return COption::GetOptionString("calendar", "~ft_b_calendar_event", false);
	}

	public static function getUserIndex()
	{
		return self::$userIndex;
	}

	public static function getEventForViewInterface($entryId, $params = [])
	{
		$params['eventDate'] = ($params['eventDate'] ?? null);
		$params['timezoneOffset'] = ($params['timezoneOffset'] ?? null);
		$params['userId'] = ($params['userId'] ?? null);

		$fromTs = \CCalendar::Timestamp($params['eventDate']) - $params['timezoneOffset'];
		$userDateFrom = \CCalendar::Date($fromTs);

		$entry = self::GetList([
			'arFilter' => [
				"ID" => $entryId,
				"DELETED" => "N",
				"FROM_LIMIT" => $userDateFrom,
				"TO_LIMIT" => $userDateFrom,
			],
			'parseRecursion' => true,
			'maxInstanceCount' => 1,
			'preciseLimits' => true,
			'fetchAttendees' => true,
			'checkPermissions' => true,
			'setDefaultLimit' => false,
			'getUserfields' => true,
		]);

		if (
			$params['eventDate']
			&& isset($entry[0])
			&& is_array($entry[0])
			&& $entry[0]['RRULE']
			&& $entry[0]['EXDATE']
			&& in_array($params['eventDate'], self::GetExDate($entry[0]['EXDATE']))
		)
		{
			$entry = self::GetList([
                'arFilter' => [
                    "RECURRENCE_ID" => $entryId,
                    "DELETED" => "N",
                    "FROM_LIMIT" => $params['eventDate'],
                    "TO_LIMIT" => $params['eventDate'],
                ],
                'parseRecursion' => true,
                'maxInstanceCount' => 1,
                'preciseLimits' => true,
                'fetchAttendees' => true,
                'checkPermissions' => true,
                'setDefaultLimit' => false,
                'getUserfields' => true,
            ]);
		}

		if (!$entry || !is_array($entry[0]))
		{
			$entry = self::GetList([
				'arFilter' => [
					"ID" => $entryId,
					"DELETED" => "N",
				],
				'parseRecursion' => true,
				'maxInstanceCount' => 1,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false,
                'getUserfields' => true,
			]);
		}

		// Here we can get events with wrong RRULE ('parseRecursion' => false)
		if (!$entry || !is_array($entry[0]))
		{
			$entry = self::GetList([
				'arFilter' => [
					"ID" => $entryId,
					"DELETED" => "N",
				],
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false,
                'getUserfields' => true,
			]);
		}

		if ($entry && is_array($entry[0]))
		{
			$entry = $entry[0];
			if ($entry['IS_MEETING'] && (int)$entry['PARENT_ID'] !== (int)$entry['ID'])
			{
				$parentEntry = false;
				$parentEntryList = self::GetList([
					'arFilter' => [
						"ID" => (int)$entry['PARENT_ID'],
					],
					'parseRecursion' => false,
					'maxInstanceCount' => 1,
					'preciseLimits' => false,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false,
                    'getUserfields' => true,

				]);

				if (!empty($parentEntryList[0]) && is_array($parentEntryList[0]))
				{
					$parentEntry = $parentEntryList[0];
				}

				if ($parentEntry)
				{
					if ($parentEntry['DELETED'] === 'Y')
					{
						self::CleanEventsWithDeadParents();
						$entry = false;
					}

					if ((int)$parentEntry['MEETING_HOST'] === (int)$params['userId'])
					{
						$entry = $parentEntry;
					}
				}
			}

			if (
				isset($entry['UF_WEBDAV_CAL_EVENT'])
				&& is_array($entry['UF_WEBDAV_CAL_EVENT'])
				&& empty($entry['UF_WEBDAV_CAL_EVENT'])
			)
			{
				$entry['UF_WEBDAV_CAL_EVENT'] = null;
			}
		}

		if (
			($entry['IS_MEETING'] ?? null)
			&& !empty($entry['ATTENDEE_LIST'])
			&& is_array($entry['ATTENDEE_LIST'])
			&& (int)$entry['CREATED_BY'] !== $params['userId']
			&& (int)$entry['OWNER_ID'] !== $params['userId']
			&& ($params['recursion'] ?? null) !== false
			&& ($entry['CAL_TYPE'] !== Dictionary::CALENDAR_TYPE['open_event'])
		)
		{
			foreach($entry['ATTENDEE_LIST'] as $attendee)
			{
				if ((int)$attendee['id'] === (int)$params['userId'])
				{
					$entry = self::GetList([
						'arFilter' => [
							'PARENT_ID' => $entry['PARENT_ID'],
							'OWNER_ID' => $params['userId'],
							'DELETED' => 'N',
						],
						'parseRecursion' => false,
						'maxInstanceCount' => 1,
						'preciseLimits' => false,
						'fetchAttendees' => false,
						'checkPermissions' => true,
						'setDefaultLimit' => false,
                        'getUserfields' => true,
					]);

					if ($entry && is_array($entry[0]) && $entry[0]['CAL_TYPE'] === 'location')
					{
						$params['recursion'] = false;
						$entry = self::getEventForViewInterface($entry[0]['PARENT_ID'], $params);
					}
					else if ($entry && is_array($entry[0]))
					{
						$params['recursion'] = false;
						$entry = self::getEventForViewInterface($entry[0]['ID'], $params);
					}
				}
			}
		}

		return $entry;
	}

	public static function getEventForEditInterface($entryId, $params = [])
	{
		$entry = self::GetList(
			[
				'arFilter' => [
					"ID" => $entryId,
					"DELETED" => "N",
					"FROM_LIMIT" => $params['eventDate'] ?? null,
					"TO_LIMIT" => $params['eventDate'] ?? null,
				],
				'parseRecursion' => true,
				'maxInstanceCount' => 1,
				'preciseLimits' => true,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false,
			]
		);

		if (!$entry || !is_array($entry[0]))
		{
			$entry = self::GetList(
				[
					'arFilter' => [
						"ID" => $entryId,
						"DELETED" => "N",
					],
					'parseRecursion' => true,
					'maxInstanceCount' => 1,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false,
				]
			);
		}

		// Here we can get events with wrong RRULE ('parseRecursion' => false)
		if (!$entry || !is_array($entry[0]))
		{
			$entry = self::GetList(
				[
					'arFilter' => [
						"ID" => $entryId,
						"DELETED" => "N",
					],
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false,
				]
			);
		}

		$entry = is_array($entry) ? $entry[0] : null;

		if (is_array($entry) && $entry['ID'] !== $entry['PARENT_ID'])
		{
			return self::getEventForEditInterface($entry['PARENT_ID'], $params);
		}

		return $entry;
	}

	public static function handleAccessCodes($accessCodes = [], $params = [])
	{
		$accessCodes = is_array($accessCodes) ? $accessCodes : [];
		$userId = isset($params['userId']) ? $params['userId'] : \CCalendar::getCurUserId();

		if (empty($accessCodes))
		{
			$accessCodes[] = 'U'.$userId;
		}

		$accessCodes = array_unique($accessCodes);

		return $accessCodes;
	}

	/**
	 * @param mixed $entryFields
	 * @param array $params
	 * @param mixed $currentEvent
	 * @param int $userId
	 * @throws Rooms\OccupancyCheckerException
	 */
	public static function checkLocationOccupancy(
		mixed $entryFields,
		array $params,
		mixed $currentEvent,
		int $userId
	)
	{
		$entryFields['TZ_FROM'] ??= null;
		$entryFields['TZ_TO'] ??= null;
		$entryFields['TZ_OFFSET_FROM'] ??= 0;
		$entryFields['TZ_OFFSET_TO'] ??= 0;

		$fieldsToCheckOccupancy = $entryFields;
		if (!empty($params['checkLocationOccupancyFields']))
		{
			$fieldsToCheckOccupancy = $params['checkLocationOccupancyFields'];
			$fieldsToCheckOccupancy['LOCATION'] = [
				'NEW' => $fieldsToCheckOccupancy['LOCATION'] ?? '',
			];
			self::CheckFields($fieldsToCheckOccupancy, $currentEvent, $userId);
		}

		$occupancyCheckResult = (new Rooms\OccupancyChecker())->check($fieldsToCheckOccupancy);
		if (!$occupancyCheckResult->isSuccess())
		{
			$disturbingEventsFormatted = $occupancyCheckResult->getData()['disturbingEventsFormatted'];
			if ($occupancyCheckResult->getData()['isDisturbingEventsAmountOverShowLimit'])
			{
				$message = Loc::getMessage('EC_LOCATION_REPEAT_BUSY_TOO_MANY', [
					'#DATES#' => $disturbingEventsFormatted,
				]);
			}
			else
			{
				$message = Loc::getMessage('EC_LOCATION_REPEAT_BUSY', [
					'#DATES#' => $disturbingEventsFormatted,
				]);
			}

			throw new Rooms\OccupancyCheckerException($message);
		}
	}

	/**
	 * @param bool $isNewEvent
	 * @param mixed $entryFields
	 * @param int $userId
	 * @param mixed $currentEvent
	 * @return mixed
	 */
	public static function tryingToFindEventSection(
		bool $isNewEvent,
		mixed $entryFields,
		int $userId,
		mixed $currentEvent
	): mixed
	{
		// It's new event we have to find section where to put it automatically
		if ($isNewEvent)
		{
			if (
				!empty($entryFields['IS_MEETING'])
				&& !empty($entryFields['PARENT_ID'])
				&& ($entryFields['CAL_TYPE'] ?? null) === 'user'
			)
			{
				$sectionId = CCalendar::GetMeetingSection($entryFields['OWNER_ID'] ?? null);
			}
			else
			{
				$sectionId = CCalendarSect::GetLastUsedSection(
					$entryFields['CAL_TYPE'] ?? null, $entryFields['OWNER_ID'] ?? null, $userId
				);
			}

			if ($sectionId)
			{
				$res = CCalendarSect::GetList([
					'arFilter' => [
						'CAL_TYPE' => $entryFields['CAL_TYPE'] ?? null, 'OWNER_ID' => $entryFields['OWNER_ID'] ?? null,
						'ID' => $sectionId,
					],
				]);

				if (empty($res[0]))
				{
					$sectionId = false;
				}
			}
			else
			{
				$sectionId = false;
			}

			if (empty($sectionId))
			{
				$sectRes = CCalendarSect::GetSectionForOwner($entryFields['CAL_TYPE'], $entryFields['OWNER_ID'], true);
				$sectionId = $sectRes['sectionId'];
			}
		}
		else
		{
			$sectionId = $currentEvent['SECTION_ID'] ?? $currentEvent['SECT_ID'];
		}

		return $sectionId;
	}

	/**
	 * @param array $involvedAttendees
	 * @param $meetingHost
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function generateUserIndex(array $involvedAttendees, $meetingHost): array
	{
		$userIndex = [];

		// Here we collecting information about EXTERNAL_AUTH_ID to
		// know if some of the users are external
		$orm = UserTable::getList([
			'filter' => [
				'=ID' => $involvedAttendees, '=ACTIVE' => 'Y',
			], 'select' => [
				'ID', 'EXTERNAL_AUTH_ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL', 'TITLE',
				'UF_DEPARTMENT',
			],
		]);

		while ($user = $orm->fetch())
		{
			if ($user['ID'] === ($meetingHost ?? null))
			{
				$user['STATUS'] = 'accepted';
			}
			else
			{
				$user['STATUS'] = 'needs_action';
			}

			$userIndex[$user['ID']] = $user;
		}

		return $userIndex;
	}

	/**
	 * @param string|null $serializedMeetingInfo
	 * @return string|null
	 */
	private static function getSenderEmailForIcal(string $serializedMeetingInfo = null): ?string
	{
		$meetingInfo = unserialize($serializedMeetingInfo, ['allowed_classes' => false]);

		return !empty($meetingInfo) && !empty($meetingInfo['MAIL_FROM'])
			? $meetingInfo['MAIL_FROM']
			: null;
	}

	/**
	 * @param $userIndex
	 * @param $organizerId
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getSenderForIcal($userIndex, $organizerId): ?array
	{
		if (!empty($userIndex) && !empty($userIndex[$organizerId]))
		{
			return $userIndex[$organizerId];
		}

		$userOrm = UserTable::getList([
			'filter' => [
				'=ID' => $organizerId,
				'=ACTIVE' => 'Y',
			],
			'select' => [
				'ID',
				'EXTERNAL_AUTH_ID',
				'NAME',
				'LAST_NAME',
				'SECOND_NAME',
				'LOGIN',
				'EMAIL',
				'TITLE',
				'UF_DEPARTMENT',
			],
		]);

		if ($user = $userOrm->fetch())
		{
			return $user;
		}

		return null;
	}

	/**
	 * @deprecated
	 * @param array $event
	 * @param array $fields
	 */
	public static function updateEventFields(array $event, array $fields): void
	{
		global $DB;

		if (!$fields)
		{
			return;
		}

		CTimeZone::Disable();

		$strSql = "UPDATE b_calendar_event SET ".
			$DB->PrepareUpdate("b_calendar_event", $fields)
			. " WHERE ID=" . (int)$event['ID'] . "; ";
		$DB->Query($strSql);


		CTimeZone::Enable();
	}

	/**
	 * @deprecated
	 * @param int $eventId
	 * @param string $status
	 */
	public static function updateSyncStatus(int $eventId, string $status): void
	{
		global $DB;

		if (in_array($status, Bitrix\Calendar\Sync\Google\Dictionary::SYNC_STATUS, true))
		{
			$DB->Query(
				"UPDATE b_calendar_event"
				. " SET " . $DB->PrepareUpdate('b_calendar_event', ['SYNC_STATUS' => $status])
				. " WHERE ID = " . $eventId . ";"
			);
		}
	}

	public static function checkLocationField($location, $isNewEvent)
	{
		$parsedNew = Bitrix\Calendar\Rooms\Util::parseLocation($location['NEW']);
		if (!empty($parsedNew['room_event_id']))
		{
			$location['NEW'] = 'calendar_' . $parsedNew['room_id'];
		}

		if ($isNewEvent)
		{
			$location['OLD'] = '';
		}

		return $location;
	}

	/**
	 * @param array $childParams
	 * @return void
	 */
	public static function prepareArFieldBeforeSyncEvent(array &$childParams): void
	{
		if (is_string($childParams['arFields']['MEETING']))
		{
			$childParams['arFields']['MEETING'] = unserialize($childParams['arFields']['MEETING'], ['allowed_classes' => false]);
		}

		$childParams['arFields']['MEETING']['LANGUAGE_ID'] = CCalendar::getUserLanguageId((int)$childParams['arFields']['OWNER_ID']);
	}

	/**
	 * @param array $childParams
	 * @return string
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function getUidForChildEvent(array $event): string
	{
		return UidGenerator::createInstance()
			->setPortalName(Util::getServerName())
			->setDate(new Date(
				Util::getDateObject(
					$event['DATE_FROM'],
					($event['SKIP_TIME'] ?? false),
					$event['TZ_FROM'] ?? null,
				)
			))
			->setUserId((int)$event['OWNER_ID'])
			->getUidWithDate()
		;
	}

	/**
	 * @param array $eventData
	 * @param array $params
	 *
	 * @return void
	 * @throws \Bitrix\Calendar\Core\Base\BaseException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	private static function onEventDelete(
		array $eventData,
		array $params = []
	): void
	{
		/** @var \Bitrix\Calendar\Core\Mappers\Factory $mapperFactory */
		$mapperFactory = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$exDate = null;
		$originalDate = null;
		if (empty($eventData))
		{
			return;
		}
		/** @var Section $section */
		$section = $mapperFactory->getSection()->getById((int)$eventData['SECTION_ID']);

		if ($section === null)
		{
			return;
		}

		$factories = FactoriesCollection::createBySection($section);

		if ($factories->count() === 0)
		{
			return;
		}

		$recId = $eventData['RECURRENCE_ID'];
		if ($recId)
		{
			$exDate = $eventData['DATE_FROM'];
			$originalDate = $eventData['ORIGINAL_DATE_FROM'];
			$originalEventData = $eventData;
			$eventData = Internals\EventTable::getRow([
				'filter' => [
					'=PARENT_ID' => $eventData['RECURRENCE_ID'],
					'=OWNER_ID' => $eventData['OWNER_ID'],
					'=DELETED' => 'N',
				],
			]);
		}
		if ($eventData)
		{
			$event = (new \Bitrix\Calendar\Core\Builders\EventBuilderFromArray($eventData))->build();
		}
		else
		{
			return;
		}

		$syncManager = new Synchronization($factories);
		$context = new Context([]);

		// TODO: there's a dependence of the general on the particular
		if (
			!empty($params['originalFrom'])
			&& (int)($params['userId'] ?? null) === (int)$eventData['OWNER_ID']
		)
		{
			$context->add('sync', 'originalFrom', $params['originalFrom']);
			$connection = $mapperFactory->getConnection()->getMap([
				'=ACCOUNT_TYPE' => $params['originalFrom'],
				'=ENTITY_TYPE' => $event->getCalendarType(),
				'=ENTITY_ID' => $event->getOwner()?->getId(),
			])->fetch();
			if ($connection)
			{
				$syncManager->upEventVersion(
					$event,
					$connection,
					$arFields['VERSION'] ?? 1
				);
			}
		}

		if ($exDate)
		{
			$exDate = new \Bitrix\Main\Type\Date(CCalendar::Date(CCalendar::Timestamp($exDate), false));
			$context->add('sync', 'excludeDate', new \Bitrix\Calendar\Core\Base\Date($exDate));
		}

		if ($originalDate)
		{
			$originalDate = new \Bitrix\Main\Type\DateTime(CCalendar::Date(CCalendar::Timestamp($originalDate)));
			$context->add('sync', 'originalDate', new \Bitrix\Calendar\Core\Base\Date($originalDate));
		}

		if ($recId)
		{
			$result = $syncManager->deleteInstance($event, $context);
			if (!empty($originalEventData) && !$result->isSuccess())
			{
				$originalEvent = (new \Bitrix\Calendar\Core\Builders\EventBuilderFromArray($originalEventData))->build();
				$syncManager->deleteEvent($originalEvent, $context);
			}
		}
		else
		{
			$syncManager->deleteEvent($event, $context);
		}
	}

	private static function isNewAttendee($attendees, $currentId): bool
	{
		foreach ($attendees as $attendee)
		{
			if ((int)$attendee['id'] === (int)$currentId)
			{
				return false;
			}
		}

		return true;
	}

	public static function sortByDay(array $byDay)
	{
		uasort($byDay, function($a, $b){
			$map = [
				'MO' => 0,
				'TU' => 1,
				'WE' => 2,
				'TH' => 3,
				'FR' => 4,
				'SA' => 5,
				'SU' => 6,
			];

			return $map[$a] < $map[$b] ? -1 : 1;
		});

		return $byDay;
	}

	/**
	 * @param array $arFields
	 * @param int $toTs
	 * @param $currentExDate
	 *
	 * @return void
	 */
	private static function checkRecurringRuleField(array &$arFields, int $toTs, $currentExDate): void
	{
		// Check rrules
		if (
			!empty($arFields['RRULE']['FREQ'])
			&& in_array($arFields['RRULE']['FREQ'], ['HOURLY', 'DAILY', 'MONTHLY', 'YEARLY', 'WEEKLY'])
		)
		{
			// Interval
			if (isset($arFields['RRULE']['INTERVAL']) && (int)$arFields['RRULE']['INTERVAL'] > 1)
				$arFields['RRULE']['INTERVAL'] = (int)$arFields['RRULE']['INTERVAL'];

			// Until date

			$untilTs = CCalendar::Timestamp($arFields['RRULE']['UNTIL'] ?? null, false, false);
			if (isset($arFields['RRULE']['COUNT']))
			{
				$arFields['RRULE']['COUNT'] = (int)$arFields['RRULE']['COUNT'];
			}

			if (!$untilTs)
			{
				$arFields['RRULE']['UNTIL'] = CCalendar::GetMaxDate();
				$untilTs = CCalendar::Timestamp($arFields['RRULE']['UNTIL'], false, false);
			}
			elseif ($untilTs + CCalendar::GetDayLen() < $toTs)
			{
				$untilTs = $toTs;
			}

			$arFields['DATE_TO_TS_UTC'] = $untilTs + CCalendar::GetDayLen();
			if (isset($arFields['RRULE']['COUNT']))
			{
				$arFields['DATE_TO_TS_UTC'] = self::calculateUntilForCountRRule($arFields);
			}

			$arFields['RRULE']['UNTIL'] = CCalendar::Date($untilTs, false);
			unset($arFields['RRULE']['~UNTIL']);

			if (isset($arFields['RRULE']['BYDAY']))
			{
				if (is_array($arFields['RRULE']['BYDAY']))
				{
					$BYDAY = $arFields['RRULE']['BYDAY'];
				}
				else
				{
					$BYDAY = [];
					$days = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
					$bydays = explode(',', $arFields['RRULE']['BYDAY']);
					foreach ($bydays as $day)
					{
						$day = mb_strtoupper($day);
						if (in_array($day, $days, true))
						{
							$BYDAY[] = $day;
						}
					}
				}
				$arFields['RRULE']['BYDAY'] = implode(',', $BYDAY);
			}

			if (isset($arFields["EXDATE"]))
			{
				$excludeDates = self::GetExDate($arFields["EXDATE"]);
			}
			else
			{
				$excludeDates = self::GetExDate($currentExDate);
			}

			if (!empty($excludeDates) && $untilTs)
			{
				$arFields["EXDATE"] = self::SetExDate($excludeDates, $untilTs);
			}

			$arFields['RRULE'] = self::PackRRule($arFields['RRULE']);
		}
		else
		{
			$arFields['RRULE'] = '';
			$arFields['EXDATE'] = '';
		}
	}

	protected static function calculateUntilForCountRRule(array $arFields, int $offsetTs = 0): int
	{
		$rrule = self::ParseRRULE($arFields['RRULE']);
		$interval = (int)$rrule['INTERVAL'];
		$count = (int)$rrule['COUNT'];

		$fromTS = CCalendar::Timestamp($arFields['DATE_FROM']) + $offsetTs;
		$toTS = CCalendar::Timestamp($arFields['DATE_TO']) + $offsetTs;
		$length = $toTS - $fromTS;

		$h = (int)date('H', $toTS);
		$min = (int)date('i', $toTS);
		$d = (int)date('d', $toTS);
		$m = (int)date('m', $toTS);
		$y = (int)date('Y', $toTS);

		if ($rrule['FREQ'] === 'DAILY')
		{
			return mktime($h, $min, 0, $m, $d + $count * $interval, $y);
		}
		if ($rrule['FREQ'] === 'MONTHLY')
		{
			return mktime($h, $min, 0, $m + $count * $interval, $d, $y);
		}
		if ($rrule['FREQ'] === 'YEARLY')
		{
			return mktime($h, $min, 0, $m, $d, $y + $count * $interval);
		}
		if ($rrule['FREQ'] === 'WEEKLY')
		{
			$byDay = $rrule['BYDAY'];

			if (!is_array($byDay))
			{
				$days = [];
				$weekDays = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

				foreach (explode(',', $byDay) as $day)
				{
					if (in_array($day, $weekDays, true))
					{
						$days[] = $day;
					}
				}

				$byDay = $days;
			}

			if (empty($byDay))
			{
				return 0;
			}

			return mktime($h, $min, $length, $m, $d + round(($count - 1) / count($byDay) * 7 * $interval), $y);
		}

		return 0;
	}

	/**
	 * @param $PARENT_ID
	 * @param $userId
	 * @param $status
	 *
	 * @return void
	 *
	 * @throws \Bitrix\Calendar\Core\Base\BaseException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function pushUpdateDescriptionToQueue($PARENT_ID, $userId, $status): void
	{
		$message = (new \Bitrix\Calendar\Core\Queue\Message\Message())
			->setBody([
				'parentId' => $PARENT_ID,
				'userId' => $userId,
				'meetingStatus' => $status,
			])
			->setRoutingKey('calendar:update_meeting_status');
		(new \Bitrix\Calendar\Core\Queue\Producer\Producer())->send($message);
	}

	public static function getEventPermissions(array $event, int $userId = 0)
	{
		if ($userId <= 0)
		{
			$userId = CCalendar::GetUserId();
		}
		$accessController = new EventAccessController($userId);
		$eventModel = self::getEventModelForPermissionCheck((int)($event['ID'] ?? 0), $event, $userId);
		$request = [
			ActionDictionary::ACTION_EVENT_VIEW_FULL => [],
			ActionDictionary::ACTION_EVENT_VIEW_TIME => [],
			ActionDictionary::ACTION_EVENT_VIEW_TITLE => [],
			ActionDictionary::ACTION_EVENT_VIEW_COMMENTS => [],
			ActionDictionary::ACTION_EVENT_EDIT => [],
			ActionDictionary::ACTION_EVENT_EDIT_LOCATION => [],
			ActionDictionary::ACTION_EVENT_EDIT_ATTENDEES => [],
			ActionDictionary::ACTION_EVENT_DELETE => [],
		];
		$accessResult = $accessController->batchCheck($request, $eventModel);

		return [
			'view_full' => $accessResult[ActionDictionary::ACTION_EVENT_VIEW_FULL],
			'view_time' => $accessResult[ActionDictionary::ACTION_EVENT_VIEW_TIME],
			'view_title' => $accessResult[ActionDictionary::ACTION_EVENT_VIEW_TITLE],
			'view_comments' => $accessResult[ActionDictionary::ACTION_EVENT_VIEW_COMMENTS],
			'edit' => $accessResult[ActionDictionary::ACTION_EVENT_EDIT],
			'editLocation' => $accessResult[ActionDictionary::ACTION_EVENT_EDIT_LOCATION],
			'editAttendees' => $accessResult[ActionDictionary::ACTION_EVENT_EDIT_ATTENDEES],
			'delete' => $accessResult[ActionDictionary::ACTION_EVENT_DELETE],
		];
	}

	public static function getEventModelForPermissionCheck(int $eventId, array $event = [], int $userId = 0): EventModel
	{
		if ($userId <= 0)
		{
			$userId = CCalendar::GetUserId();
		}

		$select = [
			'ID',
			'OWNER_ID',
			'SECTION_ID',
			'CAL_TYPE',
			'EVENT_TYPE',
			'RRULE',
			'DT_SKIP_TIME',
			'DT_LENGTH',
			'PARENT_ID',
			'DATE_FROM',
			'DATE_TO',
			'PARENT_ID',
			'TZ_FROM',
			'TZ_TO',
			'TZ_OFFSET_FROM',
			'TZ_OFFSET_TO',
			'DATE_FROM_TS_UTC',
			'DATE_TO_TS_UTC',
			'CREATED_BY',
			'ACCESSIBILITY',
			'REMIND',
			'MEETING_HOST',
			'MEETING_STATUS',
			'IMPORTANCE',
		];

		$userEvent = self::GetList(
			[
				'arFilter' => [
					'PARENT_ID' => $eventId,
					'OWNER_ID' => $userId,
					'CAL_TYPE' => [Dictionary::CALENDAR_TYPE['user'], Dictionary::CALENDAR_TYPE['open_event']],
					'DELETED' => 'N',
				],
				'arSelect' => $select,
				'parseRecursion' => false,
				'fetchMeetings' => false,
				'userId' => $userId,
				'checkPermissions' => false,
				'getPermissions' => false,
			]
		);

		if ($userEvent)
		{
			$userEvent = $userEvent[0];
		}

		if (!$userEvent && (empty($event) || ((int)($event['ID'] ?? 0) !== $eventId)))
		{
			$event = self::GetList([
				'arFilter' => [
					'ID' => $eventId,
					'DELETED' => 'N',
				],
				'arSelect' => $select,
				'parseRecursion' => false,
				'fetchMeetings' => false,
				'userId' => $userId,
				'checkPermissions' => false,
				'getPermissions' => false,
			]);

			if ($event)
			{
				$event = $event[0];
			}
		}

		return EventModel::createFromArray($userEvent ?: $event ?: []);
	}

	public static function checkAttendeeBelongsToEvent($eventId, $userId)
	{
		if (empty($eventId) || empty($userId))
		{
			return false;
		}

		if (isset(self::$attendeeBelongingToEvent[$eventId][$userId]))
		{
			return self::$attendeeBelongingToEvent[$eventId][$userId];
		}

		$event = Internals\EventTable::query()
			->setSelect(['ID'])
			->where('PARENT_ID', $eventId)
			->where('OWNER_ID', $userId)
			->exec()->fetch()
		;

		if (!isset(self::$attendeeBelongingToEvent[$eventId]))
		{
			self::$attendeeBelongingToEvent[$eventId] = [];
		}
		if (!isset(self::$attendeeBelongingToEvent[$eventId][$userId]))
		{
			self::$attendeeBelongingToEvent[$eventId][$userId] = !empty($event);
		}

		return self::$attendeeBelongingToEvent[$eventId][$userId];
	}

	public static function getLimitDates(int $yearFrom, int $monthFrom, int $yearTo, int $monthTo): array
	{
		$userTimezoneName = \CCalendar::GetUserTimezoneName(\CCalendar::GetUserId());
		$offset = Util::getTimezoneOffsetUTC($userTimezoneName);

		return [
			'from' => \CCalendar::Date(mktime(0, 0, 0, $monthFrom, 1, $yearFrom) - $offset, false),
			'to' => \CCalendar::Date(mktime(0, 0, 0, $monthTo, 1, $yearTo) - $offset, false),
		];
	}

	private static function getOpenEventSection(): ?\Bitrix\Calendar\Core\Section\Section
	{
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');

		$openEventSection = $mapperFactory->getSection()->getMap([
			'=CAL_TYPE' => Dictionary::CALENDAR_TYPE['open_event'],
		])->fetch();

		if (!$openEventSection)
		{
			return null;
		}

		return $openEventSection;
	}

	private static function getParentCollabConnections(array $eventList): array
	{
		$parentIds = [];
		foreach ($eventList as $event)
		{
			if (
				!empty($event['EVENT_TYPE'])
				&& !in_array(
					$event['EVENT_TYPE'],
					[Dictionary::EVENT_TYPE['collab'], Dictionary::EVENT_TYPE['shared_collab']],
					true
				)
			)
			{
				continue;
			}

			if ($event['ID'] !== $event['PARENT_ID'])
			{
				$parentIds[$event['PARENT_ID']] = (int)$event['PARENT_ID'];
			}
		}

		if (empty($parentIds))
		{
			return $parentIds;
		}

		$collection = Internals\EventTable::query()
			->whereIn('ID', $parentIds)
			->where('CAL_TYPE', Dictionary::CALENDAR_TYPE['group'])
			->setSelect(['ID', 'PARENT_ID', 'OWNER_ID'])
			->fetchCollection()
		;

		return array_combine($collection->getParentIdList(), $collection->getOwnerIdList());
	}

	private static function getCollabIdByEvent(array $event, array $parentCollabConnections): ?int
	{
		if (
			!($event['EVENT_TYPE'] ?? null)
			|| !in_array(
				$event['EVENT_TYPE'],
				[Dictionary::EVENT_TYPE['collab'], Dictionary::EVENT_TYPE['shared_collab']],
				true
			)
		)
		{
			return null;
		}

		$collabId = null;
		$parentId = (int)$event['PARENT_ID'];
		if ((int)$event['ID'] !== $parentId && !empty($parentCollabConnections[$parentId]))
		{
			$collabId = $parentCollabConnections[$parentId];
		}
		else if ($event['CAL_TYPE'] === Dictionary::CALENDAR_TYPE['group'])
		{
			$collabId = (int)$event['OWNER_ID'];
		}

		return $collabId;
	}

	/**
	 * @param int $parentId
	 *
	 * @return int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getCollabIdByParentId(int $parentId): int
	{
		if (isset(self::$collabIdByParent[$parentId]))
		{
			return self::$collabIdByParent[$parentId];
		}

		$result = Internals\EventTable::query()
			->setSelect(['ID', 'CAL_TYPE', 'OWNER_ID'])
			->where('CAL_TYPE', Dictionary::CALENDAR_TYPE['group'])
			->where('ID', $parentId)
			->setLimit(1)
			->exec()->fetch()
		;

		self::$collabIdByParent[$parentId] = (int)($result['OWNER_ID'] ?? 0);

		return self::$collabIdByParent[$parentId];
	}
}
