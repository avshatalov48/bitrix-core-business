<?
class CCalendarReminder
{
	public static function ReminderAgent($eventId = 0, $userId = 0, $viewPath = '', $calendarType = '', $ownerId = 0, $index = 0)
	{
		if ($eventId > 0 && $userId > 0 && $calendarType != '' && \Bitrix\Main\Loader::includeModule("im"))
		{
			$event = false;
			$skipReminding = false;
			$nowTime = time();
			//$nowTime = CCalendar::Timestamp('01.01.2018 12:00:00');
			$bTmpUser = CCalendar::TempUser(false, true);
			$minReminderOffset = 30;

			// We have to use this to set timezone offset to local user's timezone
			CCalendar::SetOffset(false, CCalendar::GetOffset($userId));

			$events = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"ID" => $eventId,
						"DELETED" => "N",
						"FROM_LIMIT" => CCalendar::Date($nowTime - 3600, false),
						"TO_LIMIT" => CCalendar::Date(CCalendar::GetMaxTimestamp(), false),
						"ACTIVE_SECTION" => "Y"
					),
					'parseRecursion' => true,
					'maxInstanceCount' => 3,
					'preciseLimits' => true,
					'fetchAttendees' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false
				)
			);

			if ($events && is_array($events[0]))
				$event = $events[0];

			if ($event && $event['IS_MEETING'])
			{
				$attendees = CCalendarEvent::GetAttendees($event['PARENT_ID']);
				$attendees = $attendees[$event['PARENT_ID']];
				foreach($attendees as $attendee)
				{
					// If current user is an attendee but his status is 'N' we don't take care about reminding
					if ($attendee['USER_ID'] == $userId && $attendee['STATUS'] == 'N')
					{
						$skipReminding = true;
						break;
					}
				}
			}

			if ($event && $event['DELETED'] != 'Y' && !$skipReminding)
			{
				// Get Calendar Info
				$section = CCalendarSect::GetById($event['SECT_ID'], false);
				if ($section)
				{
					$arNotifyFields = array(
						'FROM_USER_ID' => $userId,
						'TO_USER_ID' => $userId,
						'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
						'NOTIFY_MODULE' => "calendar",
						'NOTIFY_EVENT' => "reminder",
						'NOTIFY_TAG' => "CALENDAR|INVITE|".$eventId."|".$userId."|REMINDER",
						'NOTIFY_SUB_TAG' => "CALENDAR|INVITE|".$eventId
					);

					$fromTs = CCalendar::Timestamp($event['DATE_FROM'], false, $event['DT_SKIP_TIME'] !== 'Y');
					if ($event['DT_SKIP_TIME'] !== 'Y')
					{
						$fromTs -= $event['~USER_OFFSET_FROM'];
					}
					$arNotifyFields['MESSAGE'] = GetMessage('EC_EVENT_REMINDER', Array(
						'#EVENT_NAME#' => $event["NAME"],
						'#DATE_FROM#' => CCalendar::Date($fromTs, $event['DT_SKIP_TIME'] !== 'Y', true, true)
					));

					$sectionName = $section['NAME'];
					$ownerName = CCalendar::GetOwnerName($calendarType, $ownerId);
					if ($calendarType == 'user' && $ownerId == $userId)
						$arNotifyFields['MESSAGE'] .= ' '.GetMessage('EC_EVENT_REMINDER_IN_PERSONAL', Array('#CALENDAR_NAME#' => htmlspecialcharsbx($sectionName)));
					else if($calendarType == 'user')
						$arNotifyFields['MESSAGE'] .= ' '.GetMessage('EC_EVENT_REMINDER_IN_USER', Array('#CALENDAR_NAME#' => $sectionName, '#USER_NAME#' => $ownerName));
					else if($calendarType == 'group')
						$arNotifyFields['MESSAGE'] .= ' '.GetMessage('EC_EVENT_REMINDER_IN_GROUP', Array('#CALENDAR_NAME#' => $sectionName, '#GROUP_NAME#' => $ownerName));
					else
						$arNotifyFields['MESSAGE'] .= ' '.GetMessage('EC_EVENT_REMINDER_IN_COMMON', Array('#CALENDAR_NAME#' => $sectionName, '#IBLOCK_NAME#' => $ownerName));

					if ($viewPath != '')
					{
						$viewPath = CHTTP::urlDeleteParams($viewPath, array("EVENT_DATE"));
						$viewPath = CHTTP::urlAddParams($viewPath, array('EVENT_DATE' => CCalendar::Date($fromTs, false)));
						$arNotifyFields['MESSAGE'] .= "\n".GetMessage('EC_EVENT_REMINDER_DETAIL', Array('#URL_VIEW#' => $viewPath));
					}

					$arNotifyFields["PUSH_MESSAGE"] = GetMessage('EC_EVENT_REMINDER_PUSH', Array(
						'#EVENT_NAME#' => $event["NAME"],
						'#DATE_FROM#' => CCalendar::GetFromToHtml(
							$fromTs,
							$fromTs + $event['DT_LENGTH'],
							$event['DT_SKIP_TIME'] == 'Y',
							$event['DT_LENGTH']
						)
					));
					$arNotifyFields["PUSH_MESSAGE"] = str_replace('&ndash;', '-', $arNotifyFields["PUSH_MESSAGE"]);

					$arNotifyFields["PUSH_MESSAGE"] = substr($arNotifyFields["PUSH_MESSAGE"], 0, \CCalendarNotify::PUSH_MESSAGE_MAX_LENGTH);

					CIMNotify::Add($arNotifyFields);

					foreach(GetModuleEvents("calendar", "OnRemindEvent", true) as $arEvent)
						ExecuteModuleEventEx($arEvent, array(
							array(
								'eventId' => $eventId,
								'userId' => $userId,
								'viewPath' => $viewPath,
								'calType' => $calendarType,
								'ownerId' => $ownerId
							)
						));

					if (CCalendarEvent::CheckRecurcion($event) && ($nextEvent = $events[1]))
					{
						$remAgentParams = array(
							'eventId' => $eventId,
							'userId' => $userId,
							'viewPath' => CHTTP::urlDeleteParams($viewPath, array("EVENT_DATE")),
							'calendarType' => $calendarType,
							'ownerId' => $ownerId,
							'maxIndex' => 10
						);

						// 1. clean reminders
						self::RemoveAgent($remAgentParams);

						$startTs = CCalendar::Timestamp($nextEvent['DATE_FROM'], false, $event["DT_SKIP_TIME"] !== 'Y');
						if ($nextEvent["DT_SKIP_TIME"] == 'N' && $nextEvent["TZ_FROM"])
						{
							$startTs = $startTs - CCalendar::GetTimezoneOffset($nextEvent["TZ_FROM"], $startTs); // UTC timestamp
						}

						// 2. Set new reminders
						foreach($nextEvent['REMIND'] as $reminder)
						{
							if ($reminder)
							{
								$delta = intVal($reminder['count']) * 60; //Minute
								if ($reminder['type'] == 'hour')
									$delta = $delta * 60; //Hour
								elseif ($reminder['type'] == 'day')
									$delta =  $delta * 60 * 24; //Day

								// $startTs - UTC timestamp;  date("Z", $startTs) - offset of the server
								$agentTime = $startTs + date("Z", $startTs)  - $delta;
								if ($agentTime >= time() + $minReminderOffset)
								{
									self::AddAgent(CCalendar::Date($agentTime - $delta), $remAgentParams);
								}
							}
						}
					}
				}
			}

			CCalendar::SetOffset(false, null);

			if ($bTmpUser)
				CCalendar::TempUser($bTmpUser, false);
		}
	}

	public static function RemoveAgent($params)
	{
		// remove obsolete agents
		$res = CAgent::getList(array(), array(
			'NAME' => "CCalendar::ReminderAgent(".$params['eventId'].", ".$params['userId']."%",
			'MODULE_ID' => 'calendar')
		);
		while($item = $res->fetch())
		{
			CAgent::Delete($item['ID']);
		}
	}

	public static function AddAgent($remindTime, $params)
	{
		global $DB;
		if (strlen($remindTime) > 0 && $DB->IsDate($remindTime, false, LANG, "FULL"))
		{
			$tzEnabled = CTimeZone::Enabled();
			if ($tzEnabled)
			{
				CTimeZone::Disable();
			}
			$indexParam = '';
			if (isset($params['index']))
				$indexParam = ', '.$params['index'];

			CAgent::AddAgent(
				"CCalendar::ReminderAgent(".intVal($params['eventId']).", ".intVal($params['userId']).", '".addslashes($params['viewPath'])."', '".addslashes($params['calendarType'])."', ".intVal($params['ownerId']).$indexParam.");",
				"calendar",
				"Y",
				0,
				"",
				"Y",
				$remindTime,
				100,
				false,
				false
			);

			if ($tzEnabled)
			{
				CTimeZone::Enable();
			}
		}
	}

	public static function UpdateReminders($params = array())
	{
		$eventId = intVal($params['id']);
		$reminders = $params['reminders'];
		$arFields = $params['arFields'];
		$userId = $params['userId'];
		$minReminderOffset = 30; // In seconds

		$path = $params['path'];
		$path = CHTTP::urlDeleteParams($path, array("action", "sessid", "bx_event_calendar_request", "EVENT_ID", "EVENT_DATE"));
		$viewPath = CHTTP::urlAddParams($path, array('EVENT_ID' => $eventId));

		$remAgentParams = array(
			'eventId' => $eventId,
			'userId' => $arFields["CREATED_BY"],
			'viewPath' => $viewPath,
			'calendarType' => $arFields["CAL_TYPE"],
			'ownerId' => $arFields["OWNER_ID"],
			'maxIndex' => 10
		);

		// 1. clean reminders
		self::RemoveAgent($remAgentParams);

		// 2. Set new reminders
		$startTs = $arFields['DATE_FROM_TS_UTC']; // Start of the event in UTC
		$i = 0;

		foreach($reminders as $reminder)
		{
			$delta = intVal($reminder['count']) * 60; //Minute
			if ($reminder['type'] == 'hour')
				$delta = $delta * 60; //Hour
			elseif ($reminder['type'] == 'day')
				$delta =  $delta * 60 * 24; //Day

			// $startTs - UTC timestamp;
			// date("Z", $startTs) - offset of the server
			$agentTime = $startTs + date("Z", $startTs)  - $delta;
			$remAgentParams['index'] = $i++;

			if ($agentTime >= time() + $minReminderOffset)
			{
				self::AddAgent(CCalendar::Date($agentTime), $remAgentParams);
			}
			elseif($arFields['RRULE'] != '')
			{
				$events = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"ID" => $eventId,
							"DELETED" => "N",
							"FROM_LIMIT" => CCalendar::Date(time() - 3600, false),
							"TO_LIMIT" => CCalendar::GetMaxDate()
						),
						'userId' => $userId,
						'parseRecursion' => true,
						'maxInstanceCount' => 2,
						'preciseLimits' => true,
						'fetchAttendees' => true,
						'checkPermissions' => false,
						'setDefaultLimit' => false
					)
				);

				if ($events && is_array($events[0]))
				{
					$nextEvent = $events[0];
					$startTs = CCalendar::Timestamp($nextEvent['DATE_FROM'], false, $events[0]["DT_SKIP_TIME"] !== 'Y');
					if ($nextEvent["DT_SKIP_TIME"] == 'N' && $nextEvent["TZ_FROM"])
					{
						$startTs = $startTs - CCalendar::GetTimezoneOffset($nextEvent["TZ_FROM"], $startTs); // UTC timestamp
					}

					if (($startTs + date("Z", $startTs)) < time() && $events[1])
					{
						$nextEvent = $events[1];
					}

					$startTs = CCalendar::Timestamp($nextEvent['DATE_FROM'], false, $events[0]["DT_SKIP_TIME"] !== 'Y');
					if ($nextEvent["DT_SKIP_TIME"] == 'N' && $nextEvent["TZ_FROM"])
					{
						$startTs = $startTs - CCalendar::GetTimezoneOffset($nextEvent["TZ_FROM"], $startTs); // UTC timestamp
					}

					$reminder = $nextEvent['REMIND'][0];
					if ($reminder)
					{
						$delta = intVal($reminder['count']) * 60; //Minute
						if ($reminder['type'] == 'hour')
							$delta = $delta * 60; //Hour
						elseif ($reminder['type'] == 'day')
							$delta =  $delta * 60 * 24; //Day

						// $startTs - UTC timestamp;
						// date("Z", $startTs) - offset of the server
						$agentTime = $startTs + date("Z", $startTs)  - $delta;
						if ($agentTime >= time() + $minReminderOffset)
						{
							self::AddAgent(CCalendar::Date($agentTime), $remAgentParams);
						}
					}
				}
			}
		}
	}
}
?>