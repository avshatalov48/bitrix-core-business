<?php
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/calendar/classes/general/calendar.php");

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Sync\Factories\FactoriesCollection;
use Bitrix\Calendar\Core\Event\Tools\UidGenerator;
use Bitrix\Calendar\Sync\Managers\Synchronization;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Type\DateTime;
use Bitrix\Calendar\ICal\
{Builder,
	Builder\Attendee,
	Builder\AttendeesCollection,
	IncomingEventManager,
	MailInvitation\Context as IcalMailContext,
	MailInvitation\Helper,
	MailInvitation\MailAddresser,
	MailInvitation\MailInvitationManager,
	MailInvitation\MailReceiver,
	MailInvitation\SenderCancelInvitation,
	MailInvitation\SenderEditInvitation,
	MailInvitation\SenderRequestInvitation};
use Bitrix\Calendar\ICal\Basic\ICalUtil;
use Bitrix\Calendar\Internals;
use Bitrix\Calendar\Util;
use Bitrix\Calendar\Rooms;
use Bitrix\Disk\AttachedObject;
use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use \Bitrix\Calendar\Integration\Bitrix24Manager;

class CCalendarEvent
{
	public static
		$eventUFDescriptions,
		$TextParser;

	private static
		$fields = [],
		$userIndex = [],
		$isAddIcalFailEmailError = false;

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
	 */
	public static function Edit($params = [])
	{
		global $DB, $CACHE_MANAGER;
		$entryFields = $params['arFields'] ?? [];
		$arAffectedSections = [];
		$entryChanges = [];
		$sendInvitations = ($params['sendInvitations'] ?? null) !== false;
		$sendEditNotification = ($params['sendEditNotification'] ?? null) !== false;
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
					'NEW' => $entryFields['LOCATION'] ?? null
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
					$attendeesCount = count($attendees[$currentEvent['PARENT_ID']]);
					for ($i = 0; $i < $attendeesCount; $i++)
					{
						$entryFields['ATTENDEES'][] = $attendees[$currentEvent['PARENT_ID']][$i]['USER_ID'];
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
                            $entryFields['CAL_TYPE'] ?? null,
                            $entryFields['OWNER_ID'] ?? null,
                            $userId);
					}

					if ($sectionId)
					{
						$res = CCalendarSect::GetList([
							'arFilter' => [
								'CAL_TYPE' => $entryFields['CAL_TYPE'] ?? null,
								'OWNER_ID' => $entryFields['OWNER_ID'] ?? null,
								'ID'=> $sectionId
							]
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
			}
			$entryFields['SECTION_ID'] = $sectionId;
			$arAffectedSections[] = $sectionId;

			$section = CCalendarSect::GetList(['arFilter' => ['ID' => $sectionId],
				'checkPermissions' => false,
				'getPermissions' => false
			])[0] ?? null;

			// Here we take type and owner parameters from section data
			if ($section)
			{
				$entryFields['CAL_TYPE'] = $section['CAL_TYPE'];
				$entryFields['OWNER_ID'] = $section['OWNER_ID'] ?? '';
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
			}

			$attendeesCodes = $entryFields['ATTENDEES_CODES'] ?? null;
			if (is_array($attendeesCodes))
			{
				$entryFields['ATTENDEES_CODES'] = implode(',', $attendeesCodes);
			}

			if (
                !isset($entryFields['MEETING_STATUS'])
                && (int)($entryFields['MEETING_HOST'] ?? null) === (int)($entryFields['CREATED_BY'] ?? null))
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
				$meetingHostSettings = \Bitrix\Calendar\UserSettings::get($entryFields['MEETING_HOST'] ?? null);
				$entryFields['MEETING']['MAIL_FROM'] = $meetingHostSettings['sendFromEmail'] ?? null;
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

			if ($isNewEvent) // Add
			{
				$eventId = $DB->Add("b_calendar_event", $dbFields, ['DESCRIPTION', 'MEETING', 'EXDATE']);
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
					'EXDATE' => $entryFields['EXDATE'] ?? null
				));
			}

			CTimeZone::Enable();

			if (
				$userId
				&& $params
				&& ($params['overSaving'] ?? null) !== true
				&& \Bitrix\Calendar\Sync\Util\RequestLogger::isEnabled())
			{
				$loggerParams = $params;
				$loggerParams['arFields'] = $entryFields;
				$loggerParams['loggerUuid'] = $eventId;

				(new \Bitrix\Calendar\Sync\Util\RequestLogger($userId, 'portal_edit'))->write($loggerParams);
			}

			if ($isNewEvent && !isset($dbFields['DAV_XML_ID']))
			{
				$strSql =
					"UPDATE b_calendar_event SET ".
						$DB->PrepareUpdate("b_calendar_event", ['DAV_XML_ID' => (int)$eventId]).
						" WHERE ID = ". (int)$eventId;
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			// Deprecated. Now connection saved in the table
			if (
				!Util::isSectionStructureConverted() &&
				($isNewEvent || $sectionId !== $currentEvent['SECTION_ID']))
			{
				self::ConnectEventToSection($eventId, $sectionId);
			}

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
					$DB->Query("UPDATE b_calendar_event SET ".$DB->PrepareUpdate("b_calendar_event", array("PARENT_ID" => $eventId))." WHERE ID=". (int)$eventId, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
				}

				$mailEvent = ICalUtil::isMailUser($entryFields['MEETING_HOST'] ?? null);

				if ((empty($entryFields['PARENT_ID']) || $entryFields['PARENT_ID'] === $eventId) && !$mailEvent)
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
				$DB->Query("UPDATE b_calendar_event SET ".$DB->PrepareUpdate("b_calendar_event", array("PARENT_ID" => $eventId))." WHERE ID=".intval($eventId), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
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
				'path' => $params['path'] ?? null
			]);

			// Update search index
			self::updateSearchIndex($eventId, ['userId' => $userId]);
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
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$entryFields['OWNER_ID']);
						CCalendarNotify::Send([
							'mode' => 'change_notify',
							'name' => $entryFields['NAME'] ?? null,
							"from" => $fromTo['DATE_FROM'] ?? null,
							"to" => $fromTo['DATE_TO'] ?? null,
							"location" => CCalendar::GetTextLocation($entryFields["LOCATION"] ?? null),
							"guestId" => $entryFields['OWNER_ID'] ?? null,
							"eventId" => $entryFields['PARENT_ID'] ?? null,
							"userId" => $userId,
							"fields" => $entryFields,
							"isSharing" => ($entryFields['EVENT_TYPE'] ?? null) === Dictionary::EVENT_TYPE['shared'],
							"entryChanges" => $entryChanges
						]);
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
							"userId" => $userId,
							"isSharing" => ($entryFields['EVENT_TYPE'] ?? null) === Dictionary::EVENT_TYPE['shared'],
							"fields" => $entryFields
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
					'attendeesCodes' => $attendeesCodes
				]);
			}

			CCalendar::ClearCache('event_list');

			$result = $eventId;

			if (!empty($entryFields['LOCATION']))
			{
				Rooms\Manager::setEventIdForLocation($eventId);
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
							[]
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
							$currentEvent['ATTENDEE_LIST']
						]
					);
				}
			}

			$pullUserId = (isset($entryFields['CREATED_BY']) && (int)($entryFields['CREATED_BY'] ?? null) > 0)
                ? (int)$entryFields['CREATED_BY'] : $userId;

			if (
				$pullUserId > 0
				&& ($params['overSaving'] ?? null) !== true
			)
			{
				$entryFields = self::calculateUserOffset($pullUserId, $entryFields);
				Util::addPullEvent(
					'edit_event',
					$pullUserId,
					[
						'fields' => $entryFields,
						'newEvent' => $isNewEvent,
						'requestUid' => $params['requestUid'] ?? null
					]
				);
			}
		}

		return $result;
	}

	/**
	 * @param $id
	 * @param $checkPermissions
	 * @return array|false
	 */
	public static function GetById($id, $checkPermissions = true)
	{
		if ($id > 0)
		{
			$event = self::GetList([
				'arFilter' => [
					'ID' => $id,
					'DELETED' => 'N'
				],
				'parseRecursion' => false,
				'fetchAttendees' => $checkPermissions,
				'checkPermissions' => $checkPermissions,
				'setDefaultLimit' => false
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
		global $DB, $USER_FIELD_MANAGER;
		$getUF = ($params['getUserfields'] ?? null) !== false;
		$checkPermissions = ($params['checkPermissions'] ?? null) !== false;
		$getPermissions = ($params['getPermissions'] ?? null) !== false;
		$bCache = CCalendar::CacheTime() > 0;
		$params['setDefaultLimit'] = ($params['setDefaultLimit'] ?? null) === true;
		$userId = (isset($params['userId']) && $params['userId']) ? (int)$params['userId'] : CCalendar::GetCurUserId();
		$params['parseDescription'] = $params['parseDescription'] ?? true;
		$fetchSection = $params['fetchSection'] ?? null;
		$resultEntryList = null;
		$userIndex = null;

		CTimeZone::Disable();
		if ($bCache)
		{
			$cache = new CPHPCache;
			$cacheId = 'eventlist'.md5(serialize($params)).CCalendar::GetOffset();
			if ($checkPermissions)
				$cacheId .= 'perm'.CCalendar::GetCurUserId().'|';
			if (CCalendar::IsSocNet() && CCalendar::IsSocnetAdmin())
				$cacheId .= 'socnetAdmin|';
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
			if ($getUF)
			{
				$obUserFieldsSql = new CUserTypeSQL();
				$obUserFieldsSql->SetEntity("CALENDAR_EVENT", "CE.ID");
				$obUserFieldsSql->SetSelect(array("UF_*"));
				$obUserFieldsSql->SetFilter($arFilter);
			}

			$params['fetchAttendees'] = ($params['fetchAttendees'] ?? null) !== false;

			if (($params['setDefaultLimit'] ?? null) !== false) // Deprecated
			{
				if (!isset($arFilter["FROM_LIMIT"])) // default 3 month back
					$arFilter["FROM_LIMIT"] = CCalendar::Date(time() - 31 * 3 * 24 * 3600, false);

				if (!isset($arFilter["TO_LIMIT"])) // default one year into the future
					$arFilter["TO_LIMIT"] = CCalendar::Date(time() + 365 * 24 * 3600, false);
			}

			// Array('ID' => 'asc')
			$arOrder = $params['arOrder'] ?? [];
			$arFields = self::GetFields();

			if (isset($arFilter["DELETED"]) && ($arFilter["DELETED"] === false))
			{
				unset($arFilter["DELETED"]);
			}
			elseif (!isset($arFilter["DELETED"]))
			{
				$arFilter["DELETED"] = "N";
			}

			$join = '';

			$arSqlSearch = [];
			if (is_array($arFilter))
			{
				$filter_keys = array_keys($arFilter);
				for ($i = 0, $l = count($filter_keys); $i<$l; $i++)
				{
					$n = mb_strtoupper($filter_keys[$i]);
					$val = $arFilter[$filter_keys[$i]];
					if ((is_string($val) && $val == '') || (!is_array($val) && (string)$val === "NOT_REF"))
					{
						continue;
					}

					if ($n === 'FROM_LIMIT')
					{
						$ts = CCalendar::Timestamp($val, false);
						if ($ts > 0)
						{
							$arSqlSearch[] = "CE.DATE_TO_TS_UTC>=" . $ts;
						}
					}
					else if ($n === 'TO_LIMIT')
					{
						$ts = CCalendar::Timestamp($val, false);
						if ($ts > 0)
						{
							$arSqlSearch[] = "CE.DATE_FROM_TS_UTC<=" . ($ts + 86399);
						}
					}
					else if ($n === 'ID' || $n === 'PARENT_ID' || $n === 'RECURRENCE_ID')
					{
						if (is_array($val))
						{
							$val = array_map('intval', $val);
							$arSqlSearch[] = 'CE.'.$n.' IN (\''.implode('\',\'', $val).'\')';
						}
						else if ((int)$val > 0)
						{
							$arSqlSearch[] = 'CE.'.$n.'='.(int)$val;
						}
					}
					elseif ($n === '>ID' && (int)$val > 0)
					{
						$arSqlSearch[] = "CE.ID > ". (int)$val;
					}
					elseif ($n === 'G_EVENT_ID')
					{
						$arSqlSearch[] = "CE.G_EVENT_ID = '". $DB->ForSql($val)."'";
					}
					elseif ($n === 'OWNER_ID')
					{
						if (is_array($val))
						{
							$val = array_map('intval', $val);
							$arSqlSearch[] = 'CE.OWNER_ID IN (\''.implode('\',\'', $val).'\')';
						}
						else if ((int)$val > 0)
						{
							$arSqlSearch[] = "CE.OWNER_ID=". (int)$val;
						}
					}
					elseif ($n === 'MEETING_HOST')
					{
						if (is_array($val))
						{
							$val = array_map('intval', $val);
							$arSqlSearch[] = 'CE.MEETING_HOST IN (\''.implode('\',\'', $val).'\')';
						}
						else if ((int)$val > 0)
						{
							$arSqlSearch[] = "CE.MEETING_HOST=". (int)$val;
						}
					}
					elseif ($n === 'NAME')
					{
						$arSqlSearch[] = "CE.NAME='".$DB->ForSql($val)."'";
					}
					elseif ($n === 'CAL_TYPE')
					{
						$arSqlSearch[] = "CE.CAL_TYPE='".$DB->ForSql($val)."'";
					}
					elseif ($n === 'CREATED_BY')
					{
						if (is_array($val))
						{
							$val = array_map('intval', $val);
							$arSqlSearch[] = 'CE.CREATED_BY IN (\''.implode('\',\'', $val).'\')';
						}
						else if ((int)$val > 0)
						{
							$arSqlSearch[] = "CE.CREATED_BY=". (int)$val;
						}
					}
					elseif ($n === 'SECTION')
					{
						if (!is_array($val))
						{
							$val = [$val];
						}

						$q = "";
						if (is_array($val))
						{
							$sval = '';
							foreach($val as $sectid)
							{
								if ((int)$sectid > 0)
								{
									$sval .= (int)$sectid .',';
								}
							}
							$sval = trim($sval, ' ,');

							if ($sval)
							{
								if (Util::isSectionStructureConverted())
								{
									$q = count($val) === 1 ? 'CE.SECTION_ID='.$sval : 'CE.SECTION_ID in ('.$sval.')';
								}
								else
								{
									$q = 'CES.SECT_ID in ('.$sval.')';
								}
							}
						}

						if ($q != "")
						{
							$arSqlSearch[] = $q;
						}
					}
					elseif ($n === 'ACTIVE_SECTION' && $val === "Y")
					{
						$arSqlSearch[] = "CS.ACTIVE='Y'";
						if (Util::isSectionStructureConverted())
						{
							$join .= 'LEFT JOIN b_calendar_section CS ON (CE.SECTION_ID=CS.ID)';
						}
						else
						{
							$join .= 'LEFT JOIN b_calendar_section CS ON (CES.SECT_ID=CS.ID)';
						}
					}
					elseif ($n === 'DAV_XML_ID' && is_array($val))
					{
						$val = array_map(array($DB, 'ForSQL'), $val);
						$arSqlSearch[] = 'CE.DAV_XML_ID IN (\''.implode('\',\'', $val).'\')';
					}
					elseif ($n === 'DAV_XML_ID' && is_string($val))
					{
						$arSqlSearch[] = "CE.DAV_XML_ID='".$DB->ForSql($val)."'";
					}
					elseif ($n === '*SEARCHABLE_CONTENT') // Full text index match
					{
						$sqlWhere = new CSQLWhere();
						$arSqlSearch[] = $sqlWhere->match('SEARCHABLE_CONTENT', $val, true);
					}
					elseif ($n === '*%SEARCHABLE_CONTENT') // partial full text match based on LIKE
					{
						$sqlWhere = new CSQLWhere();
						$arSqlSearch[] = $sqlWhere->matchLike('SEARCHABLE_CONTENT', $val);
					}
					elseif (isset($arFields[$n]) && $arFields[$n]["FIELD_TYPE"] === 'date')
					{
						$arSqlSearch[] = $DB->DateToCharFunction("CE.".$n)."='".$DB->ForSql($val)."'";
					}
					elseif ($n === 'RECURRENCE_ID' && (int)$val)
					{
						$arSqlSearch[] = "CE.RECURRENCE_ID=". (int)$val;
					}
					elseif ($n === 'DELETED')
					{
						$arSqlSearch[] = "CE.DELETED='".$DB->ForSql($val)."'";
					}
					elseif (isset($arFields[$n]))
					{
						$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val, 'N');
					}
				}
			}

			if ($getUF)
			{
				$r = $obUserFieldsSql->GetFilter();
				if ($r <> '')
				{
					$arSqlSearch[] = "(".$r.")";
				}
			}

			$selectList = "";
			foreach($arFields as $fieldKey => $field)
			{
				if (
					(
						!isset($params['arSelect'])
						|| !is_array($params['arSelect'])
						|| in_array($fieldKey, $params['arSelect'])
					)
					&& $fieldKey !== 'SEARCHABLE_CONTENT')
				{
					$selectList .= $field['FIELD_NAME'].", ";
				}
			}

			if ($fetchSection && $arFilter['ACTIVE_SECTION'] === 'Y')
			{
				$selectList .= "CS.CAL_DAV_CAL as SECTION_DAV_XML_ID,";
			}

			$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
			$strOrderBy = '';
			foreach($arOrder as $by=>$order)
			{
				if (isset($arFields[mb_strtoupper($by)]))
				{
					$strOrderBy .= $arFields[mb_strtoupper($by)]["FIELD_NAME"].' '.(mb_strtolower($order) === 'desc'?'desc':'asc').',';
				}
			}

			if ($strOrderBy)
			{
				$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");
			}

			$strLimit = '';
			if (isset($params['limit']) && (int)$params['limit'] > 0)
			{
				$strLimit = 'LIMIT '. (int)$params['limit'];
			}

			if (Util::isSectionStructureConverted())
			{
				$strSql = "
					SELECT ".
						trim($selectList, ', ').
						($getUF ? $obUserFieldsSql->GetSelect() : '')."
					FROM
						b_calendar_event CE
					".$join."
					".($getUF ? $obUserFieldsSql->GetJoin("CE.ID") : '')."
					WHERE
						$strSqlSearch
					$strOrderBy
					$strLimit";
			}
			else
			{
				$strSql = "
					SELECT ".
						$selectList.
						"CES.SECT_ID, CES.REL
						".($getUF ? $obUserFieldsSql->GetSelect() : '')."
					FROM
						b_calendar_event CE
					LEFT JOIN b_calendar_event_sect CES ON (CE.ID=CES.EVENT_ID)
					".$join."
					".($getUF ? $obUserFieldsSql->GetJoin("CE.ID") : '')."
					WHERE
						$strSqlSearch
					$strOrderBy
					$strLimit";
			}

			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($getUF)
			{
				$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT"));
			}

			$resultEntryList = [];
			$arMeetingIds = [];
			$arEvents = [];
			$involvedUsersIdList = [];

			$defaultMeetingSection = false;
			while($event = $res->Fetch())
			{
				if (empty($event['SECT_ID']) && !empty($event['SECTION_ID']))
				{
					$event['SECT_ID'] = $event['SECTION_ID'];
				}

				$event['IS_MEETING'] = (int)($event['IS_MEETING'] ?? 0) > 0;

				if (!empty($event['NAME']))
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

				if (
					$event['IS_MEETING']
					&& $event['CAL_TYPE'] === 'user'
					&& (int)$event['OWNER_ID'] === $userId
					&& !$event['SECT_ID']
				)
				{
					if (!$defaultMeetingSection)
					{
						$defaultMeetingSection = CCalendar::GetMeetingSection($userId);
						if (!$defaultMeetingSection || !CCalendarSect::GetById($defaultMeetingSection, false))
						{
							$sectRes = CCalendarSect::GetSectionForOwner($event['CAL_TYPE'], $userId);
							$defaultMeetingSection = $sectRes['sectionId'];
						}
					}

					if (!Util::isSectionStructureConverted())
					{
						self::ConnectEventToSection($event['ID'], $defaultMeetingSection);
					}
					$event['SECT_ID'] = $defaultMeetingSection;
					$event['SECTION_ID'] = $defaultMeetingSection;
				}

				$arEvents[] = $event;
				if (!empty($event['IS_MEETING']) && CCalendar::IsIntranetEnabled())
				{
					$arMeetingIds[] = $event['PARENT_ID'];
				}

				$involvedUsersIdList[] = $event['CREATED_BY'];
			}

			if (!empty($params['fetchAttendees']) && count($arMeetingIds) > 0)
			{
				$attendeeListData = self::getAttendeeList($arMeetingIds);
				$attendeeList = $attendeeListData['attendeeList'];
				$involvedUsersIdList = array_unique(array_merge($involvedUsersIdList, $attendeeListData['userIdList']));
			}
			$userIndex = self::getUsersDetails($involvedUsersIdList);

			foreach($arEvents as $event)
			{
				$event["ACCESSIBILITY"] = trim($event["ACCESSIBILITY"]);
				if (
					isset($event['MEETING'])
					&& $event['MEETING']
					&& CCalendar::IsIntranetEnabled()
				)
				{
					$event['MEETING'] = unserialize($event['MEETING'], ['allowed_classes' => false]);
					if (!is_array($event['MEETING']))
					{
						$event['MEETING'] = [];
					}
				}

				if (isset($event['RELATIONS']) && $event['RELATIONS'])
				{
					$event['RELATIONS'] = unserialize($event['RELATIONS'], ['allowed_classes' => false]);
					if (!is_array($event['RELATIONS']))
					{
						$event['RELATIONS'] = [];
					}
				}

				if (isset($event['REMIND']) && $event['REMIND'])
				{
					$event['REMIND'] = unserialize($event['REMIND'], ['allowed_classes' => false]);
				}
				if (!is_array($event['REMIND']))
				{
					$event['REMIND'] = [];
				}

				if (
					$event['IS_MEETING']
					&& isset($attendeeList[$event['PARENT_ID']])
					&& CCalendar::IsIntranetEnabled()
				)
				{
					$event['ATTENDEE_LIST'] = $attendeeList[$event['PARENT_ID']];
				}
				else
				{
					$event['ATTENDEE_LIST'] = [
						[
							'id' => (int)$event['MEETING_HOST'],
							'entryId' => $event['ID'],
							'status' => in_array($event['MEETING_STATUS'], ['Y', 'N', 'Q', 'H'])
								? $event['MEETING_STATUS']
								: 'H'
							,
						]
					];
				}

				if ($checkPermissions)
				{
					$checkPermissionsForEvent = $userId !== (int)$event['CREATED_BY']; // It's creator

					// It's event in user's calendar
					if (
						$checkPermissionsForEvent
						&& $event['CAL_TYPE'] === 'user'
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
						&& is_array($event['ATTENDEE_LIST'])
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
					$event = self::PreHandleEvent(
						$event,
						[
							'parseDescription' => $params['parseDescription']
						]
					);

					if (!empty($params['parseRecursion']) && self::CheckRecurcion($event))
					{
						self::ParseRecursion($resultEntryList, $event, [
							'userId' => $userId,
							'fromLimit' => $arFilter["FROM_LIMIT"] ?? null,
							'toLimit' => $arFilter["TO_LIMIT"] ?? null,
							'loadLimit' => $params["limit"] ?? null,
							'instanceCount' => $params['maxInstanceCount'] ?? false,
							'preciseLimits' => isset($params['preciseLimits']) ? $params['preciseLimits'] : false
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
				"SECTION_ID" => Array("FIELD_NAME" => "CE.SECTION_ID", "FIELD_TYPE" => "int")
			);
			CTimeZone::Enable();
		}
		return self::$fields;
	}

	public static function ConnectEventToSection($eventId, $sectionId)
	{
		global $DB;
		$DB->Query(
			"DELETE FROM b_calendar_event_sect WHERE EVENT_ID=". (int)$eventId,
			false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$DB->Query(
			"INSERT INTO b_calendar_event_sect(EVENT_ID, SECT_ID) ".
			"SELECT ". (int)$eventId .", ID ".
			"FROM b_calendar_section ".
			"WHERE ID=". (int)$sectionId,
			false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
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

				$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				while($entry = $res->Fetch())
				{
					$entry['USER_ID'] = (int)$entry['USER_ID'];
					if (!isset($attendeeList[$entry['PARENT_ID']]))
					{
						$attendeeList[$entry['PARENT_ID']] = [];
					}
					$entry["STATUS"] = trim($entry["MEETING_STATUS"]);
					if ($entry['PARENT_ID'] === $entry['ID'] || $entry['USER_ID'] === $entry['MEETING_HOST'])
					{
						$entry["STATUS"] = "H";
					}
					$attendeeList[$entry['PARENT_ID']][] = [
						'id' => $entry['USER_ID'],
						'entryId' => $entry['ID'],
						'status' => $entry["STATUS"]
					];

					if (!in_array($entry['USER_ID'], $userIdList, true))
					{
						$userIdList[] = $entry['USER_ID'];
					}
				}
			}
		}

		return [
			'attendeeList' => $attendeeList,
			'userIdList' => $userIdList
		];
	}

	public static function getUsersDetails($userIdList = [], $params = [])
	{
		$users = [];
		$userList = [];
		if ($userIdList)
		{
			$userList = UserTable::getList([
				'select' => [
					'ID',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME',
					'LOGIN',
					'PERSONAL_PHOTO',
					'EMAIL',
					'EXTERNAL_AUTH_ID'
				],
				'filter' => [
					'=ID' => $userIdList
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
				'SHARING_USER' => $userData['EXTERNAL_AUTH_ID'] === 'calendar_sharing',
			];
		}

		return $users;
	}

	public static function GetAttendees($eventIdList = [])
	{
		global $DB;
		$attendees = [];

		if (CCalendar::IsSocNet())
		{
			$eventIdList = is_array($eventIdList) ? array_map('intval', array_unique($eventIdList)) : array((int)$eventIdList);

			if (!empty($eventIdList))
			{
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
					CE.DELETED = 'N' AND
					CE.PARENT_ID in (".implode(',', $eventIdList).")";

				$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				while($entry = $res->Fetch())
				{
					$parentId = (int)$entry['PARENT_ID'];
					$attendeeId = (int)$entry['USER_ID'];
					if (!isset($attendees[$parentId]))
					{
						$attendees[$parentId] = [];
					}
					$entry["STATUS"] = trim($entry["MEETING_STATUS"]);
					if ($parentId === (int)$entry['ID'] || $attendeeId === (int)$entry['MEETING_HOST'])
					{
						$entry["STATUS"] = "H";
					}

					CCalendar::SetUserDepartment($attendeeId, (empty($entry['UF_DEPARTMENT'])
						? []
						: unserialize($entry['UF_DEPARTMENT'], ['allowed_classes' => false])));
					$entry['DISPLAY_NAME'] = CCalendar::GetUserName($entry);
					$entry['URL'] = CCalendar::GetUserUrl($attendeeId);
					$entry['AVATAR'] = CCalendar::GetUserAvatarSrc($entry['ID']);
					$entry['EVENT_ID'] = $entry['ID'];

					unset($entry['ID'], $entry['PARENT_ID'], $entry['UF_DEPARTMENT'], $entry['LOGIN']);
					$attendees[$parentId][] = $entry;
				}
			}
		}

		return $attendees;
	}

	public static function ApplyAccessRestrictions($event, $userId = false)
	{
		$sectId = $event['SECT_ID'];
		if (empty($event['ACCESSIBILITY']))
		{
			$event['ACCESSIBILITY'] = 'busy';
		}

		$private = $event['PRIVATE_EVENT'] && $event['CAL_TYPE'] === 'user';
		$isAttendee = false;

		if (is_array($event['ATTENDEE_LIST']))
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
			$event['CAL_TYPE'] === 'user'
			&& $event['IS_MEETING']
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
				$event['ATTENDEES_CODES']
			);

			foreach($event as $k => $value)
			{
				if (mb_strpos($k, 'UF_') === 0)
				{
					unset($event[$k]);
				}
			}
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
		$item['LOCATION'] = trim($item['LOCATION'] ?? '');

		if (!empty($item['IS_MEETING']) && !empty($item['MEETING']) && !is_array($item['MEETING']))
		{
			$item['MEETING'] = unserialize($item['MEETING'], ['allowed_classes' => false]);
			if (!is_array($item['MEETING']))
				$item['MEETING'] = [];
		}

		if (self::CheckRecurcion($item))
		{
			$item['EXDATE'] = !empty($item['EXDATE']) ? self::convertDateToCulture($item['EXDATE']) : '';
			$item['RRULE'] = self::ParseRRULE(self::convertDateToCulture($item['RRULE']));
			$item['~RRULE_DESCRIPTION'] = self::GetRRULEDescription($item, false);
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
		}
//		if (empty($item['ATTENDEES_CODES']))
//		{
//			$item['ATTENDEES_CODES'] = ['U'.$item['CREATED_BY']];
//		}
		$item['attendeesEntityList'] = Util::convertCodesToEntities($item['ATTENDEES_CODES'] ?? null);

		if (!empty($item['IS_MEETING']) && (int)$item['ID'] === (int)$item['PARENT_ID'])
		{
			$item['MEETING_STATUS'] = 'H';
		}

		$item['DT_SKIP_TIME'] = $item['DT_SKIP_TIME'] === 'Y' ? 'Y' : 'N';

		$item['ACCESSIBILITY'] = trim($item['ACCESSIBILITY']);
		$item['IMPORTANCE'] = trim($item['IMPORTANCE']);
		if (empty($item['IMPORTANCE']))
		{
			$item['IMPORTANCE'] = 'normal';
		}
		$item['PRIVATE_EVENT'] = trim((string)($item['PRIVATE_EVENT'] ?? null));

		$item['DESCRIPTION'] = trim((string)($item['DESCRIPTION'] ?? null));
		if (!empty($params['parseDescription']))
		{
			if (!empty($item['PARENT_ID']))
			{
				$item['~DESCRIPTION'] = self::ParseText(
					$item['DESCRIPTION'],
					$item['PARENT_ID'],
					$item['UF_WEBDAV_CAL_EVENT'] ?? null
				);
			}
			else
			{
				$item['~DESCRIPTION'] = self::ParseText($item['DESCRIPTION'], $item['ID'], $item['UF_WEBDAV_CAL_EVENT']);
			}
		}

		if (isset($item['UF_CRM_CAL_EVENT']) && is_array($item['UF_CRM_CAL_EVENT']) && count($item['UF_CRM_CAL_EVENT']) == 0)
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
					"USER" => "Y"
				);
			}

			self::$TextParser->allow["USERFIELDS"] = self::getUFForParseText($eventId, $arUFWDValue);
			$text = self::$TextParser->convertText($text);
			$text = preg_replace("/<br \/>/i", "<br>", $text);
		}
		return $text;
	}

	public static function getUFForParseText($eventId = 0, $arUFWDValue = [])
	{
		if (!isset(self::$eventUFDescriptions))
		{
			global $USER_FIELD_MANAGER;
			$USER_FIELDS = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $eventId, LANGUAGE_ID);
			$USER_FIELDS = array(
				'UF_WEBDAV_CAL_EVENT' => $USER_FIELDS['UF_WEBDAV_CAL_EVENT']
			);
			self::$eventUFDescriptions = $USER_FIELDS;
		}
		else
		{
			$USER_FIELDS = self::$eventUFDescriptions;
		}

		if (empty($arUFWDValue))
		{
			$arUFWDValue = $USER_FIELDS['UF_WEBDAV_CAL_EVENT']['VALUE'];
		}

		$USER_FIELDS['UF_WEBDAV_CAL_EVENT']['VALUE'] = $arUFWDValue;
		$USER_FIELDS['UF_WEBDAV_CAL_EVENT']['ENTITY_VALUE_ID'] = $eventId;

		return $USER_FIELDS;
	}

	public static function ParseRecursion(&$res, $event, $params = [])
	{
		$event['DT_LENGTH'] = (int)$event['DT_LENGTH'];// length in seconds
		$length = $event['DT_LENGTH'];

		$rrule = self::ParseRRULE($event['RRULE']);
		$exDate = self::GetExDate($event['EXDATE']);
		$tsFrom = CCalendar::Timestamp($event['DATE_FROM']);
		$tsTo = CCalendar::Timestamp($event['DATE_TO']);

		if (($tsTo - $tsFrom) > $event['DT_LENGTH'] + CCalendar::DAY_LENGTH)
		{
			$toTS = $tsFrom + $event['DT_LENGTH'];
			if (($event['DT_SKIP_TIME'] ?? null) === 'Y')
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
		$limitToTS += $event['TZ_OFFSET_TO'];
		$limitToTS += CCalendar::GetDayLen();
		$limitFromTSReal = $limitFromTS;

		$skipTime = $event['DT_SKIP_TIME'] === 'Y';

		if ($length > CCalendar::GetDayLen() && $skipTime)
		{
			$limitFromTSReal += $length - CCalendar::GetDayLen();
		}

		if ($limitFromTS < $event['DATE_FROM_TS_UTC'])
		{
			$limitFromTS = $event['DATE_FROM_TS_UTC'];
		}
		if ($limitToTS > $event['DATE_TO_TS_UTC'])
		{
			$limitToTS = $event['DATE_TO_TS_UTC'];
		}

		$fromTS = $evFromTS;

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

		$hour = date("H", $fromTS);
		$min = date("i", $fromTS);
		$sec = date("s", $fromTS);

		$orig_d = date("d", $fromTS);
		$orig_m = date("m", $fromTS);
		$orig_y = date("Y", $fromTS);

		$count = 0;
		$realCount = 0;
		$dispCount = 0;

		while(true)
		{
			$d = date("d", $fromTS);
			$m = date("m", $fromTS);
			$y = date("Y", $fromTS);
			$toTS = mktime($hour, $min, $sec + $length, $m, $d, $y);

			if (
				(isset($rrule['COUNT']) && $rrule['COUNT'] > 0 && $realCount >= $rrule['COUNT'])
				|| ($loadLimit && $dispCount >= $loadLimit)
				|| ($fromTS >= $limitToTS)
				|| ($instanceCount && $dispCount >= $instanceCount)
				|| (!$fromTS || $fromTS < $evFromTS - CCalendar::GetDayLen()) // Emergensy exit (mantis: 56981)
			)
			{
				break;
			}

			// Common handling
			$event['DATE_FROM'] = CCalendar::Date($fromTS, !$skipTime, false);
			$event['RRULE'] = $rrule;
			$event['RINDEX'] = $realCount;

			$exclude = false;

			if (count($exDate) > 0)
			{
				$fromDate = CCalendar::Date($fromTS, false);
				$exclude = in_array($fromDate, $exDate);
			}

			if ($rrule['FREQ'] === 'WEEKLY')
			{
				$weekDay = CCalendar::WeekDayByInd(date("w", $fromTS));

				if (!empty($rrule['BYDAY'][$weekDay]))
				{
					if (($preciseLimits && $toTS >= $limitFromTSReal) || (!$preciseLimits && $toTS > $limitFromTS - $h24))
					{
						if (($event['DT_SKIP_TIME'] ?? null) === 'Y')
						{
							$toTS -= CCalendar::GetDayLen();
						}
						$event['DATE_TO'] = CCalendar::Date($toTS - ($event['TZ_OFFSET_FROM'] - $event['TZ_OFFSET_TO']), !$skipTime, false);

						if (!$exclude)
						{
							self::HandleEvent($res, $event, $params['userId']);
							$dispCount++;
						}
					}
					$realCount++;
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
			else // HOURLY, DAILY, MONTHLY, YEARLY
			{
				if (($event['DT_SKIP_TIME'] ?? null) === 'Y')
				{
					$toTS -= CCalendar::GetDayLen();
				}

				if (($preciseLimits && $toTS >= $limitFromTSReal) ||
					(!$preciseLimits && $toTS > $limitFromTS - $h24))
				{
					$event['DATE_TO'] = CCalendar::Date($toTS - ($event['TZ_OFFSET_FROM'] - $event['TZ_OFFSET_TO']), !$skipTime, false);
					//$event['DATE_TO'] = CCalendar::Date($toTS, !$skipTime, false);
					if (!$exclude)
					{
						self::HandleEvent($res, $event, $params['userId']);
						$dispCount++;
					}
				}
				$realCount++;
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

	public static function ParseRRULE($rule = '')
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
							&& substr($arPar[1],2,1) === '.'
							&& substr($arPar[1],5,1) === '.'
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
						if (count($res[$arPar[0]]) === 0)
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
						if (count($res[$arPar[0]]) === 0)
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
						if (count($res[$arPar[0]]) === 0)
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
						if (count($res[$arPar[0]]) === 0)
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
						if (count($res[$arPar[0]]) === 0)
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
				!isset($res['BYDAY'])
				|| !is_array($res['BYDAY'])
				|| count($res['BYDAY']) === 0
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
			$toTs = $fromTs + $event['DT_LENGTH'];

			$event['~USER_OFFSET_FROM'] = CCalendar::GetTimezoneOffset($event['TZ_FROM'], $fromTs)
				- \CCalendar::GetTimezoneOffset($currentUserTimezone, $fromTs);

			$event['~USER_OFFSET_TO'] = CCalendar::GetTimezoneOffset($event['TZ_TO'], $toTs)
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
		if (!isset($arFields['DT_LENGTH']) || (int)$arFields['DT_LENGTH'] === 0)
		{
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
                    : ""
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
			'IMPORTANCE'
		];

		foreach ($fieldList as $fieldKey)
		{
			if ($fieldKey === 'LOCATION')
			{
				if (
					is_array($newFields[$fieldKey])
					&& ($newFields[$fieldKey]['NEW'] ?? null) !== ($currentFields[$fieldKey] ?? null)
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey] ?? null,
						'newValue' => $newFields[$fieldKey]['NEW'] ?? null,
					];
				}
				else if (
					!is_array($newFields[$fieldKey]) && $newFields[$fieldKey] !== $currentFields[$fieldKey]
					&& (CCalendar::GetTextLocation($newFields["LOCATION"]) ?? '') !== (CCalendar::GetTextLocation($currentFields["LOCATION"]) ?? '')
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey]
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
						'newValue' => $newFields[$fieldKey]
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
						'newValue' => $newFields[$fieldKey]
					];
				}
			}
			else if ($fieldKey === 'IMPORTANCE')
			{
				if (
					$newFields[$fieldKey] != $currentFields[$fieldKey]
					&& $newFields[$fieldKey] === 'high'
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey]
					];
				}
			}
			else if ($fieldKey === 'DESCRIPTION')
			{
				if (mb_strtolower(trim($newFields[$fieldKey])) != mb_strtolower(trim($currentFields[$fieldKey])))
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey]
					];
				}
			}
			else if ($fieldKey === 'RRULE')
			{
				$newRule = self::ParseRRULE($newFields[$fieldKey]);
				$oldRule = self::ParseRRULE($currentFields[$fieldKey]);

				if (
                    (($newRule['FREQ'] ?? null) !== ($oldRule['FREQ'] ?? null))
					|| (($newRule['INTERVAL'] ?? null) !== ($oldRule['INTERVAL'] ?? null))
					|| (($newRule['BYDAY'] ?? null) !== ($oldRule['BYDAY'] ?? null))
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $oldRule,
						'newValue' => $newRule
					];
				}
			}
			else if ($newFields[$fieldKey] !== $currentFields[$fieldKey])
			{
				$changes[] = [
					'fieldKey' => $fieldKey,
					'oldValue' => $currentFields[$fieldKey],
					'newValue' => $newFields[$fieldKey]
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
				'newValue' => $newFields['ATTENDEES_CODES']
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
				$strRes .= $key.'='.$val.';';
		}
		$strRes = trim($strRes, ', ');
		return $strRes;
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
		global $DB, $CACHE_MANAGER;
		$parentId = (int) $parentId;
		$isNewEvent = !isset($arFields['ID']) || $arFields['ID'] <= 0;
		$chatId = (int) ($arFields['~MEETING']['CHAT_ID'] ?? null) ;
		$involvedAttendees = []; // List of all attendees to invite or to exclude from event
		$isMailAvailable = Loader::includeModule("mail");
		$isCalDavEnabled = CCalendar::IsCalDAVEnabled();
		$isPastEvent = ($arFields['DATE_TO_TS_UTC'] ?? null) < (time() - (int)date('Z'));

		$userId = $params['userId'];
		$attendees = is_array($arFields['ATTENDEES']) ? $arFields['ATTENDEES'] : []; // List of attendees for event
		$eventManagersCollection = [];
		$attaches = [];
		$chat = null;
		$isIncreaseMailLimit = false;

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
		if (!$isNewEvent)
		{
			$curAttendees = self::GetAttendees($parentId);
			$curAttendees = is_array(($curAttendees[$parentId] ?? null)) ? $curAttendees[$parentId] : [];
			foreach($curAttendees as $user)
			{
				$currentAttendeesIndex[$user['USER_ID']] = $user;
				if (
					$user['USER_ID'] !== $arFields['MEETING_HOST'] &&
					($user['USER_ID'] !== $arFields['OWNER_ID'] || $arFields['CAL_TYPE'] !== 'user')
				)
				{
					$deletedAttendees[$user['USER_ID']] = $user['USER_ID'];
					$involvedAttendees[] = $user['USER_ID'];
				}
			}
		}
		$involvedAttendees = array_unique($involvedAttendees);
		$meetingInfo = unserialize($arFields['MEETING'], ['allowed_classes' => false]);

		$userIndex = [];
		if ($isMailAvailable)
		{
			// Here we collecting information about EXTERNAL_AUTH_ID to
			// know if some of the users are external
			$orm = UserTable::getList([
				'filter' => [
					'=ID' => $involvedAttendees,
					'=ACTIVE' => 'Y'
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
				]
			]);

			while ($user = $orm->fetch())
			{
				if ($user['ID'] === ($arFields['MEETING_HOST'] ?? null))
				{
					$user['STATUS'] = 'accepted';
				}
				else
				{
					$user['STATUS'] = 'needs_action';
				}

				$userIndex[$user['ID']] = $user;
			}
		}

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
					$userIndex[$attendeeId]
					&& $userIndex[$attendeeId]['EXTERNAL_AUTH_ID'] === 'email'
					&& $isNewEvent
					&& !$isIncreaseMailLimit
				)
				{
					if (Bitrix24Manager::isEventWithEmailGuestAllowed())
					{
						Bitrix24Manager::increaseEventWithEmailGuestAmount();
						$isIncreaseMailLimit = true;
					}
					else
					{
						// Just skip external emil users if they are not allowed
						// We will show warning on the client's side
						continue;
					}
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

					if (empty($childParams['arFields']['DAV_XML_ID']) && !$clonedParams['editInstance'])
					{
						$childParams['arFields']['DAV_XML_ID'] = self::getUidForChildEvent($childParams['arFields']);
					}

					$parentEvent = Internals\EventTable::query()
						->where('PARENT_ID' , (int)($childParams['arFields']['RECURRENCE_ID'] ?? 0))
						->where('OWNER_ID' , (int)($childParams['arFields']['OWNER_ID'] ?? 0))
						->setSelect(['*'])
						->exec()
						->fetch() ?: [];
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
							'originalDavXmlId' => $parentEvent['G_EVENT_ID'] ?? null,
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

				if ($isNewAttendee && !empty($childParams['arFields']['RECURRENCE_ID']))
				{
					$childParams['arFields']['RECURRENCE_ID'] = '';
				}

				$curEvent = null;
				if (!empty($childParams['arFields']['ID']))
				{
					$curEvent = self::GetList(
						[
							'arFilter' => [
								"ID" => (int)$childParams['arFields']['ID'],
								"DELETED" => 'N',
							],
							'checkPermissions' => false,
							'parseRecursion' => false,
							'fetchAttendees' => true,
							'fetchMeetings' => false,
							'userId' => $userId,
						]
					);
				}
				if ($curEvent)
				{
					$curEvent = $curEvent[0];
				}

				$id = self::Edit($childParams);

				if (
					$userIndex[$attendeeId]
					&& $userIndex[$attendeeId]['EXTERNAL_AUTH_ID'] === 'email'
					&& ((!($clonedParams['fromWebservice'] ?? false)) || !empty($changeFields))
					&& !$isPastEvent
					&& ($childParams['overSaving'] ?? false) !== true
				)
				{
					if (empty($attaches))
					{
						$isChangeFiles = false;
						$attaches = Helper::getMailAttaches($clonedParams['UF'] ?? null, $arFields['MEETING_HOST'], $parentId, $isChangeFiles);
						if ($isChangeFiles)
						{
							$changeFields[] = [
								'fieldKey' => 'FILES',
							];
						}
					}

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
					$additionalChildArFields['ATTACHES'] = $attaches;
					$additionalChildArFields['ID'] = $id;
					$additionalChildArFields['ICAL_ORGANIZER'] = self::getOrganizerForIcal($userIndex, (int)$childParams['arFields']['MEETING_HOST'], $sender['EMAIL']);
					$additionalChildArFields['ICAL_ATTENDEES'] = self::createMailAttendeesCollection(
						$userIndex,
						$childParams['arFields']['MEETING']['HIDE_GUESTS'] ?? true,
						[$attendeeId, $childParams['arFields']['MEETING_HOST']],
						$attendees
					);
					$additionalChildArFields['ICAL_ATTACHES'] = $attaches;

					if (count($eventManagersCollection) <= 3)
					{
						if (!empty($currentAttendeesIndex[$attendeeId]))
						{
							if (
								count($changeFields) !== 1
								|| $changeFields[0]['fieldKey'] !== 'ATTENDEES'
								|| !$meetingInfo['HIDE_GUESTS']
							)
							{
								$eventManagersCollection[] = SenderEditInvitation::createInstance(
									array_merge(
										self::prepareChildParamsForIcalInvitation($childParams['arFields']),
										$additionalChildArFields
									),
									IcalMailContext::createInstance(
										self::getMailAddresser($sender, $meetingInfo['MAIL_FROM']),
										self::getMailReceiver($userIndex[$attendeeId])
									)->setChangeFields($changeFields)
								);
							}
						}
						else
						{
							$eventManagersCollection[] = SenderRequestInvitation::createInstance(
								array_merge(
									self::prepareChildParamsForIcalInvitation($childParams['arFields']),
									$additionalChildArFields
								),
								IcalMailContext::createInstance(
									self::getMailAddresser($sender, $meetingInfo['MAIL_FROM']),
									self::getMailReceiver($userIndex[$attendeeId])
								)
							);
						}
					}
					else
					{
						MailInvitationManager::createAgentSent($eventManagersCollection);
						$eventManagersCollection = [];
					}

					unset($additionalChildArFields);
				}

				if (
					$chatId > 0
					&& $chat
					&& $userIndex[$attendeeId]
					&& $userIndex[$attendeeId]['EXTERNAL_AUTH_ID'] !== 'email'
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

		if (!empty($eventManagersCollection))
		{
			MailInvitationManager::createAgentSent($eventManagersCollection);
			$eventManagersCollection = [];
		}

		// Delete
		$delIdStr = '';
		if (!$isNewEvent && count($deletedAttendees) > 0)
		{
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
					CCalendarNotify::Send(array(
						"mode" => 'cancel',
						"name" => $arFields['NAME'] ?? null,
						"from" => $fromTo['DATE_FROM'] ?? null,
						"to" => $fromTo['DATE_TO'] ?? null,
						"location" => CCalendar::GetTextLocation($arFields["LOCATION"] ?? null),
						"guestId" => $att["USER_ID"] ?? null,
						"eventId" => $parentId,
						"userId" => $arFields['MEETING_HOST'] ?? null,
						"fields" => $arFields
					));
				}
				//add pull event to update calendar grid after event delete
				$pullUserId = (int)$attendeeId;
				if ($pullUserId > 0)
				{
					Util::addPullEvent(
						'delete_event',
						$pullUserId,
						[
							'fields' => $arFields,
							'requestUid' => $params['userId']
						]
					);
				}
				CCalendarNotify::ClearNotifications($arFields['PARENT_ID'], $pullUserId);
				$delIdStr .= ','.(int)($att['EVENT_ID'] ?? null);

				$currentEvent = self::GetList(
					array(
						'arFilter' => array(
							"PARENT_ID" => $parentId,
							"OWNER_ID" => $attendeeId,
							"IS_MEETING" => 1,
							"DELETED" => "N"
						),
						'parseRecursion' => false,
						'fetchAttendees' => true,
						'fetchMeetings' => true,
						'checkPermissions' => false,
						'setDefaultLimit' => false
					)
				);
				$currentEvent = $currentEvent[0];

				$isExchangeEnabled = CCalendar::IsExchangeEnabled($attendeeId);
				if (($isExchangeEnabled || $isCalDavEnabled) && $currentEvent)
				{
					CCalendarSync::DoDeleteToDav([
						'bCalDav' => $isCalDavEnabled,
						'bExchangeEnabled' => $isExchangeEnabled,
						'sectionId' => $currentEvent['SECT_ID'] ?? null
					], $currentEvent);
				}

				if ($currentEvent)
				{
					self::onEventDelete($currentEvent, $params);
				}

				if (isset($att['EXTERNAL_AUTH_ID']) && $att['EXTERNAL_AUTH_ID'] === 'email' && !$isPastEvent)
				{
					$declinedUser = $receiver = $userIndex[$attendeeId];
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
						$meetingHostSettings = \Bitrix\Calendar\UserSettings::get($arFields['MEETING_HOST']);
						$sender['EMAIL'] = $meetingHostSettings['sendFromEmail'];
					}
					if (empty($sender['ID']) && isset($sender['USER_ID']))
					{
						$sender['ID'] = (int)$sender['USER_ID'];
					}

//					$sender = $currentAttendeesIndex[$arFields['MEETING_HOST']];

					$declinedUser['STATUS'] = 'declined';
					$additionalChildArFields['ICAL_ORGANIZER'] = self::getOrganizerForIcal($currentAttendeesIndex, (int)$arFields['MEETING_HOST'], $sender['EMAIL']);
					$additionalChildArFields['ICAL_ATTENDEES'] = self::createMailAttendeesCollection([$declinedUser['ID'] => $declinedUser], false);

					if (count($eventManagersCollection) <= 3)
					{
						$eventManagersCollection[] = SenderCancelInvitation::createInstance(
							array_merge(self::prepareChildParamsForIcalInvitation($arFields), $additionalChildArFields),
							IcalMailContext::createInstance(self::getMailAddresser($sender, $meetingInfo['MAIL_FROM']), self::getMailReceiver($receiver))
						);
					}
					else
					{
						MailInvitationManager::createAgentSent($eventManagersCollection);
					}
				}
			}

			if (!empty($eventManagersCollection))
			{
				MailInvitationManager::createAgentSent($eventManagersCollection);
			}
		}

		$delIdStr = trim($delIdStr, ', ');

		if ($delIdStr !== '')
		{
			$strSql =
				"UPDATE b_calendar_event SET ".
				$DB->PrepareUpdate("b_calendar_event", ["DELETED" => "Y"]).
				" WHERE PARENT_ID=". (int)$parentId ." AND ID IN (" . $delIdStr . ")";

			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		if (count($involvedAttendees) > 0)
		{
			$involvedAttendees = array_unique($involvedAttendees);
			CCalendar::UpdateCounter($involvedAttendees);
		}
	}

	public static function UpdateParentEventExDate($recurrenceId, $exDate, $attendeeIds)
	{
		global $DB, $CACHE_MANAGER;
		$parameters = [
			'select' => [
				'EXDATE',
			],
			'filter' => [
				'=PARENT_ID' => $recurrenceId,
			],
			'limit' => 1,
		];

		$exDates = Internals\EventTable::getList($parameters)->fetchAll();
		$exDates = self::GetExDate($exDates[0]['EXDATE']);
		$exDates[] = date(
			ExcludedDatesCollection::EXCLUDED_DATE_FORMAT,
			\CCalendar::Timestamp($exDate)
		);
		$exDates = array_unique($exDates);
		$strExDates = implode(';', $exDates);

		$strSql =
			"UPDATE b_calendar_event SET ".
			$DB->PrepareUpdate("b_calendar_event", array('EXDATE' => $strExDates)).
			" WHERE PARENT_ID=". (int)$recurrenceId;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

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
			"TS_TO" => $toTs
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
			$arFields['MEETING'] = $arFields['~MEETING'];

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

	public static function UpdateUserFields($eventId, $arFields = [])
	{
		$eventId = (int)$eventId;
		if (!is_array($arFields) || count($arFields) == 0 || $eventId <= 0)
			return false;

		global $USER_FIELD_MANAGER;
		if ($USER_FIELD_MANAGER->CheckFields("CALENDAR_EVENT", $eventId, $arFields))
			$USER_FIELD_MANAGER->Update("CALENDAR_EVENT", $eventId, $arFields);

		foreach(GetModuleEvents("calendar", "OnAfterCalendarEventUserFieldsUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($eventId, $arFields));

		self::updateSearchIndex($eventId);

		return true;
	}

	public static function GetChildEvents($parentId)
	{
		global $DB;

		$arFields = self::GetFields();
		$childEvents = [];
		$selectList = "";
		foreach($arFields as $field)
			$selectList .= $field['FIELD_NAME'].", ";
		$selectList = trim($selectList, ' ,').' ';

		if ($parentId > 0)
		{

			$strSql = "
				SELECT ".
				$selectList.
				"FROM b_calendar_event CE WHERE CE.PARENT_ID=". (int)$parentId;

			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			while($event = $res->Fetch())
			{
				$childEvents[] = $event;
			}
		}
		return false;
	}

	public static function Delete($params)
	{
		global $DB, $CACHE_MANAGER;
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
						'ID' => $id
					],
					'parseRecursion' => false
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
							"ID" => $entry['PARENT_ID']
						],
						'parseRecursion' => false
                    ]);
					$parentEvent = $parentEvent[0];
					if ($parentEvent)
					{
						$accessController = new EventAccessController($userId);
						$eventModel = self::getEventModelForPermissionCheck(
							(int)($parentEvent['ID'] ?? 0),
							$parentEvent,
							$userId
						);
						$eventModel->setSectionId($parentEvent['SECT_ID']);

						$perm = $accessController->check(ActionDictionary::ACTION_EVENT_EDIT, $eventModel);
						if (!$perm)
						{
							if (in_array($entry['MEETING_STATUS'] ?? null,
								[
									\Bitrix\Calendar\Core\Event\Tools\Dictionary::MEETING_STATUS['Yes'],
									\Bitrix\Calendar\Core\Event\Tools\Dictionary::MEETING_STATUS['Question'],
								], true))
							{
								self::SetMeetingStatus([
			                        'userId' => $userId,
			                        'eventId' => $entry['ID'],
			                        'status' => 'N'
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
					CCalendarLiveFeed::OnDeleteCalendarEventEntry($entry['PARENT_ID'], $entry);
				}
				else
				{
					CCalendarLiveFeed::OnDeleteCalendarEventEntry($entry['ID'], $entry);
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

					$chEventIds = [];
					$icalManagersCollection = [];
					foreach($childEvents as $chEvent)
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$chEvent["OWNER_ID"]);
						$isSharing = $chEvent['EVENT_TYPE'] === Dictionary::EVENT_TYPE['shared']
							|| $chEvent['EVENT_TYPE'] === Dictionary::EVENT_TYPE['shared_crm']
						;
						if ($chEvent["MEETING_STATUS"] !== "N" && $sendNotification && !$isPastEvent && !$isSharing)
						{
							$fromTo = self::GetEventFromToForUser($entry, $chEvent["OWNER_ID"]);
							CCalendarNotify::Send(array(
								'mode' => 'cancel',
								'name' => $chEvent['NAME'],
								"from" => $fromTo["DATE_FROM"],
								"to" => $fromTo["DATE_TO"],
								"location" => CCalendar::GetTextLocation($chEvent["LOCATION"]),
								"guestId" => $chEvent["OWNER_ID"],
								"eventId" => $id,
								"userId" => $userId,
							));
						}
						$chEventIds[] = $chEvent["ID"];

						if ($chEvent["MEETING_STATUS"] === "Q")
						{
							$involvedAttendees[] = $chEvent["OWNER_ID"];
						}

						$bExchange = CCalendar::IsExchangeEnabled($chEvent["OWNER_ID"]);
						if ($bExchange || $bCalDav)
						{
							CCalendarSync::DoDeleteToDav(array(
									'bCalDav' => $bCalDav,
									'bExchangeEnabled' => $bExchange,
									'sectionId' => $chEvent['SECT_ID']
							), $chEvent);
						}

						self::onEventDelete($chEvent, $params);

						$isParent = $chEvent['ID'] === $chEvent['PARENT_ID'];
						if (
							ICalUtil::isMailUser($chEvent['OWNER_ID'])
							&& !$isParent
							&& !$isPastEvent
						)
						{
							if (is_iterable($chEvent['ATTENDEE_LIST']))
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

							$declinedUser = $attendees[$chEvent['OWNER_ID']];
							$declinedUser['STATUS'] = 'declined';
							$additionalChildArFields['ICAL_ORGANIZER'] = self::getOrganizerForIcal($attendees, (int)$chEvent['MEETING_HOST'], $sender['EMAIL']);
							$additionalChildArFields['ICAL_ATTENDEES'] = self::createMailAttendeesCollection([$declinedUser['ID'] => $declinedUser], false);
							/** increment version to delete event in outside service */
							$chEvent['VERSION'] = (int)$chEvent['VERSION'] + 1;

							$icalManagersCollection[] = SenderCancelInvitation::createInstance(
								array_merge(self::prepareChildParamsForIcalInvitation($chEvent), $additionalChildArFields),
								IcalMailContext::createInstance(
									self::getMailAddresser($sender, $chEvent['MEETING']['MAIL_FROM']),
									self::getMailReceiver($attendees[$chEvent['OWNER_ID']])
								)
							);
						}

						$pullUserId = (int)$chEvent['CREATED_BY'] > 0 ? (int)$chEvent['CREATED_BY'] : $userId;
						if ($pullUserId)
						{
							Util::addPullEvent(
								'delete_event',
								$pullUserId,
								[
									'fields' => $chEvent,
									'requestUid' => $params['requestUid']
								]
							);
						}
					}

					if (!empty($icalManagersCollection))
					{
						$managerChunks = array_chunk($icalManagersCollection, 3);
						foreach ($managerChunks as $chunk)
						{
							MailInvitationManager::createAgentSent($chunk);
						}
					}

					// Set flag
					if (!empty($params['bMarkDeleted']))
					{
						$DB->Query("UPDATE b_calendar_event SET ".
							$DB->PrepareUpdate("b_calendar_event", array("DELETED" => "Y")).
							" WHERE PARENT_ID=".$id, false, "File: ".__FILE__."<br>Line: ".__LINE__);
					}
					else // Actual deleting
					{
						$strSql = "DELETE from b_calendar_event WHERE PARENT_ID=".$id;
						$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

						if (!Util::isSectionStructureConverted())
						{
							$strChEvent = join(',', $chEventIds);
							if (!empty($chEventIds))
							{
								$DB->Query("DELETE FROM b_calendar_event_sect WHERE EVENT_ID in (".$strChEvent.")", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
							}
						}
					}

					if (!empty($involvedAttendees))
					{
						CCalendar::UpdateCounter($involvedAttendees);
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
					$DB->Query("UPDATE b_calendar_event SET ".
						$DB->PrepareUpdate("b_calendar_event", array("DELETED" => "Y")).
						" WHERE ID=".$id, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
				else
				{
					// Real deleting
					$DB->Query("DELETE from b_calendar_event WHERE ID=".$id, false, "File: ".__FILE__."<br>Line: ".__LINE__);

					// Del link from table
					if (!Util::isSectionStructureConverted())
					{
						$DB->Query("DELETE FROM b_calendar_event_sect WHERE EVENT_ID=".$id, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					}
				}

				if (!empty($arAffectedSections))
				{
					CCalendarSect::UpdateModificationLabel($arAffectedSections);
				}

				foreach(EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarEventDelete") as $event)
				{
					ExecuteModuleEventEx($event, [$id, $entry]);
				}

				CCalendar::ClearCache('event_list');

				$pullUserId = (int)$entry['CREATED_BY'] > 0 ? (int)$entry['CREATED_BY'] : $userId;
				if ($pullUserId)
				{
					Util::addPullEvent(
						'delete_event',
						$pullUserId,
						[
							'fields' => $entry,
							'requestUid' => $params['requestUid'] ?? null
						]
					);
				}

				return true;
			}
		}

		return false;
	}

	public static function SetMeetingStatusEx($params)
	{
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
						"ID" => $params['parentId']
					],
					'silentErrorMode' => false,
					'recursionEditMode' => $reccurentMode,
					'userId' => $event['MEETING_HOST'],
					'checkPermission' => false,
					'currentEventDateFrom' => $currentDateFrom,
					'sendEditNotification' => false,
					'editMeetingStatus' => $params,
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
						'personalNotification' => true
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
						'personalNotification' => true
					]);
				}

				foreach($recRelatedEvents as $ev)
				{
					if ($ev['ID'] == ($params['eventId'] ?? null))
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
							'status' => $params['status']
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
			]);
		}
	}

	public static function SetMeetingStatus($params)
	{
		CTimeZone::Disable();
		global $DB, $CACHE_MANAGER;
		$eventId = $params['eventId'] = (int)$params['eventId'];
		$userId = $params['userId'] = (int)$params['userId'];
		$status = mb_strtoupper($params['status']);
		$prevStatus = null;
		if (!in_array($status, ["Q", "Y", "N", "H", "M"], true))
		{
			$status = $params['status'] = "Q";
		}

		$event = self::GetList(
			array(
				'arFilter' => array(
					"ID" => $eventId,
					"IS_MEETING" => 1,
					"DELETED" => "N"
				),
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'fetchMeetings' => true,
				'checkPermissions' => false,
				'setDefaultLimit' => false
			));

		if (!empty($event))
		{
			$event = $event[0];
			$prevStatus = $event['MEETING_STATUS'];
		}

		if ($event && $event['IS_MEETING'] && (int)$event['PARENT_ID'] > 0)
		{
			if ($event['EVENT_TYPE'] === Dictionary::EVENT_TYPE['shared_crm'])
			{
				Sharing\SharingEventManager::onSharingEventMeetingStatusChange(
					(int)$event['PARENT_ID'],
					$userId,
					$status
				);
			}

			if (
				$status === 'N'
				&& in_array($event['EVENT_TYPE'], [Dictionary::EVENT_TYPE['shared'], Dictionary::EVENT_TYPE['shared_crm']], true)
			)
			{
				Sharing\SharingEventManager::setCanceledTimeOnSharedLink((int)$event['PARENT_ID']);
			}

			if (ICalUtil::isMailUser($event['MEETING_HOST']))
			{
				IncomingEventManager::rehandleRequest([
					'event' => $event,
					'userId' => $userId,
					'answer' => $status === 'Y',
				]);
			}

			$strSql = "UPDATE b_calendar_event SET ".
				$DB->PrepareUpdate("b_calendar_event", ["MEETING_STATUS" => $status]).
				" WHERE PARENT_ID=".(int)$event['PARENT_ID']." AND OWNER_ID=".$userId;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

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
					"fields" => $event
				]);
			}

			if (
				(
					$event['EVENT_TYPE'] === Dictionary::EVENT_TYPE['shared']
					|| $event['EVENT_TYPE'] === Dictionary::EVENT_TYPE['shared_crm']
				)
				&& $status === 'Y'
				&& ($params['sharingAutoAccept'] ?? null) === true
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
					Util::addPullEvent(
						'set_meeting_status',
						$attendee['id'],
						[
							'fields' => $event,
							'requestUid' => $params['requestUid'] ?? null
						]
					);
					$addedPullUserList[] = (int)$attendee['id'];
				}
			}

			$pullUserId = (int)($event['CREATED_BY'] ?? $userId);
			if ($pullUserId && !in_array($pullUserId, $addedPullUserList, true))
			{
				Util::addPullEvent(
					'set_meeting_status',
					$pullUserId,
					[
						'fields' => $event,
						'requestUid' => $params['requestUid']
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
					"isSharing" => $event['EVENT_TYPE'] === Dictionary::EVENT_TYPE['shared'],
					"fields" => $event
				]);
			}
			CCalendarSect::UpdateModificationLabel(array($event['SECTIONS'][0] ?? null));

			if ($status === "N")
			{
				$childEvent = self::GetList(
					array(
						'arFilter' => array(
							"PARENT_ID" => $event['PARENT_ID'],
							"CREATED_BY" => $userId,
							"IS_MEETING" => 1,
							"DELETED" => "N"
						),
						'parseRecursion' => false,
						'fetchAttendees' => true,
						'checkPermissions' => false,
						'setDefaultLimit' => false
					)
				);

				if ($childEvent && $childEvent[0])
				{
					$childEvent = $childEvent[0];
					$bCalDav = CCalendar::IsCalDAVEnabled();
					$bExchange = CCalendar::IsExchangeEnabled($userId);

					if ($bExchange || $bCalDav)
					{
						CCalendarSync::DoDeleteToDav(array(
							'bCalDav' => $bCalDav,
							'bExchangeEnabled' => $bExchange,
							'sectionId' => $childEvent['SECT_ID']
						), $childEvent);
					}

					self::onEventDelete($childEvent);
				}
			}

			if ($status === "Y")
			{
				if (($params['affectRecRelatedEvents'] ?? null) !== false)
				{
					$event = self::GetList(
						array(
							'arFilter' => array(
								"ID" => $eventId,
								"IS_MEETING" => 1,
								"DELETED" => "N"
							),
							'parseRecursion' => false,
							'fetchAttendees' => true,
							'fetchMeetings' => true,
							'checkPermissions' => false,
							'setDefaultLimit' => false
						)
					);

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
							'setDefaultLimit' => false
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
								'affectRecRelatedEvents' => false
							]);

							self::SetMeetingStatusForRecurrenceEvents(
								$event['RECURRENCE_ID'],
								$userId,
								$params['eventId'],
								$status
							);
						}
					}

					if (!empty($event['RRULE']) && in_array($prevStatus, ['N', 'Q', 'H']))
					{
						self::SetMeetingStatusForRecurrenceEvents($event['PARENT_ID'], $userId, $params['eventId'], $status);
					}
				}

				if ($prevStatus === 'N')
				{
					CCalendar::syncChange(
						$eventId,
						[
							"MEETING_STATUS" => $status
						],
						[
							'userId' => $userId,
							'originalFrom' => null,
						],
						null //$event
					);
				}
			}

			if (!empty($event['RECURRENCE_ID']))
			{
				self::pushUpdateDescriptionToQueue($event['RECURRENCE_ID'], $userId, $status);
			}
			if (!empty($event['PARENT_ID']) && $event['PARENT_ID'] != $event['RECURRENCE_ID'])
			{
				self::pushUpdateDescriptionToQueue($event['PARENT_ID'], $userId, $status);
			}

			CCalendarLiveFeed::OnChangeMeetingStatusEventEntry(array(
				'userId' => $userId,
				'eventId' => $eventId,
				'status' => $status,
				'event' => $event
			));

			CCalendar::UpdateCounter($userId);

			$CACHE_MANAGER->ClearByTag('calendar_user_'.$userId);
			$CACHE_MANAGER->ClearByTag('calendar_user_'.$event['CREATED_BY']);
		}
		else
		{
			CCalendarNotify::ClearNotifications($eventId);
		}

		CTimeZone::Enable();
		CCalendar::ClearCache(['attendees_list', 'event_list']);
	}

	public static function SetMeetingStatusForRecurrenceEvents(
		int $recurrenceId,
		int $userId,
		int $eventId,
		string $status
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
				'affectRecRelatedEvents' => false
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
		global $DB;
		$eventId = (int)$eventId;
		$userId = (int)$userId;
		$status = false;
		$event = self::GetById($eventId, false);
		if ($event && $event['IS_MEETING'] && (int)$event['PARENT_ID'] > 0)
		{
			if ((int)$event['CREATED_BY'] === $userId)
			{
				$status = $event['MEETING_STATUS'];
			}
			else
			{
				$res = $DB->Query("SELECT MEETING_STATUS from b_calendar_event 
                      WHERE PARENT_ID=". (int)$event['PARENT_ID'] ." 
                      AND CREATED_BY=".$userId, false, "File: ".__FILE__."<br>Line: ".__LINE__
				);
				$event = $res->Fetch();
				$status = $event['MEETING_STATUS'];
			}
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
	public static function GetAccessibilityForUsers($params = [])
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

		$events = self::GetList(
			array(
				'arFilter' => array(
					"FROM_LIMIT" => $params['from'],
					"TO_LIMIT" => $params['to'],
					"CAL_TYPE" => 'user',
					"OWNER_ID" => $users,
					"ACTIVE_SECTION" => "Y"
				),
				'parseRecursion' => true,
				'fetchAttendees' => true,
				'fetchSection' => true,
				'parseDescription' => false,
				'setDefaultLimit' => false,
				'checkPermissions' => $params['checkPermissions']
			)
		);

		foreach($events as $event)
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

			$accessibility[$event['OWNER_ID']][] = array(
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
				"EVENT_TYPE" => $event["EVENT_TYPE"]
			);
		}

		return $accessibility;
	}

	public static function GetAbsent($users = false, $params = [])
	{
		// Can be called from agent... So we have to create $USER if it is not exists
		$tempUser = CCalendar::TempUser(false, true);
		$checkPermissions = $params['checkPermissions'] !== false;
		$curUserId = isset($params['userId']) ? (int)$params['userId'] : CCalendar::GetCurUserId();
		$arUsers = [];

		if ($users !== false && is_array($users))
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

		$arFilter = array(
			'DELETED' => 'N',
			'ACCESSIBILITY' => 'absent',
		);

		if ($users)
		{
			$arFilter['CREATED_BY'] = $users;
		}

		if (isset($params['fromLimit']))
			$arFilter['FROM_LIMIT'] = CCalendar::Date(CCalendar::Timestamp($params['fromLimit'], false), true, false);
		if (isset($params['toLimit']))
			$arFilter['TO_LIMIT'] = CCalendar::Date(CCalendar::Timestamp($params['toLimit'], false), true, false);

		$arEvents = self::GetList(
			array(
				'arFilter' => $arFilter,
				'parseRecursion' => true,
				'getUserfields' => false,
				'fetchAttendees' => false,
				'userId' => $curUserId,
				'preciseLimits' => true,
				'checkPermissions' => false,
				'parseDescription' => false,
				'skipDeclined' => true
			)
		);

		//$bSocNet = Loader::includeModule("socialnetwork");
		$result = [];
		$settings = false;

		foreach($arEvents as $event)
		{
			$userId = $event['CREATED_BY'];
			if ($users !== false && !in_array($userId, $arUsers))
			{
				continue;
			}

			//if ($bSocNet && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $userId, "calendar"))
			//	continue;

			if ($event['IS_MEETING'] && $event["MEETING_STATUS"] === 'N')
			{
				continue;
			}

			if (
				$checkPermissions
				&& ($event['CAL_TYPE'] !== 'user' || $curUserId !== (int)$event['OWNER_ID'])
				&& $curUserId !== (int)$event['CREATED_BY'])
			{
				$sectId = $event['SECT_ID'];
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
			$result[] = array(
				'ID' => $event['ID'],
				'NAME' => $event['NAME'],
				'DATE_FROM' => CCalendar::Date($fromTs, !$skipTime, false),
				'DATE_TO' => CCalendar::Date($toTs, !$skipTime, false),
				'DT_FROM_TS' => $fromTs,
				'DT_TO_TS' => $toTs,
				'CREATED_BY' => $userId,
				'DETAIL_TEXT' => '',
				'USER_ID' => $userId
			);
		}

		// Sort by DATE_FROM_TS_UTC
		usort($result, array('CCalendar', '_NearestSort'));

		CCalendar::TempUser($tempUser, false);
		return $result;
	}

	public static function DeleteEmpty()
	{
		global $DB;
		if (Util::isSectionStructureConverted())
		{
			$strSql = 'SELECT CE.ID, CE.LOCATION
				FROM b_calendar_event CE
				LEFT JOIN b_calendar_section CS ON (CS.ID=CE.SECTION_ID)
				WHERE CS.ID is null';
		}
		else
		{
			$strSql = 'SELECT CE.ID, CE.LOCATION
				FROM b_calendar_event CE
				LEFT JOIN b_calendar_event_sect CES ON (CE.ID=CES.EVENT_ID)
				WHERE CES.SECT_ID is null';
		}
		$itemIds = [];
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		while($arRes = $res->Fetch())
		{
			$loc = $arRes['LOCATION'] ?? null;
			if ($loc && mb_strlen($loc) > 5 && mb_substr($loc, 0, 5) === 'ECMR_')
			{
				$loc = Rooms\Util::parseLocation($loc);
				if ($loc['mrid'] !== false && $loc['mrevid'] !== false) // Release MR
				{
					Rooms\Util::releaseLocation($loc);
				}
			}
			else if ($loc && mb_strlen($loc) > 9 && mb_substr($loc, 0, 9) === 'calendar_')
			{
				$loc = Rooms\Util::parseLocation($loc);
				if ($loc['room_id'] !== false && $loc['room_event_id'] !== false) // Release calendar_room
				{
					Rooms\Util::releaseLocation($loc);
				}
			}
			$itemIds[] = (int)$arRes['ID'];
		}

		// Clean from 'b_calendar_event'
		if (!empty($itemIds))
		{
			$DB->Query("DELETE FROM b_calendar_event WHERE ID in (".implode(',', $itemIds).")", false,
				"FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		CCalendar::ClearCache(array('section_list', 'event_list'));
	}

	public static function CleanEventsWithDeadParents()
	{
		global $DB;
		$strSql = "SELECT PARENT_ID from b_calendar_event where PARENT_ID in (SELECT ID from b_calendar_event where MEETING_STATUS='H' and DELETED='Y') AND DELETED='N'";
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

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
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		CCalendar::ClearCache(['section_list', 'event_list']);
	}

	public static function CheckEndUpdateAttendeesCodes($event)
	{
		if ($event['ID'] > 0 && $event['IS_MEETING']
			&& empty($event['ATTENDEES_CODES']) && is_array($event['ATTENDEE_LIST']))
		{
			$event['ATTENDEES_CODES'] = [];
			foreach($event['ATTENDEE_LIST'] as $attendee)
			{
				if ((int)$attendee['id'] > 0)
				{
					$event['ATTENDEES_CODES'][] = 'U'. (int)$attendee['id'];
				}
			}
			$event['ATTENDEES_CODES'] = array_unique($event['ATTENDEES_CODES']);

			global $DB;
			$strSql =
				"UPDATE b_calendar_event SET ".
				"ATTENDEES_CODES='".implode(',', $event['ATTENDEES_CODES'])."'".
				" WHERE PARENT_ID=". (int)$event['ID'];
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			CCalendar::ClearCache(array('event_list'));
		}
		return $event['ATTENDEES_CODES'];
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
							"CREATED_BY" => $userId
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
		if (!empty($event['PARENT_ID']))
		{
			$UF = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $event['PARENT_ID'], LANGUAGE_ID);
		}
		else
		{
			$UF = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $event['ID'] ?? null, LANGUAGE_ID);
		}
		return $UF;
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
				'DELETED' => 'N'
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
				'setDefaultLimit' => false
			]);
		}
		return [];
	}

	public static function GetEventCommentXmlId($event)
	{
		if (
			isset($event['ORIGINAL_DATE_FROM'], $event['RECURRENCE_ID'])
		)
		{
			return 'EVENT'
				. '_' . $event['RECURRENCE_ID']
				. '_' . CCalendar::Date(CCalendar::Timestamp($event['ORIGINAL_DATE_FROM']), false)
			;
		}

		$commentXmlId = "EVENT_" . $event['PARENT_ID'] ?? $event['ID'];

		if (
			self::CheckRecurcion($event)
			&& (!isset($event['RINDEX']) || $event['RINDEX'] > 0)
			&& (CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false)
				!== CCalendar::Date(CCalendar::Timestamp($event['~DATE_FROM']), false))
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

	public static function GetRRULEDescription($event, $html = false, $showUntil = true)
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
						$res = GetMessage('EC_RRULE_EVERY_DAY');
					}
					else
					{
						$res = GetMessage('EC_RRULE_EVERY_DAY_1', array('#DAY#' => $event['RRULE']['INTERVAL']));
					}
					break;
				case 'WEEKLY':
					$daysList = [];
					foreach ($event['RRULE']['BYDAY'] as $day)
					{
						$daysList[] = GetMessage('EC_'.$day);
					}

					$daysList = implode(', ', $daysList);
					if ((int)$event['RRULE']['INTERVAL'] === 1)
					{
						$res = GetMessage('EC_RRULE_EVERY_WEEK', [
							'#DAYS_LIST#' => $daysList
						]);
					}
					else
					{
						$res = GetMessage('EC_RRULE_EVERY_WEEK_1', [
							'#WEEK#' => $event['RRULE']['INTERVAL'],
							'#DAYS_LIST#' => $daysList
						]);
					}
					break;
				case 'MONTHLY':
					if ((int)$event['RRULE']['INTERVAL'] === 1)
					{
						$res = GetMessage('EC_RRULE_EVERY_MONTH');
					}
					else
					{
						$res = GetMessage('EC_RRULE_EVERY_MONTH_1', [
							'#MONTH#' => $event['RRULE']['INTERVAL']
						]);
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
						$res = GetMessage('EC_RRULE_EVERY_YEAR', [
							'#DAY#' => FormatDate('j', $fromTs), // day
							'#MONTH#' => FormatDate('n', $fromTs) // month
						]);
					}
					else
					{
						$res = GetMessage('EC_RRULE_EVERY_YEAR_1', [
							'#YEAR#' => $event['RRULE']['INTERVAL'],
							'#DAY#' => FormatDate('j', $fromTs), // day
							'#MONTH#' => FormatDate('n', $fromTs) // month
						]);
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
				$res .= GetMessage('EC_RRULE_FROM', array('#FROM_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['~DATE_FROM']), false)));
			}
			else
			{
				$res .= GetMessage('EC_RRULE_FROM', array('#FROM_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false)));
			}

			if ($showUntil && ($event['RRULE']['UNTIL'] ?? null) != CCalendar::GetMaxDate())
			{
				$res .= ' '.GetMessage('EC_RRULE_UNTIL', array('#UNTIL_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['RRULE']['UNTIL']), false)));
			}
			elseif ($showUntil && (($event['RRULE']['COUNT'] ?? null) > 0))
			{
				$res .= ', '.GetMessage('EC_RRULE_COUNT', array('#COUNT#' => $event['RRULE']['COUNT']));
			}
		}

		return $res;
	}

	public static function ExcludeInstance($eventId, $excludeDate)
	{
		global $CACHE_MANAGER;
		$eventId = (int)$eventId;
		$excludeDateTs = CCalendar::Timestamp($excludeDate);
		$excludeDate = CCalendar::Date($excludeDateTs, false);

		$event = self::GetList(
			array(
				'arFilter' => array(
					"ID" => $eventId,
					"DELETED" => "N"
				),
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'setDefaultLimit' => false
			)
		);
		if ($event && is_array($event[0]))
			$event = $event[0];

		if ($event && self::CheckRecurcion($event) && $excludeDate)
		{
			$excludeDates = self::GetExDate($event['EXDATE']);
			$excludeDates[] = $excludeDate;

			$id = CCalendar::SaveEvent(array(
				'arFields' => array(
					'ID' => $event['ID'],
					'DATE_FROM' => $event['DATE_FROM'],
					'DATE_TO' => $event['DATE_TO'],
					'EXDATE' => self::SetExDate($excludeDates)
				),
				'silentErrorMode' => false,
				'recursionEditMode' => 'skip',
				'editParentEvents' => true,
			));

			if (is_array($event['ATTENDEE_LIST']))
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
						CCalendarNotify::Send(array(
							"mode" => 'cancel_this',
							"name" => $event['NAME'],
							"from" => $excludeDate,
							"guestId" => $attendee["id"],
							"eventId" => $event['PARENT_ID'],
							"userId" => isset($event['MEETING']['MEETING_CREATOR']) ? $event['MEETING']['MEETING_CREATOR'] : $event['MEETING_HOST'],
							"fields" => $event
						));
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
						'ID' => $attachedIdList
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
			$events = self::getList(
				array(
					'arFilter' => array(
						"ID" => $eventId,
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false
				)
			);
			$res = is_array($events[0]) ? self::formatSearchIndexContent($events[0]) : '';
		}
		return $res;
	}

	public static function getSearchIndexContentBatch($eventIdList = [])
	{
		$res = [];
		if (is_array($eventIdList))
		{
			$events = self::getList(
				array(
					'arFilter' => array(
						"ID" => $eventIdList,
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false
				)
			);

			foreach($events as $event)
			{
				$res[$event['ID']] = self::formatSearchIndexContent($event);
			}
		}
		return $res;
	}

	public static function updateSearchIndex($eventIdList = [], $params = [])
	{
		global $DB;

		if (isset($params['events']))
		{
			$events = $params['events'];
		}
		else
		{
			if (!is_array($eventIdList))
				$eventIdList = array($eventIdList);

			$events = self::getList(
				array(
					'arFilter' => array(
						"ID" => $eventIdList,
						"DELETED" => false
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false,
					'userId' => $params['userId'] ?? null
				)
			);
		}

		if (is_array($events))
		{
			foreach($events as $event)
			{
				$content = self::formatSearchIndexContent($event);
				$strSql = "UPDATE b_calendar_event SET ".
					$DB->PrepareUpdate("b_calendar_event", array('SEARCHABLE_CONTENT' => $content)).
					" WHERE ID=". (int)$event['ID'];
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
	}

	public static function formatSearchIndexContent($entry = [])
	{
		$content = '';
		if (!empty($entry))
		{
			$content = static::prepareToken(
				Emoji::encode($entry['NAME'])
				. ' '
				. Emoji::encode($entry['DESCRIPTION'])
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
							if (mb_substr($code, 0, 1) !== 'U')
							{
								$attendeesCodes[] = $code;
							}
						}
					}
					else
					{
						$attendeesCodes = $entry['ATTENDEES_CODES'];
					}
					$content .= ' '.static::prepareToken(join(' ', Bitrix\Socialnetwork\Item\LogIndex::getEntitiesName($attendeesCodes)));
				}
			}
			else
			{
				$content .= ' '.static::prepareToken(CCalendar::GetUserName($entry['CREATED_BY']));
			}

			try
			{
				if (
					!empty($entry['UF_WEBDAV_CAL_EVENT'])
					&& \Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false)
				)
				{
					$fileNameList = self::getDiskUFFileNameList($entry['UF_WEBDAV_CAL_EVENT']);
					if (!empty($fileNameList))
					{
						$content .= ' '.static::prepareToken(join(' ', $fileNameList));
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

						$index[] = $title;
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
		$res = $DB->Query('select count(*) as c  from b_calendar_event', false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $res->Fetch())
		{
			$count = $res['c'];
		}

		return $count;
	}

	public static function updateColor($eventId, $color = '')
	{
		global $DB;
		$strSql = "UPDATE b_calendar_event SET ".
			$DB->PrepareUpdate("b_calendar_event", array('COLOR' => $color)).
			" WHERE ID=". (int)$eventId;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
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
				"TO_LIMIT" => $userDateFrom
			],
			'parseRecursion' => true,
			'maxInstanceCount' => 1,
			'preciseLimits' => true,
			'fetchAttendees' => true,
			'checkPermissions' => true,
			'setDefaultLimit' => false
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
					  "TO_LIMIT" => $params['eventDate']
				  ],
				  'parseRecursion' => true,
				  'maxInstanceCount' => 1,
				  'preciseLimits' => true,
				  'fetchAttendees' => true,
				  'checkPermissions' => true,
				  'setDefaultLimit' => false
			  ]);
		}

		if (!$entry || !is_array($entry[0]))
		{
			$entry = self::GetList([
				'arFilter' => [
					"ID" => $entryId,
					"DELETED" => "N"
				],
				'parseRecursion' => true,
				'maxInstanceCount' => 1,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false
			]);
		}

		// Here we can get events with wrong RRULE ('parseRecursion' => false)
		if (!$entry || !is_array($entry[0]))
		{
			$entry = self::GetList([
				'arFilter' => [
					"ID" => $entryId,
					"DELETED" => "N"
				],
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false
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
					'fetchAttendees' => false,
					'checkPermissions' => true,
					'setDefaultLimit' => false
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

//					if ($parentEntry['CAL_TYPE'] !== $entry['CAL_TYPE'])
//					{
//						$entry = $parentEntry;
//					}

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
			&& isset($entry['ATTENDEE_LIST'])
			&& is_array($entry['ATTENDEE_LIST'])
			&& $entry['CREATED_BY'] !== $params['userId']
			&& ($params['recursion'] ?? null) !== false
		)
		{
			foreach($entry['ATTENDEE_LIST'] as $attendee)
			{
				if ((int)$attendee['id'] === (int)$params['userId'])
				{
					$entry = self::GetList([
						'arFilter' => [
							"PARENT_ID" => $entry['PARENT_ID'],
							"CREATED_BY" => $params['userId'],
							"DELETED" => "N"
						],
						'parseRecursion' => false,
						'maxInstanceCount' => 1,
						'preciseLimits' => false,
						'fetchAttendees' => false,
						'checkPermissions' => true,
						'setDefaultLimit' => false
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
				'setDefaultLimit' => false
			]
		);

		if (!$entry || !is_array($entry[0]))
		{
			$entry = self::GetList(
				[
					'arFilter' => [
						"ID" => $entryId,
						"DELETED" => "N"
					],
					'parseRecursion' => true,
					'maxInstanceCount' => 1,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false
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
						"DELETED" => "N"
					],
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false
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

	public static function getLocalBatchEvent(int $userId, int $sectionId, int $syncTimestamp, int $count = 50): array
	{
		global $DB;
		$events = [];

		$queryString = "SELECT e.*"
				. ", " . $DB->DateToCharFunction('e.DATE_FROM') . " as DATE_FROM"
				. ", " . $DB->DateToCharFunction('e.DATE_TO') . " as DATE_TO"
				. ", " . $DB->DateToCharFunction('e.DATE_CREATE') . " as DATE_CREATE"
				. ", " . $DB->DateToCharFunction('e.TIMESTAMP_X') . " as TIMESTAMP_X"
			. " FROM b_calendar_event e"
			. " INNER JOIN b_calendar_section s"
				. " ON s.ID = e.SECTION_ID"
			. " WHERE"
				. " e.CAL_TYPE = 'user'"
				. " AND e.OWNER_ID = " . $userId
				. " AND e.DELETED <> 'Y'"
				. " AND e.DATE_TO_TS_UTC >= " . $syncTimestamp
				. " AND e.SECTION_ID = " . $sectionId
				. " AND e.SYNC_STATUS IS NULL"
				. " AND (e.RECURRENCE_ID IS NULL OR e.RECURRENCE_ID = '')"
				. " AND (e.RRULE IS NULL OR e.RRULE = '')"
				. " AND (e.MEETING_STATUS != 'N'"
					. " OR e.MEETING_STATUS IS NULL)"
				. " AND s.EXTERNAL_TYPE = 'local'"
				. " AND s.GAPI_CALENDAR_ID IS NOT NULL"
			. " ORDER BY ID ASC"
			. " LIMIT " . $count
			. ";"
		;

		$eventsDb = $DB->Query($queryString);
		while ($event = $eventsDb->Fetch())
		{
			if (isset($event['REMIND']) && $event['REMIND'] !== "")
			{
				$event['REMIND'] = unserialize($event['REMIND'], ['allowed_classes' => false]);
			}
			$events[] = $event;
		}

		return $events;
	}

	public static function getLocalBatchRecurrentEvent(int $userId, int $sectionId, int $syncTimestamp = 0, int $count = 50): array
	{
		global $DB;
		$events = [];

		$queryString = "SELECT DISTINCT "
			. "e.*"
			. ", " . $DB->DateToCharFunction('e.DATE_FROM') . " as DATE_FROM"
			. ", " . $DB->DateToCharFunction('e.DATE_TO') . " as DATE_TO"
			. ", " . $DB->DateToCharFunction('e.DATE_CREATE') . " as DATE_CREATE"
			. ", " . $DB->DateToCharFunction('e.TIMESTAMP_X') . " as TIMESTAMP_X"
			// . ", GROUP_CONCAT(". $DB->DateToCharFunction('i.ORIGINAL_DATE_FROM', 'SHORT')." SEPARATOR ';') as EXDATES"
			. ", GROUP_CONCAT(". $DB->DateToCharFunction('x.ORIGINAL_DATE_FROM', 'SHORT')." SEPARATOR ';') as INSTANCES_EXDATES"
			. ", GROUP_CONCAT(". $DB->DateToCharFunction('x.DATE_FROM', 'SHORT')." SEPARATOR ';') as INSTANCES_DATE_FROM"
			. " FROM b_calendar_event e"
			// . " LEFT JOIN b_calendar_event i"
			// 	. " ON i.RECURRENCE_ID = e.PARENT_ID"
			// 	. " AND e.OWNER_ID = i.OWNER_ID"
			// 	. " AND  i.MEETING_STATUS = 'N'"
			. " LEFT JOIN b_calendar_event x "
			. " ON x.RECURRENCE_ID = e.PARENT_ID"
			. " AND e.OWNER_ID = x.OWNER_ID"
			. " AND (x.MEETING_STATUS != 'N' OR x.MEETING_STATUS IS NULL)"
			. " AND x.DELETED = 'N'"
			. " INNER JOIN b_calendar_section s"
			. " ON s.ID = e.SECTION_ID"
			. " WHERE"
			. " e.RRULE IS NOT NULL"
			. " AND e.RRULE <> ''"
			. " AND e.CAL_TYPE = 'user'"
			. " AND e.OWNER_ID = " . $userId
			. " AND e.SECTION_ID = " . $sectionId
			. " AND e.DELETED <> 'Y'"
			. " AND e.SYNC_STATUS IS NULL"
			. " AND e.DATE_TO_TS_UTC >= " . $syncTimestamp
			. " AND (e.MEETING_STATUS != 'N'"
			. " OR e.MEETING_STATUS IS NULL)"
			. " AND s.EXTERNAL_TYPE = 'local'"
			. " AND s.GAPI_CALENDAR_ID IS NOT NULL"
			. " GROUP BY e.ID"
			. " ORDER BY e.ID DESC"
			. " LIMIT " . $count
			. ";"
		;
		$eventsDb = $DB->Query($queryString);

		while ($event = $eventsDb->Fetch())
		{
			$event['RRULE'] = self::ParseRRULE($event['RRULE']);
			if (isset($event['REMIND']) && $event['REMIND'] !== "")
			{
				$event['REMIND'] = unserialize($event['REMIND'], ['allowed_classes' => false]);
			}
			// unset($event['EXDATE']);
			if (!empty($event['INSTANCES_EXDATES']) || !empty($event['INSTANCES_DATE_FROM']))
			{
				$eventExdate = explode(';', $event['EXDATE']);
				$instanceExdate = array_unique(explode(';', $event['INSTANCES_EXDATES']));
				$instanceDateFrom = array_unique(explode(';', $event['INSTANCES_DATE_FROM']));
				if (is_array($eventExdate) && is_array($instanceExdate))
				{
					$event['EXDATE'] = implode(';', array_diff($eventExdate, $instanceExdate, $instanceDateFrom));
				}
			}


			$events[] = $event;
		}

		return $events;
	}


	public static function getLocalBatchInstances(int $userId, int $sectionId, int $syncTimestamp, int $count = 50): array
	{
		global $DB;

		$strQuery =
			"SELECT"
				. " i.*"
				. ", " . $DB->DateToCharFunction('i.DATE_FROM') . " as DATE_FROM"
				. ", " . $DB->DateToCharFunction('i.DATE_TO') . " as DATE_TO"
				. ", " . $DB->DateToCharFunction('i.DATE_CREATE') . " as DATE_CREATE"
				. ", " . $DB->DateToCharFunction('i.TIMESTAMP_X') . " as TIMESTAMP_X"
				. ", " . $DB->DateToCharFunction('p.DATE_FROM') . " as PARENT_DATE_FROM"
				. ", " . $DB->DateToCharFunction('i.ORIGINAL_DATE_FROM') . " as ORIGINAL_DATE_FROM"
				. ", p.TZ_FROM as PARENT_TZ_FROM"
				. ", p.DAV_XML_ID as PARENT_DAV_XML_ID"
				. ", p.G_EVENT_ID as PARENT_G_EVENT_ID"
			. " FROM b_calendar_event as i"
			. " INNER JOIN b_calendar_event as p"
				. " ON (i.RECURRENCE_ID = p.PARENT_ID AND p.OWNER_ID = " . $userId . ")"
			. " INNER JOIN b_calendar_section s"
				. " ON s.ID = i.SECTION_ID"
			. " WHERE"
				. " p.RRULE IS NOT NULL"
				. " AND i.SYNC_STATUS IS NULL"
				. " AND (i.MEETING_STATUS != 'N'"
					. " OR i.MEETING_STATUS IS NULL)"
				. " AND p.DATE_TO_TS_UTC >= " . $syncTimestamp
				. " AND p.G_EVENT_ID IS NOT NULL"
				. " AND p.G_EVENT_ID <> ''"
				. " AND p.DELETED != 'Y'"
				. " AND i.SECTION_ID = ". $sectionId
				. " AND i.OWNER_ID = ". $userId
				. " AND s.EXTERNAL_TYPE = 'local'"
				. " AND s.GAPI_CALENDAR_ID IS NOT NULL"
			. " LIMIT " . $count
			. ";"
		;

		$instances = [];
		$instancesDb = $DB->Query($strQuery);
		while($instance = $instancesDb->Fetch())
		{
			if (isset($instance['REMIND']) && $instance['REMIND'] !== "")
			{
				$instance['REMIND'] = unserialize($instance['REMIND'], ['allowed_classes' => false]);
			}

			$instances[] = $instance;
		}

		return $instances;
	}

	/**
	 * this method for kill old agent to send invitation
	 * @param $arEventManagerInstances
	 */
	public static function sendEventInvitationUsingIcal($arEventManagerInstances): void
	{
	}

	/**
	 * @param iterable $userIndex
	 * @param bool $hideGuests
	 * @param array $attendeeIds
	 * @param array|null $attendeesId
	 * @return AttendeesCollection
	 */
	private static function createMailAttendeesCollection(iterable $userIndex, bool $hideGuests = true, array $attendeeIds = [], ?array $attendeesId = null): AttendeesCollection
	{
		$attendeesCollection = AttendeesCollection::createInstance();

		foreach ($userIndex as $attendeeId => $attendee)
		{
			if ($hideGuests && !in_array($attendeeId, $attendeeIds, true))
			{
				continue;
			}

			if ($attendeesId && !in_array($attendeeId, $attendeesId, true))
			{
				continue;
			}

			$attendeesCollection->add(
				Attendee::createInstance(
					$attendee['EMAIL'],
					$attendee['NAME'],
					$attendee['LAST_NAME'],
					Builder\Dictionary::ATTENDEE_STATUS[$attendee['STATUS']],
					Builder\Dictionary::ATTENDEE_ROLE['REQ_PARTICIPANT']
				)
			);
		}

		return $attendeesCollection;
	}

	/**
	 * @param string[] $receiver
	 * @return MailReceiver
	 */
	private static function getMailReceiver(array $receiver): MailReceiver
	{
		return MailReceiver::createInstance(
			$receiver['ID'],
			$receiver['EMAIL'],
			$receiver['NAME'],
			$receiver['LAST_NAME']
		);
	}

	/**
	 * @param string[] $addresser
	 * @param string $mailFrom
	 * @return MailAddresser
	 */
	private static function getMailAddresser(array $addresser, string $mailFrom): MailAddresser
	{
		return MailAddresser::createInstance(
			$addresser['ID'],
			$addresser['EMAIL'],
			$addresser['NAME'],
			$addresser['LAST_NAME'],
			$mailFrom
		);
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
				'=ACTIVE' => 'Y'
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
			]
		]);

		if ($user = $userOrm->fetch())
		{
			return $user;
		}

		AddMessage2Log("The meeting organizer cannot be identified for ical", "calendar");
		return null;
	}

	/**
	 * @param array $userIndex
	 * @param int $organizerId
	 * @param string $email
	 * @return Attendee
	 */
	private static function getOrganizerForIcal(array $userIndex, int $organizerId, string $email): ?Attendee
	{
		$organizer = $userIndex[$organizerId];
		if (empty($organizer))
		{
			$organizer = Helper::getUserById($organizerId);
			if ($organizer === null)
			{
				return null;
			}
		}

		return Attendee::createInstance(
			$organizer['EMAIL'],
			$organizer['NAME'],
			$organizer['LAST_NAME'],
			null,
			null,
			null,
			$email
		);
	}

	/**
	 * @param array $arFields
	 * @return array
	 */
	private static function prepareChildParamsForIcalInvitation(array $arFields): array
	{
		if (isset($arFields['DESCRIPTION']))
		{
			unset($arFields['DESCRIPTION']);
		}

		if (isset($arFields['~DESCRIPTION']))
		{
			unset($arFields['~DESCRIPTION']);
		}

		return $arFields;
	}

	/**
	 * @param array $events
	 * @param string[] $fields
	 */
	public static function updateBatchEventFields(array $events, array $fields): void
	{
		global $DB;

		CTimeZone::Disable();

		foreach ($events as $event)
		{
			$dbFields = [];
			foreach ($fields as $field)
			{
				$dbFields[$field] = $event[$field];
			}

			if (empty($dbFields))
			{
				continue;
			}

			$strUpdate = $DB->PrepareUpdate("b_calendar_event", $dbFields);
			if (!empty($strUpdate))
			{
				$strSql = "UPDATE b_calendar_event SET " . $strUpdate
					. " WHERE ID = " . (int)$event['ID'] . ";";
				$DB->Query($strSql);
			}
		}


		CTimeZone::Enable();
	}


	/**
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
	 * @param int $sectionId
	 * @param array $fields
	 */
	public static function cleanFieldsValueBySectionId(int $sectionId, array $fields): void
	{
		global $DB;
		$dbFields = [];

		foreach ($fields as $field)
		{
			$dbFields[$field] = false;
		}

		if (!$dbFields)
		{
			return;
		}

		$DB->Query("UPDATE b_calendar_event SET "
			. $DB->PrepareUpdate('b_calendar_event', $dbFields)
			. " WHERE SECTION_ID = " . $sectionId);
	}

	/**
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
					$event['SKIP_TIME'] === 'Y',
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
		if (!$eventData)
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
				]
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
				'=ENTITY_ID' => $event->getOwner()->getId()
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
			if (!$result->isSuccess() && !empty($originalEventData))
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

	private static function isNewAttendee($attendees, $currentId)
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
				'SU' => 6
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
			$arFields['RRULE']['UNTIL'] = CCalendar::Date($untilTs, false);
			unset($arFields['RRULE']['~UNTIL']);
			if (isset($arFields['RRULE']['COUNT']))
			{
				$arFields['RRULE']['COUNT'] = (int)$arFields['RRULE']['COUNT'];
			}

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
		];
		$accessResult = $accessController->batchCheck($request, $eventModel);

		return [
			'view_full' => $accessResult[ActionDictionary::ACTION_EVENT_VIEW_FULL],
			'view_time' => $accessResult[ActionDictionary::ACTION_EVENT_VIEW_TIME],
			'view_title' => $accessResult[ActionDictionary::ACTION_EVENT_VIEW_TITLE],
			'view_comments' => $accessResult[ActionDictionary::ACTION_EVENT_VIEW_COMMENTS],
			'edit' => $accessResult[ActionDictionary::ACTION_EVENT_EDIT],
		];
	}

	public static function getEventModelForPermissionCheck(int $eventId, array $event = [], int $userId = 0): EventModel
	{
		if ($userId <= 0)
		{
			$userId = CCalendar::GetUserId();
		}

		if (empty($event) || ((int)($event['ID'] ?? 0) !== $eventId))
		{
			$event = self::GetById($eventId, false);
		}

		$userEvent = self::GetList(
			[
				'arFilter' => [
					'PARENT_ID' => $eventId,
					'OWNER_ID' => $userId,
					'CAL_TYPE' => Dictionary::CALENDAR_TYPE['user'],
					'DELETED' => 'N',
				],
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

		return EventModel::createFromArray($userEvent ?: $event ?: []);
	}
}