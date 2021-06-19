<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\Internals;

class CCalendarReminder
{
	const TYPE_DAY_BEFORE = 'daybefore';
	const TYPE_SPECIFIC_DATETIME = 'date';
	const SIMPLE_TYPE_LIST = ['min', 'hour', 'day'];
	const REMINDER_INACCURACY = 30;
	const REMINDER_NEXT_DELAY = 120;

	public static function ReminderAgent($eventId = 0, $userId = 0, $viewPath = '', $calendarType = '', $ownerId = 0, $index = 0)
	{
		if ($eventId > 0 && $userId > 0 && \Bitrix\Main\Loader::includeModule("im"))
		{
			$event = false;
			$nowTime = time();

			$events = CCalendarEvent::GetList([
				'arFilter' => [
					"ID" => $eventId,
					"DELETED" => "N",
					"FROM_LIMIT" => CCalendar::Date($nowTime - 3600, false),
					"TO_LIMIT" => CCalendar::Date(CCalendar::GetMaxTimestamp(), false),
					"ACTIVE_SECTION" => "Y"
				],
				'userId' => $userId,
				'parseRecursion' => true,
				'maxInstanceCount' => 3,
				'preciseLimits' => true,
				'fetchAttendees' => true,
				'checkPermissions' => false,
				'setDefaultLimit' => false
			]);

			if ($events && is_array($events[0]))
			{
				$event = $events[0];
		}

			if ($event && $event['MEETING_STATUS'] !== 'N')
			{
				$fromTs = CCalendar::Timestamp($event['DATE_FROM'], false, $event['DT_SKIP_TIME'] !== 'Y');
				if ($event['DT_SKIP_TIME'] !== 'Y')
				{
					$fromTs -= $event['~USER_OFFSET_FROM'];
				}

				if (empty($calendarType))
				{
					$calendarType = $event['CAL_TYPE'];
				}

				$viewPath = CHTTP::urlDeleteParams($viewPath, ["EVENT_DATE"]);
				$viewPath = CHTTP::urlAddParams($viewPath, ['EVENT_DATE' => CCalendar::Date($fromTs, false)]);

				CIMNotify::Add(CCalendarReminder::getNotifyFields([
					'userId' => $userId,
					'entryId' => $eventId,
					'entryName' => $event['NAME'],
					'calendarType' => $calendarType,
					'fromTs' => $fromTs,
					'dateFrom' => CCalendar::Date($fromTs, $event['DT_SKIP_TIME'] !== 'Y', true, true),
					'viewPath' => $viewPath,
					'dateFromFormatted' => CCalendar::GetFromToHtml(
						$fromTs,
						$fromTs + $event['DT_LENGTH'],
						$event['DT_SKIP_TIME'] == 'Y',
						$event['DT_LENGTH']
					),
					'index' => $index
				]));

				foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("calendar", "OnRemindEvent") as $event)
				{
					ExecuteModuleEventEx($event, [[
						'eventId' => $eventId,
						'userId' => $userId,
						'viewPath' => $viewPath,
						'calType' => $calendarType,
						'ownerId' => $ownerId
					]]);
				}

				if (CCalendarEvent::CheckRecurcion($event))
				{
					CCalendarReminder::updateReminders([
						'id' => $eventId,
						'arFields' => $event,
						'userId' => $userId,
						'path' => $viewPath,
						'updateRecursive' => true
					]);
				}
			}

			CCalendar::SetOffset(false, null);
		}
	}

	public static function RemoveAgent($params)
	{
		// remove obsolete agents
		$res = CAgent::getList([], [
			'NAME' => "CCalendar::ReminderAgent(".$params['eventId'].", ".$params['userId']."%",
			'MODULE_ID' => 'calendar'
		]);
		while($item = $res->fetch())
		{
			CAgent::Delete($item['ID']);
		}
	}

	public static function getNotifyFields($params = [])
	{
		$userId = $params['userId'];
		$entryId = $params['entryId'];

		$notifyFields = array(
			'FROM_USER_ID' => $userId,
			'TO_USER_ID' => $userId,
			'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
			'NOTIFY_MODULE' => "calendar",
			'NOTIFY_EVENT' => "reminder",
			'NOTIFY_TAG' => "CALENDAR|INVITE|".$entryId."|".$userId."|REMINDER|".$params['fromTs']."|".$params['index'],
			'NOTIFY_SUB_TAG' => "CALENDAR|INVITE|".$entryId
		);
		$notifyFields['MESSAGE'] = GetMessage('EC_EVENT_REMINDER_1', [
			'#EVENT_NAME#' => $params['entryName'],
			'#DATE_FROM#' => $params['dateFrom'],
			'#URL_VIEW#' => $params['viewPath']
		]);

		$notifyFields["PUSH_MESSAGE"] = GetMessage('EC_EVENT_REMINDER_PUSH', [
			'#EVENT_NAME#' => $params['entryName'],
			'#DATE_FROM#' => $params['dateFromFormatted']
		]);
		$notifyFields["PUSH_MESSAGE"] = str_replace('&ndash;', '-', $notifyFields["PUSH_MESSAGE"]);
		$notifyFields["PUSH_MESSAGE"] = mb_substr($notifyFields["PUSH_MESSAGE"], 0, \CCalendarNotify::PUSH_MESSAGE_MAX_LENGTH);

		return $notifyFields;
	}

	public static function AddAgent($remindTime, $params)
	{
		global $DB;
		if ($remindTime <> '' && $DB->IsDate($remindTime, false, LANG, "FULL"))
		{
			$tzEnabled = CTimeZone::Enabled();
			if ($tzEnabled)
			{
				CTimeZone::Disable();
			}
			$indexParam = isset($params['index']) ? ', '.$params['index'] : '';

			CAgent::AddAgent(
				"CCalendar::ReminderAgent(".intval($params['eventId']).", ".intval($params['userId']).", '".addslashes($params['viewPath'])."', '".addslashes($params['calendarType'])."', ".intval($params['ownerId']).$indexParam.");",
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

	public static function updateReminders($params = [])
	{
		$eventId = (int)$params['id'];
		$entryFields = $params['arFields'];
		$reminders = $params['reminders'];
		$userId = (int)$params['userId'];

		if (!isset($reminders))
		{
			$reminders = self::prepareReminder($entryFields['REMIND']);
		}

		$path = $params['path'];
		if (!empty($path))
		{
			$path = CCalendar::GetPath($entryFields['CAL_TYPE'], $entryFields['OWNER_ID'], true);
		}
		$path = CHTTP::urlDeleteParams($path, ["action", "sessid", "bx_event_calendar_request", "EVENT_ID", "EVENT_DATE"]);
		$viewPath = CHTTP::urlAddParams($path, ['EVENT_ID' => $eventId]);

		$agentParams = [
			'eventId' => $eventId,
			'userId' => $entryFields["CREATED_BY"],
			'viewPath' => $viewPath,
			'calendarType' => $entryFields["CAL_TYPE"],
			'ownerId' => $entryFields["OWNER_ID"]
		];

		// 1. clean reminders
		self::RemoveAgent($agentParams);

		// Prevent dublication of reminders for non-user's calendar context (mantis:0128287)
		if (!$entryFields['IS_MEETING'] || $entryFields['CAL_TYPE'] === 'user')
		{
			// 2. Set new reminders
			if (CCalendarEvent::CheckRecurcion($entryFields))
			{
				$entryList = CCalendarEvent::GetList(
					[
						'arFilter' => [
							"ID" => $eventId,
							"DELETED" => "N",
							"FROM_LIMIT" => CCalendar::Date(time() - 3600, false),
							"TO_LIMIT" => CCalendar::GetMaxDate()
						],
						'userId' => $userId,
						'parseRecursion' => true,
						'maxInstanceCount' => 4,
						'preciseLimits' => true,
						'fetchAttendees' => true,
						'checkPermissions' => false,
						'setDefaultLimit' => false
					]
				);

				if (is_array($entryList))
				{
					$index = 0;
					foreach ($entryList as $entry)
					{
						$eventTimestamp = CCalendar::Timestamp($entry['DATE_FROM'], false, true);
						$eventTimestamp = $eventTimestamp - CCalendar::GetTimezoneOffset(
								$entry["TZ_FROM"],
								$eventTimestamp
							) + date("Z", $eventTimestamp);

						// List of added timestamps of reminders to avoid duplication
						$addedIndex = [];
						foreach ($reminders as $reminder)
						{
							$reminderTimestamp = self::getReminderTimestamp(
								$eventTimestamp,
								$reminder,
								$entryFields['TZ_FROM']
							);

							$limitTime = isset($params['updateRecursive']) && $params['updateRecursive']
								? time() + self::REMINDER_NEXT_DELAY
								: time() - self::REMINDER_INACCURACY;

							if (
								!is_null($reminderTimestamp)
								&& !in_array($reminderTimestamp, $addedIndex)
								&& $reminderTimestamp >= $limitTime
							)
							{
								$agentParams['index'] = $index++;
								if ($reminder['type'] === self::TYPE_SPECIFIC_DATETIME)
								{
									unset($agentParams['index']);
								}
								self::AddAgent(\CCalendar::Date($reminderTimestamp), $agentParams);
								$addedIndex[] = $reminderTimestamp;
							}
						}
					}
				}
			}
			else
			{
				// Start of the event in server timezone
				$eventTimestamp = $entryFields['DATE_FROM_TS_UTC'] + date("Z", $entryFields['DATE_FROM_TS_UTC']);
				$index = 0;
				// List of added timestamps of reminders to avoid duplication
				$addedIndex = [];
				foreach ($reminders as $reminder)
				{
					$reminderTimestamp = self::getReminderTimestamp(
						$eventTimestamp,
						$reminder,
						$entryFields['TZ_FROM']
					);

					if (
						!is_null($reminderTimestamp)
						&& !in_array($reminderTimestamp, $addedIndex)
						&& $reminderTimestamp >= time() + self::REMINDER_INACCURACY
					)
					{
						$agentParams['index'] = $index++;
						self::AddAgent(\CCalendar::Date($reminderTimestamp), $agentParams);
						$addedIndex[] = $reminderTimestamp;
					}
				}
			}
		}
	}

	public static function sortReminder($a, $b)
	{
		return self::getReminderDelta($a) - self::getReminderDelta($b);
	}

	private static function getReminderDelta($reminder)
	{
		$delta = 0;
		if (is_array($reminder) && in_array($reminder['type'], self::SIMPLE_TYPE_LIST))
		{
			$delta = intval($reminder['count']) * 60;
			if ($reminder['type'] == 'hour')
			{
				$delta = $delta * 60; //Hour
			}
			elseif ($reminder['type'] == 'day')
			{
				$delta =  $delta * 60 * 24; //Day
			}
		}
		return $delta;
	}

	private static function getReminderTimestamp($eventTimestamp, $reminder, $timezoneName = null)
	{
		$reminderTimestamp = null;

		if (is_array($reminder) && isset($reminder['type']))
		{
			$type = $reminder['type'];

			if (in_array($type, self::SIMPLE_TYPE_LIST))
			{
				$delta = intval($reminder['count']) * 60;
				if ($reminder['type'] == 'hour')
				{
					$delta = $delta * 60; //Hour
				}
				elseif ($reminder['type'] == 'day')
				{
					$delta =  $delta * 60 * 24; //Day
				}

				$reminderTimestamp = $eventTimestamp - $delta;
			}
			elseif($type === self::TYPE_DAY_BEFORE)
			{
				$daysBefore = intval($reminder['before']);
				$hour = floor(intval($reminder['time']) / 60);
				$min = intval($reminder['time'] - $hour * 60);

				$reminderTimestamp = mktime($hour, $min, 0, date("m", $eventTimestamp), date("d", $eventTimestamp) - $daysBefore, date("Y", $eventTimestamp));

				if ($timezoneName)
				{
					$timezoneServerOffset = \CCalendar::GetTimezoneOffset($timezoneName, $eventTimestamp) - date("Z", $eventTimestamp);
					$reminderTimestamp -= $timezoneServerOffset;
				}
			}
			elseif($type === self::TYPE_SPECIFIC_DATETIME)
			{
				$reminderTimestamp = \CCalendar::Timestamp($reminder['value'], false, true);
				$reminderTimestamp = $reminderTimestamp - \CCalendar::GetTimezoneOffset($timezoneName, $reminderTimestamp) + date("Z", $reminderTimestamp);
			}
		}

		return $reminderTimestamp;
	}

	public static function prepareReminder($reminder = [])
	{
		$reminderList = [];
		if (is_array($reminder))
		{
			foreach ($reminder as $remindValue)
			{
				if (is_array($remindValue))
				{
					if (isset($remindValue['type']) && in_array($remindValue['type'], self::SIMPLE_TYPE_LIST))
					{
						$reminderList[] = [
							'type' => $remindValue['type'],
							'count' => intval($remindValue['count'])
						];
					}
					elseif ($remindValue['type'] === self::TYPE_DAY_BEFORE)
					{
						$reminderList[] = [
							'type' => $remindValue['type'],
							'before' => intval($remindValue['before']),
							'time' => intval($remindValue['time'])
						];
					}
					elseif ($remindValue['type'] === self::TYPE_SPECIFIC_DATETIME)
					{
						$reminderList[] = [
							'type' => $remindValue['type'],
							'value' => \CCalendar::Date(\CCalendar::Timestamp($remindValue['value']))
						];
					}
				}
				else
				{
					$exploadedValue = explode('|', $remindValue);
					if (count($exploadedValue) > 1)
					{
						if ($exploadedValue[0] === self::TYPE_DAY_BEFORE)
						{
							$reminderList[] = [
								'type' => self::TYPE_DAY_BEFORE,
								'before' => intval($exploadedValue[1]),
								'time' => intval($exploadedValue[2])
							];
						}
						elseif($exploadedValue[0] === self::TYPE_SPECIFIC_DATETIME)
						{
							$reminderList[] = [
								'type' => self::TYPE_SPECIFIC_DATETIME,
								'value' => \CCalendar::Date(\CCalendar::Timestamp($exploadedValue[1]))
							];
						}
					}
					else
					{
						$reminderList[] = [
							'type' => 'min',
							'count' => intval($remindValue)
						];
					}
				}
			}
		}

		usort($reminderList, ['CCalendarReminder', 'sortReminder']);

		return $reminderList;
	}

	public static function GetTextReminders($valueList = array())
	{
		if (is_array($valueList))
		{
			foreach($valueList as $i => $value)
			{
				if($value['type'] == 'min')
				{
					$value['text'] = Loc::getMessage('EC_REMIND1_VIEW_'.$value['count']);
					if(!$value['text'])
					{
						$value['text'] = Loc::getMessage('EC_REMIND1_VIEW_MIN_COUNT', array('#COUNT#' => intval($value['count'])));
					}
				}
				elseif($value['type'] == 'hour')
				{
					$value['text'] = Loc::getMessage('EC_REMIND1_VIEW_HOUR_COUNT', array('#COUNT#' => intval($value['count'])));
				}
				elseif($value['type'] == 'day')
				{
					$value['text'] = Loc::getMessage('EC_REMIND1_VIEW_DAY_COUNT', array('#COUNT#' => intval($value['count'])));
				}
				$valueList[$i] = $value;
			}
		}
		return $valueList;
	}
}
?>