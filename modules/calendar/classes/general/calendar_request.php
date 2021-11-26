<?
use \Bitrix\Calendar\Ui\CalendarFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Ui\CountersManager;

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
				case 'set_meeting_status':
					self::SetStatus();
					break;
				case 'get_planner':
					self::GetPlanner();
					break;
				case 'update_planner':
					self::UpdatePlanner();
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
				$counters = CountersManager::getValues((int)$arFilter['OWNER_ID']);
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