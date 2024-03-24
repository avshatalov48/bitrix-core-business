<?php
namespace Bitrix\Calendar;
use Bitrix\Main;
use \Bitrix\Main\Web\Json;

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
			'lastUsedSection' => false,
			'sendFromEmail' => false,
			'defaultSections' => [],
			'syncPeriodPast' => 3,
			'syncPeriodFuture' => 12,
			'defaultReminders' => [
				'fullDay' => [
					'type' => 'daybefore',
					'before' => 0,
					'time' => 480,
				],
				'withTime' => [
					'type' => 'min',
					'count' => 15
				]
			],
			// 'enableLunchTime' => 'N',
			// 'lunchStart' => '13:00',
			// 'lunchEnd' => '14:00',
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
			foreach($settings as $optionName => $value)
			{
				if (isset(self::$settings[$optionName]))
				{
					if (
						($optionName === 'defaultSections' || $optionName === 'defaultReminders')
						&& is_array($value)
					)
					{
						$curSet[$optionName] = Json::encode($value);
					}
					else
					{
						$curSet[$optionName] = $value;
					}
				}
			}
			\CUserOptions::setOption("calendar", "user_settings", $curSet, false, $userId);
		}
	}

	public static function get($userId = null)
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
				foreach($settings as $optionName => $value)
				{
					if (
						($optionName === 'defaultSections' || $optionName === 'defaultReminders')
						&& !is_array($value)
					)
					{
						$resSettings[$optionName] = Json::decode($value);
					}
					else
					{
						$resSettings[$optionName] = $value;
					}
				}
			}

			$resSettings['timezoneName'] = \CCalendar::getUserTimezoneName($userId);
			$resSettings['timezoneOffsetUTC'] = \CCalendar::getCurrentOffsetUTC($userId);
			$resSettings['timezoneDefaultName'] = '';

			if (isset($settings['denyBusyInvitation']))
			{
				$resSettings['denyBusyInvitation'] = !!$settings['denyBusyInvitation'];
			}

			if (isset($settings['showDeclined']))
			{
				$resSettings['showDeclined'] = !!$settings['showDeclined'];
			}

			// We don't have default timezone for this offset for this user
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
		{
			$userId = \CCalendar::getUserId();
		}

		$res = [];
		$str = \CUserOptions::getOption("calendar", "superpose_tracking_users", false, $userId);

		if ($str !== false && CheckSerializedData($str))
		{
			$ids = unserialize($str, ['allowed_classes' => false]);
			if (is_array($ids) && count($ids) > 0)
			{
				foreach($ids as $id)
				{
					if ((int)$id > 0)
					{
						$res[] = (int)$id;
					}
				}
			}
		}
		if (is_array($res) && !in_array($userId, $res))
		{
			$res[] = $userId;
		}

		if (isset($params['userList']))
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
		{
			$userId = \CCalendar::getUserId();
		}

		if (!is_array($value))
		{
			$value = [];
		}
		array_walk($value, 'intval');
		$value = array_unique($value);

		\CUserOptions::setOption("calendar", "superpose_tracking_users", serialize($value), false, $userId);
	}

	public static function getTrackingGroups($userId = false, $params = [])
	{
		$res = [];
		$str = \CUserOptions::getOption("calendar", "superpose_tracking_groups", false, $userId);

		if ($str !== false && CheckSerializedData($str))
		{
			$ids = unserialize($str, ['allowed_classes' => false]);
			if (is_array($ids))
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
	public static function setTrackingGroups($userId = false, $value = [])
	{
		if (!$userId)
		{
			$userId = \CCalendar::getUserId();
		}

		if (!is_array($value))
		{
			$value = [];
		}

		\CUserOptions::setOption("calendar", "superpose_tracking_groups", serialize($value), false, $userId);
	}

	public static function getHiddenSections($userId = false, $options = []): array
	{
		$res = [];
		if (class_exists('CUserOptions') && $userId > 0)
		{
			$optionName = ($options['isPersonalCalendarContext'] ?? null) ? 'hidden_sections' : 'hidden_sections_'.$options['type'];
			$res = \CUserOptions::getOption('calendar', $optionName, false, $userId);

			if (is_array($res) && isset($res[$optionName]) && is_string($res[$optionName]))
			{
				$res = explode(',', $res[$optionName]);
			}

			if ($res === false && isset($options['defaultHiddenSections']) && is_array($options['defaultHiddenSections']))
			{
				$res = $options['defaultHiddenSections'];
			}

			if (is_array($res))
			{
				$res = array_values(array_unique(array_filter($res, function($k) {
					return $k === 'tasks' || is_numeric($k);
				})));
			}
		}

		return is_array($res) ? $res : [];
	}

	public static function saveHiddenSections(int $userId, array $sections)
	{
		\CUserOptions::SetOption('calendar', 'hidden_sections', $sections, false, $userId);
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
			$result = unserialize($str, ['allowed_classes' => false]);
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

		\Bitrix\Calendar\Util::addPullEvent(
			'change_section_customization',
			$userId, []
		);
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
				$idList = unserialize($str, ['allowed_classes' => false]);
				if (is_array($idList))
				{
					foreach($idList as $id)
					{
						if (intval($id) > 0)
						{
							$sectionIdList[] = intval($id);
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