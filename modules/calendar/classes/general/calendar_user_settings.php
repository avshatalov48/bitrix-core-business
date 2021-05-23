<?
/**
 * Class CCalendarUserSettings
 */
class CCalendarUserSettings
{
	private static
		$settings = array(
			'view' => 'month',
			'CalendarSelCont' => false,
			'SPCalendarSelCont' => false,
			'meetSection' => false,
			'crmSection' => false,
			'showDeclined' => false,
			'denyBusyInvitation' => false,
			'collapseOffHours' => 'Y',
			'showWeekNumbers' => 'N',
			'showTasks' => 'Y',
			'showCompletedTasks' => 'N',
			'syncPeriodPast' => 3,
			'syncPeriodFuture' => 12,
		);

	public static function Set($settings = array(), $userId = false)
	{
		if (!$userId)
			$userId = CCalendar::GetUserId();
		if (!$userId)
			return;

		if ($settings === false)
		{
			CUserOptions::SetOption("calendar", "user_settings", false, false, $userId);
		}
		elseif(is_array($settings))
		{
			$curSet = self::Get($userId);
			foreach($settings as $key => $val)
			{
				if (isset(self::$settings[$key]))
					$curSet[$key] = $val;
			}
			CUserOptions::SetOption("calendar", "user_settings", $curSet, false, $userId);
		}
	}

	public static function Get($userId = false)
	{
		if (!$userId)
		{
			$userId = CCalendar::GetUserId();
		}

		$resSettings = self::$settings;

		if ($userId)
		{
			$settings = CUserOptions::GetOption("calendar", "user_settings", false, $userId);
			if (is_array($settings))
			{
				foreach($settings as $key => $value)
				{
					$resSettings[$key] = $value;
				}
			}

			$resSettings['timezoneName'] = CCalendar::GetUserTimezoneName($userId);
			$resSettings['timezoneOffsetUTC'] = CCalendar::GetCurrentOffsetUTC($userId);
			$resSettings['timezoneDefaultName'] = '';

			// We don't have default timezone for this offset for this user
			// We will ask him but we should suggest some suitable for his offset
			// We will ask him but we should suggest some suitable for his offset
			if (!$resSettings['timezoneName'])
			{
				$resSettings['timezoneDefaultName'] = CCalendar::GetGoodTimezoneForOffset($resSettings['timezoneOffsetUTC']);
			}

			$workTime = CUserOptions::GetOption("calendar", "workTime", false, $userId);
			if ($workTime)
			{
				$resSettings['work_time_start'] = $workTime['start'].'.00';
				$resSettings['work_time_end'] = $workTime['end'].'.00';
			}
		}

		$resSettings['showDeclined'] = intval($resSettings['showDeclined']);
		$resSettings['denyBusyInvitation'] = intval($resSettings['denyBusyInvitation']);

		return $resSettings;
	}

	public static function getFormSettings($formType, $userId = false)
	{
		if (!$userId)
			$userId = CCalendar::GetUserId();

		$defaultValues = array(
			'slider_main' => array(
				'pinnedFields' => implode(',',array('location', 'rrule'))
			)
		);
		if (!isset($defaultValues[$formType]))
			$defaultValues[$formType] = false;

		//CUserOptions::DeleteOption("calendar", $formType);
		$settings = CUserOptions::GetOption("calendar", $formType, $defaultValues[$formType], $userId);
		if (!is_array($settings['pinnedFields']))
			$settings['pinnedFields'] = explode(',', $settings['pinnedFields']);
		return $settings;
	}

	public static function getTrackingUsers($userId = false, $params = array())
	{
		if (!$userId)
			$userId = CCalendar::GetUserId();

		$res = array();
		$str = CUserOptions::GetOption("calendar", "superpose_tracking_users", false, $userId);

		if ($str !== false && CheckSerializedData($str))
		{
			$ids = unserialize($str, ['allowed_classes' => false]);
			if (is_array($ids) && count($ids) > 0)
			{
				foreach($ids as $id)
				{
					if (intval($id) > 0)
					{
						$res[] = intval($id);
					}
				}
			}
		}

		if ($params && isset($params['userList']))
		{
			$params['userList'] = array_unique($params['userList']);
			$diff = array_diff($params['userList'], $res);
			if (count($diff) > 0)
			{
				$res = array_merge($res, $diff);
				self::setTrackingUsers($userId, $res);
			}
		}

		$res = \Bitrix\Main\UserTable::getList(
			array(
				'filter' => array('ID' => $res),
				'select' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME')
			)
		);

		$trackedUsers = array();
		while ($user = $res->fetch())
		{
			$user['FORMATTED_NAME'] = CCalendar::GetUserName($user);
			$trackedUsers[] = $user;
		}

		return $trackedUsers;
	}
	public static function setTrackingUsers($userId = false, $value = array())
	{
		if (!$userId)
			$userId = CCalendar::GetUserId();

		if (!is_array($value))
			$value = array();

		CUserOptions::SetOption("calendar", "superpose_tracking_users", serialize($value), false, $userId);
	}
	public static function getTrackingGroups($userId = false, $params = array())
	{
		$res = array();
		$str = CUserOptions::GetOption("calendar", "superpose_tracking_groups", false, $userId);

		if ($str !== false && CheckSerializedData($str))
		{
			$ids = unserialize($str, ['allowed_classes' => false]);
			if (is_array($ids) && count($ids) > 0)
			{
				foreach($ids as $id)
				{
					if (intval($id) > 0)
					{
						$res[] = intval($id);
					}
				}
			}
		}

		if ($params && isset($params['groupList']))
		{
			$params['groupList'] = array_unique($params['groupList']);
			$diff = array_diff($params['groupList'], $res);
			if (count($diff) > 0)
			{
				$res = array_merge($res, $diff);
				self::setTrackingGroups($userId, $res);
			}
		}

		return $res;
	}
	public static function setTrackingGroups($userId = false, $value = array())
	{
		if (!$userId)
			$userId = CCalendar::GetUserId();

		if (!is_array($value))
			$value = array();

		CUserOptions::SetOption("calendar", "superpose_tracking_groups", serialize($value), false, $userId);
	}

	public static function getHiddenSections($userId = false)
	{
		$res = array();

		if (class_exists('CUserOptions') && $userId > 0)
		{
			$res = CUserOptions::GetOption("calendar", "hidden_sections", false, $userId);
			if ($res !== false && is_array($res) && isset($res['hidden_sections']))
				$res = explode(',', $res['hidden_sections']);
		}
		if (!is_array($res))
			$res = array();

		return $res;
	}
}
?>