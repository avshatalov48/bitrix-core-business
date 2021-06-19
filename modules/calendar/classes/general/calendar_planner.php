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
			$config['id'] = (isset($config['id']) && $config['id'] <> '') ? $config['id'] : 'bx_calendar_planner'.mb_substr(uniqid(mt_rand(), true), 0, 4);

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
		$curEventId = (int)$params['entry_id'];
		$curUserId = (int)$params['user_id'];
		$hostUserId = (int)$params['host_id'];

		$skipEntryList = (isset($params['skipEntryList']) && is_array($params['skipEntryList']))
			? $params['skipEntryList']
			: [];

		$resourceIdList = [];
		$userIds = [];
		$users = [];
		$result = [
			'users' => [],
			'entries' => [],
			'accessibility' => []
		];

		if (isset($params['codes']) && is_array($params['codes']))
		{
			$params['codes'] = array_unique($params['codes']);
			$users = CCalendar::GetDestinationUsers($params['codes'], true);
		}

		if (!empty($users))
		{
			foreach($users as $user)
			{
				$userIds[] = $user['USER_ID'];
				$status = ($hostUserId && $hostUserId === (int)$user['USER_ID']
					|| !$hostUserId && $curUserId == $user['USER_ID'])
					? 'h'
					: '';

				$userSettings = \Bitrix\Calendar\UserSettings::get($user['USER_ID']);
				$result['entries'][] = array(
					'type' => 'user',
					'id' => $user['USER_ID'],
					'name' => CCalendar::GetUserName($user),
					'status' => $status,
					'url' => CCalendar::GetUserUrl($user['USER_ID']),
					'avatar' => CCalendar::GetUserAvatarSrc($user),
					'strictStatus' => $userSettings['denyBusyInvitation'],
					'emailUser' => $user['EXTERNAL_AUTH_ID'] === 'email'
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
				$resourceId = intval($resource['id']);
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
		$currentUserOffset = CCalendar::GetCurrentOffsetUTC($curUserId);

		foreach($accessibility as $userId => $entries)
		{
			if (empty($entries))
			{
				continue;
			}

			$result['accessibility'][$userId] = [];
			foreach($entries as $entry)
			{
				if (in_array($entry['ID'], $skipEntryList))
				{
					continue;
				}

				$dateFrom = $entry['DATE_FROM'];
				$dateTo = $entry['DATE_TO'];

				if ($entry['DT_SKIP_TIME'] !== "Y")
				{
					$dateFrom = CCalendar::Date(
						CCalendar::Timestamp($entry['DATE_FROM'])
						- $entry['~USER_OFFSET_FROM']
					);
					$dateTo = CCalendar::Date(
						CCalendar::Timestamp($entry['DATE_TO'])
						- $entry['~USER_OFFSET_TO']
					);
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
			$roomEventId = $location['room_event_id'];

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
					if (in_array((int)$entry['ID'], $skipEntryList))
					{
						continue;
					}

					$dateFrom = $entry['DATE_FROM'];
					$dateTo = $entry['DATE_TO'];

					if ($entry['DT_SKIP_TIME'] !== "Y")
					{
						$dateFrom = CCalendar::Date(
							CCalendar::Timestamp($entry['DATE_FROM'])
							- $entry['~USER_OFFSET_FROM']
						);
						$dateTo = CCalendar::Date(
							CCalendar::Timestamp($entry['DATE_TO'])
							- $entry['~USER_OFFSET_TO']
						);
					}

					$result['accessibility'][$roomId][] = array(
						'id' => $entry['ID'],
						'name' => $entry['NAME'],
						'dateFrom' => $dateFrom,
						'dateTo' => $dateTo
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

			foreach($resEntries as $entry)
			{
				if (in_array($entry['ID'], $skipEntryList))
				{
					continue;
				}

				$dateFrom = $entry['DATE_FROM'];
				$dateTo = $entry['DATE_TO'];

				if ($entry['DT_SKIP_TIME'] !== "Y")
				{
					$dateFrom = CCalendar::Date(
						CCalendar::Timestamp($entry['DATE_FROM'])
						- $entry['~USER_OFFSET_FROM']
					);
					$dateTo = CCalendar::Date(
						CCalendar::Timestamp($entry['DATE_TO'])
						- $entry['~USER_OFFSET_TO']
					);
				}

				$result['accessibility'][$entry['SECT_ID']][] = array(
					'id' => $entry["ID"],
					'name' => $entry["NAME"],
					'dateFrom' => $dateFrom,
					'dateTo' => $dateTo
				);
			}
		}

		if ($params['initPullWatches'] === true)
		{
			\Bitrix\Calendar\Util::initPlannerPullWatches(
				$curUserId,
				$userIds
			);
		}

		return $result;
	}
}

?>