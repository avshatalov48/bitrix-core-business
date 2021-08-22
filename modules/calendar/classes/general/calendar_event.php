<?
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/calendar/classes/general/calendar.php");

use Bitrix\Calendar\ICal\Basic\ICalUtil;
use Bitrix\Calendar\Util;
use Bitrix\Calendar\ICal\
{IncomingEventManager,
	Builder\Attendee,
	Builder\AttendeesCollection,
	MailInvitation\Context as IcalMailContext,
	MailInvitation\MailReceiver,
	MailInvitation\MailAddresser,
	MailInvitation\MailInvitationManager,
	MailInvitation\SenderCancelInvitation,
	MailInvitation\SenderEditInvitation,
	MailInvitation\SenderRequestInvitation,
	Builder,
	MailInvitation\Helper};
use \Bitrix\Main\Loader;
use \Bitrix\Main\UserTable;
use \Bitrix\Disk\Uf\FileUserType;
use \Bitrix\Disk\AttachedObject;
use Bitrix\Calendar\Internals;
use \Bitrix\Calendar\Integration\Bitrix24\Limitation;

class CCalendarEvent
{
	public static
		$eventUFDescriptions,
		$TextParser;

	private static
		$fields = [],
		$userIndex = [],
		$isAddIcalFailEmailError = false;

	public static function CheckRRULE($RRule = array())
	{
		if (is_array($RRule) && $RRule['FREQ'] !== 'WEEKLY' && isset($RRule['BYDAY']))
			unset($RRule['BYDAY']);
		return $RRule;
	}

	/**
	 * @param array $params
	 * params['arFields'] event fields
	 * params['userId'] user id
	 * params['saveAttendeesStatus'] sending notification flag
	 * @return bool|mixed
	 */
	public static function Edit($params = [])
	{
		global $DB, $CACHE_MANAGER;
		$entryFields = $params['arFields'];
		$arAffectedSections = [];
		$entryChanges = [];
		$sendInvitations = $params['sendInvitations'] !== false;
		$sendEditNotification = $params['sendEditNotification'] !== false;
		$result = false;
		$attendeesCodes = [];
		// Get current user id
		$userId = (isset($params['userId']) && (int)$params['userId'] > 0)
			? (int)$params['userId']
			: CCalendar::GetCurUserId();
		if (!$userId && isset($entryFields['CREATED_BY']))
		{
			$userId = (int)$entryFields['CREATED_BY'];
		}

		if (((!isset($entryFields['ID']) || $entryFields['ID'] <= 0)
			&& !empty($entryFields['G_EVENT_ID'])
			&& $entryFields['DAV_XML_ID'] == $entryFields['G_EVENT_ID'].'@google.com')
			|| ($params['sync'] === true))
		{
			$event = Internals\EventTable::getList([
				'filter' => [
					"=G_EVENT_ID" => $entryFields['G_EVENT_ID'],
				],
				'select' => [
					'ID',
				],
				'limit' => 1,
			])->fetch();

			if (isset($event['ID']))
			{
				$entryFields['ID'] = $event['ID'];
			}
		}

		$isNewEvent = !isset($entryFields['ID']) || !$entryFields['ID'];
		$entryFields['TIMESTAMP_X'] = CCalendar::Date(time(), true, false);

		// Current event
		$currentEvent = [];
		if ($entryFields['IS_MEETING'] && !isset($entryFields['ATTENDEES']) && isset($entryFields['ATTENDEES_CODES']))
		{
			$entryFields['ATTENDEES'] = \CCalendar::getDestinationUsers($entryFields['ATTENDEES_CODES']);
		}

		if (!$isNewEvent)
		{
			$currentEvent = isset($params['currentEvent']) ? $params['currentEvent'] : CCalendarEvent::GetById($entryFields['ID']);

			if (!is_array($entryFields['LOCATION']) || !isset($entryFields['LOCATION']['NEW']))
			{
				$entryFields['LOCATION'] = [
					'NEW' => $entryFields['LOCATION']
				];
			}

			if (is_array($entryFields['MEETING'])
				&& is_array($currentEvent['MEETING'])
				&& isset($currentEvent['MEETING']['CHAT_ID'])
				&& !isset($entryFields['MEETING']['CHAT_ID'])
			)
			{
				$entryFields['MEETING']['CHAT_ID'] = $currentEvent['MEETING']['CHAT_ID'];
			}

			if(empty($entryFields['LOCATION']['OLD']))
			{
				$entryFields['LOCATION']['OLD'] = $currentEvent['LOCATION'];
			}

			if($currentEvent['IS_MEETING'] && !isset($entryFields['ATTENDEES']) && $currentEvent['PARENT_ID'] == $currentEvent['ID'] && $entryFields['IS_MEETING'])
			{
				$entryFields['ATTENDEES'] = array();
				$attendees = self::GetAttendees($currentEvent['PARENT_ID']);
				if($attendees[$currentEvent['PARENT_ID']])
				{
					for($i = 0, $l = count($attendees[$currentEvent['PARENT_ID']]); $i < $l; $i++)
					{
						$entryFields['ATTENDEES'][] = $attendees[$currentEvent['PARENT_ID']][$i]['USER_ID'];
					}
				}
			}

			if($currentEvent['PARENT_ID'])
			{
				$entryFields['PARENT_ID'] = (int)$currentEvent['PARENT_ID'];
			}
		}

		if (self::CheckFields($entryFields, $currentEvent, $userId))
		{
			$attendees = is_array($entryFields['ATTENDEES']) ? $entryFields['ATTENDEES'] : [];
			if (
				$entryFields['CAL_TYPE'] !== \CCalendarLocation::TYPE
				&& (!$entryFields['PARENT_ID'] || $entryFields['PARENT_ID'] === $entryFields['ID'])
			)
			{
				$fromTs = $entryFields['DATE_FROM_TS_UTC'];
				$toTs = $entryFields['DATE_TO_TS_UTC'];
				if ($entryFields['DT_SKIP_TIME'] !== "Y")
				{
					$fromTs += date('Z', $entryFields['DATE_FROM_TS_UTC']);
					$toTs += date('Z', $entryFields['DATE_TO_TS_UTC']);
				}

				$entryFields['LOCATION'] = CCalendar::SetLocation(
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
						'bRecreateReserveMeetings' => $entryFields['LOCATION']['RE_RESERVE'] !== 'N'
					]
				);
			}
			else
			{
				$entryFields['LOCATION'] = CCalendar::GetTextLocation($entryFields['LOCATION']['NEW']);
			}

			// Section
			if (isset($entryFields['SECTION_ID']))
			{
				$sectionId = (int)$entryFields['SECTION_ID'];
			}
			else
			{
				$sectionId = (is_array($entryFields['SECTIONS']) && $entryFields['SECTIONS'][0])
					? (int)$entryFields['SECTIONS'][0]
					: false;
			}

			if (!$sectionId)
			{
				// It's new event we have to find section where to put it automatically
				if ($isNewEvent)
				{
					if (
						$entryFields['IS_MEETING']
						&& $entryFields['PARENT_ID']
						&& $entryFields['CAL_TYPE'] === 'user'
					)
					{
						$sectionId = CCalendar::GetMeetingSection($entryFields['OWNER_ID']);
					}
					else
					{
						$sectionId = CCalendarSect::GetLastUsedSection($entryFields['CAL_TYPE'], $entryFields['OWNER_ID'], $userId);
					}

					if ($sectionId)
					{
						$res = CCalendarSect::GetList(array(
							'arFilter' => array(
								'CAL_TYPE' => $entryFields['CAL_TYPE'],
								'OWNER_ID' => $entryFields['OWNER_ID'],
								'ID'=> $sectionId
							))
						);
						if (!$res || !$res[0])
						{
							$sectionId = false;
						}
					}
					else
					{
						$sectionId = false;
					}

					if (!$sectionId)
					{
						$sectRes = CCalendarSect::GetSectionForOwner($entryFields['CAL_TYPE'], $entryFields['OWNER_ID'], true);
						$sectionId = $sectRes['sectionId'];
					}
				}
				else
				{
					$sectionId = $currentEvent['SECTION_ID'] ? $currentEvent['SECTION_ID'] : $currentEvent['SECT_ID'];
				}
			}
			$entryFields['SECTION_ID'] = $sectionId;
			$arAffectedSections[] = $sectionId;

			$section = CCalendarSect::GetList(['arFilter' => ['ID' => $sectionId],
				'checkPermissions' => false,
				'getPermissions' => false
			]);
			$section = $section[0];

			// Here we take type and owner parameters from section data
			if ($section)
			{
				$entryFields['CAL_TYPE'] = $section['CAL_TYPE'];
				$entryFields['OWNER_ID'] = is_null($section['OWNER_ID']) ? '' : $section['OWNER_ID'];
			}

			if ($entryFields['CAL_TYPE'] == 'user')
			{
				$CACHE_MANAGER->ClearByTag('calendar_user_'.$entryFields['OWNER_ID']);
			}

			if ($isNewEvent)
			{
				if (!isset($entryFields['CREATED_BY']))
				{
					$entryFields['CREATED_BY'] = ($entryFields['IS_MEETING'] && $entryFields['CAL_TYPE'] == 'user' && $entryFields['OWNER_ID']) ? $entryFields['OWNER_ID'] : $userId;
				}

				if (!isset($entryFields['DATE_CREATE']))
				{
					$entryFields['DATE_CREATE'] = $entryFields['TIMESTAMP_X'];
				}
			}
			else
			{
				$arAffectedSections[] = $currentEvent['SECTION_ID'] ? $currentEvent['SECTION_ID'] : $currentEvent['SECT_ID'];
			}

			if (!isset($entryFields['IS_MEETING']) &&
				isset($entryFields['ATTENDEES']) && is_array($entryFields['ATTENDEES']) && empty($entryFields['ATTENDEES']))
			{
				$entryFields['IS_MEETING'] = false;
			}

			if ($entryFields['IS_MEETING'])
			{
				if (!$isNewEvent)
				{
					$entryChanges = self::CheckEntryChanges($entryFields, $currentEvent);
				}

				$attendeesCodes = $entryFields['ATTENDEES_CODES'];
				if (is_array($entryFields['ATTENDEES_CODES']))
				{
					$entryFields['ATTENDEES_CODES'] = implode(',', $entryFields['ATTENDEES_CODES']);
				}

				if (!isset($entryFields['MEETING_STATUS']) && $entryFields['MEETING_HOST'] == $entryFields['CREATED_BY'])
				{
					$entryFields['MEETING_STATUS'] = 'H';
				}
			}

			if (is_array($entryFields['MEETING']))
			{
				$entryFields['~MEETING'] = $entryFields['MEETING'];
				$entryFields['MEETING']['REINVITE'] = false;

				if ($entryFields['IS_MEETING'])
				{
					$meetingHostSettings = \Bitrix\Calendar\UserSettings::get($entryFields['MEETING_HOST']);
					$entryFields['MEETING']['MAIL_FROM'] = $meetingHostSettings['sendFromEmail'];
				}
				$entryFields['MEETING'] = serialize($entryFields['MEETING']);
			}

			if (is_array($entryFields['RELATIONS']))
			{
				$entryFields['~RELATIONS'] = $entryFields['RELATIONS'];
				$entryFields['RELATIONS'] = serialize($entryFields['RELATIONS']);
			}

			if (isset($entryFields['REMIND']) && ($isNewEvent
				|| !$entryFields['IS_MEETING']
				|| intval($entryFields['CREATED_BY']) === $userId))
			{
				$reminderList = CCalendarReminder::prepareReminder($entryFields['REMIND']);
			}
			elseif($currentEvent['REMIND'])
			{
				$reminderList = CCalendarReminder::prepareReminder($currentEvent['REMIND']);
			}
			else
			{
				$reminderList = [];
			}
			$entryFields['REMIND'] = serialize($reminderList);


			$AllFields = self::GetFields();
			$dbFields = [];

			foreach($entryFields as $field => $val)
			{
				if(isset($AllFields[$field]) && $field != "ID")
				{
					$dbFields[$field] = $entryFields[$field];
				}
			}

			CTimeZone::Disable();

			if ($isNewEvent) // Add
			{
				$eventId = $DB->Add("b_calendar_event", $dbFields, array('DESCRIPTION', 'MEETING', 'EXDATE'));
			}
			else // Update
			{
				$eventId = $entryFields['ID'];
				$strUpdate = $DB->PrepareUpdate("b_calendar_event", $dbFields);
				$strSql =
					"UPDATE b_calendar_event SET ".
						$strUpdate.
						" WHERE ID=".intval($eventId);

				$DB->QueryBind($strSql, array(
					'DESCRIPTION' => $entryFields['DESCRIPTION'],
					'MEETING' => $entryFields['MEETING'],
					'EXDATE' => $entryFields['EXDATE']
				));
			}

			CTimeZone::Enable();

			if ($isNewEvent && !isset($dbFields['DAV_XML_ID']))
			{
				$strSql =
					"UPDATE b_calendar_event SET ".
						$DB->PrepareUpdate("b_calendar_event", array('DAV_XML_ID' => $eventId)).
						" WHERE ID=".intval($eventId);
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

			// Deprecated. Now connection saved in the table
			if (!Util::isSectionStructureConverted() &&
				($isNewEvent || $sectionId !== $currentEvent['SECTION_ID']))
			{
				self::ConnectEventToSection($eventId, $sectionId);
			}

			if (count($arAffectedSections) > 0)
			{
				CCalendarSect::UpdateModificationLabel($arAffectedSections);
			}

			if ($entryFields['IS_MEETING']
				|| (!$isNewEvent && $currentEvent['IS_MEETING'])
			)
			{
				if (!$entryFields['PARENT_ID'])
				{
					$DB->Query("UPDATE b_calendar_event SET ".$DB->PrepareUpdate("b_calendar_event", array("PARENT_ID" => $eventId))." WHERE ID=".intval($eventId), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
				}

				$mailEvent = ICalUtil::isMailUser($entryFields['MEETING_HOST']);

				if ((!$entryFields['PARENT_ID'] || $entryFields['PARENT_ID'] === $eventId) && !$mailEvent)
				{
					self::CreateChildEvents($eventId, $entryFields, $params, $entryChanges);
				}

				if ((!$entryFields['PARENT_ID'] || $entryFields['PARENT_ID'] === $eventId) && $entryFields['RECURRENCE_ID'])
				{
					self::UpdateParentEventExDate($entryFields['RECURRENCE_ID'], $entryFields['DATE_FROM'], $entryFields['ATTENDEES']);
				}

				if (!$entryFields['PARENT_ID'])
				{
					$entryFields['PARENT_ID'] = intval($eventId);
				}
			}
			else
			{
				if (($isNewEvent && !$entryFields['PARENT_ID']) || (!$isNewEvent && !$currentEvent['PARENT_ID']))
				{
					$DB->Query("UPDATE b_calendar_event SET ".$DB->PrepareUpdate("b_calendar_event", array("PARENT_ID" => $eventId))." WHERE ID=".intval($eventId), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					if (!$entryFields['PARENT_ID'])
					{
						$entryFields['PARENT_ID'] = intval($eventId);
					}
				}
			}

			// Update reminders for event
			CCalendarReminder::updateReminders([
				'id' => $eventId,
				'reminders' => $reminderList,
				'arFields' => $entryFields,
				'userId' => $userId,
				'path' => $params['path']
			]);

			// Update search index
			self::updateSearchIndex($eventId);

			// Send invitations and notifications
			if ($entryFields['IS_MEETING'])
			{
				$fromTo = CCalendarEvent::GetEventFromToForUser($entryFields, $entryFields['OWNER_ID']);
				$nowUtc = time() - date('Z', time());

				// If it's event in the past we skipping notifications.
				// The past is the past...
				if ($entryFields['DATE_TO_TS_UTC'] > $nowUtc)
				{
					if ($sendEditNotification
						&& $entryFields['PARENT_ID'] != $eventId
						&& $entryFields['MEETING_STATUS'] === "Y"
						&& count($entryChanges) > 0
						&& $entryFields['PARENT_ID'] != $entryFields['ID'])
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$entryFields['OWNER_ID']);
						CCalendarNotify::Send([
							'mode' => 'change_notify',
							'name' => $entryFields['NAME'],
							"from" => $fromTo['DATE_FROM'],
							"to" => $fromTo['DATE_TO'],
							"location" => CCalendar::GetTextLocation($entryFields["LOCATION"]),
							"guestId" => $entryFields['OWNER_ID'],
							"eventId" => $entryFields['PARENT_ID'],
							"userId" => $userId,
							"fields" => $entryFields,
							"entryChanges" => $entryChanges
						]);
					}
					elseif ($sendInvitations
						&& $entryFields['PARENT_ID'] != $eventId
						&& $entryFields['MEETING_STATUS'] === 'Q')
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$entryFields['OWNER_ID']);
						CCalendarNotify::Send(array(
							"mode" => 'invite',
							"name" => $entryFields['NAME'],
							"from" => $fromTo['DATE_FROM'],
							"to" => $fromTo['DATE_TO'],
							"location" => CCalendar::GetTextLocation($entryFields["LOCATION"]),
							"guestId" => $entryFields['OWNER_ID'],
							"eventId" => $entryFields['PARENT_ID'],
							"userId" => $userId,
							"fields" => $entryFields
						));
					}
				}
			}

			if ($entryFields['IS_MEETING']
				&& !empty($entryFields['ATTENDEES_CODES'])
				&& (int)$entryFields['PARENT_ID'] === (int)$eventId)
			{
				CCalendarLiveFeed::OnEditCalendarEventEntry(array(
					'eventId' => $eventId,
					'arFields' => $entryFields,
					'attendeesCodes' => $attendeesCodes
				));
			}

			CCalendar::ClearCache('event_list');

			$result = $eventId;

			if ($isNewEvent)
			{
				foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarEntryAdd") as $event)
				{
					ExecuteModuleEventEx($event, array($eventId, $entryFields));
				}
			}
			else
			{
				foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarEntryUpdate") as $event)
				{
					ExecuteModuleEventEx($event, array($eventId, $entryFields));
				}
			}

			$pullUserId = (int)$entryFields['CREATED_BY'] > 0 ? (int)$entryFields['CREATED_BY'] : $userId;
			if ($pullUserId > 0)
			{
				Util::addPullEvent(
					'edit_event',
					$pullUserId,
					[
						'fields' => $entryFields,
						'newEvent' => $isNewEvent,
						'requestUid' => $params['requestUid']
					]
				);
			}
		}

		return $result;
	}

	public static function GetById($ID, $checkPermissions = true)
	{
		if ($ID > 0)
		{
			$Event = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"ID" => $ID,
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => $checkPermissions,
					'checkPermissions' => $checkPermissions,
					'setDefaultLimit' => false
				)
			);
			if ($Event && is_array($Event[0]))
				return $Event[0];
		}
		return false;
	}

	public static function GetList($params = array())
	{
		global $DB, $USER_FIELD_MANAGER;
		$getUF = $params['getUserfields'] !== false;
		$checkPermissions = $params['checkPermissions'] !== false;
		$bCache = CCalendar::CacheTime() > 0;
		$params['setDefaultLimit'] = $params['setDefaultLimit'] === true;
		$userId = isset($params['userId']) ? (int)$params['userId'] : CCalendar::GetCurUserId();
		$params['parseDescription'] = isset($params['parseDescription']) ? $params['parseDescription'] : true;
		$fetchSection = $params['fetchSection'];
		$resultEntryList = null;
		$userIndex = null;

		CTimeZone::Disable();
		if($bCache)
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
				$resultEntryList = $cachedData["resultEntryList"];
				$userIndex = $cachedData["userIndex"];
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

			$params['fetchAttendees'] = $params['fetchAttendees'] !== false;

			if ($params['setDefaultLimit'] !== false) // Deprecated
			{
				if (!isset($arFilter["FROM_LIMIT"])) // default 3 month back
					$arFilter["FROM_LIMIT"] = CCalendar::Date(time() - 31 * 3 * 24 * 3600, false);

				if (!isset($arFilter["TO_LIMIT"])) // default one year into the future
					$arFilter["TO_LIMIT"] = CCalendar::Date(time() + 365 * 24 * 3600, false);
			}

			// Array('ID' => 'asc')
			$arOrder = isset($params['arOrder']) ? $params['arOrder'] : Array();
			$arFields = self::GetFields();

			if ($arFilter["DELETED"] === false)
			{
				unset($arFilter["DELETED"]);
			}
			elseif (!isset($arFilter["DELETED"]))
			{
				$arFilter["DELETED"] = "N";
			}

			$join = '';

			$arSqlSearch = array();
			if(is_array($arFilter))
			{
				$filter_keys = array_keys($arFilter);
				for($i = 0, $l = count($filter_keys); $i<$l; $i++)
				{
					$n = mb_strtoupper($filter_keys[$i]);
					$val = $arFilter[$filter_keys[$i]];
					if(is_string($val) && $val == '' || strval($val) == "NOT_REF")
						continue;

					if($n == 'FROM_LIMIT')
					{
						$ts = CCalendar::Timestamp($val, false);
						if ($ts > 0)
							$arSqlSearch[] = "CE.DATE_TO_TS_UTC>=".$ts;
					}
					elseif($n == 'TO_LIMIT')
					{
						$ts = CCalendar::Timestamp($val, false);
						if ($ts > 0)
							$arSqlSearch[] = "CE.DATE_FROM_TS_UTC<=".($ts + 86399);
					}
					elseif($n == 'ID')
					{
						if(is_array($val))
						{
							$val = array_map('intval', $val);
							$arSqlSearch[] = 'CE.ID IN (\''.implode('\',\'', $val).'\')';
						}
						else if (intval($val) > 0)
						{
							$arSqlSearch[] = "CE.ID=".intval($val);
						}
					}
					elseif($n == '>ID' && intval($val) > 0)
					{
						$arSqlSearch[] = "CE.ID > ".intval($val);
					}
					elseif ($n == 'G_EVENT_ID')
					{
						$arSqlSearch[] = "CE.G_EVENT_ID = '". $DB->ForSql($val)."'";
					}
					elseif($n == 'OWNER_ID')
					{
						if(is_array($val))
						{
							$val = array_map('intval', $val);
							$arSqlSearch[] = 'CE.OWNER_ID IN (\''.implode('\',\'', $val).'\')';
						}
						else if (intval($val) > 0)
						{
							$arSqlSearch[] = "CE.OWNER_ID=".intval($val);
						}
					}
					elseif($n == 'MEETING_HOST')
					{
						if(is_array($val))
						{
							$val = array_map('intval', $val);
							$arSqlSearch[] = 'CE.MEETING_HOST IN (\''.implode('\',\'', $val).'\')';
						}
						else if (intval($val) > 0)
						{
							$arSqlSearch[] = "CE.MEETING_HOST=".intval($val);
						}
					}
					elseif($n == 'NAME')
					{
						$arSqlSearch[] = "CE.NAME='".$DB->ForSql($val)."'";
					}
					elseif($n == 'CAL_TYPE')
					{
						$arSqlSearch[] = "CE.CAL_TYPE='".$DB->ForSql($val)."'";
					}
					elseif($n == 'CREATED_BY')
					{
						if(is_array($val))
						{
							$val = array_map('intval', $val);
							$arSqlSearch[] = 'CE.CREATED_BY IN (\''.implode('\',\'', $val).'\')';
						}
						else if (intval($val) > 0)
						{
							$arSqlSearch[] = "CE.CREATED_BY=".intval($val);
						}
					}
					elseif($n == 'SECTION')
					{
						if (!is_array($val))
							$val = array($val);

						$q = "";
						if (is_array($val))
						{
							$sval = '';
							foreach($val as $sectid)
							{
								if(intval($sectid) > 0)
								{
									$sval .= intval($sectid).',';
								}
							}
							$sval = trim($sval, ' ,');

							if ($sval != '')
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
							$arSqlSearch[] = $q;
					}
					elseif($n == 'ACTIVE_SECTION' && $val == "Y")
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
					elseif($n == 'DAV_XML_ID' && is_array($val))
					{
						$val = array_map(array($DB, 'ForSQL'), $val);
						$arSqlSearch[] = 'CE.DAV_XML_ID IN (\''.implode('\',\'', $val).'\')';
					}
					elseif($n == 'DAV_XML_ID' && is_string($val))
					{
						$arSqlSearch[] = "CE.DAV_XML_ID='".$DB->ForSql($val)."'";
					}
					elseif($n == '*SEARCHABLE_CONTENT') // Full text index match
					{
						$sqlWhere = new CSQLWhere();
						$arSqlSearch[] = $sqlWhere->match('SEARCHABLE_CONTENT', $val, true);
					}
					elseif($n == '*%SEARCHABLE_CONTENT') // partial full text match based on LIKE
					{
						$sqlWhere = new CSQLWhere();
						$arSqlSearch[] = $sqlWhere->matchLike('SEARCHABLE_CONTENT', $val);
					}
					elseif(isset($arFields[$n]) && $arFields[$n]["FIELD_TYPE"] == 'date')
					{
						$arSqlSearch[] = $DB->DateToCharFunction("CE.".$n)."='".$DB->ForSql($val)."'";
					}
					elseif($n == 'RECURRENCE_ID' && intval($val))
					{
						$arSqlSearch[] = "CE.RECURRENCE_ID=".intval($val);
					}
					elseif($n == 'DELETED')
					{
						$arSqlSearch[] = "CE.DELETED='".$DB->ForSql($val)."'";
					}
					elseif(isset($arFields[$n]))
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
				if ((!is_array($params['arSelect']) || in_array($fieldKey, $params['arSelect']))
					&& $fieldKey !== 'SEARCHABLE_CONTENT')
				{
					$selectList .= $field['FIELD_NAME'].", ";
				}
			}

			if ($fetchSection && $arFilter['ACTIVE_SECTION'] == 'Y')
			{
				$selectList .= "CS.CAL_DAV_CAL as SECTION_DAV_XML_ID,";
			}

			$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
			$strOrderBy = '';
			foreach($arOrder as $by=>$order)
			{
				if(isset($arFields[mb_strtoupper($by)]))
				{
					$strOrderBy .= $arFields[mb_strtoupper($by)]["FIELD_NAME"].' '.(mb_strtolower($order) == 'desc'?'desc'.($DB->type == "ORACLE"?" NULLS LAST":""):'asc'.($DB->type == "ORACLE"?" NULLS FIRST":"")).',';
				}
			}

			if($strOrderBy <> '')
			{
				$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");
			}

			$strLimit = '';
			if (isset($params['limit']) && intval($params['limit']) > 0)
			{
				$strLimit = 'LIMIT '.intval($params['limit']);
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

			$defaultMeetingSection = false;
			while($event = $res->Fetch())
			{
				if (!$event['SECT_ID'] && $event['SECTION_ID'])
				{
					$event['SECT_ID'] = $event['SECTION_ID'];
				}

				$event['IS_MEETING'] = intval($event['IS_MEETING']) > 0;

				if ($event['IS_MEETING'] && $event['CAL_TYPE'] == 'user' && $event['OWNER_ID'] == $userId && !$event['SECT_ID'])
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
				if ($event['IS_MEETING'] && CCalendar::IsIntranetEnabled())
				{
					$arMeetingIds[] = $event['PARENT_ID'];
				}
			}

			if ($params['fetchAttendees'] && count($arMeetingIds) > 0)
			{
				$attendeeListData = self::getAttendeeList($arMeetingIds);
				$attendeeList = $attendeeListData['attendeeList'];
				$userIndex = self::getUsersDetails($attendeeListData['userIdList']);
			}

			foreach($arEvents as $event)
			{
				$event["ACCESSIBILITY"] = trim($event["ACCESSIBILITY"]);
				if (
					isset($event['MEETING'])
					&& $event['MEETING'] !== ""
					&& CCalendar::IsIntranetEnabled()
				)
				{
					$event['MEETING'] = unserialize($event['MEETING'], ['allowed_classes' => false]);
					if (!is_array($event['MEETING']))
					{
						$event['MEETING'] = [];
					}
				}

				if (isset($event['RELATIONS']) && $event['RELATIONS'] !== "")
				{
					$event['RELATIONS'] = unserialize($event['RELATIONS'], ['allowed_classes' => false]);
					if (!is_array($event['RELATIONS']))
					{
						$event['RELATIONS'] = [];
					}
				}

				if (isset($event['REMIND']) && $event['REMIND'] != "")
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
							'id' => $event['MEETING_HOST'],
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
						&& $event['USER_MEETING']
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

					if($checkPermissionsForEvent)
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

					if ($params['parseRecursion'] && self::CheckRecurcion($event))
					{
						self::ParseRecursion($resultEntryList, $event, [
							'userId' => $userId,
							'fromLimit' => $arFilter["FROM_LIMIT"],
							'toLimit' => $arFilter["TO_LIMIT"],
							'loadLimit' => $params["limit"],
							'instanceCount' => isset($params['maxInstanceCount']) ? $params['maxInstanceCount'] : false,
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
					"userIndex" => $userIndex
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
			"DELETE FROM b_calendar_event_sect WHERE EVENT_ID=".intval($eventId),
			false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$DB->Query(
			"INSERT INTO b_calendar_event_sect(EVENT_ID, SECT_ID) ".
			"SELECT ".intval($eventId).", ID ".
			"FROM b_calendar_section ".
			"WHERE ID=".intval($sectionId),
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

			if(count($entryIdList))
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
					if(!isset($attendeeList[$entry['PARENT_ID']]))
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
		$userList = UserTable::getList([
			'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO', 'EMAIL', 'EXTERNAL_AUTH_ID'],
			'filter' => ['=ID' => $userIdList]
		]);
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
			];
		}

		return $users;
	}

	public static function GetAttendees($eventIdList = array())
	{
		global $DB;
		$attendees = array();

		if (CCalendar::IsSocNet())
		{
			$eventIdList = is_array($eventIdList) ? array_map('intval', array_unique($eventIdList)) : array(intval($eventIdList));

			if(count($eventIdList))
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
					$parentId = $entry['PARENT_ID'];
					$attendeeId = $entry['USER_ID'];
					if(!isset($attendees[$parentId]))
					{
						$attendees[$parentId] = array();
					}
					$entry["STATUS"] = trim($entry["MEETING_STATUS"]);
					if ($parentId == $entry['ID'] || $entry['USER_ID'] == $entry['MEETING_HOST'])
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
		if (!$event['ACCESSIBILITY'])
			$event['ACCESSIBILITY'] = 'busy';

		$private = $event['PRIVATE_EVENT'] && $event['CAL_TYPE'] == 'user';
		$isAttendee = false;

		if (is_array($event['ATTENDEE_LIST']))
		{
			foreach($event['ATTENDEE_LIST'] as $attendee)
			{
				if ($attendee['id'] == $userId)
				{
					$isAttendee = true;
					break;
				}
			}
		}

		if(!$userId)
		{
			$userId = CCalendar::GetUserId();
		}

		$settings = CCalendar::GetSettings(array('request' => false));
		$isManager = (Loader::includeModule('intranet') && $event['CAL_TYPE'] == 'user' && $settings['dep_manager_sub']) && Bitrix\Calendar\Util::isManagerForUser($userId, $event['OWNER_ID']);

		if ($event['CAL_TYPE'] == 'user' && $event['IS_MEETING'] && $event['OWNER_ID'] != $userId)
		{
			if ($isAttendee)
			{
				$sectId = CCalendar::GetMeetingSection($userId);
			}
			elseif (isset($event['USER_MEETING']['ATTENDEE_ID']) && $event['USER_MEETING']['ATTENDEE_ID'] !== $userId)
			{
				$sectId = CCalendar::GetMeetingSection($event['USER_MEETING']['ATTENDEE_ID']);
				$event['SECT_ID'] = $sectId;
				$event['OWNER_ID'] = $event['USER_MEETING']['ATTENDEE_ID'];
			}
		}

		if ($private || (!CCalendarSect::CanDo('calendar_view_full', $sectId, $userId) && !$isManager && !$isAttendee))
		{
			if ($private)
			{
				$event['NAME'] = '['.GetMessage('EC_ACCESSIBILITY_'.mb_strtoupper($event['ACCESSIBILITY'])).']';
				if (!$isManager && !CCalendarSect::CanDo('calendar_view_time', $sectId, $userId))
					return false;
			}
			else
			{
				if (!CCalendarSect::CanDo('calendar_view_title', $sectId, $userId))
				{
					if (CCalendarSect::CanDo('calendar_view_time', $sectId, $userId))
						$event['NAME'] = '['.GetMessage('EC_ACCESSIBILITY_'.mb_strtoupper($event['ACCESSIBILITY'])).']';
					else
						return false;
				}
				else
				{
					$event['NAME'] = $event['NAME'].' ['.GetMessage('EC_ACCESSIBILITY_'.mb_strtoupper($event['ACCESSIBILITY'])).']';
				}
			}

			// Clear information about
			unset($event['DESCRIPTION'], $event['LOCATION'],
				$event['REMIND'],$event['USER_MEETING'],$event['ATTENDEE_LIST'],$event['ATTENDEES_CODES']);

			foreach($event as $k => $value)
			{
				if (mb_substr($k, 0, 3) == 'UF_')
					unset($event[$k]);
			}
		}

		return $event;
	}

	private static function PreHandleEvent($item, $params = array())
	{
		$item['LOCATION'] = trim($item['LOCATION']);

		if ($item['IS_MEETING'] && $item['MEETING'] != "" && !is_array($item['MEETING']))
		{
			$item['MEETING'] = unserialize($item['MEETING'], ['allowed_classes' => false]);
			if (!is_array($item['MEETING']))
				$item['MEETING'] = array();
		}

		if (self::CheckRecurcion($item))
		{
			$item['RRULE'] = CCalendarEvent::ParseRRULE($item['RRULE']);
			$item['~RRULE_DESCRIPTION'] = CCalendarEvent::GetRRULEDescription($item, false);
			$tsFrom = CCalendar::Timestamp($item['DATE_FROM']);
			$tsTo = CCalendar::Timestamp($item['DATE_TO']);
			if (($tsTo - $tsFrom) > $item['DT_LENGTH'] + CCalendar::DAY_LENGTH)
			{
				$toTS = $tsFrom + $item['DT_LENGTH'];
				if ($item['DT_SKIP_TIME'] == 'Y')
				{
					$toTS -= CCalendar::GetDayLen();
				}
				$item['DATE_TO'] = CCalendar::Date($toTS);
			}
		}

		if ($item['ATTENDEES_CODES'] != '' && is_string($item['ATTENDEES_CODES']))
		{
			$item['ATTENDEES_CODES'] = explode(',', $item['ATTENDEES_CODES']);
		}
		if (empty($item['ATTENDEES_CODES']))
		{
			$item['ATTENDEES_CODES'] = ['U'.$item['CREATED_BY']];
		}
		$item['attendeesEntityList'] = Util::convertCodesToEntities($item['ATTENDEES_CODES']);

		if ($item['IS_MEETING'])
		{
			if ($item['ID'] == $item['PARENT_ID'])
			{
				$item['MEETING_STATUS'] = 'H';
			}
		}

		$item['DT_SKIP_TIME'] = $item['DT_SKIP_TIME'] === 'Y' ? 'Y' : 'N';

		$item['ACCESSIBILITY'] = trim($item['ACCESSIBILITY']);
		$item['IMPORTANCE'] = trim($item['IMPORTANCE']);
		if ($item['IMPORTANCE'] == '')
			$item['IMPORTANCE'] = 'normal';
		$item['PRIVATE_EVENT'] = trim($item['PRIVATE_EVENT']);

		$item['DESCRIPTION'] = trim($item['DESCRIPTION']);
		if ($params['parseDescription'])
		{
			if($item['PARENT_ID'])
			{
				$item['~DESCRIPTION'] = self::ParseText($item['DESCRIPTION'], $item['PARENT_ID'], $item['UF_WEBDAV_CAL_EVENT']);
			}
			else
			{
				$item['~DESCRIPTION'] = self::ParseText($item['DESCRIPTION'], $item['ID'], $item['UF_WEBDAV_CAL_EVENT']);
			}
		}

		if (isset($item['UF_CRM_CAL_EVENT']) && is_array($item['UF_CRM_CAL_EVENT']) && count($item['UF_CRM_CAL_EVENT']) == 0)
			$item['UF_CRM_CAL_EVENT'] = '';

		unset($item['SEARCHABLE_CONTENT']);
		return $item;
	}

	public static function CheckRecurcion($event)
	{
		return !empty($event['RRULE']);
	}

	public static function ParseText($text = "", $eventId = 0, $arUFWDValue = array())
	{
		if ($text != "")
		{
			if (!is_object(self::$TextParser))
			{
				self::$TextParser = new CTextParser();
				self::$TextParser->allow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "Y", "VIDEO" => "Y", "TABLE" => "Y", "CUT_ANCHOR" => "N", "ALIGN" => "Y", "USER" => "Y");
			}

			self::$TextParser->allow["USERFIELDS"] = self::__GetUFForParseText($eventId, $arUFWDValue);
			$text = self::$TextParser->convertText($text);
		}
		return $text;
	}

	public static function __GetUFForParseText($eventId = 0, $arUFWDValue = array())
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
		$event['DT_LENGTH'] = intval($event['DT_LENGTH']);// length in seconds
		$length = $event['DT_LENGTH'];

		$rrule = self::ParseRRULE($event['RRULE']);
		$exDate = self::GetExDate($event['EXDATE']);
		$tsFrom = CCalendar::Timestamp($event['DATE_FROM']);
		$tsTo = CCalendar::Timestamp($event['DATE_TO']);

		if (($tsTo - $tsFrom) > $event['DT_LENGTH'] + CCalendar::DAY_LENGTH)
		{
			$toTS = $tsFrom + $event['DT_LENGTH'];
			if ($event['DT_SKIP_TIME'] == 'Y')
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
			$length = $h24;

		// Time boundaries
		if (isset($params['fromLimitTs']))
			$limitFromTS = intval($params['fromLimitTs']);
		else if ($params['fromLimit'])
			$limitFromTS = CCalendar::Timestamp($params['fromLimit']);
		else
			$limitFromTS = CCalendar::Timestamp(CCalendar::GetMinDate());

		if (isset($params['toLimitTs']))
			$limitToTS = intval($params['toLimitTs']);
		else if ($params['toLimit'])
			$limitToTS = CCalendar::Timestamp($params['toLimit']);
		else
			$limitToTS = CCalendar::Timestamp(CCalendar::GetMaxDate());

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
			$limitFromTS = $event['DATE_FROM_TS_UTC'];
		if ($limitToTS > $event['DATE_TO_TS_UTC'])
			$limitToTS = $event['DATE_TO_TS_UTC'];

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
				(!$fromTS || $fromTS < $evFromTS - CCalendar::GetDayLen()) // Emergensy exit (mantis: 56981)
				|| ($rrule['COUNT'] > 0 && $realCount >= $rrule['COUNT'])
				|| (!$rrule['COUNT'] && $fromTS >= $limitToTS)
				|| ($instanceCount && $dispCount >= $instanceCount)
				|| ($loadLimit && $dispCount >= $loadLimit)
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

			if ($rrule['FREQ'] == 'WEEKLY')
			{
				$weekDay = CCalendar::WeekDayByInd(date("w", $fromTS));

				if ($rrule['BYDAY'][$weekDay])
				{
					if (($preciseLimits && $toTS >= $limitFromTSReal) || (!$preciseLimits && $toTS > $limitFromTS - $h24))
					{
						if ($event['DT_SKIP_TIME'] == 'Y')
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

				if (isset($weekDay) && $weekDay == 'SU')
					$delta = ($rrule['INTERVAL'] - 1) * 7 + 1;
				else
					$delta = 1;

				$fromTS = mktime($hour, $min, $sec, $m, $d + $delta, $y);
			}
			else // HOURLY, DAILY, MONTHLY, YEARLY
			{
				if ($event['DT_SKIP_TIME'] == 'Y')
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
			$count++;
		}
	}

	public static function ParseRRULE($rule = '')
	{
		$res = array();
		if (!$rule || $rule === '')
			return $res;
		if (is_array($rule))
			return isset($rule['FREQ']) ? $rule : $res;

		$arRule = explode(";", $rule);
		if (!is_array($arRule))
			return $res;
		foreach($arRule as $par)
		{
			$arPar = explode("=", $par);
			if ($arPar[0])
			{
				switch($arPar[0])
				{
					case 'FREQ':
						if (in_array($arPar[1], array('HOURLY', 'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY')))
							$res['FREQ'] = $arPar[1];
						break;
					case 'COUNT':
					case 'INTERVAL':
						if (intval($arPar[1]) > 0)
							$res[$arPar[0]] = intval($arPar[1]);
						break;
					case 'UNTIL':
						$res['UNTIL'] = CCalendar::Timestamp($arPar[1]) ? $arPar[1] : CCalendar::Date(intval($arPar[1]), false, false);
						break;
					case 'BYDAY':
						$res[$arPar[0]] = array();
						foreach(explode(',', $arPar[1]) as $day)
						{
							$matches = array();
							if (preg_match('/((\-|\+)?\d+)?(MO|TU|WE|TH|FR|SA|SU)/', $day, $matches))
								$res[$arPar[0]][$matches[3]] = $matches[1] == '' ? $matches[3] : $matches[1];
						}
						if (count($res[$arPar[0]]) == 0)
							unset($res[$arPar[0]]);
						break;
					case 'BYMONTHDAY':
						$res[$arPar[0]] = array();
						foreach(explode(',', $arPar[1]) as $day)
							if (abs($day) > 0 && abs($day) <= 31)
								$res[$arPar[0]][intval($day)] = intval($day);
						if (count($res[$arPar[0]]) == 0)
							unset($res[$arPar[0]]);
						break;
					case 'BYYEARDAY':
					case 'BYSETPOS':
						$res[$arPar[0]] = array();
						foreach(explode(',', $arPar[1]) as $day)
							if (abs($day) > 0 && abs($day) <= 366)
								$res[$arPar[0]][intval($day)] = intval($day);
						if (count($res[$arPar[0]]) == 0)
							unset($res[$arPar[0]]);
						break;
					case 'BYWEEKNO':
						$res[$arPar[0]] = array();
						foreach(explode(',', $arPar[1]) as $day)
							if (abs($day) > 0 && abs($day) <= 53)
								$res[$arPar[0]][intval($day)] = intval($day);
						if (count($res[$arPar[0]]) == 0)
							unset($res[$arPar[0]]);
						break;
					case 'BYMONTH':
						$res[$arPar[0]] = array();
						foreach(explode(',', $arPar[1]) as $m)
							if ($m > 0 && $m <= 12)
								$res[$arPar[0]][intval($m)] = intval($m);
						if (count($res[$arPar[0]]) == 0)
							unset($res[$arPar[0]]);
						break;
				}
			}
		}

		if ($res['FREQ'] == 'WEEKLY' && (!isset($res['BYDAY']) || !is_array($res['BYDAY']) || count($res['BYDAY']) == 0))
			$res['BYDAY'] = array('MO' => 'MO');

		if ($res['FREQ'] != 'WEEKLY' && isset($res['BYDAY']))
			unset($res['BYDAY']);

		$res['INTERVAL'] = intval($res['INTERVAL']);
		if ($res['INTERVAL'] <= 1)
			$res['INTERVAL'] = 1;

		$res['~UNTIL'] = $res['UNTIL'];
		if ($res['UNTIL'] == CCalendar::GetMaxDate())
		{
			$res['~UNTIL'] = '';
		}
		return $res;
	}

	public static function GetExDate($exDate = '')
	{
		if (!is_array($exDate))
			$exDate = $exDate == '' ? array() : explode(';', $exDate);
		return $exDate;
	}

	private static function HandleEvent(&$res, $item = [], $userId = null)
	{
		$userId = $userId ? $userId : CCalendar::GetCurUserId();

		if ($item['DT_SKIP_TIME'] === 'N')
		{
			$currentUserTimezone = \CCalendar::GetUserTimezoneName($userId);

			$fromTs = \CCalendar::Timestamp($item['DATE_FROM']);
			$toTs = $fromTs + $item['DT_LENGTH'];

			$item['~USER_OFFSET_FROM'] = CCalendar::GetTimezoneOffset($item['TZ_FROM'], $fromTs)
				- \CCalendar::GetTimezoneOffset($currentUserTimezone, $fromTs);

			$item['~USER_OFFSET_TO'] = CCalendar::GetTimezoneOffset($item['TZ_TO'], $toTs)
				- \CCalendar::GetTimezoneOffset($currentUserTimezone, $toTs);
		}
		else
		{
			$item['~USER_OFFSET_FROM'] = 0;
			$item['~USER_OFFSET_TO'] = 0;
		}

		$res[] = $item;
	}

	public static function CheckFields(&$arFields, $currentEvent = array(), $userId = false)
	{
		$arFields['ID'] = (int)$arFields['ID'];
		$arFields['PARENT_ID'] = (int)$arFields['PARENT_ID'];
		$arFields['OWNER_ID'] = (int)$arFields['OWNER_ID'];

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
		elseif(isset($arFields['DT_SKIP_TIME']) && $arFields['DT_SKIP_TIME'] != 'Y' && $arFields['DT_SKIP_TIME'] != 'N')
		{
			unset($arFields['DT_SKIP_TIME']);
		}

		unset($arFields['DT_FROM']);
		unset($arFields['DT_TO']);

		$arFields['DT_SKIP_TIME'] = $arFields['DT_SKIP_TIME'] !== 'Y' ? 'N' : 'Y';
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

		if ($arFields['DT_SKIP_TIME'] !== 'Y')
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
		if ($arFields['DT_SKIP_TIME'] == 'Y')
		{
			unset($arFields['TZ_FROM']);
			unset($arFields['TZ_TO']);
			unset($arFields['TZ_OFFSET_FROM']);
			unset($arFields['TZ_OFFSET_TO']);
		}

		// Event length in seconds
		if (!isset($arFields['DT_LENGTH']) || $arFields['DT_LENGTH'] == 0)
		{
			if($fromTs == $toTs && date('H:i', $fromTs) == '00:00' && $arFields['DT_SKIP_TIME'] == 'Y') // One day
			{
				$arFields['DT_LENGTH'] = $h24;
			}
			else
			{
				$arFields['DT_LENGTH'] = intval($arFields['DATE_TO_TS_UTC'] - $arFields['DATE_FROM_TS_UTC']);
				if ($arFields['DT_SKIP_TIME'] == "Y") // We have dates without times
				{
					$arFields['DT_LENGTH'] += $h24;
				}
			}
		}

		if (!$arFields['VERSION'])
			$arFields['VERSION'] = 1;

		// Accessibility
		$arFields['ACCESSIBILITY'] = trim(mb_strtolower($arFields['ACCESSIBILITY']));
		if (!in_array($arFields['ACCESSIBILITY'], array('busy', 'quest', 'free', 'absent')))
			$arFields['ACCESSIBILITY'] = 'busy';

		// Importance
		$arFields['IMPORTANCE'] = trim(mb_strtolower($arFields['IMPORTANCE']));
		if (!in_array($arFields['IMPORTANCE'], array('high', 'normal', 'low')))
			$arFields['IMPORTANCE'] = 'normal';

		// Color
		$arFields['COLOR'] = CCalendar::Color($arFields['COLOR'], false);

		// Section
		if (!is_array($arFields['SECTIONS']) && intval($arFields['SECTIONS']) > 0)
			$arFields['SECTIONS'] = array(intval($arFields['SECTIONS']));

		// Check rrules
		if (is_array($arFields['RRULE']) && isset($arFields['RRULE']['FREQ']) && in_array($arFields['RRULE']['FREQ'], array('HOURLY','DAILY','MONTHLY','YEARLY','WEEKLY')))
		{
			// Interval
			if (isset($arFields['RRULE']['INTERVAL']) && intval($arFields['RRULE']['INTERVAL']) > 1)
				$arFields['RRULE']['INTERVAL'] = intval($arFields['RRULE']['INTERVAL']);

			// Until date
			$untilTs = CCalendar::Timestamp($arFields['RRULE']['UNTIL'], false, false);
			if (!$untilTs)
			{
				$arFields['RRULE']['UNTIL'] = CCalendar::GetMaxDate();
				$untilTs = CCalendar::Timestamp($arFields['RRULE']['UNTIL'], false, false);
			}
			$arFields['DATE_TO_TS_UTC'] = $untilTs + CCalendar::GetDayLen();
			$arFields['RRULE']['UNTIL'] = CCalendar::Date($untilTs, false);

			if (isset($arFields['RRULE']['COUNT']))
				$arFields['RRULE']['COUNT'] = intval($arFields['RRULE']['COUNT']);

			if (isset($arFields['RRULE']['BYDAY']))
			{
				if (is_array($arFields['RRULE']['BYDAY']))
				{
					$BYDAY = $arFields['RRULE']['BYDAY'];
				}
				else
				{
					$BYDAY = array();
					$days = array('SU','MO','TU','WE','TH','FR','SA');
					$bydays = explode(',', $arFields['RRULE']['BYDAY']);
					foreach($bydays as $day)
					{
						$day = mb_strtoupper($day);
						if (in_array($day, $days))
							$BYDAY[] = $day;
					}
				}
				$arFields['RRULE']['BYDAY'] = implode(',',$BYDAY);
			}
			unset($arFields['RRULE']['~UNTIL']);

			if (isset($arFields["EXDATE"]))
				$excludeDates = self::GetExDate($arFields["EXDATE"]);
			else
				$excludeDates = self::GetExDate($currentEvent['EXDATE']);

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

		// Location
		if (!is_array($arFields['LOCATION']))
			$arFields['LOCATION'] = Array("NEW" => is_string($arFields['LOCATION']) ? $arFields['LOCATION'] : "");

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
			if ($fieldKey == 'LOCATION')
			{
				if (is_array($newFields[$fieldKey]) && $newFields[$fieldKey]['NEW'] != $currentFields[$fieldKey])
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey]['NEW']
					];
				}
				else if (!is_array($newFields[$fieldKey]) && $newFields[$fieldKey] != $currentFields[$fieldKey]
					&& CCalendar::GetTextLocation($newFields["LOCATION"]) !== CCalendar::GetTextLocation($currentFields["LOCATION"]))
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey]
					];
				}
			}
			else if ($fieldKey == 'DATE_FROM')
			{
				if (($newFields[$fieldKey] !== $currentFields[$fieldKey] || $newFields['TZ_FROM'] !== $currentFields['TZ_FROM']))
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey]
					];
				}
			}
			else if ($fieldKey == 'DATE_TO')
			{
				if (
					($newFields['DATE_FROM'] === $currentFields['DATE_FROM'] && $newFields['TZ_FROM'] === $currentFields['TZ_FROM'])
					&&
					($newFields[$fieldKey] !== $currentFields[$fieldKey] || $newFields['TZ_TO'] !== $currentFields['TZ_TO'])
				)
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey]
					];
				}
			}
			else if ($fieldKey == 'IMPORTANCE')
			{
				if ($newFields[$fieldKey] != $currentFields[$fieldKey] && $newFields[$fieldKey] == 'high')
				{
					$changes[] = [
						'fieldKey' => $fieldKey,
						'oldValue' => $currentFields[$fieldKey],
						'newValue' => $newFields[$fieldKey]
					];
				}
			}
			else if ($fieldKey == 'DESCRIPTION')
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
			else if ($fieldKey == 'RRULE')
			{
				$newRule = self::ParseRRULE($newFields[$fieldKey]);
				$oldRule = self::ParseRRULE($currentFields[$fieldKey]);

				if ($newRule['FREQ'] !== $oldRule['FREQ']
					|| $newRule['INTERVAL'] !== $oldRule['INTERVAL']
					|| $newRule['BYDAY'] !== $oldRule['BYDAY']

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

	private static function PackRRule($RRule = array())
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
	private static function CreateChildEvents($parentId, $arFields, $params, $changeFields)
	{
		global $DB, $CACHE_MANAGER;
		$parentId = (int) $parentId;
		$isNewEvent = !isset($arFields['ID']) || $arFields['ID'] <= 0;
		$chatId = (int) $arFields['~MEETING']['CHAT_ID'];
		$involvedAttendees = []; // List of all attendees to invite or to exclude from event
		$isMailAvailable = Loader::includeModule("mail");
		$isCalDavEnabled = CCalendar::IsCalDAVEnabled();
		$userId = $params['userId'];
		$attendees = is_array($arFields['ATTENDEES']) ? $arFields['ATTENDEES'] : []; // List of attendees for event
		$eventManagersCollection = [];
		$attaches = [];
		$chat = null;
		$isIncreaseMailLimit = false;

		if($chatId > 0 && Loader::includeModule('im'))
		{
			$chat = new \CIMChat(0);
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
			$curAttendees = is_array($curAttendees[$parentId]) ? $curAttendees[$parentId] : [];
			foreach($curAttendees as $user)
			{
				$currentAttendeesIndex[$user['USER_ID']] = $user;
				if ($user['USER_ID'] !== $arFields['MEETING_HOST'] &&
					($user['USER_ID'] !== $arFields['OWNER_ID'] || $arFields['CAL_TYPE'] !== 'user'))
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
					'ACTIVE' => 'Y'
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
				if ($user['ID'] === $arFields['MEETING_HOST'])
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
			$attendeeId = (int)$userKey;
			$CACHE_MANAGER->ClearByTag('calendar_user_'.$attendeeId);

			// Skip creation of child event if it's event inside his own user calendar
			if ($attendeeId
				&& ($arFields['CAL_TYPE'] !== 'user' || (int)$arFields['OWNER_ID'] !== $attendeeId))
			{
				$childParams = $params;
				$childParams['arFields']['CAL_TYPE'] = 'user';
				$childParams['arFields']['PARENT_ID'] = $parentId;
				$childParams['arFields']['OWNER_ID'] = $attendeeId;
				$childParams['arFields']['CREATED_BY'] = $attendeeId;
				$childParams['arFields']['CREATED'] = $arFields['DATE_CREATE'];
				$childParams['arFields']['MODIFIED'] = $arFields['TIMESTAMP_X'];
				$childParams['arFields']['ACCESSIBILITY'] = $arFields['ACCESSIBILITY'];
				$childParams['arFields']['MEETING'] = $arFields['~MEETING'];
				$childParams['arFields']['TEXT_LOCATION'] = CCalendar::GetTextLocation($arFields["LOCATION"]);
				$childParams['arFields']['MEETING_STATUS'] = 'Q';
				$childParams['sendInvitations'] = $params['sendInvitations'];

				if ((int)$arFields['CREATED_BY'] === $attendeeId)
				{
					$childParams['arFields']['MEETING_STATUS'] = 'Y';
				}
				elseif ($isNewEvent && (int)$arFields['~MEETING']['MEETING_CREATOR'] === $attendeeId)
				{
					$childParams['arFields']['MEETING_STATUS'] = 'Y';
				}
				else
				{
					if ($params['saveAttendeesStatus']
						&& $params['currentEvent']
						&& is_array($params['currentEvent']['ATTENDEE_LIST']))
					{
						foreach($params['currentEvent']['ATTENDEE_LIST'] as $currentAttendee)
						{
							if ($currentAttendee['id'] == $attendeeId)
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
				}

				unset($childParams['arFields']['SECTIONS'],
					$childParams['currentEvent'],
					$childParams['arFields']['ID'],
					$childParams['arFields']['DAV_XML_ID'],
					$childParams['arFields']['G_EVENT_ID']
				);

				$isExchangeEnabled = CCalendar::IsExchangeEnabled($attendeeId);

				if ($userIndex[$attendeeId]
					&& $userIndex[$attendeeId]['EXTERNAL_AUTH_ID'] === 'email'
					&& $isNewEvent
					&& !$isIncreaseMailLimit
				)
				{
					if (Limitation::isEventWithEmailGuestAllowed())
					{
						Limitation::increaseEventWithEmailGuestAmount();
						$isIncreaseMailLimit = true;
					}
					else
					{
						// Just skip external emil users if they not allowed
						// We will show warning on the client's side
						continue;
					}
				}

				if ($currentAttendeesIndex[$attendeeId])
				{
					$childParams['arFields']['ID'] = $currentAttendeesIndex[$attendeeId]['EVENT_ID'];

					if (!$arFields['~MEETING']['REINVITE'])
					{
						$childParams['arFields']['MEETING_STATUS'] = $currentAttendeesIndex[$attendeeId]['STATUS'];

						$childParams['sendInvitations'] = $childParams['sendInvitations'] &&  $currentAttendeesIndex[$attendeeId]['STATUS'] !== 'Q';
					}

					if ($params['sendInvitesToDeclined']
						&& $childParams['arFields']['MEETING_STATUS'] === 'N')
					{
						$childParams['arFields']['MEETING_STATUS'] = 'Q';
						$childParams['sendInvitations'] = true;
					}


					if ($isExchangeEnabled || $isCalDavEnabled)
					{
						$childParams['currentEvent'] = CCalendarEvent::GetById($childParams['arFields']['ID'], false);
						CCalendarSync::DoSaveToDav([
							'bCalDav' => $isCalDavEnabled,
							'bExchange' => $isExchangeEnabled,
							'sectionId' => $childParams['currentEvent']['SECT_ID'],
							'modeSync' => true
						], $childParams['arFields'], $childParams['currentEvent']);
					}
				}
				else
				{
					$childSectId = CCalendar::GetMeetingSection($attendeeId, true);
					if ($childSectId)
					{
						$childParams['arFields']['SECTIONS'] = [$childSectId];
					}

					// CalDav & Exchange
					if ($isExchangeEnabled || $isCalDavEnabled)
					{
						CCalendarSync::DoSaveToDav([
							'bCalDav' => $isCalDavEnabled,
							'bExchange' => $isExchangeEnabled,
							'sectionId' => $childSectId,
							'modeSync' => true
						], $childParams['arFields']);
					}
				}

				$id = self::Edit($childParams);

				if ($userIndex[$attendeeId]
					&& $userIndex[$attendeeId]['EXTERNAL_AUTH_ID'] === 'email'
					&& ((!$params['fromWebservice']) || !empty($changeFields)))
				{
					if (empty($attaches))
					{
						$isChangeFiles = false;
						$attaches = Helper::getMailAttaches($params['UF'], $arFields['MEETING_HOST'], $parentId, $isChangeFiles);
						if ($isChangeFiles)
						{
							$changeFields[] = [
								'fieldKey' => 'FILES',
							];
						}
					}

					$sender = self::getSenderForIcal($userIndex, $childParams['arFields']['MEETING_HOST']);
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
						if ($currentAttendeesIndex[$attendeeId])
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

				if ($chatId > 0
					&& $chat
					&& $userIndex[$attendeeId]
					&& $userIndex[$attendeeId]['EXTERNAL_AUTH_ID'] !== 'email'
					&& $childParams['arFields']['MEETING_STATUS'] !== 'N')
				{
					$chat->AddUser($chatId, $attendeeId, $hideHistory = false, $skipMessage = false);
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
			$mailAttendeesIndex = [];
			foreach ($attendees as $attendee)
			{
				if (!in_array($attendee['USER_ID'], $deletedAttendees))
				{
					$mailAttendeesIndex[$attendee['USER_ID']] = [
						'ID' => $attendee['USER_ID'],
						'NAME' => $attendee['NAME'],
						'LAST_NAME' => $attendee['LAST_NAME'],
						'EMAIL' => $attendee['EMAIL'],
					];
				}
			}

			foreach($deletedAttendees as $attendeeId)
			{
				if($chatId > 0 && $chat)
				{
					$chat->DeleteUser($chatId, $attendeeId, false);
				}

				$att = $currentAttendeesIndex[$attendeeId];
				if ($params['sendInvitations'] !== false && $att['STATUS'] === 'Y')
				{
					$CACHE_MANAGER->ClearByTag('calendar_user_'.$att["USER_ID"]);
					$fromTo = CCalendarEvent::GetEventFromToForUser($arFields, $att["USER_ID"]);
					CCalendarNotify::Send(array(
						"mode" => 'cancel',
						"name" => $arFields['NAME'],
						"from" => $fromTo['DATE_FROM'],
						"to" => $fromTo['DATE_TO'],
						"location" => CCalendar::GetTextLocation($arFields["LOCATION"]),
						"guestId" => $att["USER_ID"],
						"eventId" => $parentId,
						"userId" => $arFields['MEETING_HOST'],
						"fields" => $arFields
					));
				}
				$delIdStr .= ','.(int)$att['EVENT_ID'];

				$isExchangeEnabled = CCalendar::IsExchangeEnabled($attendeeId);
				if ($isExchangeEnabled || $isCalDavEnabled)
				{
					$currentEvent = CCalendarEvent::GetList(
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

					if ($currentEvent)
					{
						CCalendarSync::DoDeleteToDav(array(
							'bCalDav' => $isCalDavEnabled,
							'bExchangeEnabled' => $isExchangeEnabled,
							'sectionId' => $currentEvent['SECT_ID']
						), $currentEvent);
					}
				}

				if ($att['EXTERNAL_AUTH_ID'] === 'email')
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
					if (!$sender['ID'] && isset($sender['USER_ID']))
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

		if ($delIdStr != '')
		{
			$strSql =
				"UPDATE b_calendar_event SET ".
				$DB->PrepareUpdate("b_calendar_event", array("DELETED" => "Y")).
				" WHERE PARENT_ID=".intval($parentId)." AND ID IN(".$delIdStr.")";
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
		$exDates[] = \CCalendar::Date(\CCalendar::Timestamp($exDate), false);
		$exDates = array_unique($exDates);
		$strExDates = implode(';', $exDates);

		$strSql =
			"UPDATE b_calendar_event SET ".
			$DB->PrepareUpdate("b_calendar_event", array('EXDATE' => $strExDates)).
			" WHERE PARENT_ID=".intval($recurrenceId);
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		foreach ($attendeeIds as $id)
		{
			$CACHE_MANAGER->ClearByTag('calendar_user_'.$id);
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
			$fromTs = $fromTs - (CCalendar::GetTimezoneOffset($params['TZ_FROM']) - CCalendar::GetCurrentOffsetUTC($userId));
			$toTs = $toTs - (CCalendar::GetTimezoneOffset($params['TZ_TO']) - CCalendar::GetCurrentOffsetUTC($userId));
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

	public static function OnPullPrepareArFields($arFields = array())
	{
		$arFields['~DESCRIPTION'] = self::ParseText($arFields['DESCRIPTION']);

		$arFields['~LOCATION'] = '';
		if ($arFields['LOCATION'] !== '')
		{
			$arFields['~LOCATION'] = $arFields['LOCATION'];
			$arFields['LOCATION'] = CCalendar::GetTextLocation($arFields["LOCATION"]);
		}

		if (isset($arFields['~MEETING']))
			$arFields['MEETING'] = $arFields['~MEETING'];

		if ($arFields['REMIND'] !== '' && !is_array($arFields['REMIND']))
		{
			$arFields['REMIND'] = unserialize($arFields['REMIND'], ['allowed_classes' => false]);
		}
		if (!is_array($arFields['REMIND']))
		{
			$arFields['REMIND'] = [];
		}

		$arFields['RRULE'] = self::ParseRRULE($arFields['RRULE']);

		return $arFields;
	}

	public static function UpdateUserFields($eventId, $arFields = array())
	{
		$eventId = intval($eventId);
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
		$childEvents = array();
		$selectList = "";
		foreach($arFields as $field)
			$selectList .= $field['FIELD_NAME'].", ";
		$selectList = trim($selectList, ' ,').' ';

		if ($parentId > 0)
		{

			$strSql = "
				SELECT ".
				$selectList.
				"FROM b_calendar_event CE WHERE CE.PARENT_ID=".intval($parentId);

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
		$sendNotification = $params['sendNotification'] !== false;

		if ($id)
		{
			$userId = (isset($params['userId']) && (int)$params['userId'] > 0)
				? (int)$params['userId']
				: CCalendar::GetCurUserId();

			$arAffectedSections = array();
			$entry = $params['Event'];

			if (!isset($entry) || !is_array($entry))
			{
				CCalendar::SetOffset(false, 0);
				$res = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"ID" => $id
						),
						'parseRecursion' => false
					)
				);
				$entry = $res[0];
			}

			if ($entry)
			{
				if ($entry['IS_MEETING'] && $entry['PARENT_ID'] !== $entry['ID'])
				{
					if ($entry['MEETING_STATUS'] === 'Y' || $entry['MEETING_STATUS'] === 'Q')
					{
						self::SetMeetingStatus(array(
							'userId' => $userId,
							'eventId' => $entry['ID'],
							'status' => 'N'
						));
					}
				}
				else
				{
					foreach(GetModuleEvents("calendar", "OnBeforeCalendarEventDelete", true) as $arEvent)
						ExecuteModuleEventEx($arEvent, array($id, $entry));

					if ($entry['PARENT_ID'])
						CCalendarLiveFeed::OnDeleteCalendarEventEntry($entry['PARENT_ID'], $entry);
					else
						CCalendarLiveFeed::OnDeleteCalendarEventEntry($entry['ID'], $entry);

					$arAffectedSections[] = $entry['SECT_ID'];
					// Check location: if reserve meeting was reserved - clean reservation
					if ($entry['LOCATION'] != "")
					{
						$loc = CCalendar::ParseLocation($entry['LOCATION']);
						if ($loc['mrevid'] || $loc['room_event_id'])
						{
							CCalendar::ReleaseLocation($loc);
						}
					}

					if ($entry['CAL_TYPE'] === 'user')
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$entry['OWNER_ID']);

					if ($entry['IS_MEETING'])
					{
						CCalendarNotify::ClearNotifications($entry['PARENT_ID']);

						if (Loader::includeModule("im"))
						{
							CIMNotify::DeleteBySubTag("CALENDAR|INVITE|".$entry['PARENT_ID']);
							CIMNotify::DeleteBySubTag("CALENDAR|STATUS|".$entry['PARENT_ID']);
						}

						$involvedAttendees = array();

						$CACHE_MANAGER->ClearByTag('calendar_user_'.$userId);
						$childEvents = CCalendarEvent::GetList([
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
							if ($chEvent["MEETING_STATUS"] !== "N" && $sendNotification)
							{
								if ($chEvent['DATE_TO_TS_UTC'] + date("Z", $chEvent['DATE_TO_TS_UTC']) > (time() - 60 * 5))
								{
									$fromTo = CCalendarEvent::GetEventFromToForUser($entry, $chEvent["OWNER_ID"]);
									CCalendarNotify::Send(array(
										'mode' => 'cancel',
										'name' => $chEvent['NAME'],
										"from" => $fromTo["DATE_FROM"],
										"to" => $fromTo["DATE_TO"],
										"location" => CCalendar::GetTextLocation($chEvent["LOCATION"]),
										"guestId" => $chEvent["OWNER_ID"],
										"eventId" => $id,
										"userId" => $userId
									));
								}
							}
							$chEventIds[] = $chEvent["ID"];

							if ($chEvent["MEETING_STATUS"] === "Q")
								$involvedAttendees[] = $chEvent["OWNER_ID"];

							$bExchange = CCalendar::IsExchangeEnabled($chEvent["OWNER_ID"]);
							if ($bExchange || $bCalDav)
							{
								CCalendarSync::DoDeleteToDav(array(
										'bCalDav' => $bCalDav,
										'bExchangeEnabled' => $bExchange,
										'sectionId' => $chEvent['SECT_ID']
								), $chEvent);
							}

							$parentEvent = $chEvent['ID'] === $chEvent['PARENT_ID'];
							if (ICalUtil::isMailUser($chEvent['OWNER_ID']) && !$parentEvent)
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
						if ($params['bMarkDeleted'])
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
								if (count($chEventIds) > 0)
								{
									$DB->Query("DELETE FROM b_calendar_event_sect WHERE EVENT_ID in (".$strChEvent.")", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
								}
							}
						}

						if (count($involvedAttendees) > 0)
						{
							CCalendar::UpdateCounter($involvedAttendees);
						}
					}

					if ($params['bMarkDeleted'])
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

					if (count($arAffectedSections) > 0)
					{
						CCalendarSect::UpdateModificationLabel($arAffectedSections);
					}

					foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarEventDelete") as $event)
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
								'requestUid' => $params['requestUid']
							]
						);
					}
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
			$recurrenceId = $event['RECURRENCE_ID'] ? $event['RECURRENCE_ID'] : $event['ID'];

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
					'sendEditNotification' => false
				]);

				if ($res && $res['recEventId'])
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
				$recRelatedEvents = CCalendarEvent::GetEventsByRecId($recurrenceId, false);

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
					if ($ev['ID'] == $params['eventId'])
						continue;

					if($reccurentMode == 'all' ||
						($untilTimestamp && CCalendar::Timestamp($ev['DATE_FROM']) > $untilTimestamp))
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
				'userId' => $params['attendeeId'],
				'eventId' => $params['eventId'],
				'status' => $params['status']
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
		if (!in_array($status, ["Q", "Y", "N", "H", "M"], true))
		{
			$status = $params['status'] = "Q";
		}

		$event = CCalendarEvent::GetList(
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

		if ($event && count($event) > 0)
		{
			$event = $event[0];
		}

		if ($event && $event['IS_MEETING'] && (int)$event['PARENT_ID'] > 0)
		{
			if (ICalUtil::isMailUser($event['MEETING_HOST']))
			{
				IncomingEventManager::rehandleRequest([
					'event' => $event,
					'userId' => $userId,
					'answer' => $status === 'Y',
				]);
			}

			$strSql = "UPDATE b_calendar_event SET ".
				$DB->PrepareUpdate("b_calendar_event", array("MEETING_STATUS" => $status)).
				" WHERE PARENT_ID=".(int)$event['PARENT_ID']." AND OWNER_ID=".$userId;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			CCalendarSect::UpdateModificationLabel($event['SECT_ID']);

			// Clear invitation in messager
			CCalendarNotify::ClearNotifications($event['PARENT_ID'], $userId);

			// Add new notification in messenger
			if ($params['personalNotification'] && (int)CCalendar::getCurUserId() === $userId)
			{
				$fromTo = CCalendarEvent::GetEventFromToForUser($event, $userId);
				CCalendarNotify::Send(array(
					'mode' => $status === "Y" ? 'status_accept' : 'status_decline',
					'name' => $event['NAME'],
					"from" => $fromTo["DATE_FROM"],
					"guestId" => $userId,
					"eventId" => $event['PARENT_ID'],
					"userId" => $userId,
					"markRead" => true,
					"fields" => $event
				));
			}

			$addedPullUserList = [];
			if (is_array($event['ATTENDEE_LIST']))
			{
				foreach ($event['ATTENDEE_LIST'] as $attendee)
				{
					Util::addPullEvent(
						'set_meeting_status',
						$attendee['id'],
						[
							'fields' => $event,
							'requestUid' => $params['requestUid']
						]
					);
					$addedPullUserList[] = $attendee['id'];
				}
			}
			
			$pullUserId = (int)$event['CREATED_BY'] > 0 ? (int)$event['CREATED_BY'] : $userId;
			if ($pullUserId && !in_array($pullUserId, $addedPullUserList))
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

			// If it's open meeting and our attendee is not on the list
			if ($event['MEETING'] && $event['MEETING']['OPEN'] && ($status === 'Y' || $status === 'M'))
			{
				$arAttendees = self::GetAttendees([$event['PARENT_ID']]);
				$arAttendees = $arAttendees[$event['PARENT_ID']];
				$attendeeExist = false;
				foreach($arAttendees as $attendee)
				{
					if ($attendee['USER_ID'] == $userId)
					{
						$attendeeExist = true;
						break;
					}
				}

				if (!$attendeeExist && is_array($event))
				{
					// 1. Create another childEvent for new attendee
					$AllFields = self::GetFields();
					$dbFields = array();
					foreach($event as $field => $val)
					{
						if(isset($AllFields[$field]) && $field != "ID" && $field != "ATTENDEES_CODES")
						{
							$dbFields[$field] = $event[$field];
						}
					}
					$dbFields['MEETING_STATUS'] = $status;
					$dbFields['CAL_TYPE'] = 'user';
					$dbFields['OWNER_ID'] = $userId;
					$dbFields['PARENT_ID'] = $event['PARENT_ID'];
					$dbFields['MEETING'] = serialize($event['MEETING']);
					$dbFields['REMIND'] = serialize($event['REMIND']);

					$sectionId = CCalendarSect::GetLastUsedSection('user', $userId, $userId);
					if (!$sectionId || !CCalendarSect::GetById($sectionId, false))
					{
						$sectRes = CCalendarSect::GetSectionForOwner('user', $userId);
						$sectionId = $sectRes['sectionId'];
					}

					$dbFields['SECTION_ID'] = $sectionId;
					$eventId = $DB->Add("b_calendar_event", $dbFields, array('DESCRIPTION', 'MEETING', 'EXDATE'));
					$DB->Query("UPDATE b_calendar_event SET ".
						$DB->PrepareUpdate("b_calendar_event", array('DAV_XML_ID' => $eventId)).
						" WHERE ID=".intval($eventId), false, "File: ".__FILE__."<br>Line: ".__LINE__);

					if (!Util::isSectionStructureConverted() && $eventId && $sectionId)
					{
						self::ConnectEventToSection($eventId, $sectionId);
					}

					// 2. Update ATTENDEES_CODES
					$attendeesCodes = $event['ATTENDEES_CODES'];
					$attendeesCodes[] = 'U'.intval($userId);

					$attendeesCodes = array_unique($attendeesCodes);
					$DB->Query("UPDATE b_calendar_event SET ".
						"ATTENDEES_CODES='".implode(',', $attendeesCodes)."'".
						" WHERE PARENT_ID=".intval($event['PARENT_ID']), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

					CCalendarSect::UpdateModificationLabel(array($sectionId));
				}
			}

			// Notify author of event
			if ($event['MEETING']['NOTIFY'] && $userId != $event['MEETING_HOST'] &&
				$params['hostNotification'] !== false)
			{
				// Send message to the author
				$fromTo = CCalendarEvent::GetEventFromToForUser($event, $event['MEETING_HOST']);
				CCalendarNotify::Send(array(
					'mode' => $status == "Y" ? 'accept' : 'decline',
					'name' => $event['NAME'],
					"from" => $fromTo["DATE_FROM"],
					"to" => $fromTo["DATE_TO"],
					"location" => CCalendar::GetTextLocation($event["LOCATION"]),
					"guestId" => $userId,
					"eventId" => $event['PARENT_ID'],
					"userId" => isset($event['MEETING']['MEETING_CREATOR']) ? $event['MEETING']['MEETING_CREATOR'] : $event['MEETING_HOST'],
					"fields" => $event
				));
			}
			CCalendarSect::UpdateModificationLabel(array($event['SECTIONS'][0]));

			if ($status === "N")
			{
				$childEvent = CCalendarEvent::GetList(
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
				}
			}

			if ($status === "Y" && $params['affectRecRelatedEvents'] !== false)
			{
				$event = CCalendarEvent::GetList(
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

				if ($event && count($event) > 0)
				{
					$event = $event[0];
				}

				$recurrenceId = $event['RECURRENCE_ID'] ? $event['RECURRENCE_ID'] : $event['ID'];

				if ($recurrenceId)
				{
					$recRelatedEvents = CCalendarEvent::GetEventsByRecId($recurrenceId, false);
					foreach($recRelatedEvents as $ev)
					{
						if ($ev['ID'] == $params['eventId'])
							continue;

						self::SetMeetingStatus(array(
							'userId' => $userId,
							'eventId' => $ev['ID'],
							'status' => $status,
							'personalNotification' => false,
							'hostNotification' => false,
							'affectRecRelatedEvents' => false
						));
					}
				}
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
		CCalendar::ClearCache(array('attendees_list', 'event_list'));
	}

	/*
	 * $params['dateFrom']
	 * $params['dateTo']
	 *
	 * */

	public static function GetMeetingStatus($userId, $eventId)
	{
		global $DB;
		$eventId = intval($eventId);
		$userId = intval($userId);
		$status = false;
		$event = CCalendarEvent::GetById($eventId, false);
		if ($event && $event['IS_MEETING'] && intval($event['PARENT_ID']) > 0)
		{
			if ($event['CREATED_BY'] == $userId)
			{
				$status = $event['MEETING_STATUS'];
			}
			else
			{
				$res = $DB->Query("SELECT MEETING_STATUS from b_calendar_event WHERE PARENT_ID=".intval($event['PARENT_ID'])." AND CREATED_BY=".$userId, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$event = $res->Fetch();
				$status = $event['MEETING_STATUS'];
			}
		}
		return $status;
	}

	public static function GetAccessibilityForUsers($params = array())
	{
		$curEventId = intval($params['curEventId']);
		$curUserId = isset($params['userId']) ? intval($params['userId']) : CCalendar::GetCurUserId();
		if (!is_array($params['users']) || count($params['users']) == 0)
			return array();

		if (!isset($params['checkPermissions']))
			$params['checkPermissions'] = true;

		$users = array();
		$accessibility = array();
		foreach($params['users'] as $userId)
		{
			$userId = intval($userId);
			if ($userId)
			{
				$users[] = $userId;
				$accessibility[$userId] = array();
			}
		}

		if (count($users) == 0)
			return array();

		$events = CCalendarEvent::GetList(
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
			if ($curEventId && ($event["ID"] == $curEventId || $event["PARENT_ID"] == $curEventId))
				continue;
			if ($event["ACCESSIBILITY"] == 'free')
				continue;
			if ($event["IS_MEETING"] && $event["MEETING_STATUS"] == "N")
				continue;
			if (CCalendarSect::CheckGoogleVirtualSection($event['SECTION_DAV_XML_ID']))
				continue;

			$name = $event["NAME"];
			if (($event['PRIVATE_EVENT'] && $event['CAL_TYPE'] == 'user' && $event['OWNER_ID'] !== $curUserId)
				|| !CCalendarSect::CanDo('calendar_view_title', $event['SECTION_ID'], $curUserId)
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

	public static function GetAbsent($users = false, $params = array())
	{
		// Can be called from agent... So we have to create $USER if it is not exists
		$tempUser = CCalendar::TempUser(false, true);
		$checkPermissions = $params['checkPermissions'] !== false;
		$curUserId = isset($params['userId']) ? intval($params['userId']) : CCalendar::GetCurUserId();
		$arUsers = array();

		if ($users !== false && is_array($users))
		{
			foreach($users as $id)
			{
				if($id > 0)
				{
					$arUsers[] = intval($id);
				}
			}
			if (!count($arUsers))
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

		$arEvents = CCalendarEvent::GetList(
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
				continue;

			//if ($bSocNet && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $userId, "calendar"))
			//	continue;

			if ($event['IS_MEETING'] && $event["MEETING_STATUS"] == 'N')
				continue;

			if ($checkPermissions
				&& ($event['CAL_TYPE'] != 'user' || $curUserId != $event['OWNER_ID'])
				&& $curUserId != $event['CREATED_BY'])
			{
				$sectId = $event['SECT_ID'];
				if (!$event['ACCESSIBILITY'])
					$event['ACCESSIBILITY'] = 'busy';

				if ($settings === false)
				{
					$settings = CCalendar::GetSettings(array('request' => false));
				}
				$private = $event['PRIVATE_EVENT'] && $event['CAL_TYPE'] == 'user';
				$isManager = (!$private && CCalendar::IsIntranetEnabled() && Loader::includeModule('intranet') && $event['CAL_TYPE'] == 'user' && $settings['dep_manager_sub']) && Bitrix\Calendar\Util::isManagerForUser($curUserId, $event['OWNER_ID']);

				if ($private || (!$isManager && !CCalendarSect::CanDo('calendar_view_full', $sectId)))
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
			$loc = $arRes['LOCATION'];
			if ($loc && mb_strlen($loc) > 5 && mb_substr($loc, 0, 5) == 'ECMR_')
			{
				$loc = CCalendar::ParseLocation($loc);
				if ($loc['mrid'] !== false && $loc['mrevid'] !== false) // Release MR
				{
					CCalendar::ReleaseLocation($loc);
				}
			}
			$itemIds[] = intval($arRes['ID']);
		}

		// Clean from 'b_calendar_event'
		if (count($itemIds) > 0)
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
		while($res = $res->Fetch())
		{
			$strItems .= ",".intval($res['ID']);
		}

		if ($strItems != "0")
		{
			$strSql =
				"UPDATE b_calendar_event SET ".
				$DB->PrepareUpdate("b_calendar_event", array("DELETED" => "Y")).
				" WHERE PARENT_ID in (".$strItems.")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		CCalendar::ClearCache(array('section_list', 'event_list'));
	}

	public static function CheckEndUpdateAttendeesCodes($event)
	{
		if ($event['ID'] > 0 && $event['IS_MEETING']
			&& empty($event['ATTENDEES_CODES']) && is_array($event['ATTENDEE_LIST']))
		{
			$event['ATTENDEES_CODES'] = array();
			foreach($event['ATTENDEE_LIST'] as $attendee)
			{
				if (intval($attendee['id']) > 0)
				{
					$event['ATTENDEES_CODES'][] = 'U'.intval($attendee['id']);
				}
			}
			$event['ATTENDEES_CODES'] = array_unique($event['ATTENDEES_CODES']);

			global $DB;
			$strSql =
				"UPDATE b_calendar_event SET ".
				"ATTENDEES_CODES='".implode(',', $event['ATTENDEES_CODES'])."'".
				" WHERE PARENT_ID=".intval($event['ID']);
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			CCalendar::ClearCache(array('event_list'));
		}
		return $event['ATTENDEES_CODES'];
	}

	public static function CanView($eventId, $userId)
	{
		if (intval($eventId) > 0)
		{
			Loader::includeModule("calendar");
			$event = CCalendarEvent::GetList(
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
				$event = CCalendarEvent::GetList(
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

			if ($event && is_array($event[0]))
			{
				// Event is not partly accessible - so it was not cleaned before by ApplyAccessRestrictions
				if (isset($event[0]['DESCRIPTION']) || isset($event[0]['IS_MEETING']) || isset($event[0]['LOCATION']))
					return true;
			}
		}

		return false;
	}

	public static function GetEventUserFields($event)
	{
		global $USER_FIELD_MANAGER;
		if ($event['PARENT_ID'])
		{
			$UF = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $event['PARENT_ID'], LANGUAGE_ID);
		}
		else
		{
			$UF = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $event['ID'], LANGUAGE_ID);
		}
		return $UF;
	}

	public static function SetExDate($exDate = array(), $untilTimestamp = false)
	{
		if ($untilTimestamp && !empty($exDate) && is_array($exDate))
		{
			$exDateRes = array();

			foreach($exDate as $date)
			{
				if (CCalendar::Timestamp($date) <= $untilTimestamp)
					$exDateRes[] = $date;
			}

			$exDate = $exDateRes;
		}

		$exDate = array_unique($exDate);

		return implode(';', $exDate);
	}

	public static function GetEventsByRecId($recurrenceId, $checkPermissions = true)
	{
		if ($recurrenceId > 0)
		{
			$events = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"RECURRENCE_ID" => $recurrenceId,
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => false,
					'checkPermissions' => $checkPermissions,
					'setDefaultLimit' => false
				)
			);
			return $events;
		}
		return array();
	}

	public static function GetEventCommentXmlId($event)
	{
		if (is_array($event['RELATIONS']) && array_key_exists('COMMENT_XML_ID', $event['RELATIONS']) && $event['RELATIONS']['COMMENT_XML_ID'])
		{
			$commentXmlId = $event['RELATIONS']['COMMENT_XML_ID'];
		}
		else
		{
			$eventCommentId = $event['PARENT_ID'] ? $event['PARENT_ID'] : $event['ID'];
			$commentXmlId = "EVENT_".$eventCommentId;

			if ($event['RECURRENCE_ID'])
			{
				$commentXmlId = "EVENT_".$event['RECURRENCE_ID'];
				$commentXmlId .= '_'.CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false);
			}
			elseif (CCalendarEvent::CheckRecurcion($event))
			{
				if (CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false)
					!== CCalendar::Date(CCalendar::Timestamp($event['~DATE_FROM']), false)
					&& (!isset($event['RINDEX']) || $event['RINDEX'] > 0))
				{
					$commentXmlId .= '_'.CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false);
				}
			}
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
		if($event['RRULE'])
		{
			$event['RRULE'] = CCalendarEvent::ParseRRULE($event['RRULE']);

			switch($event['RRULE']['FREQ'])
			{
				case 'DAILY':
					if($event['RRULE']['INTERVAL'] == 1)
						$res = GetMessage('EC_RRULE_EVERY_DAY');
					else
						$res = GetMessage('EC_RRULE_EVERY_DAY_1', array('#DAY#' => $event['RRULE']['INTERVAL']));
					break;
				case 'WEEKLY':
					$daysList = array();
					foreach($event['RRULE']['BYDAY'] as $day)
						$daysList[] = GetMessage('EC_'.$day);
					$daysList = implode(', ', $daysList);
					if($event['RRULE']['INTERVAL'] == 1)
						$res = GetMessage('EC_RRULE_EVERY_WEEK', array('#DAYS_LIST#' => $daysList));
					else
						$res = GetMessage('EC_RRULE_EVERY_WEEK_1', array('#WEEK#' => $event['RRULE']['INTERVAL'], '#DAYS_LIST#' => $daysList));
					break;
				case 'MONTHLY':
					if($event['RRULE']['INTERVAL'] == 1)
						$res = GetMessage('EC_RRULE_EVERY_MONTH');
					else
						$res = GetMessage('EC_RRULE_EVERY_MONTH_1', array('#MONTH#' => $event['RRULE']['INTERVAL']));
					break;
				case 'YEARLY':
					$fromTs = CCalendar::Timestamp($event['DATE_FROM']);
					if ($event['DT_SKIP_TIME'] !== "Y")
					{
						$fromTs -= $event['~USER_OFFSET_FROM'];
					}

					if($event['RRULE']['INTERVAL'] == 1)
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
				$res .= '<br>';
			else
				$res .= ', ';

			if (isset($event['~DATE_FROM']))
			{
				$res .= GetMessage('EC_RRULE_FROM', array('#FROM_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['~DATE_FROM']), false)));
			}
			else
			{
				$res .= GetMessage('EC_RRULE_FROM', array('#FROM_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false)));
			}

			if($showUntil && $event['RRULE']['UNTIL'] != CCalendar::GetMaxDate())
			{
				$res .= ' '.GetMessage('EC_RRULE_UNTIL', array('#UNTIL_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['RRULE']['UNTIL']), false)));
			}
			elseif($showUntil && $event['RRULE']['COUNT'] > 0)
			{
				$res .= ', '.GetMessage('EC_RRULE_COUNT', array('#COUNT#' => $event['RRULE']['COUNT']));
			}
		}

		return $res;
	}

	public static function ExcludeInstance($eventId, $excludeDate)
	{
		global $CACHE_MANAGER;
		$eventId = intval($eventId);
		$excludeDateTs = CCalendar::Timestamp($excludeDate);
		$excludeDate = CCalendar::Date($excludeDateTs, false);

		$event = CCalendarEvent::GetList(
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

		if ($event && CCalendarEvent::CheckRecurcion($event) && $excludeDate)
		{
			$excludeDates = CCalendarEvent::GetExDate($event['EXDATE']);
			$excludeDates[] = $excludeDate;

			$id = CCalendar::SaveEvent(array(
				'arFields' => array(
					'ID' => $event['ID'],
					'DATE_FROM' => $event['DATE_FROM'],
					'DATE_TO' => $event['DATE_TO'],
					'EXDATE' => CCalendarEvent::SetExDate($excludeDates)
				),
				'silentErrorMode' => false,
				'recursionEditMode' => 'skip',
				'editParentEvents' => true,
			));

			if (is_array($event['ATTENDEE_LIST']))
			{
				foreach($event['ATTENDEE_LIST'] as $attendee)
				{
					if ($attendee['status'] == 'Y')
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

	public static function getDiskUFFileNameList($valueList = array())
	{
		$result = array();

		if (
			!empty($valueList)
			&& is_array($valueList)
			&& Loader::includeModule('disk')
		)
		{
			$attachedIdList = array();
			foreach($valueList as $value)
			{
				[$type, $realValue] = FileUserType::detectType($value);
				if($type == FileUserType::TYPE_NEW_OBJECT)
				{
					$file = \Bitrix\Disk\File::loadById($realValue, array('STORAGE'));
					$result[] = strip_tags($file->getName());
				}
				else
				{
					$attachedIdList[] = $realValue;
				}
			}

			if(!empty($attachedIdList))
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
		if (intval($eventId) > 0)
		{
			$events = \CCalendarEvent::getList(
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
			$res = is_array($events[0]) && is_array($events[0]) ? self::formatSearchIndexContent($events[0]) : '';
		}
		return $res;
	}

	public static function getSearchIndexContentBatch($eventIdList = array())
	{
		$res = array();
		if (is_array($eventIdList))
		{
			$events = \CCalendarEvent::getList(
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

	public static function updateSearchIndex($eventIdList = array(), $params = array())
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

			$events = \CCalendarEvent::getList(
				array(
					'arFilter' => array(
						"ID" => $eventIdList,
						"DELETED" => false
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false
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
					" WHERE ID=".intval($event['ID']);
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
	}

	public static function formatSearchIndexContent($entry = array())
	{
		$content = '';
		if (!empty($entry))
		{
			$content = static::prepareToken($entry['NAME'].' '.$entry['DESCRIPTION']);

			if ($entry['IS_MEETING'])
			{
				$attendeesWereHandled = false;
				if(!empty($entry['ATTENDEE_LIST']) && is_array($entry['ATTENDEE_LIST']))
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

				if(!empty($entry['ATTENDEES_CODES']))
				{
					$attendeesCodes = $entry['ATTENDEES_CODES'];
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
				if (!empty($entry['UF_WEBDAV_CAL_EVENT'])
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

		if($res = $res->Fetch())
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
			" WHERE ID=".intval($eventId);
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
		$entry = \CCalendarEvent::GetList([
			'arFilter' => [
				"ID" => $entryId,
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

		if (!$entry || !is_array($entry[0]))
		{
			$entry = \CCalendarEvent::GetList([
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
			$entry = \CCalendarEvent::GetList([
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
			if ($entry['IS_MEETING'] && $entry['PARENT_ID'] != $entry['ID'])
			{
				$parentEntry = \CCalendarEvent::GetById(intval($entry['PARENT_ID']));
				if($parentEntry['DELETED'] == 'Y')
				{
					\CCalendarEvent::CleanEventsWithDeadParents();
					$entry = false;
				}

				if ($parentEntry['MEETING_HOST'] == $params['userId'])
				{
					$entry = $parentEntry;
				}
			}
		}

		if ($entry['IS_MEETING']
			&& is_array($entry['ATTENDEE_LIST'])
			&& $entry['CREATED_BY'] !== $params['userId']
			&& $params['recursion'] !== false)
		{
			foreach($entry['ATTENDEE_LIST'] as $attendee)
			{
				if (intval($attendee['id']) === intval($params['userId']))
				{
					$entry = \CCalendarEvent::GetList([
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

					if ($entry && is_array($entry[0]))
					{
						$params['recursion'] = false;
						$entry = self:: getEventForViewInterface($entry[0]['ID'], $params);
					}
				}
			}
		}

		return $entry;
	}

	public static function getEventForEditInterface($entryId, $params = [])
	{
		$entry = \CCalendarEvent::GetList(
			[
				'arFilter' => [
					"ID" => $entryId,
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
			]
		);

		if (!$entry || !is_array($entry[0]))
		{
			$entry = \CCalendarEvent::GetList(
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
			$entry = \CCalendarEvent::GetList(
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
			return self::getEventForEditInterface($entry['PARENT_ID']);
		}

		return $entry;
	}

	public static function handleAccessCodes($accessCodes = [], $params = [])
	{
		$accessCodes = is_array($accessCodes) ? $accessCodes : [];
		$userId = isset($params['userId']) ? $params['userId'] : \CCalendar::getCurUserId();

		if(empty($accessCodes))
		{
			$accessCodes[] = 'U'.$userId;
		}

		$accessCodes = array_unique($accessCodes);

		return $accessCodes;
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
				'ACTIVE' => 'Y'
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
}
?>
