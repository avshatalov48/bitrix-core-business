<?
class CCalendarPlanner
{
	public static function Init($config = [], $initialParams = false)
	{
		self::InitJsCore($config, $initialParams);
	}

	public static function InitJsCore($config = [], $initialParams)
	{
		global $APPLICATION;
		\Bitrix\Main\UI\Extension::load(['ajax', 'window', 'popup', 'access', 'date', 'viewer', 'socnetlogdest']);
		\Bitrix\Main\UI\Extension::load(['calendar.planner']);

		// Config
		if (!$config['id'])
			$config['id'] = (isset($config['id']) && strlen($config['id']) > 0) ? $config['id'] : 'bx_calendar_planner'.substr(uniqid(mt_rand(), true), 0, 4);

		$APPLICATION->AddHeadScript('/bitrix/js/calendar/planner.js');
		$APPLICATION->SetAdditionalCSS("/bitrix/js/calendar/planner.css");

		$mess_lang = \Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);
		?>
		<div id="<?= htmlspecialcharsbx($config['id'])?>" class="calendar-planner-wrapper"></div>
		<script type="text/javascript">
			BX.namespace('BX.Calendar');
			if(typeof BX.Calendar.PlannerManager === 'undefined')
			{
				BX.Calendar.PlannerManager = {
					planners: {},
					Get: function(id)
					{
						return BX.Calendar.PlannerManager.planners[id] || false;
					},
					Init: function(id, config, initialParams)
					{
						if (window.CalendarPlanner)
						{
							BX.Calendar.PlannerManager.planners[id] = new window.CalendarPlanner(config, initialParams);
							//BX.Calendar.PlannerManager.planners[id] = new BX.Calendar.Planner(config, initialParams);
						}
					}
				}
			}

			BX.message(<?=CUtil::PhpToJSObject($mess_lang, false);?>);
			BX.ready(function()
			{
				BX.Calendar.PlannerManager.Init(
					'<?= CUtil::JSEscape($config['id'])?>',
					<?=\Bitrix\Main\Web\Json::encode($config, false);?>,
					<?=\Bitrix\Main\Web\Json::encode($initialParams);?>
				);
			});
		</script>
		<?
	}

	public static function prepareData($params = [])
	{
		$curEventId = intVal($params['entry_id']);
		$curUserId = intVal($params['user_id']);
		$hostUserId = intVal($params['host_id']);
		$skipEntryList = (isset($params['skipEntryList']) && is_array($params['skipEntryList'])) ? $params['skipEntryList'] : [];
		$resourceIdList = [];

		$result = array(
			'users' => [],
			'entries' => [],
			'accessibility' => []
		);
		$userIds = [];

		if (isset($params['codes']) && is_array($params['codes']))
		{
			$params['codes'] = array_unique($params['codes']);
			$users = CCalendar::GetDestinationUsers($params['codes'], true);

			foreach($users as $user)
			{
				$userIds[] = $user['USER_ID'];
				$status = '';
				if ($hostUserId && $hostUserId == $user['USER_ID'])
					$status = 'h';

				if (!$hostUserId && $curUserId == $user['USER_ID'])
					$status = 'h';

				$userSettings = \Bitrix\Calendar\UserSettings::get($user['USER_ID']);
				$result['entries'][] = array(
					'type' => 'user',
					'id' => $user['USER_ID'],
					'name' => CCalendar::GetUserName($user),
					'status' => $status,
					'url' => CCalendar::GetUserUrl($user['USER_ID']),
					'avatar' => CCalendar::GetUserAvatarSrc($user),
					'strictStatus' => $userSettings['denyBusyInvitation']
				);
			}
		}
		elseif(isset($params['entries']) && is_array($params['entries']))
		{
			foreach($params['entries'] as $userId)
			{
				$userIds[] = intval($userId);
			}
		}

		if (isset($params['resources']) && is_array($params['resources']))
		{
			foreach($params['resources'] as $resource)
			{
				$resourceId = intVal($resource['id']);
				$resourceIdList[] = $resourceId;
				$resource['type'] = preg_replace("/[^a-zA-Z0-9_]/i", "", $resource['type']);
				$result['entries'][] = array(
					'type' => $resource['type'],
					'id' => $resourceId,
					'name' => $resource['name']
				);
				$result['accessibility'][$resourceId] = [];
			}
		}

		$from = $params['date_from'];
		$to = $params['date_to'];

		$accessibility = CCalendar::GetAccessibilityForUsers(array(
			'users' => $userIds,
			'from' => $from, // date or datetime in UTC
			'to' => $to, // date or datetime in UTC
			'curEventId' => $curEventId,
			'getFromHR' => true,
			'checkPermissions' => false
		));

		$result['accessibility'] = [];
		$deltaOffset = isset($params['timezone']) ? (CCalendar::GetTimezoneOffset($params['timezone']) - CCalendar::GetCurrentOffsetUTC($curUserId)) : 0;

		foreach($accessibility as $userId => $entries)
		{
			if (empty($entries))
				continue;

			$result['accessibility'][$userId] = [];
			foreach($entries as $entry)
			{
				if (in_array($entry['ID'], $skipEntryList))
				{
					continue;
				}

				if (isset($entry['DT_FROM']) && !isset($entry['DATE_FROM']))
				{
					$dateFrom = $entry['DT_FROM'];
					$dateTo = $entry['DT_FROM'];
				}
				else
				{
					$dateFrom = $entry['DATE_FROM'];
					$dateTo = $entry['DATE_TO'];

					if ($entry['DT_SKIP_TIME'] !== "Y"
						&&
						($entry['~USER_OFFSET_FROM'] != $deltaOffset
							|| $entry['~USER_OFFSET_TO'] != $deltaOffset))
					{
						$dateFrom = CCalendar::Date(CCalendar::Timestamp($entry['DATE_FROM']) - $entry['~USER_OFFSET_FROM'] + $deltaOffset);
						$dateTo = CCalendar::Date(CCalendar::Timestamp($entry['DATE_TO']) - $entry['~USER_OFFSET_TO'] + $deltaOffset);
					}
				}

				$result['accessibility'][$userId][] = array(
					'id' => $entry['ID'],
					'name' => $entry['NAME'],
					'dateFrom' => $dateFrom,
					'dateTo' => $dateTo,
					'type' => $entry['FROM_HR'] ? 'hr' : 'event'
				);
			}
		}

		if (isset($params['location']))
		{
			$location = CCalendar::ParseLocation($params['location']);
			$roomEventId = intval($params['roomEventId']);

			if ($roomEventId && !in_array($roomEventId, $skipEntryList))
			{
				$skipEntryList[] = $roomEventId;
			}

			if($location['mrid'])
			{
				$mrid = 'MR_'.$location['mrid'];
				$entry = [
					'type' => 'room',
					'id' => $mrid,
					'name' => 'meeting room'
				];

				$roomList = CCalendar::GetMeetingRoomList();
				foreach($roomList as $room)
				{
					if ($room['ID'] == $location['mrid'])
					{
						$entry['name'] = $room['NAME'];
						$entry['url'] = $room['URL'];
						break;
					}
				}

				$result['entries'][] = $entry;
				$result['accessibility'][$mrid] = [];

				$meetingRoomRes = CCalendar::GetAccessibilityForMeetingRoom([
					'allowReserveMeeting' => true,
					'id' => $location['mrid'],
					'from' => $from,
					'to' => $to,
					'curEventId' => $roomEventId
				]);

				foreach($meetingRoomRes as $entry)
				{
					if (!in_array($entry['ID'], $skipEntryList))
					{
						$result['accessibility'][$mrid][] = array(
							'id' => $entry['ID'],
							'dateFrom' => $entry['DT_FROM'],
							'dateTo' => $entry['DT_TO']
						);
					}
				}
			}
			elseif ($location['room_id'])
			{
				$roomId = 'room_'.$location['room_id'];
				$entry = array(
					'type' => 'room',
					'id' => $roomId,
					'roomId' => $location['room_id'],
					'name' => 'meeting room'
				);

				$sectionList = CCalendarLocation::getList();
				foreach($sectionList as $room)
				{
					if ($room['ID'] == $location['room_id'])
					{
						$entry['name'] = $room['NAME'];
					}
				}

				$result['entries'][] = $entry;
				$result['accessibility'][$roomId] = [];
				$meetingRoomRes = CCalendarLocation::getRoomAccessibility($location['room_id'], $from, $to);

				foreach($meetingRoomRes as $entry)
				{
					if (in_array($entry['ID'], $skipEntryList))
						continue;

					$fromTs = CCalendar::Timestamp($entry['DATE_FROM']);
					$toTs = CCalendar::Timestamp($entry['DATE_TO']);
					if ($entry['DT_SKIP_TIME'] !== "Y")
					{
						$fromTs -= $entry['~USER_OFFSET_FROM'];
						$toTs -= $entry['~USER_OFFSET_TO'];
						$fromTs += $deltaOffset;
						$toTs += $deltaOffset;
					}

					$result['accessibility'][$roomId][] = array(
						'id' => $entry['ID'],
						'name' => $entry['NAME'],
						'dateFrom' => CCalendar::Date($fromTs, $entry['DT_SKIP_TIME'] != 'Y'),
						'dateTo' => CCalendar::Date($toTs, $entry['DT_SKIP_TIME'] != 'Y')
					);
				}
			}
		}

		if (!empty($resourceIdList))
		{
			$resEntries = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"FROM_LIMIT" => $from,
						"TO_LIMIT" => $to,
						"CAL_TYPE" => 'resource',
						"ACTIVE_SECTION" => "Y",
						"SECTION" => $resourceIdList
					),
					'parseRecursion' => true,
					'setDefaultLimit' => false
				)
			);

			foreach($resEntries as $row)
			{
				if (in_array($row['ID'], $skipEntryList))
					continue;

				$fromTs = CCalendar::Timestamp($row["DATE_FROM"]);
				$toTs = CCalendar::Timestamp($row['DATE_TO']);
				if ($row['DT_SKIP_TIME'] !== "Y")
				{
					$fromTs -= $row['~USER_OFFSET_FROM'];
					$toTs -= $row['~USER_OFFSET_TO'];
					$fromTs += $deltaOffset;
					$toTs += $deltaOffset;
				}
				$result['accessibility'][$row['SECT_ID']][] = array(
					'id' => $row["ID"],
					'name' => $row["NAME"],
					'dateFrom' => CCalendar::Date($fromTs, $row['DT_SKIP_TIME'] != 'Y'),
					'dateTo' => CCalendar::Date($toTs, $row['DT_SKIP_TIME'] != 'Y')
				);
			}
		}

		return $result;
	}
}

?>