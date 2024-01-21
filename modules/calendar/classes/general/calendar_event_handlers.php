<?

use Bitrix\Calendar\UserSettings;

IncludeModuleLangFile(__FILE__);

/*
RegisterModuleDependences('intranet', 'OnPlannerInit', 'calendar', 'CCalendarEventHandlers', 'OnPlannerInit');
RegisterModuleDependences('intranet', 'OnPlannerAction', 'calendar', 'CCalendarEventHandlers', 'OnPlannerAction');
*/

class CCalendarEventHandlers
{
	public static function OnPlannerInit($params)
	{
		global $USER, $DB, $CACHE_MANAGER;

		if (!isset($params['USER_ID']) || (int)$params['USER_ID'] <= 0)
		{
			if (!is_object($USER))
			{
				return false;
			}
			$userId = $USER->GetID();
		}
		else
		{
			$userId = $params['USER_ID'];
		}

		if ($userId <= 0)
		{
			return false;
		}
		
		$arEvents = [];
		$eventTime = -1;
		
		$now = time() + CCalendar::GetOffset($userId);
		
		if (($params['FULL'] ?? null) !== true)
		{
			$eventTimeCalculated = self::calculateEventTime($userId);

			CJSCore::RegisterExt('calendar_planner_handler', array(
				'js' => '/bitrix/js/calendar/core_planner_handler.js',
				'css' => '/bitrix/js/calendar/core_planner_handler.css',
				'lang' => BX_ROOT.'/modules/calendar/lang/'.LANGUAGE_ID.'/core_planner_handler.php',
				'rel' => array('date', 'timer')
			));
			
			return [
				'DATA' => [
					'CALENDAR_ENABLED' => true,
					'EVENTS' => $arEvents,
					'EVENT_TIME' => $eventTimeCalculated < 0
						? ''
						: (FormatDate(IsAmPmMode() ? "g:i a" : "H:i", $eventTimeCalculated))
					,
				],
				'SCRIPTS' => ['calendar_planner_handler']
			];
		}

		$CACHE_MANAGER->RegisterTag('calendar_user_'.$userId);
		$pathToCalendar = CHTTP::urlDeleteParams(CCalendar::GetPathForCalendarEx($userId), [
			'action',
			'sessid',
			'bx_event_calendar_request',
			'EVENT_ID'
		]);

		$date_from = CCalendar::Date(time() - date('Z') + CCalendar::GetCurrentOffsetUTC($userId), false);
		$ts_date_from = CCalendar::Timestamp($date_from) - CCalendar::GetCurrentOffsetUTC($userId);
		$date_from = CCalendar::Date($ts_date_from);
		$ts_date_to = $ts_date_from + CCalendar::GetDayLen() - 1;
		$date_to = $date_from;
		
		$arNewEvents = CCalendarEvent::GetList([
			'arFilter' => [
				'CAL_TYPE' => 'user',
				'OWNER_ID' => $userId,
				'FROM_LIMIT' => $date_from,
				'TO_LIMIT' => $date_to,
				'ACTIVE_SECTION' => 'Y'
			],
			'arSelect' => \CCalendarEvent::$defaultSelectEvent,
			'parseRecursion' => true,
			'preciseLimits' => true,
			'userId' => $userId,
			'skipDeclined' => true,
			'fetchAttendees' => false,
			'fetchMeetings' => true,
			'getUserfields' => false,
		]);

		if (!empty($arNewEvents))
		{
			$today = ConvertTimeStamp($now, 'SHORT');

			$format = $DB::dateFormatToPHP(IsAmPmMode() ? 'H:MI T' : 'HH:MI');

			foreach ($arNewEvents as $arEvent)
			{
				if ($arEvent['IS_MEETING'] && $arEvent['MEETING_STATUS'] === 'N')
				{
					continue;
				}

				$fromTo = CCalendarEvent::GetEventFromToForUser($arEvent, $userId);

				$ts_from = $fromTo['TS_FROM'];

				$ts_from_utc = $arEvent['DATE_FROM_TS_UTC'];
				$ts_to_utc = $arEvent['DATE_TO_TS_UTC'];

				if ($arEvent['RRULE'])
				{
					$ts_from_utc = $fromTo['TS_FROM'] - CCalendar::GetCurrentOffsetUTC($userId);
					$ts_to_utc = $ts_from_utc + $arEvent['DT_LENGTH'];
				}

				if ($arEvent['RRULE'] && ($ts_to_utc <= $ts_date_from || $ts_from_utc >= $ts_date_to))
				{
					continue;
				}

				if(($eventTime < 0 || $eventTime > $ts_from) && $ts_from >= $now)
				{
					$eventTime = $ts_from;
				}

				if($params['FULL'])
				{
					$eventPath = CHTTP::urlAddParams($pathToCalendar, [
						'EVENT_ID' => $arEvent['ID'],
						'EVENT_DATE' => $today
					]);
					$arEvents[] = [
						'ID' => $arEvent['ID'],
						'CAL_TYPE' => 'user',
						'OWNER_ID' => $userId,
						'CREATED_BY' => $arEvent['CREATED_BY'],
						'NAME' => $arEvent['NAME'],
						'DATE_FROM' => $fromTo['DATE_FROM'],
						'DATE_TO' => $fromTo['DATE_TO'],
						'TIME_FROM' => FormatDate($format, $fromTo['TS_FROM']),
						'TIME_TO' => FormatDate($format, $fromTo['TS_TO']),
						'IMPORTANCE' => $arEvent['IMPORTANCE'],
						'ACCESSIBILITY' => $arEvent['ACCESSIBILITY'],
						'DATE_FROM_TODAY' => $today === ConvertTimeStamp($fromTo['TS_FROM'], 'SHORT'),
						'DATE_TO_TODAY' => $today === ConvertTimeStamp($fromTo['TS_TO'], 'SHORT'),
						'SORT' => $fromTo['TS_FROM'],
						'EVENT_PATH' => $eventPath
					];
				}
			}
		}

		// Sort
		usort($arEvents, array('CCalendarEventHandlers', 'DateSort'));

		CJSCore::RegisterExt('calendar_planner_handler', array(
			'js' => '/bitrix/js/calendar/core_planner_handler.js',
			'css' => '/bitrix/js/calendar/core_planner_handler.css',
			'lang' => BX_ROOT.'/modules/calendar/lang/'.LANGUAGE_ID.'/core_planner_handler.php',
			'rel' => array('date', 'timer')
		));

		return [
			'DATA' => [
				'CALENDAR_ENABLED' => true,
				'EVENTS' => $arEvents,
				'EVENT_TIME' => $eventTime < 0 ? '' : (FormatDate(IsAmPmMode() ? "g:i a" : "H:i", $eventTime)),
			],
			'SCRIPTS' => ['calendar_planner_handler']
		];
	}

	private static function calculateEventTime($userId)
	{
		$now = time() + CCalendar::GetOffset($userId);
		$eventTime = -1;

		$date_from = CCalendar::Date(time() - date('Z') + CCalendar::GetCurrentOffsetUTC($userId), false);
		$ts_date_from = CCalendar::Timestamp($date_from) - CCalendar::GetCurrentOffsetUTC($userId);
		$date_from = CCalendar::Date($ts_date_from);
		$ts_date_to = $ts_date_from + CCalendar::GetDayLen() - 1;
		$date_to = $date_from;

		$arNewEvents = CCalendarEvent::GetList([
			'arFilter' => [
				'CAL_TYPE' => 'user',
				'OWNER_ID' => $userId,
				'FROM_LIMIT' => $date_from,
				'TO_LIMIT' => $date_to,
				'ACTIVE_SECTION' => 'Y'
			],
			'arSelect' => [
				'OWNER_ID',
				'SECTION_ID',
				'DATE_FROM',
				'DATE_TO',
				'TZ_FROM',
				'TZ_TO',
				'TZ_OFFSET_FROM',
				'TZ_OFFSET_TO',
				'DATE_FROM_TS_UTC',
				'DATE_TO_TS_UTC',
				'DT_SKIP_TIME',
				'DT_LENGTH',
				'CAL_TYPE',
				'MEETING_STATUS',
				'IS_MEETING',
				'RRULE',
				'EXDATE',
			],
			'parseRecursion' => true,
			'preciseLimits' => true,
			'userId' => $userId,
			'skipDeclined' => true,
			'fetchAttendees' => false,
			'getUserfields' => false,
			'checkPermissions' => false,
		]);

		if (!empty($arNewEvents))
		{
			foreach ($arNewEvents as $arEvent)
			{
				if ($arEvent['IS_MEETING'] && $arEvent['MEETING_STATUS'] === 'N')
				{
					continue;
				}

				$fromTo = CCalendarEvent::GetEventFromToForUser($arEvent, $userId);
				$ts_from = $fromTo['TS_FROM'];

				$ts_from_utc = $arEvent['DATE_FROM_TS_UTC'];
				$ts_to_utc = $arEvent['DATE_TO_TS_UTC'];

				if ($arEvent['RRULE'])
				{
					$ts_from_utc = $fromTo['TS_FROM'] - CCalendar::GetCurrentOffsetUTC($userId);
					$ts_to_utc = $ts_from_utc + $arEvent['DT_LENGTH'];
				}

				if ($arEvent['RRULE'] && ($ts_to_utc <= $ts_date_from || $ts_from_utc >= $ts_date_to))
				{
					continue;
				}

				if(($eventTime < 0 || $eventTime > $ts_from) && $ts_from >= $now)
				{
					$eventTime = $ts_from;
				}
			}
		}

		return $eventTime;
	}

	public static function OnPlannerAction($action, $params)
	{
		switch($action)
		{
			case 'calendar_add':

				return self::plannerActionAdd(array(
					'NAME' => $_REQUEST['name'],
					'FROM' => $_REQUEST['from'],
					'TO' => $_REQUEST['to'],
					'ABSENCE' => $_REQUEST['absence']
				));

			break;

			case 'calendar_show':

				return self::plannerActionShow(array(
					'ID' => (int)$_REQUEST['id'],
					'SITE_ID' => $params['SITE_ID']
				));

			break;
		}
	}

	protected static function getEvent($arParams)
	{
		global $USER;

		$userId = $USER->GetID();
		$date_from = CCalendar::Date(time() - date('Z') + CCalendar::GetCurrentOffsetUTC(), false);
		$ts_date_from = CCalendar::Timestamp($date_from) - CCalendar::GetCurrentOffsetUTC();
		$date_from = CCalendar::Date($ts_date_from);
		$date_to = $date_from;
		$ts_date_to = $ts_date_from + CCalendar::GetDayLen() - 1;

		$res = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					"ID" => $arParams['ID'],
					"FROM_LIMIT" => $date_from,
					"TO_LIMIT" => $date_to
				),
				'parseRecursion' => true,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'skipDeclined' => true
			)
		);

		$arEvents = array();
		foreach ($res as $arEvent)
		{
			if ($arEvent['IS_MEETING'] && $arEvent['MEETING_STATUS'] === 'N')
			{
				continue;
			}
			$fromTo = CCalendarEvent::GetEventFromToForUser($arEvent, $userId);

			if ($arEvent['RRULE'])
			{
				$ts_from_utc = $fromTo['TS_FROM'] - CCalendar::GetCurrentOffsetUTC();
				$ts_to_utc = $ts_from_utc + $arEvent['DT_LENGTH'];
				if ($ts_to_utc <= $ts_date_from || $ts_from_utc >= $ts_date_to)
				{
					continue;
				}
			}

			$arEvents[] = $arEvent;
		}


		if (is_array($arEvents) && !empty($arEvents))
		{
			$arEvent = $arEvents[0];

			$arEvent['GUESTS'] = array();
			if ($arEvent['IS_MEETING'] && is_array($arEvent['ATTENDEE_LIST']))
			{
				$userIndex = CCalendarEvent::getUserIndex();
				foreach ($arEvent['ATTENDEE_LIST'] as $attendee)
				{
					if (isset($userIndex[$attendee["id"]]))
					{
						$arEvent['GUESTS'][] = array(
							'id' => $attendee['id'],
							'name' => $userIndex[$attendee["id"]]['DISPLAY_NAME'],
							'status' => $attendee['status'],
							'accessibility' => $arEvent['ACCESSIBILITY'],
							'bHost' => (int)$attendee['id'] === (int)$arEvent['MEETING_HOST'],
						);

						if ((int)$attendee['id'] === (int)$USER->GetID())
						{
							$arEvent['STATUS'] = $attendee['status'];
						}
					}
				}
			}

			$set = CCalendar::GetSettings();
			$url = str_replace(
				'#user_id#', $arEvent['CREATED_BY'], $set['path_to_user_calendar']
			).'?EVENT_ID='.$arEvent['ID'];

			$fromTo = CCalendarEvent::GetEventFromToForUser($arEvent, $USER->GetID());

			return [
				'ID' => $arEvent['ID'],
				'NAME' => $arEvent['NAME'],
				'DETAIL_TEXT' => $arEvent['DESCRIPTION'],
				'DATE_FROM' => $fromTo['DATE_FROM'],
				'DATE_TO' => $fromTo['DATE_TO'],
				'ACCESSIBILITY' => $arEvent['ACCESSIBILITY'],
				'IMPORTANCE' => $arEvent['IMPORTANCE'],
				'STATUS' => $arEvent['STATUS'],
				'IS_MEETING' => $arEvent['IS_MEETING'] ? 'Y' : 'N',
				'GUESTS' => $arEvent['GUESTS'],
				'UF_WEBDAV_CAL_EVENT' => $arEvent['UF_WEBDAV_CAL_EVENT'],
				'URL' => $url,
			];
		}
	}

	protected static function MakeDateTime($date, $time)
	{
		global $DB;

		if (!IsAmPmMode())
		{
			$date_start = FormatDate(
				$DB::DateFormatToPhp(FORMAT_DATETIME),
				MakeTimeStamp(
					$date.' '.$time,
					FORMAT_DATE.' HH:MI'
				)
			);
		}
		else
		{
			$date_start = FormatDate(
				$DB::DateFormatToPhp(FORMAT_DATETIME),
				MakeTimeStamp(
					$date.' '.$time,
					FORMAT_DATE.' H:MI T'
				)
			);
		}

		return $date_start;
	}

	protected static function plannerActionAdd($arParams)
	{
		global $USER;
		$today = ConvertTimeStamp(time() + CCalendar::GetOffset(), 'SHORT');
		$userId = $USER->GetID();
		$userSettings = UserSettings::get($userId);
		$reminderList = $userSettings['defaultReminders']['withTime'];
		$data = [
			'CAL_TYPE' => 'user',
			'OWNER_ID' => $USER->GetID(),
			'NAME' => $arParams['NAME'],
			'DT_FROM' => self::MakeDateTime($today, $arParams['FROM']),
			'DT_TO' => self::MakeDateTime($today, $arParams['TO']),
			'SECTIONS' => CCalendar::GetMeetingSection($userId, true),
			'ATTENDEES_CODES' => ['U' . $userId],
			'ATTENDEES' => [$userId],
			'MEETING_HOST' => $userId,
			'REMIND' => $reminderList,
		];

		if ($arParams['ABSENCE'] === 'Y')
		{
			$data['ACCESSIBILITY'] = 'absent';
		}

		CCalendar::SaveEvent(array(
			'arFields' => $data,
			'userId' => $userId
		));
	}

	protected static function plannerActionShow($arParams)
	{
		global $DB, $USER;

		$res = false;

		if($arParams['ID'] > 0)
		{
			$event = self::getEvent(array(
				'ID' => $arParams['ID'],
				'SITE_ID' => $arParams['SITE_ID']
			));
			
			if ($event)
			{
				$today = ConvertTimeStamp(time() + \CCalendar::GetOffset(), 'SHORT');
				$now = time();

				$res = array(
					'ID' => $event['ID'],
					'NAME' => $event['NAME'],
					'DESCRIPTION' => CCalendarEvent::ParseText($event['DETAIL_TEXT'], $event['ID'], $event['UF_WEBDAV_CAL_EVENT']),
					'URL' => '/company/personal/user/'.$USER->GetID().'/calendar/?EVENT_ID=' .$event['ID'],
					'DATE_FROM' => MakeTimeStamp($event['DATE_FROM']),
					'DATE_TO' => MakeTimeStamp($event['DATE_TO']),
					'STATUS' => $event['STATUS'],
				);

				$res['DATE_FROM_TODAY'] = ConvertTimeStamp($res['DATE_FROM'],'SHORT') == $today;
				$res['DATE_TO_TODAY'] = ConvertTimeStamp($res['DATE_TO'], 'SHORT') == $today;

				if ($res['DATE_FROM_TODAY'])
				{
					if (IsAmPmMode())
					{
						$res['DATE_F'] = FormatDate("today g:i a", $res['DATE_FROM']);
						$res['DATE_T'] = FormatDate("g:i a", $res['DATE_TO']);
					}
					else
					{
						$res['DATE_F'] = FormatDate("today H:i", $res['DATE_FROM']);
						$res['DATE_T'] = FormatDate("H:i", $res['DATE_TO']);
					}

					if ($res['DATE_TO_TODAY'])
						$res['DATE_F'] .= ' - '.$res['DATE_T'];

					if ($res['DATE_FROM'] > $now)
					{

						$res['DATE_F_TO'] = GetMessage('TM_IN').' '.FormatDate('Hdiff', time()*2-($res['DATE_FROM'] - \CCalendar::GetOffset()));
					}
				}
				else if ($res['DATE_TO_TODAY'])
				{
					$res['DATE_F'] = FormatDate(str_replace(
						array('#today#', '#time#'),
						array('today', 'H:i'),
						GetMessage('TM_TILL')
					), $res['DATE_TO']);
				}
				else
				{
					$fmt = preg_replace('/:s$/', '', $DB::DateFormatToPHP(CSite::GetDateFormat("FULL")));
					$res['DATE_F'] = FormatDate($fmt, $res['DATE_FROM']);
					$res['DATE_F_TO'] = FormatDate($fmt, $res['DATE_TO']);
				}

				if ($event['IS_MEETING'] === 'Y')
				{
					$arGuests = array('Y' => array(), 'N' => array(), 'Q' => array());
					foreach ($event['GUESTS'] as $key => $guest)
					{
						$guest['url'] = str_replace(
							array('#ID#', '#USER_ID#'),
							$guest['id'],
							\COption::GetOptionString('intranet', 'path_user', '/company/personal/user/#USER_ID#/', $arParams['SITE_ID'])
						);

						if ($guest['bHost'])
						{
							$res['HOST'] = $guest;
						}
						else
						{
							$arGuests[$guest['status']][] = $guest;
						}
					}

					$res['GUESTS'] = array_merge($arGuests['Y'], $arGuests['N'], $arGuests['Q']);
				}

				if (mb_strlen($res['DESCRIPTION']) > 150)
				{
					$res['DESCRIPTION'] = CUtil::closetags(mb_substr($res['DESCRIPTION'], 0, 150)).'...';
				}

				$res = array('EVENT' => $res);
			}
		}
		else
		{
			$res = array('error' => 'event not found');
		}

		return $res;
	}

	private static function DateSort($a, $b)
	{
		if ($a['SORT'] == $b['SORT'])
		{
			return 0;
		}
		if ($a['SORT'] < $b['SORT'])
		{
			return -1;
		}
		return 1;
	}
}
?>