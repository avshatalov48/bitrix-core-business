<?
use \Bitrix\Calendar\Ui\CalendarFilter;

class CCalendarRequest
{
	private static
		$request,
		$reqId,
		$calendar;

	public static function Process($action = '', CCalendar $calendar)
	{
		global $APPLICATION;

		self::$request = \Bitrix\Main\Context::getCurrent()->getRequest()->toArray();

		if ($_REQUEST['skip_unescape'] !== 'Y')
		{
			CUtil::decodeURIComponent(self::$request);
			CUtil::JSPostUnEscape();
		}

		self::$calendar = $calendar;

		// Export calendar
		if ($action == 'export')
		{
			// We don't need to check access  couse we will check security SIGN from the URL
			$sectId = intVal($_GET['sec_id']);
			if ($_GET['check'] == 'Y') // Just for access check from calendar interface
			{
				$APPLICATION->RestartBuffer();
				if (CCalendarSect::CheckSign($_GET['sign'], intVal($_GET['user']), $sectId > 0 ? $sectId : 'superposed_calendars'))
					echo 'BEGIN:VCALENDAR';
				CMain::FinalActions();
				die();
			}

			if (CCalendarSect::CheckAuthHash() && $sectId > 0)
			{
				// We don't need any warning in .ics file
				error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);
				CCalendarSect::ReturnICal(array(
					'sectId' => $sectId,
					'userId' => intVal($_GET['user']),
					'sign' => $_GET['sign'],
					'type' => $_GET['type'],
					'ownerId' => intVal($_GET['owner'])
				));
			}
		}
		else
		{
			// Check the access
			if (!CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()) || !check_bitrix_sessid())
			{
				$APPLICATION->ThrowException(GetMessage("EC_ACCESS_DENIED"));
				return false;
			}

			$APPLICATION->ShowAjaxHead();
			$APPLICATION->RestartBuffer();
			self::$reqId = intVal($_REQUEST['reqId']);

			switch ($action)
			{
				case 'edit_event':
					self::EditEvent();
					break;
				case 'simple_save_entry':
					self::simpleSaveEntry();
					break;
				case 'delete_entry':
					self::deleteEntry();
					break;
				case 'move_event_to_date':
					self::MoveEventToDate();
					break;
				case 'delete':
					self::DeleteEvent();
					break;
				case 'load_events':
					self::LoadEvents();
					break;
				case 'load_entries':
					self::LoadEntries();
					break;
				case 'section_edit':
					self::EditSection();
					break;
				case 'section_delete':
					self::DeleteSection();
					break;
				case 'section_caldav_hide':
					self::HideCaldavSection();
					break;
				case 'save_settings':
					self::SaveSettings();
					break;
				case 'set_meeting_status':
					self::SetStatus();
					break;
				case 'get_group_members':
					self::GetGroupMemberList();
					break;
				case 'get_accessibility':
					self::GetAccessibility();
					break;
				case 'get_mr_accessibility':
					self::GetMeetingRoomAccessibility();
					break;
				case 'check_meeting_room':
					self::CheckMeetingRoom();
					break;
				case 'connections_edit':
					self::EditConnections();
					break;
				case 'disconnect_google':
					self::DisconnectGoogle();
					break;
				case 'clear_sync_info':
					self::ClearSynchronizationInfo();
					break;
				case 'exchange_sync':
					self::SyncExchange();
					break;
//				case 'get_view_event_dialog':
//					self::GetViewEventDialog();
//					break;
//				case 'get_edit_event_dialog':
//					self::GetEditEventDialog();
//					break;
				case 'get_planner':
					self::GetPlanner();
					break;
				case 'update_planner':
					self::UpdatePlanner();
					break;
				case 'change_recurcive_event_until':
					self::ChangeRecurciveEventUntil();
					break;
				case 'exclude_recursion_date':
					self::AddExcludeRecursionDate();
					break;
				case 'get_edit_slider':
					self::GetEditSlider();
					break;
				case 'get_view_slider':
					self::GetViewSlider();
					break;
				case 'update_location_list':
					self::updateLocationList();
					break;
				case 'get_tracking_sections':
					self::getTrackingSections();
					break;
				case 'set_tracking_sections':
					self::setTrackingSections();
					break;
				case 'get_settings_slider':
					self::getSettingsSlider();
					break;
				case 'get_destination_items':
					self::getDestinationItems();
					break;
				case 'get_filter_data':
					self::getFilterData();
					break;
			}
		}

		if($ex = $APPLICATION->GetException())
			ShowError($ex->GetString());

		CMain::FinalActions();
		die();
	}

	public static function OutputJSRes($reqId = false, $res = false)
	{
		if ($res === false)
			return;
		if ($reqId === false)
			$reqId = intVal($_REQUEST['reqId']);
		if (!$reqId)
			return;
		?>
		<script>top.BXCRES['<?= $reqId?>'] = <?= CUtil::PhpToJSObject($res)?>;</script>
		<?
	}

	public static function simpleSaveEntry()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
		{
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));
		}

		$sectId = intVal(self::$request['section']);
		if (CCalendar::GetType() != 'user' || CCalendar::GetOwnerId() != CCalendar::GetUserId()) // Personal user's calendar
		{
			if (!CCalendarSect::CanDo('calendar_add', $sectId, CCalendar::GetUserId()))
			{
				return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));
			}
		}

		// Default name for events
		$name = trim(self::$request['name']);
		if ($name == '')
		{
			$name = GetMessage('EC_DEFAULT_EVENT_NAME');
		}

		$remind = array();
		foreach (self::$request['remind'] as $remindValue)
			$remind[] = array('type' => 'min', 'count' => intval($remindValue));

		// Date & Time
		$dateFrom = self::$request['date_from'];
		$dateTo = self::$request['date_to'];

		// Timezone
		$tzFrom = self::$request['tz_from'];
		$tzTo = self::$request['tz_to'];
		if (!$tzFrom && isset(self::$request['default_tz']))
		{
			$tzFrom = self::$request['default_tz'];
		}
		if (!$tzTo && isset(self::$request['default_tz']))
		{
			$tzTo = self::$request['default_tz'];
		}

		if (isset(self::$request['default_tz']) && self::$request['default_tz'] != '')
		{
			CCalendar::SaveUserTimezoneName(CCalendar::GetUserId(), self::$request['default_tz']);
		}

		$arFields = array(
			"DATE_FROM" => $dateFrom,
			"DATE_TO" => $dateTo,
			'TZ_FROM' => $tzFrom,
			"SKIP_TIME" => false,
			'TZ_TO' => $tzTo,
			'NAME' => $name,
			'SECTIONS' => $sectId,
			'LOCATION' => array(
				"NEW" => self::$request['location']
			),
			"REMIND" => $remind,
			"IS_MEETING" => !!self::$request['is_meeting']
		);

		$arAccessCodes = array();
		if (isset(self::$request['access_codes']) && is_array(self::$request['access_codes']))
		{
			$arAccessCodes = self::$request['access_codes'];
			if (!count($arAccessCodes) || CCalendar::GetType() != 'user' || CCalendar::IsPersonal())
				$arAccessCodes[] = 'U'.CCalendar::GetUserId();
			$arAccessCodes = array_unique($arAccessCodes);
		}

		$arFields['IS_MEETING'] = !empty($arAccessCodes) && $arAccessCodes != array('U'.CCalendar::GetUserId());
		if ($arFields['IS_MEETING'])
		{
			$arFields['ATTENDEES_CODES'] = $arAccessCodes;
			$arFields['ATTENDEES'] = CCalendar::GetDestinationUsers($arAccessCodes);
		}

		if (self::$request['exclude_users'] && count($arFields['ATTENDEES']) > 0)
		{
			$excludeUsers = explode(",", self::$request['exclude_users']);
			$arFields['ATTENDEES_CODES'] = array();
			if (count($excludeUsers) > 0)
			{
				$arFields['ATTENDEES'] = array_diff($arFields['ATTENDEES'], $excludeUsers);
				foreach ($arFields['ATTENDEES'] as $userId)
				{
					$arFields['ATTENDEES_CODES'][] = 'U'.intval($userId);
				}
			}
		}

		$arFields['MEETING_HOST'] = CCalendar::GetUserId();
		$arFields['MEETING'] = array(
			'HOST_NAME' => CCalendar::GetUserName($arFields['MEETING_HOST']),
			'NOTIFY' => self::$request['meeting_notify'] === 'Y',
			'ALLOW_INVITE' => self::$request['meeting_allow_invite'] === 'Y',
		);

		$newId = CCalendar::SaveEvent(array(
			'arFields' => $arFields,
			'silentErrorMode' => false
		));

		$entries = array();
		$arAttendees = array();
		$eventIds = array($newId);

		if ($newId)
		{
			$arFilter = array(
				"ID" => $newId,
				"FROM_LIMIT" => CCalendar::Date(CCalendar::Timestamp($arFields["DATE_FROM"]) - CCalendar::DAY_LENGTH * 10, false),
				"TO_LIMIT" => CCalendar::Date(CCalendar::Timestamp($arFields["DATE_TO"]) + CCalendar::DAY_LENGTH * 90, false)
			);

			$entries = CCalendarEvent::GetList(
				array(
					'arFilter' => $arFilter,
					'parseRecursion' => true,
					'fetchAttendees' => true,
					'userId' => CCalendar::GetUserId()
				)
			);

			if ($arFields['IS_MEETING'])
			{
				\Bitrix\Main\FinderDestTable::merge(array(
					"CONTEXT" => "CALENDAR",
					"CODE" => \Bitrix\Main\FinderDestTable::convertRights($arAccessCodes, array('U'.CCalendar::GetUserId()))
				));
			}
		}

		self::OutputJSRes(self::$reqId, array(
			'id' => $newId,
			'entries' => $entries,
			'attendees' => $arAttendees,
			'eventIds' => array_unique($eventIds)
		));
	}


	public static function EditEvent()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$id = intVal(self::$request['id']);
		if (isset(self::$request['section']))
		{
			$sectId = intVal(self::$request['section']);
			self::$request['sections'] = array($sectId);
		}
		else
		{
			$sectId = intVal(self::$request['sections'][0]);
		}

		if (CCalendar::GetType() != 'user' || CCalendar::GetOwnerId() != CCalendar::GetUserId()) // Personal user's calendar
		{
			if (!$id && !CCalendarSect::CanDo('calendar_add', $sectId, CCalendar::GetUserId()))
				return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

			if ($id && !CCalendarSect::CanDo('calendar_edit', $sectId, CCalendar::GetUserId()))
				return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));
		}

		// Default name for events
		self::$request['name'] = trim(self::$request['name']);
		if (self::$request['name'] == '')
			self::$request['name'] = GetMessage('EC_DEFAULT_EVENT_NAME');

		$remind = array();
		if (isset(self::$request['reminder']))
		{
			foreach (self::$request['reminder'] as $remindValue)
				$remind[] = array('type' => 'min', 'count' => intval($remindValue));
			//$remind[] = array('type' => self::$request['remind']['type'], 'count' => intval(self::$request['remind']['count']));
		}

		$rrule = self::$request['EVENT_RRULE'];
		if (self::$request['rrule_endson'] == 'never')
		{
			unset($rrule['COUNT']);
			unset($rrule['UNTIL']);
		}
		elseif (self::$request['rrule_endson'] == 'count')
		{
			if (intval($rrule['COUNT']) <= 0)
				$rrule['COUNT'] = 10;
			unset($rrule['UNTIL']);
		}
		elseif (self::$request['rrule_endson'] == 'until')
		{
			unset($rrule['COUNT']);
		}

		// Date & Time
		$dateFrom = self::$request['date_from'];
		$dateTo = self::$request['date_to'];
		$skipTime = isset(self::$request['skip_time']) && self::$request['skip_time'] == 'Y';

		if (!$skipTime)
		{
			$dateFrom .= ' '.self::$request['time_from_real'];
			$dateTo .= ' '.self::$request['time_to_real'];
		}
		$dateFrom = trim($dateFrom);
		$dateTo = trim($dateTo);

		// Timezone
		$tzFrom = self::$request['tz_from'];
		$tzTo = self::$request['tz_to'];
		if (!$tzFrom && isset(self::$request['default_tz']))
		{
			$tzFrom = self::$request['default_tz'];
		}
		if (!$tzTo && isset(self::$request['default_tz']))
		{
			$tzTo = self::$request['default_tz'];
		}

		if (isset(self::$request['default_tz']) && self::$request['default_tz'] != '')
		{
			CCalendar::SaveUserTimezoneName(CCalendar::GetUserId(), self::$request['default_tz']);
		}

		$arFields = array(
			"ID" => $id,
			"DATE_FROM" => $dateFrom,
			"DATE_TO" => $dateTo,
			'TZ_FROM' => $tzFrom,
			'TZ_TO' => $tzTo,
			'NAME' => self::$request['name'],
			'DESCRIPTION' => trim(self::$request['desc']),
			'SECTIONS' => self::$request['sections'],
			'COLOR' => self::$request['color'],
			'ACCESSIBILITY' => self::$request['accessibility'],
			'IMPORTANCE' => isset(self::$request['importance']) ? self::$request['importance'] : 'normal',
			'PRIVATE_EVENT' => self::$request['private_event'] == 'Y',
			'RRULE' => $rrule,
			'LOCATION' => array(
				"OLD" => self::$request['location_old'],
				"NEW" => self::$request['location_new']
			),
			"REMIND" => $remind,
			"IS_MEETING" => !!self::$request['is_meeting'],
			"SKIP_TIME" => $skipTime
		);

		$arAccessCodes = array();
		if (isset(self::$request['EVENT_DESTINATION']))
		{
			foreach(self::$request["EVENT_DESTINATION"] as $v => $k)
			{
				if(strlen($v) > 0 && is_array($k) && !empty($k))
				{
					foreach($k as $vv)
					{
						if(strlen($vv) > 0)
						{
							$arAccessCodes[] = $vv;
						}
					}
				}
			}

			if (!count($arAccessCodes) || CCalendar::GetType() != 'user' || CCalendar::IsPersonal())
				$arAccessCodes[] = 'U'.CCalendar::GetUserId();

			$arAccessCodes = array_unique($arAccessCodes);
		}

		$arFields['IS_MEETING'] = !empty($arAccessCodes) && $arAccessCodes != array('U'.CCalendar::GetUserId());
		if ($arFields['IS_MEETING'])
		{
			$arFields['ATTENDEES_CODES'] = $arAccessCodes;
			$arFields['ATTENDEES'] = CCalendar::GetDestinationUsers($arAccessCodes);
		}

		if (self::$request['exclude_users'] && count($arFields['ATTENDEES']) > 0)
		{
			$excludeUsers = explode(",", self::$request['exclude_users']);
			$arFields['ATTENDEES_CODES'] = array();
			if (count($excludeUsers) > 0)
			{
				$arFields['ATTENDEES'] = array_diff($arFields['ATTENDEES'], $excludeUsers);
				foreach ($arFields['ATTENDEES'] as $userId)
				{
					$arFields['ATTENDEES_CODES'][] = 'U'.intval($userId);
				}
			}
		}

		$arFields['MEETING_HOST'] = CCalendar::GetUserId();
		$arFields['MEETING'] = array(
			'HOST_NAME' => CCalendar::GetUserName($arFields['MEETING_HOST']),
			'NOTIFY' => self::$request['meeting_notify'] === 'Y',
			'REINVITE' => self::$request['meeting_reinvite'] === 'Y',
			'ALLOW_INVITE' => self::$request['allow_invite'] === 'Y',
		);

		// Userfields for event
		$arUFFields = array();
		foreach (self::$request as $field => $value)
		{
			if (substr($field, 0, 3) == "UF_")
			{
				$arUFFields[$field] = $value;
			}
		}

		$newId = CCalendar::SaveEvent(array(
			'arFields' => $arFields,
			'UF' => $arUFFields,
			'silentErrorMode' => false,
			'recursionEditMode' => $_REQUEST['rec_edit_mode'],
			'currentEventDateFrom' => CCalendar::Date(CCalendar::Timestamp(self::$request['current_date_from']), false)
		));

		$arEvents = array();
		$arAttendees = array();
		$eventIds = array($newId);
		if ($newId)
		{
			$arFilter = array(
				"ID" => $newId,
				"FROM_LIMIT" => CCalendar::Date(CCalendar::Timestamp($arFields["DATE_FROM"]) - CCalendar::DAY_LENGTH * 10, false),
				"TO_LIMIT" => CCalendar::Date(CCalendar::Timestamp($arFields["DATE_TO"]) + CCalendar::DAY_LENGTH * 90, false)
			);

			$arEvents = CCalendarEvent::GetList(
				array(
					'arFilter' => $arFilter,
					'parseRecursion' => true,
					'fetchAttendees' => true,
					'userId' => CCalendar::GetUserId()
				)
			);

			if ($arFields['IS_MEETING'])
			{
				\Bitrix\Main\FinderDestTable::merge(array(
					"CONTEXT" => "CALENDAR",
					"CODE" => \Bitrix\Main\FinderDestTable::convertRights($arAccessCodes, array('U'.CCalendar::GetUserId()))
				));
			}

			if ($arEvents && $arFields['IS_MEETING'])
				$arAttendees = CCalendarEvent::GetLastAttendees();

			if (in_array($_REQUEST['rec_edit_mode'], array('this', 'next')))
			{
				unset($arFilter['ID']);
				$arFilter['RECURRENCE_ID'] = ($arEvents && $arEvents[0] && $arEvents[0]['RECURRENCE_ID']) ? $arEvents[0]['RECURRENCE_ID'] : $newId;

				$resRelatedEvents = CCalendarEvent::GetList(
					array(
						'arFilter' => $arFilter,
						'parseRecursion' => true,
						'fetchAttendees' => true,
						'userId' => CCalendar::GetUserId()
					)
				);

				foreach ($resRelatedEvents as $ev)
				{
					$eventIds[] = $ev['ID'];
				}
				$arEvents = array_merge($arEvents, $resRelatedEvents);
			}
			elseif ($id && $arEvents && $arEvents[0] && CCalendarEvent::CheckRecurcion($arEvents[0]))
			{
				$recId = $arEvents[0]['RECURRENCE_ID'] ? $arEvents[0]['RECURRENCE_ID'] : $arEvents[0]['ID'];
				if ($arEvents[0]['RECURRENCE_ID'] && $arEvents[0]['RECURRENCE_ID'] !== $arEvents[0]['ID'])
				{
					unset($arFilter['RECURRENCE_ID']);
					$arFilter['ID'] = $arEvents[0]['RECURRENCE_ID'];
					$resRelatedEvents = CCalendarEvent::GetList(
						array(
							'arFilter' => $arFilter,
							'parseRecursion' => true,
							'fetchAttendees' => true,
							'userId' => CCalendar::GetUserId()
						)
					);
					$eventIds[] = $arEvents[0]['RECURRENCE_ID'];
					$arEvents = array_merge($arEvents, $resRelatedEvents);
				}

				if ($recId)
				{
					unset($arFilter['ID']);
					$arFilter['RECURRENCE_ID'] = $recId;
					$resRelatedEvents = CCalendarEvent::GetList(
						array(
							'arFilter' => $arFilter,
							'parseRecursion' => true,
							'fetchAttendees' => true,
							'userId' => CCalendar::GetUserId()
						)
					);

					foreach ($resRelatedEvents as $ev)
					{
						$eventIds[] = $ev['ID'];
					}
					$arEvents = array_merge($arEvents, $resRelatedEvents);
				}
			}
		}

		self::OutputJSRes(self::$reqId, array(
			'id' => $newId,
			'entries' => $arEvents,
			'attendees' => $arAttendees,
			'eventIds' => array_unique($eventIds)
		));
	}

	public static function MoveEventToDate()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$id = intVal($_POST['id']);
		$sectId = intVal($_POST['section']);
		$reload = $_POST['recursive'] === 'Y';
		$busyWarning = false;

		if (CCalendar::GetType() != 'user' || CCalendar::GetOwnerId() != CCalendar::GetUserId()) // Personal user's calendar
		{
			if (!$id && !CCalendarSect::CanDo('calendar_add', $sectId, CCalendar::GetUserId()))
				return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

			if ($id && !CCalendarSect::CanDo('calendar_edit', $sectId, CCalendar::GetUserId()))
				return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));
		}

		$skipTime = isset($_POST['skip_time']) && $_POST['skip_time'] == 'Y';
		$arFields = array(
			"ID" => $id,
			"DATE_FROM" => CCalendar::Date(CCalendar::Timestamp($_POST['date_from']), !$skipTime),
			"SKIP_TIME" => $skipTime
		);

		if (isset($_POST['date_to']))
			$arFields["DATE_TO"] = CCalendar::Date(CCalendar::Timestamp($_POST['date_to']), !$skipTime);
		$timezone = $_POST['timezone'];
		if (!$skipTime && $_POST['set_timezone'] == 'Y' && $_POST['timezone'])
		{
			$arFields["TZ_FROM"] = $_POST['timezone'];
			$arFields["TZ_TO"] = $_POST['timezone'];
		}

		if ($_POST['is_meeting'] === 'Y')
		{
			$usersToCheck = array();

			if (is_array($_POST['attendees']))
			{
				foreach ($_POST['attendees'] as $attId)
				{
					if ($attId === CCalendar::GetUserId())
						continue;
					$userSettings = CCalendarUserSettings::Get(intval($attId));
					if($userSettings && $userSettings['denyBusyInvitation'])
					{
						$usersToCheck[] = intval($attId);
					}
				}
			}

			if (count($usersToCheck) > 0)
			{
				$fromTs = CCalendar::Timestamp($arFields["DATE_FROM"]);
				$toTs = CCalendar::Timestamp($arFields["DATE_TO"]);
				$fromTs = $fromTs - CCalendar::GetTimezoneOffset($timezone, $fromTs);
				$toTs = $toTs - CCalendar::GetTimezoneOffset($timezone, $toTs);
				$dateFromUtc = CCalendar::Date($fromTs);
				$dateToUtc = CCalendar::Date($toTs);

				$accessibility = CCalendar::GetAccessibilityForUsers(array(
					'users' => $usersToCheck,
					'from' => $dateFromUtc, // date or datetime in UTC
					'to' => $dateToUtc, // date or datetime in UTC
					'curEventId' => $id,
					'getFromHR' => true,
					'checkPermissions' => false
				));

				foreach($accessibility as $userId => $entries)
				{
					foreach($entries as $entry)
					{
						$entFromTs = CCalendar::Timestamp($entry["DATE_FROM"]);
						$entToTs = CCalendar::Timestamp($entry["DATE_TO"]);

						$entFromTs -= CCalendar::GetTimezoneOffset($entry['TZ_FROM'], $entFromTs);
						$entToTs -= CCalendar::GetTimezoneOffset($entry['TZ_TO'], $entToTs);

						if ($entFromTs < $toTs && $entToTs > $fromTs)
						{
							$busyWarning = true;
							$reload = true;
							break;
						}
					}

					if ($busyWarning)
						break;
				}
			}
		}

		if (!$busyWarning)
		{
			if ($_POST['recursive'] === 'Y')
			{
				CCalendar::SaveEventEx(array(
					'arFields' => $arFields,
					'silentErrorMode' => false,
					'recursionEditMode' => 'this',
					'currentEventDateFrom' => CCalendar::Date(CCalendar::Timestamp($_POST['current_date_from']), false)
				));
			}
			else
			{
				//SaveEvent
				$id = CCalendar::SaveEvent(array(
					'arFields' => $arFields,
					'silentErrorMode' => false
				));
			}
		}

		self::OutputJSRes(self::$reqId, array(
			'id' => $id,
			'reload' => $reload,
			'busy_warning' => $busyWarning
		));
	}


	public static function deleteEntry()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$res = CCalendar::DeleteEvent(intVal(self::$request['entry_id']),
			true,
			array('recursionMode' => self::$request['recursion_mode'])
		);

		if ($res !== true)
			return CCalendar::ThrowError(strlen($res) > 0 ? $res : GetMessage('EC_EVENT_DEL_ERROR'));

		self::OutputJSRes(self::$reqId, true);
	}

	public static function DeleteEvent()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$res = CCalendar::DeleteEvent(intVal($_POST['id']), true, array('recursionMode' => $_REQUEST['rec_mode']));

		if ($res !== true)
			return CCalendar::ThrowError(strlen($res) > 0 ? $res : GetMessage('EC_EVENT_DEL_ERROR'));

		self::OutputJSRes(self::$reqId, true);
	}


	public static function LoadEntries()
	{
		$arHiddenSect = array();
		$finish = false;
		$monthFrom = intVal(self::$request['month_from']);
		$yearFrom = intVal(self::$request['year_from']);
		$monthTo = intVal(self::$request['month_to']);
		$yearTo = intVal(self::$request['year_to']);
		$parseRecursion = true;

		$params = array(
			'type' => CCalendar::GetType(),
			'section' => array(),
			'fromLimit' => $monthFrom ? CCalendar::Date(mktime(0, 0, 0, $monthFrom, 1, $yearFrom), false) : false,
			'toLimit' => $monthTo ? CCalendar::Date(mktime(0, 0, 0, $monthTo, 1, $yearTo), false) : false,
		);
		$connections = false;

		if (self::$request['loadNext'] == 'Y' || self::$request['loadPrevious'] == 'Y')
		{
			$params['limit'] = intVal(self::$request['loadLimit']);
			$parseRecursion = false;
		}

		if ($_REQUEST['cal_dav_data_sync'] == 'Y' && CCalendar::IsCalDAVEnabled())
		{
			$isGoogleApiEnabled = CCalendar::isGoogleApiEnabled();

			$JSConfig = array();
			CCalendar::InitCalDavParams($JSConfig, $isGoogleApiEnabled);
			CDavGroupdavClientCalendar::DataSync("user", CCalendar::GetOwnerId());

			if ($isGoogleApiEnabled)
            {
	            $dbConnections = CDavConnection::GetList(
		            array("SYNCHRONIZED" => "ASC"),
		            array('ACCOUNT_TYPE' => 'google_api_oauth', 'ENTITY_TYPE' => 'user', 'ENTITY_ID' => CCalendar::GetOwnerId()),
		            false,
		            false,
		            array('ID', 'ENTITY_TYPE', 'ENTITY_ID', 'ACCOUNT_TYPE', 'SERVER_SCHEME', 'SERVER_HOST', 'SERVER_PORT', 'SERVER_USERNAME', 'SERVER_PASSWORD', 'SERVER_PATH', 'SYNCHRONIZED', 'SYNC_TOKEN')
	            );
	            if ($connection = $dbConnections->Fetch())
                {
                	$connection['forceSync'] = true;
	                CCalendarSync::dataSync($connection);
                }
                CCalendar::InitGoogleApiParams($JSConfig);
            }
			if ($JSConfig['connections'])
				$connections = $JSConfig['connections'];
		}

		$bGetTask = false;
		if (is_array($_REQUEST['active_sect']))
		{
			foreach($_REQUEST['active_sect'] as $sectId)
			{
				if ($sectId == 'tasks')
					$bGetTask = true;
				elseif (intval($sectId) > 0)
					$params['section'][] = intval($sectId);
			}
		}

		if (is_array($_REQUEST['hidden_sect']))
		{
			foreach($_REQUEST['hidden_sect'] as $sectId)
			{
				if ($sectId == 'tasks')
					$arHiddenSect[] = 'tasks';
				elseif(intval($sectId) > 0)
					$arHiddenSect[] = intval($sectId);
			}
		}



		$arAttendees = array(); // List of attendees for each event Array([ID] => Array(), ..,);
		$entries = array();

		if (count($params['section']) > 0)
		{
			$arFilter = array(
				'OWNER_ID' => CCalendar::GetOwnerId(),
				'SECTION' => array()
			);

			$sect = CCalendarSect::GetList(
				array('arFilter' => array(
					'ID'=> $params['section'],
					'ACTIVE' => 'Y'
				)
			));
			foreach($sect as $section)
			{
				$arFilter['SECTION'][] = $section['ID'];
			}

			CCalendarEvent::SetLastAttendees(false);

			if (isset($params['fromLimit']))
			{
				$arFilter["FROM_LIMIT"] = $params['fromLimit'];
			}
			if (isset($params['toLimit']))
			{
				$arFilter["TO_LIMIT"] = $params['toLimit'];
			}

			if ($params['type'] == 'user')
			{
				$fetchMeetings = in_array(CCalendar::GetMeetingSection($arFilter['OWNER_ID']), $params['section']);
			}
			else
			{
				$fetchMeetings = in_array(CCalendar::GetCurUserMeetingSection(), $params['section']);
				if ($params['type'])
				{
					$arFilter['CAL_TYPE'] = $params['type'];
				}
			}

			$res = CCalendarEvent::GetList(
				array(
					'arFilter' => $arFilter,
					'parseRecursion' => $parseRecursion,
					'fetchAttendees' => true,
					'userId' => CCalendar::GetCurUserId(),
					'fetchMeetings' => $fetchMeetings,
					'setDefaultLimit' => false,
					'limit' => $params['limit']
				)
			);

			$finish = $params['limit'] && count($res) < $params['limit'];
			$entries = array();
			$lastDateTimestamp = 0;
			$firstDateTimestamp = INF;
			foreach($res as $entry)
			{
				if(in_array($entry['SECT_ID'], $params['section']))
				{
					$entries[] = $entry;

					if (self::$request['loadNext'] == 'Y' && !CCalendarEvent::CheckRecurcion($entry) && $entry['DATE_TO_TS_UTC'] > $lastDateTimestamp)
					{
						$lastDateTimestamp = $entry['DATE_TO_TS_UTC'];
					}
					elseif(self::$request['loadPrevious'] == 'Y' && !CCalendarEvent::CheckRecurcion($entry) && $entry['DATE_FROM_TS_UTC'] < $lastDateTimestamp)
					{
						$firstDateTimestamp = $entry['DATE_FROM_TS_UTC'];
					}
				}
			}

			if (self::$request['loadNext'] == 'Y')
			{
				$params['toLimit'] = CCalendar::Date($lastDateTimestamp);
			}
			if (self::$request['loadPrevious'] == 'Y')
			{
				$params['fromLimit'] = CCalendar::Date($firstDateTimestamp);
			}

			if(!$parseRecursion)
			{
				foreach($entries as $entry)
				{
					if (in_array($entry['SECT_ID'], $params['section']))
					{

						if (CCalendarEvent::CheckRecurcion($entry))
						{
							CCalendarEvent::ParseRecursion($entries, $entry, array(
								'fromLimit' => $params['fromLimit'],
								'toLimit' => $params['toLimit'],
								'instanceCount' => false,
								'preciseLimits' => true
							));
						}
					}
				}
			}

			$arAttendees = CCalendarEvent::GetLastAttendees();
		}

		if (is_array($_REQUEST['sup_sect']))
		{
			$arDisplayedSPSections = array();
			foreach($_REQUEST['sup_sect'] as $sectId)
			{
				$arDisplayedSPSections[] = intval($sectId);
			}

			if (count($arDisplayedSPSections) > 0)
			{
				$arSuperposedEvents = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"FROM_LIMIT" => $params['fromLimit'],
							"TO_LIMIT" => $params['toLimit'],
							"SECTION" => $arDisplayedSPSections
						),
						'parseRecursion' => true,
						'fetchAttendees' => true,
						'userId' => CCalendar::GetUserId()
					)
				);

				$entries = array_merge($entries, $arSuperposedEvents);
			}
		}

		//  **** GET TASKS ****
		if ($bGetTask)
		{
			$tasksEntries = CCalendar::getTaskList(
				array(
					'type' => CCalendar::GetType(),
					'ownerId' => CCalendar::GetOwnerId()
				)
			);

			if(count($tasksEntries) > 0)
			{
				$entries = array_merge($entries, $tasksEntries);
			}
		}

		self::OutputJSRes(self::$reqId, array(
			'entries' => $entries,
			'attendees' => $arAttendees,
			'connections' => $connections,
			'finish' => $finish
		));
	}

	public static function LoadEvents()
	{
		$arSect = array();
		$arHiddenSect = array();
		$month = intVal($_REQUEST['month']);
		$year = intVal($_REQUEST['year']);
		$fromLimit = CCalendar::Date(mktime(0, 0, 0, $month - 1, 20, $year), false);
		$toLimit = CCalendar::Date(mktime(0, 0, 0, $month + 1, 10, $year), false);
		$connections = false;

		if ($_REQUEST['cal_dav_data_sync'] == 'Y' && CCalendar::IsCalDAVEnabled())
		{
			CDavGroupdavClientCalendar::DataSync("user", CCalendar::GetOwnerId());

			$JSConfig = array();
			CCalendar::InitCalDavParams($JSConfig);
			if ($JSConfig['connections'])
				$connections = $JSConfig['connections'];
		}

		$bGetTask = false;
		if (is_array($_REQUEST['active_sect']))
		{
			foreach($_REQUEST['active_sect'] as $sectId)
			{
				if ($sectId == 'tasks')
					$bGetTask = true;
				elseif (intval($sectId) > 0)
					$arSect[] = intval($sectId);
			}
		}

		if (is_array($_REQUEST['hidden_sect']))
		{
			foreach($_REQUEST['hidden_sect'] as $sectId)
			{
				if ($sectId == 'tasks')
					$arHiddenSect[] = 'tasks';
				elseif(intval($sectId) > 0)
					$arHiddenSect[] = intval($sectId);
			}
		}

		$arAttendees = array(); // List of attendees for each event Array([ID] => Array(), ..,);
		$arEvents = array();

		if (count($arSect) > 0)
		{
			// NOTICE: Attendees for meetings selected inside this method and returns as array by link '$arAttendees'
			$arEvents = CCalendar::GetEventList(array(
					'type' => CCalendar::GetType(),
					'section' => $arSect,
					'fromLimit' => $fromLimit,
					'toLimit' => $toLimit
			), $arAttendees);
		}

		if (is_array($_REQUEST['sup_sect']))
		{
			$arDisplayedSPSections = array();
			foreach($_REQUEST['sup_sect'] as $sectId)
			{
				$arDisplayedSPSections[] = intval($sectId);
			}

			if (count($arDisplayedSPSections) > 0)
			{
				$arSuperposedEvents = CCalendarEvent::GetList(
						array(
								'arFilter' => array(
										"FROM_LIMIT" => $fromLimit,
										"TO_LIMIT" => $toLimit,
										"SECTION" => $arDisplayedSPSections
								),
								'parseRecursion' => true,
								'fetchAttendees' => true,
								'userId' => CCalendar::GetUserId()
						)
				);

				$arEvents = array_merge($arEvents, $arSuperposedEvents);
			}
		}

		//  **** GET TASKS ****
		$arTaskIds = array();
		if ($bGetTask)
		{
			$arTasks = CCalendar::GetTaskList(array(
				'fromLimit' => $fromLimit,
				'toLimit' => $toLimit
			), $arTaskIds);

			if (count($arTasks) > 0)
				$arEvents = array_merge($arEvents, $arTasks);
		}

		// Save hidden calendars
		//CCalendarSect::Hidden(CCalendar::GetUserId(), $arHiddenSect);

		self::OutputJSRes(self::$reqId, array(
				'events' => $arEvents,
				'attendees' => $arAttendees,
				'connections' => $connections
		));
	}

	public static function EditSection()
	{
		$id = intVal($_POST['id']);
		$bNew = (!isset($id) || $id == 0);

		if ($bNew) // For new sections
		{
			if (CCalendar::GetType() == 'group')
			{
				// It's for groups
				if (!CCalendarType::CanDo('calendar_type_edit_section', 'group'))
					return CCalendar::ThrowError('[se01]'.GetMessage('EC_ACCESS_DENIED'));
			}
			else if (CCalendar::GetType() == 'user')
			{
				if (!CCalendar::IsPersonal()) // If it's not owner of the group.
					return CCalendar::ThrowError('[se02]'.GetMessage('EC_ACCESS_DENIED'));
			}
			else // other types
			{
				if (!CCalendarType::CanDo('calendar_type_edit_section', CCalendar::GetType()))
					return CCalendar::ThrowError('[se03]'.GetMessage('EC_ACCESS_DENIED'));
			}
		}
		// For existent sections
		elseif (!CCalendar::IsPersonal() && !$bNew && !CCalendarSect::CanDo('calendar_edit_section', $id, CCalendar::GetUserId()))
		{
			return CCalendar::ThrowError(GetMessage('[se02]EC_ACCESS_DENIED'));
		}

		$type = CCalendar::GetType();
		$arFields = Array(
				'CAL_TYPE' => $type,
				'ID' => $id,
				'NAME' => trim($_POST['name']),
				'COLOR' => $_POST['color'],
				'OWNER_ID' => ($type == 'user' || $type == 'group') ? CCalendar::GetOwnerId() : '',
				'ACCESS' => is_array($_POST['access']) ? $_POST['access'] : array()
		);

		if ($bNew)
		{
			$arFields['IS_EXCHANGE'] = $_POST['is_exchange'] == 'Y';
		}

		$id = intVal(CCalendar::SaveSection(array('arFields' => $arFields)));

		if ($id > 0)
		{
			CCalendarSect::SetClearOperationCache(true);
			$oSect = CCalendarSect::GetById($id, true, true);
			if (!$oSect)
				return CCalendar::ThrowError(GetMessage('EC_CALENDAR_SAVE_ERROR'));

			if (CCalendar::GetType() == 'user' && isset($_POST['is_def_meet_calendar']) && $_POST['is_def_meet_calendar'] == 'Y')
			{
				$set = CCalendarUserSettings::Get(CCalendar::GetOwnerId());
				$set['meetSection'] = $id;
				CCalendarUserSettings::Set($set, CCalendar::GetOwnerId());
			}

			self::OutputJSRes(self::$reqId, array('calendar' => $oSect, 'accessNames' => CCalendar::GetAccessNames()));
		}

		if ($id <= 0)
			return CCalendar::ThrowError(GetMessage('EC_CALENDAR_SAVE_ERROR'));
	}

	public static function DeleteSection()
	{
		$sectId = intVal($_REQUEST['id']);

		if (!CCalendar::IsPersonal() && !CCalendarSect::CanDo('calendar_edit_section', $sectId, CCalendar::GetUserId()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		CCalendar::DeleteSection($sectId);

		self::OutputJSRes(self::$reqId, array('result' => true));
	}

	public static function HideCaldavSection()
	{
		$sectId = intVal($_REQUEST['id']);

		if (!CCalendar::IsPersonal() && !CCalendarSect::CanDo('calendar_edit_section', $sectId, CCalendar::GetUserId()))
		{
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));
		}

		$connectionRemoved = false;
		$oSect = CCalendarSect::GetById($sectId);
		// For exchange we change only calendar name
		if ($oSect && $oSect['CAL_DAV_CON'])
		{
			CCalendarSect::Edit(array(
					'arFields' => array(
							"ID" => $sectId,
							"ACTIVE" => "N"
					)));

			// Check if it's last section from connection - remove it
			$sections = CCalendarSect::GetList(
					array('arFilter' => array(
							'CAL_DAV_CON' => $oSect['CAL_DAV_CON'],
							'ACTIVE' => 'Y'
					)));

			if(!$sections || count($sections) == 0)
			{
				CCalendar::RemoveConnection(array('id' => intval($oSect['CAL_DAV_CON']), 'del_calendars' => 'Y'));
				$connectionRemoved = true;
			}
		}

		self::OutputJSRes(self::$reqId, array(
				'result' => true,
				'refreshView' => $connectionRemoved
		));
	}

	public static function SaveSettings()
	{
		// Personal
		CCalendarUserSettings::Set($_REQUEST['user_settings']);

		// Save access for type
		if (CCalendarType::CanDo('calendar_type_edit_access', CCalendar::GetType()))
		{
			// General
			if (is_array($_REQUEST['settings']))
			{
				$_REQUEST['settings']['week_holidays'] = implode('|',$_REQUEST['settings']['week_holidays']);
				CCalendar::SetSettings($_REQUEST['settings']);
			}

			CCalendarType::Edit(array(
				'arFields' => array(
					'XML_ID' => CCalendar::GetType(),
					'ACCESS' => $_REQUEST['type_access']
				)
			));
		}

		if (isset($_POST['user_timezone_name']))
		{
			CCalendar::SaveUserTimezoneName(CCalendar::GetUserId(), $_POST['user_timezone_name']);
		}

		self::OutputJSRes(self::$reqId, array('result' => true));
	}

	public static function SetStatus()
	{
		CCalendarEvent::SetMeetingStatusEx(array(
			'attendeeId' => CCalendar::GetUserId(),
			'eventId' => intVal($_REQUEST['event_id']),
			'parentId' => intVal($_REQUEST['parent_id']),
			'status' => in_array($_REQUEST['status'], array('Q', 'Y', 'N')) ? $_REQUEST['status'] : 'Q',
			'reccurentMode' => in_array($_REQUEST['reccurent_mode'], array('this', 'next', 'all')) ? $_REQUEST['reccurent_mode'] : false,
			'currentDateFrom' => CCalendar::Date(CCalendar::Timestamp($_REQUEST['current_date_from']), false)
		));

		self::OutputJSRes(self::$reqId, true);
	}

	public static function SetMeetingParams()
	{
		CCalendarEvent::SetMeetingParams(
				CCalendar::GetUserId(),
				intVal($_REQUEST['event_id']),
				array(
						'ACCESSIBILITY' => $_REQUEST['accessibility'],
						'REMIND' =>  $_REQUEST['remind']
				)
		);
		self::OutputJSRes(self::$reqId, true);
	}

	public static function GetGroupMemberList()
	{
		if (CCalendar::GetType() == 'group')
			self::OutputJSRes(self::$reqId, array('users' => CCalendar::GetGroupMembers(CCalendar::GetOwnerId())));
	}

	public static function GetAccessibility()
	{
		$res = CCalendar::GetAccessibilityForUsers(array(
				'users' => $_POST['users'],
				'from' => CCalendar::Date(CCalendar::Timestamp($_POST['from'])),
				'to' => CCalendar::Date(CCalendar::Timestamp($_POST['to'])),
				'curEventId' => intVal($_POST['cur_event_id']),
				'getFromHR' => true
		));
		self::OutputJSRes(self::$reqId, array('data' => $res));
	}

	public static function GetMeetingRoomAccessibility()
	{
		$res = CCalendar::GetAccessibilityForMeetingRoom(array(
				'id' => intVal($_POST['id']),
				'from' => CCalendar::Date(CCalendar::Timestamp($_POST['from'])),
				'to' => CCalendar::Date(CCalendar::Timestamp($_POST['to'])),
				'curEventId' => intVal($_POST['cur_event_id'])
		));

		self::OutputJSRes(self::$reqId, array('data' => $res));
	}

	public static function CheckMeetingRoom()
	{
		$from = CCalendar::Date(CCalendar::Timestamp($_POST['from']));
		$to = CCalendar::Date(CCalendar::Timestamp($_POST['to']));
		$loc_old = $_POST['location_old'] ? CCalendar::ParseLocation(trim($_POST['location_old'])) : false;
		$loc_new = CCalendar::ParseLocation(trim($_POST['location_new']));

		$Params = array(
				'dateFrom' => $from,
				'dateTo' => $to,
				'regularity' => 'NONE',
				'members' => isset($_POST['guest']) ? $_POST['guest'] : false,
		);

		if (intVal($_POST['id']) > 0)
			$Params['ID'] = intVal($_POST['id']);

		$settings = CCalendar::GetSettings(array('request' => false));
		$Params['RMiblockId'] = $settings['rm_iblock_id'];
		$Params['mrid'] = $loc_new['mrid'];
		$Params['mrevid_old'] = $loc_old ? $loc_old['mrevid'] : 0;
		$check = CCalendar::CheckMeetingRoom($Params);

		self::OutputJSRes(self::$reqId, array('check' => $check));
	}

	public static function EditConnections()
	{
		if (CCalendar::GetType() == 'user' && CCalendar::IsCalDAVEnabled())
		{
			$res = CCalendar::ManageConnections($_POST['connections']);
			if ($res !== true)
				CCalendar::ThrowError($res == '' ? 'Edit connections error' : $res);
			else
				self::OutputJSRes(self::$reqId, array('result' => true));
		}
	}
	public static function DisconnectGoogle()
	{
		if (CCalendar::GetType() == 'user' && (CCalendar::IsCalDAVEnabled() || CCalendar::isGoogleApiEnabled()))
		{
			CCalendar::RemoveConnection(array('id' => intval($_POST['connectionId']), 'del_calendars' => 'Y'));
			self::OutputJSRes(self::$reqId, array('result' => true));
		}
	}

	public static function ClearSynchronizationInfo()
	{
		CCalendar::ClearSyncInfo(CCalendar::GetUserId(), $_POST['sync_type']);
		self::OutputJSRes(self::$reqId, array('result' => true));
	}

	public static function SyncExchange()
	{
		if (CCalendar::GetType() == 'user' && CCalendar::IsExchangeEnabled(CCalendar::GetOwnerId()))
		{
			$error = "";
			$res = CDavExchangeCalendar::DoDataSync(CCalendar::GetOwnerId(), $error);
			if ($res === true || $res === false)
				self::OutputJSRes(self::$reqId, array('result' => true));
			else
				CCalendar::ThrowError($error);
		}
	}

//	public static function GetViewEventDialog()
//	{
//		global $APPLICATION;
//		$APPLICATION->ShowAjaxHead();
//
//		$jsId = $color = preg_replace('/[^\d|\w]/', '', $_REQUEST['js_id']);
//		$eventId = intval($_REQUEST['event_id']);
//		$fromTs = CCalendar::Timestamp($_REQUEST['date_from']) - $_REQUEST['date_from_offset'];
//
//		$event = CCalendarEvent::GetList(
//			array(
//				'arFilter' => array(
//					"ID" => $eventId,
//					"DELETED" => "N",
//					"FROM_LIMIT" => CCalendar::Date($fromTs),
//					"TO_LIMIT" => CCalendar::Date($fromTs)
//				),
//				'parseRecursion' => true,
//				'maxInstanceCount' => 1,
//				'preciseLimits' => true,
//				'fetchAttendees' => true,
//				'checkPermissions' => true,
//				'setDefaultLimit' => false
//			)
//		);
//
//		if (!$event || !is_array($event[0]))
//		{
//			$event = CCalendarEvent::GetList(
//				array(
//					'arFilter' => array(
//						"ID" => $eventId,
//						"DELETED" => "N"
//					),
//					'parseRecursion' => true,
//					'maxInstanceCount' => 1,
//					'fetchAttendees' => true,
//					'checkPermissions' => true,
//					'setDefaultLimit' => false
//				)
//			);
//		}
//
//		if ($event && is_array($event[0]))
//		{
//			$event = $event[0];
//			if ($event['IS_MEETING'] && $event['PARENT_ID'] != $event['ID'])
//			{
//				$parentEvent = CCalendarEvent::GetById(intval($event['PARENT_ID']));
//				if($parentEvent['DELETED'] == 'Y')
//				{
//					CCalendarEvent::CleanEventsWithDeadParents();
//					$event = false;
//				}
//			}
//		}
//
//		if ($event)
//		{
//			CCalendarSceleton::DialogViewEvent(array(
//				'id' => $jsId,
//				'event' => $event,
//				'sectionName' => $_REQUEST['section_name'],
//				'bIntranet' => CCalendar::IsIntranetEnabled(),
//				'bSocNet' => CCalendar::IsSocNet(),
//				'AVATAR_SIZE' => 21
//			));
//		}
//
//		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
//	}

//	public static function GetEditEventDialog()
//	{
//		global $APPLICATION;
//		$APPLICATION->ShowAjaxHead();
//
//		$jsId = $color = preg_replace('/[^\d|\w]/', '', $_REQUEST['js_id']);
//		$event_id = intval($_REQUEST['event_id']);
//
//		if ($event_id > 0)
//		{
//			$Event = CCalendarEvent::GetList(
//				array(
//					'arFilter' => array(
//						"ID" => $event_id
//					),
//					'parseRecursion' => false,
//					'fetchAttendees' => true,
//					'checkPermissions' => true,
//					'setDefaultLimit' => false
//				)
//			);
//
//			$Event = $Event && is_array($Event[0]) ? $Event[0] : false;
//		}
//		else
//		{
//			$Event = array();
//		}
//
//		if (!$event_id || !empty($Event))
//		{
//			CCalendarSceleton::DialogEditEvent(array(
//				'id' => $jsId,
//				'event' => $Event,
//				'type' => CCalendar::GetType(),
//				'bIntranet' => CCalendar::IsIntranetEnabled(),
//				'bSocNet' => CCalendar::IsSocNet(),
//				'AVATAR_SIZE' => 21
//			));
//		}
//
//		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
//	}

	public static function GetEditSlider()
	{
		global $APPLICATION;
		$APPLICATION->ShowAjaxHead();

		$jsId = preg_replace('/[^\d|\w\_]/', '', $_REQUEST['unique_id']);
		$formType = preg_replace('/[^\d|\w\_]/', '', $_REQUEST['form_type']);
		$entryId = intval($_REQUEST['event_id']);

		if ($entryId > 0)
		{
			$entry = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"ID" => $entryId
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false
				)
			);

			$entry = $entry && is_array($entry[0]) ? $entry[0] : false;
		}
		else
		{
			$entry = array();
		}

		if (!$entryId || !empty($entry) && CCalendarSceleton::CheckBitrix24Limits(array('id' => $jsId)))
		{
			$APPLICATION->IncludeComponent("bitrix:calendar.edit.slider", "", array(
				'id' => $jsId,
				'event' => $entry,
				'formType' => $formType,
				'type' => CCalendar::GetType(),
				'bIntranet' => CCalendar::IsIntranetEnabled(),
				'bSocNet' => CCalendar::IsSocNet(),
				'AVATAR_SIZE' => 21
			));
		}

		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	}

	public static function GetViewSlider()
	{
		global $APPLICATION;
		$APPLICATION->ShowAjaxHead();

		$jsId = preg_replace('/[^\d|\w\_]/', '', $_REQUEST['unique_id']);
		$eventId = intval($_REQUEST['entry_id']);
		$fromTs = CCalendar::Timestamp($_REQUEST['date_from']) - $_REQUEST['date_from_offset'];

		$event = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					"ID" => $eventId,
					"DELETED" => "N",
					"FROM_LIMIT" => CCalendar::Date($fromTs),
					"TO_LIMIT" => CCalendar::Date($fromTs)
				),
				'parseRecursion' => true,
				'maxInstanceCount' => 1,
				'preciseLimits' => true,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false
			)
		);

		if (!$event || !is_array($event[0]))
		{
			$event = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"ID" => $eventId,
						"DELETED" => "N"
					),
					'parseRecursion' => true,
					'maxInstanceCount' => 1,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false
				)
			);
		}

		if ($event && is_array($event[0]))
		{
			$event = $event[0];
			if ($event['IS_MEETING'] && $event['PARENT_ID'] != $event['ID'])
			{
				$parentEvent = CCalendarEvent::GetById(intval($event['PARENT_ID']));
				if($parentEvent['DELETED'] == 'Y')
				{
					CCalendarEvent::CleanEventsWithDeadParents();
					$event = false;
				}
			}
		}

		if ($event)
		{
			$APPLICATION->IncludeComponent("bitrix:calendar.view.slider", "", array(
				'id' => $jsId,
				'event' => $event,
				'type' => CCalendar::GetType(),
				'sectionName' => $_REQUEST['section_name'],
				'bIntranet' => CCalendar::IsIntranetEnabled(),
				'bSocNet' => CCalendar::IsSocNet(),
				'AVATAR_SIZE' => 21
			));
		}

		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	}

	public static function GetPlanner()
	{
		global $APPLICATION;
		$APPLICATION->ShowAjaxHead();

		$plannerId = $_REQUEST['planner_id'];
		?><?CCalendarPlanner::Init(array('id' => $plannerId));?><?

		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	}

	public static function UpdatePlanner()
	{
		$curEventId = intVal(self::$request['cur_event_id']);
		$curUserId = CCalendar::GetCurUserId();
		$codes = false;
		if (isset(self::$request['codes']) && is_array(self::$request['codes']))
		{
			$codes = array();
			foreach(self::$request['codes'] as $code)
			{
				if($code)
					$codes[] = $code;
			}

			if(self::$request['add_cur_user_to_list'] === 'Y' || count($codes) <= 0)
			{
				$codes[] = 'U'.$curUserId;
			}
		}

		$result = CCalendarPlanner::PrepareData(array(
			'entry_id' => $curEventId,
			'user_id' => $curUserId,
			'codes' => $codes,
			'entries' => self::$request['entries'],
			'date_from' => CCalendar::Date(CCalendar::Timestamp(self::$request['date_from']), false),
			'date_to' => CCalendar::Date(CCalendar::Timestamp(self::$request['date_to']), false),
			'timezone' => self::$request['timezone'],
			'location' => trim(self::$request['location']),
			'roomEventId' => intval(self::$request['roomEventId'])
		));

		self::OutputJSRes(self::$reqId, $result);
	}

	public static function ChangeRecurciveEventUntil()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$res = array('result' => false);
		$event = CCalendarEvent::GetById(intval($_POST['event_id']));
		$untilTimestamp = CCalendar::Timestamp($_POST['until_date']);
		$recId = false;

		if ($event)
		{
			if (CCalendarEvent::CheckRecurcion($event))
			{
				$event['RRULE'] = CCalendarEvent::ParseRRULE($event['RRULE']);
				$event['RRULE']['UNTIL'] = CCalendar::Date($untilTimestamp, false);
				if (isset($event['RRULE']['COUNT']))
					unset($event['RRULE']['COUNT']);

				$id = CCalendar::SaveEvent(array(
					'arFields' => array(
						"ID" => $event["ID"],
						"RRULE" => $event['RRULE']
					),
					'silentErrorMode' => false,
					'recursionEditMode' => 'skip'
				));
				$recId = $event["ID"];
				$res['id'] = $id;
			}

			if($event["RECURRENCE_ID"] > 0)
			{
				$recParentEvent = CCalendarEvent::GetById($event["RECURRENCE_ID"]);
				if ($recParentEvent && CCalendarEvent::CheckRecurcion($recParentEvent))
				{
					$recParentEvent['RRULE'] = CCalendarEvent::ParseRRULE($recParentEvent['RRULE']);

					if ($recParentEvent['RRULE']['UNTIL'] && CCalendar::Timestamp($recParentEvent['RRULE']['UNTIL']) > $untilTimestamp)
					{
						$recParentEvent['RRULE']['UNTIL'] = CCalendar::Date($untilTimestamp, false);
						$id = CCalendar::SaveEvent(array(
							'arFields' => array(
								"ID" => $recParentEvent["ID"],
								"RRULE" => $recParentEvent['RRULE']
							),
							'silentErrorMode' => false,
							'recursionEditMode' => 'skip'
						));
						$res['id'] = $id;
					}
				}

				$recId = $event["RECURRENCE_ID"];
			}

			if ($recId)
			{
				$recRelatedEvents = CCalendarEvent::GetEventsByRecId($recId, false);
				foreach($recRelatedEvents as $ev)
				{
					if(CCalendar::Timestamp($ev['DATE_FROM']) > $untilTimestamp)
					{
						CCalendar::DeleteEvent(intVal($ev['ID']), true, array('recursionMode' => 'this'));
					}
				}
			}

			$res['result'] = true;
		}

		self::OutputJSRes(self::$reqId, $res);
	}

	public static function AddExcludeRecursionDate()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		CCalendarEvent::ExcludeInstance($_POST['event_id'], $_POST['exclude_date']);

		self::OutputJSRes(self::$reqId, array('result' => true));
	}


	public static function updateLocationList()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$locationList = self::$request['data'];

		foreach($locationList as $location)
		{
			if ($location['id'] && ($location['deleted'] == 'Y' || $location['name'] === ''))
			{
				CCalendarLocation::delete($location['id']);
			}
			elseif ((!$location['id'] || $location['changed'] == 'Y') && $location['name'] !== '')
			{
				CCalendarLocation::update(array(
					'id' => $location['id'],
					'name' => $location['name']
				));
			}
		}

		CCalendarLocation::clearCache();

		self::OutputJSRes(self::$reqId,
			array(
				'result' => true,
				'locationList' => CCalendarLocation::getList()
			)
		);
	}

	public static function getTrackingSections()
	{
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$codes = $request->get('codes');
		$mode = $request->get('type');
		$users = array();

		if ($mode == 'users')
		{
			$userIds = array();
			$users = CCalendar::GetDestinationUsers($codes, true);
			foreach($users as $user)
			{
				$userIds[] = $user['ID'];
			}

			$sections = CCalendarSect::GetSuperposedList(array(
				'USERS' => $userIds
			));
		}
		elseif($mode == 'groups')
		{
			$groupIds = array();
			foreach($codes as $code)
			{
				if (substr($code, 0, 2) === 'SG')
				{
					$groupIds[] = intval(substr($code, 2));
				}
			}

			$sections = CCalendarSect::GetSuperposedList(array(
				'GROUPS' => $groupIds
			));
		}
		else
		{
			$types = array();
			$typesRes = CCalendarType::GetList();
			foreach($typesRes as $type)
			{
				if ($type['XML_ID'] != 'user' && $type['XML_ID'] !== 'group' && $type['XML_ID'] !== 'location')
				{
					$types[] = $type['XML_ID'];
				}
			}

			$sections = CCalendarSect::GetSuperposedList(array(
				'TYPES' => $types
			));
		}

		self::OutputJSRes(self::$reqId,
			array(
				'result' => true,
				'users' => $users,
				'sections' => $sections
			)
		);
	}

	public static function setTrackingSections()
	{
		$userId = CCalendar::GetCurUserId();
		if (self::$request['type'] == 'users')
		{
			$codes = self::$request['codes'];
			$users = CCalendar::GetDestinationUsers($codes);


			CCalendarUserSettings::setTrackingUsers($userId, $users);
		}
		elseif(self::$request['type'] == 'groups')
		{
			$codes = self::$request['codes'];
			$groupIds = array();
			foreach($codes as $code)
			{
				if (substr($code, 0, 2) === 'SG')
				{
					$groupIds[] = intval(substr($code, 2));
				}
			}
			CCalendarUserSettings::setTrackingGroups($userId, $groupIds);
		}

		CCalendar::SetDisplayedSuperposed($userId, self::$request['sect']);

		self::OutputJSRes(self::$reqId, array('result' => true));
	}

	public static function getSettingsSlider()
	{
		global $APPLICATION;
		$APPLICATION->ShowAjaxHead();

		$jsId = preg_replace('/[^\d|\w\_]/', '', self::$request['unique_id']);

		$APPLICATION->IncludeComponent("bitrix:calendar.settings.slider", "", array(
			'id' => $jsId,
			'is_personal' => self::$request['is_personal'] == 'Y',
			'show_general_settings' => self::$request['show_general_settings'] == 'Y'
		));

		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	}

	public static function getDestinationItems()
	{
		self::OutputJSRes(self::$reqId,
			array(
				'result' => true,
				'destinationItems' => CCalendar::GetSocNetDestination(false, self::$request['codes'])
			)
		);
	}

	public static function getFilterData()
	{
		$fields = CalendarFilter::resolveFilterFields(CalendarFilter::getFilterId(CCalendar::GetType(), CCalendar::GetOwnerId(), CCalendar::GetCurUserId()));

		$parseRecursion = false;
		$counters = false;
		$arFilter = array(
			'OWNER_ID' => CCalendar::GetOwnerId(),
			'CAL_TYPE' => CCalendar::GetType()
		);

		if (isset($fields['fields']['IS_MEETING']))
		{
			$arFilter['IS_MEETING'] = $fields['fields']['IS_MEETING'] == 'Y';
		}
		if (isset($fields['fields']['MEETING_STATUS']))
		if (isset($fields['fields']['MEETING_STATUS']))
		{
			$arFilter['MEETING_STATUS'] = $fields['fields']['MEETING_STATUS'];
			$arFilter['IS_MEETING'] = true;

			if ($fields['presetId'] == 'filter_calendar_meeting_status_q')
			{
				$arFilter['FROM_LIMIT'] = CCalendar::Date(time(), false);
				$arFilter['TO_LIMIT'] = CCalendar::Date(time() + CCalendar::DAY_LENGTH * 90, false);
				CCalendar::UpdateCounter(array(CCalendar::GetOwnerId()));
				$counters = array(
					'invitation' => CUserCounter::GetValue($arFilter['OWNER_ID'], 'calendar')
				);
			}
		}
		if (isset($fields['fields']['CREATED_BY']))
		{
			unset($arFilter['OWNER_ID'], $arFilter['CAL_TYPE']);
			$arFilter['MEETING_HOST'] = $fields['fields']['CREATED_BY'];
			// mantis: 93743
			$arFilter['CREATED_BY'] = CCalendar::GetCurUserId();
			$arFilter['IS_MEETING'] = true;
		}
		if (isset($fields['fields']['ATTENDEES']))
		{
			$arFilter['OWNER_ID'] = $fields['fields']['ATTENDEES'];
			$arFilter['IS_MEETING'] = true;
		}

		$fromTs = 0;
		$toTs = 0;
		if (isset($fields['fields']['DATE_FROM']))
		{
			$fromTs = CCalendar::Timestamp($fields['fields']['DATE_FROM'], true, false);
			$arFilter['FROM_LIMIT'] = CCalendar::Date($fromTs, false);
		}
		if (isset($fields['fields']['DATE_TO']))
		{
			$toTs = CCalendar::Timestamp($fields['fields']['DATE_TO'], true, false);
			$arFilter['TO_LIMIT'] = CCalendar::Date($toTs, false);
			if ($fromTs && $toTs < $fromTs)
			{
				$arFilter['TO_LIMIT'] = $arFilter['FROM_LIMIT'];
			}
		}
		if ($fromTs && $toTs && $fromTs <= $toTs)
		{
			$parseRecursion = true;
		}

		if (isset($fields['search']) && $fields['search'])
		{
			$arFilter[(CCalendarEvent::isFullTextIndexEnabled() ? '*' : '*%').'SEARCHABLE_CONTENT'] = CCalendarEvent::prepareToken($fields['search']);
		}

		$entries = CCalendarEvent::GetList(
			array(
				'arFilter' => $arFilter,
				'fetchAttendees' => true,
				'parseRecursion' => $parseRecursion,
				'maxInstanceCount' => 50,
				'preciseLimits' => $parseRecursion,
				'userId' => CCalendar::GetCurUserId(),
				'fetchMeetings' => true,
				'setDefaultLimit' => false
			)
		);

		self::OutputJSRes(self::$reqId,
			array(
				'result' => true,
				'entries' => $entries,
				'counters' => $counters
			)
		);
	}
}
?>