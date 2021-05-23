<?
use \Bitrix\Calendar\Ui\CalendarFilter;
use Bitrix\Main\Localization\Loc;

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
			$sectId = intval($_GET['sec_id']);
			if ($_GET['check'] == 'Y') // Just for access check from calendar interface
			{
				$APPLICATION->RestartBuffer();
				if (CCalendarSect::CheckSign($_GET['sign'], intval($_GET['user']), $sectId > 0 ? $sectId : 'superposed_calendars'))
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
					'userId' => intval($_GET['user']),
					'sign' => $_GET['sign'],
					'type' => $_GET['type'],
					'ownerId' => intval($_GET['owner'])
				));
			}
			else
			{
				$APPLICATION->RestartBuffer();
			}
		}
		else
		{
			// Check the access
			if (!CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()) || !check_bitrix_sessid())
			{
				$APPLICATION->ThrowException(Loc::getMessage("EC_ACCESS_DENIED"));
				return false;
			}

			$APPLICATION->ShowAjaxHead();
			$APPLICATION->RestartBuffer();
			self::$reqId = intval($_REQUEST['reqId']);

			switch ($action)
			{
				case 'delete_entry':
					self::deleteEntry();
					break;
				case 'delete':
					self::DeleteEvent();
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

				case 'disconnect_google':
					self::DisconnectGoogle();
					break;
				case 'clear_sync_info':
					self::ClearSynchronizationInfo();
					break;

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
			$reqId = intval($_REQUEST['reqId']);
		if (!$reqId)
			return;
		?>
		<script>top.BXCRES['<?= $reqId?>'] = <?= CUtil::PhpToJSObject($res)?>;</script>
		<?
	}

	public static function deleteEntry()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));

		$res = CCalendar::DeleteEvent(intval(self::$request['entry_id']),
			true,
			array('recursionMode' => self::$request['recursion_mode'])
		);

		if ($res !== true)
			return CCalendar::ThrowError($res <> '' ? $res : Loc::getMessage('EC_EVENT_DEL_ERROR'));

		self::OutputJSRes(self::$reqId, true);
	}

	public static function DeleteEvent()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));

		$res = CCalendar::DeleteEvent(intval($_POST['id']), true, array('recursionMode' => $_REQUEST['rec_mode']));

		if ($res !== true)
			return CCalendar::ThrowError($res <> '' ? $res : Loc::getMessage('EC_EVENT_DEL_ERROR'));

		self::OutputJSRes(self::$reqId, true);
	}

	public static function SaveSettings()
	{
		// Personal
		\Bitrix\Calendar\UserSettings::set($_REQUEST['user_settings']);

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
			'eventId' => intval($_REQUEST['event_id']),
			'parentId' => intval($_REQUEST['parent_id']),
			'status' => in_array($_REQUEST['status'], array('Q', 'Y', 'N')) ? $_REQUEST['status'] : 'Q',
			'reccurentMode' => in_array($_REQUEST['reccurent_mode'], array('this', 'next', 'all')) ? $_REQUEST['reccurent_mode'] : false,
			'currentDateFrom' => CCalendar::Date(CCalendar::Timestamp($_REQUEST['current_date_from']), false)
		));

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
				'curEventId' => intval($_POST['cur_event_id']),
				'getFromHR' => true
		));
		self::OutputJSRes(self::$reqId, array('data' => $res));
	}

	public static function GetMeetingRoomAccessibility()
	{
		$res = CCalendar::GetAccessibilityForMeetingRoom(array(
				'id' => intval($_POST['id']),
				'from' => CCalendar::Date(CCalendar::Timestamp($_POST['from'])),
				'to' => CCalendar::Date(CCalendar::Timestamp($_POST['to'])),
				'curEventId' => intval($_POST['cur_event_id'])
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

		if (intval($_POST['id']) > 0)
			$Params['ID'] = intval($_POST['id']);

		$settings = CCalendar::GetSettings(array('request' => false));
		$Params['RMiblockId'] = $settings['rm_iblock_id'];
		$Params['mrid'] = $loc_new['mrid'];
		$Params['mrevid_old'] = $loc_old ? $loc_old['mrevid'] : 0;
		$check = CCalendar::CheckMeetingRoom($Params);

		self::OutputJSRes(self::$reqId, array('check' => $check));
	}

	public static function DisconnectGoogle()
	{
		if (CCalendar::GetType() == 'user' && (CCalendar::IsCalDAVEnabled() || CCalendar::isGoogleApiEnabled()))
		{
			CCalendar::RemoveConnection(array('id' => (int)$_POST['connectionId'], 'del_calendars' => 'Y'));
			self::OutputJSRes(self::$reqId, array('result' => true));
		}
	}

	public static function ClearSynchronizationInfo()
	{
		CCalendar::ClearSyncInfo(CCalendar::GetUserId(), $_POST['sync_type']);
		self::OutputJSRes(self::$reqId, array('result' => true));
	}

	public static function GetPlanner()
	{
		global $APPLICATION;
		$APPLICATION->ShowAjaxHead();

		$plannerId = $_REQUEST['planner_id'];
		?><?CCalendarPlanner::Init(array('id' => $plannerId));?><?
	}

	public static function UpdatePlanner()
	{
		$curEventId = intval(self::$request['cur_event_id']);
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
			return CCalendar::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));

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
					'recursionEditMode' => 'skip',
					'editParentEvents' => true,
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

						if (isset($recParentEvent['RRULE']['COUNT']))
						{
							unset($recParentEvent['RRULE']['COUNT']);
						}

						$id = CCalendar::SaveEvent(array(
							'arFields' => array(
								"ID" => $recParentEvent["ID"],
								"RRULE" => $recParentEvent['RRULE']
							),
							'silentErrorMode' => false,
							'recursionEditMode' => 'skip',
							'editParentEvents' => true,
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
						CCalendar::DeleteEvent(intval($ev['ID']), true, array('recursionMode' => 'this'));
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
			return CCalendar::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));

		CCalendarEvent::ExcludeInstance($_POST['event_id'], $_POST['exclude_date']);

		self::OutputJSRes(self::$reqId, array('result' => true));
	}

	public static function updateLocationList()
	{
		if (CCalendar::GetReadonlyMode() || !CCalendarType::CanDo('calendar_type_view', CCalendar::GetType()))
			return CCalendar::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));

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