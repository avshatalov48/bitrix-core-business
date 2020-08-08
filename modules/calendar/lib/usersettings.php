<?php
namespace Bitrix\Calendar;
use Bitrix\Main;

class UserSettings
{
	private static
		$settings = [
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
			'syncTasks' => 'N',
			'showCompletedTasks' => 'N',
			'lastUsedSection' => false
		];

	public static function set($settings = [], $userId = false)
	{
		if (!$userId)
			$userId = \CCalendar::getUserId();
		if (!$userId)
			return;

		if ($settings === false)
		{
			\CUserOptions::setOption("calendar", "user_settings", false, false, $userId);
		}
		elseif(is_array($settings))
		{
			$curSet = self::get($userId);
			foreach($settings as $key => $val)
			{
				if (isset(self::$settings[$key]))
					$curSet[$key] = $val;
			}
			\CUserOptions::setOption("calendar", "user_settings", $curSet, false, $userId);
		}
	}

	public static function get($userId = false)
	{
		if (!$userId)
		{
			$userId = \CCalendar::getUserId();
		}

		$resSettings = self::$settings;

		if ($userId)
		{
			$settings = \CUserOptions::getOption("calendar", "user_settings", false, $userId);
			if (is_array($settings))
			{
				if (isset($settings['view']))
					$resSettings['view'] = $settings['view'];

				$resSettings['tabId'] = $settings['view'];

				if (isset($settings['showDeclined']))
					$resSettings['showDeclined'] = !!$settings['showDeclined'];

				if (isset($settings['meetSection']) && $settings['meetSection'] > 0)
					$resSettings['meetSection'] = intVal($settings['meetSection']);

				if (isset($settings['crmSection']) && $settings['crmSection'] > 0)
					$resSettings['crmSection'] = intVal($settings['crmSection']);

				if (isset($settings['denyBusyInvitation']))
					$resSettings['denyBusyInvitation'] = !!$settings['denyBusyInvitation'];

				if (isset($settings['lastUsedSection']) && $settings['lastUsedSection'] > 0)
					$resSettings['lastUsedSection'] = intVal($settings['lastUsedSection']);

				if (isset($settings['collapseOffHours']))
					$resSettings['collapseOffHours'] = $settings['collapseOffHours'];

				if (isset($settings['showWeekNumbers']))
					$resSettings['showWeekNumbers'] = $settings['showWeekNumbers'];

				if (isset($settings['showTasks']))
					$resSettings['showTasks'] = $settings['showTasks'];

				if (isset($settings['showCompletedTasks']))
					$resSettings['showCompletedTasks'] = $settings['showCompletedTasks'];

				if (isset($settings['syncTasks']))
					$resSettings['syncTasks'] = $settings['syncTasks'];
			}

			$resSettings['timezoneName'] = \CCalendar::getUserTimezoneName($userId);
			$resSettings['timezoneOffsetUTC'] = \CCalendar::getCurrentOffsetUTC($userId);
			$resSettings['timezoneDefaultName'] = '';

			// We don't have default timezone for this offset for this user
			// We will ask him but we should suggest some suitable for his offset
			// We will ask him but we should suggest some suitable for his offset
			if (!$resSettings['timezoneName'])
			{
				$resSettings['timezoneDefaultName'] = \CCalendar::getGoodTimezoneForOffset($resSettings['timezoneOffsetUTC']);
			}

			$workTime = \CUserOptions::getOption("calendar", "workTime", false, $userId);
			if ($workTime)
			{
				$resSettings['work_time_start'] = $workTime['start'].'.00';
				$resSettings['work_time_end'] = $workTime['end'].'.00';
			}
		}
		return $resSettings;
	}

	public static function getFormSettings($formType, $userId = false)
	{
		if (!$userId)
		{
			$userId = \CCalendar::getUserId();
		}

		$defaultValues = [
			'slider_main' => [
				'pinnedFields' => implode(',', ['location', 'rrule', 'section'])
			]
		];
		if (!isset($defaultValues[$formType]))
		{
			$defaultValues[$formType] = false;
		}
		//\CUserOptions::DeleteOption("calendar", $formType);
		$settings = \CUserOptions::getOption("calendar", $formType, $defaultValues[$formType], $userId);
		if (!is_array($settings['pinnedFields']))
		{
			$settings['pinnedFields'] = explode(',', $settings['pinnedFields']);
		}
		return $settings;
	}

	public static function getTrackingUsers($userId = false, $params = [])
	{
		if (!$userId)
			$userId = \CCalendar::getUserId();

		$res = [];
		$str = \CUserOptions::getOption("calendar", "superpose_tracking_users", false, $userId);

		if ($str !== false && CheckSerializedData($str))
		{
			$ids = unserialize($str);
			if (is_array($ids) && count($ids) > 0)
			{
				foreach($ids as $id)
				{
					if (intVal($id) > 0)
					{
						$res[] = intVal($id);
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
			[
				'filter' => ['ID' => $res],
				'select' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME']
			]
		);

		$trackedUsers = [];
		while ($user = $res->fetch())
		{
			$user['FORMATTED_NAME'] = \CCalendar::GetUserName($user);
			$trackedUsers[] = $user;
		}

		return $trackedUsers;
	}
	public static function setTrackingUsers($userId = false, $value = [])
	{
		if (!$userId)
			$userId = \CCalendar::getUserId();

		if (!is_array($value))
			$value = [];

		\CUserOptions::setOption("calendar", "superpose_tracking_users", serialize($value), false, $userId);
	}
	public static function getTrackingGroups($userId = false, $params = [])
	{
		$res = [];
		$str = \CUserOptions::getOption("calendar", "superpose_tracking_groups", false, $userId);

		if ($str !== false && CheckSerializedData($str))
		{
			$ids = unserialize($str);
			if (is_array($ids) && count($ids) > 0)
			{
				foreach($ids as $id)
				{
					if (intVal($id) > 0)
					{
						$res[] = intVal($id);
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
	public static function setTrackingGroups($userId = false, $value = [])
	{
		if (!$userId)
			$userId = \CCalendar::getUserId();

		if (!is_array($value))
			$value = [];

		\CUserOptions::setOption("calendar", "superpose_tracking_groups", serialize($value), false, $userId);
	}

	public static function getHiddenSections($userId = false)
	{
		$res = [];

		if (class_exists('CUserOptions') && $userId > 0)
		{
			//CUserOptions::DeleteOption("calendar", "hidden_sections");
			$res = \CUserOptions::getOption("calendar", "hidden_sections", false, $userId);
			if ($res !== false && is_array($res) && isset($res['hidden_sections']))
				$res = explode(',', $res['hidden_sections']);
		}
		if (!is_array($res))
			$res = [];

		return $res;
	}

	public static function getSectionCustomization($userId = false)
	{
		/*
		 * \CUserOptions::setOption("calendar", "section_customization", serialize(['tasks' => ['name' => 'Custom task name', 'color' =>
		 '#FF22FF']]), false, $userId);
		*/

		$result = [];
		$str = \CUserOptions::getOption("calendar", "section_customization", false, $userId);
		if ($str !== false && CheckSerializedData($str))
		{
			$result = unserialize($str);
		}

		return $result;
	}

	public static function setSectionCustomization($userId = false, $data = [])
	{
		$sectionCustomization = self::getSectionCustomization($userId);

		foreach($data as $sectionId => $config)
		{
			if (isset($sectionCustomization[$sectionId]) && $config === false)
			{
				unset($sectionCustomization[$sectionId]);
			}
			else
			{
				$sectionCustomization[$sectionId] = $config;
			}
		}

		\CUserOptions::setOption("calendar", "section_customization", serialize($sectionCustomization), false, $userId);
	}


	public static function getFollowedSectionIdList($userId = false)
	{
		$sectionIdList = [];
		if ($userId)
		{
			$defaultFollowedSectionId = intval(\CUserOptions::GetOption("calendar", "superpose_displayed_default", 0, $userId));
			if ($defaultFollowedSectionId)
			{
				$sectionIdList[] = $defaultFollowedSectionId;
			}

			$str = \CUserOptions::GetOption("calendar", "superpose_displayed", false, $userId);
			if (CheckSerializedData($str))
			{
				$idList = unserialize($str);
				if (is_array($idList))
				{
					foreach($idList as $id)
					{
						if (intVal($id) > 0)
						{
							$sectionIdList[] = intVal($id);
						}
					}
				}
			}

			if ($defaultFollowedSectionId)
			{
				\CUserOptions::SetOption("calendar", "superpose_displayed", serialize($sectionIdList));
				\CUserOptions::SetOption("calendar", "superpose_displayed_default", false);
			}
		}
		return $sectionIdList;
	}
}