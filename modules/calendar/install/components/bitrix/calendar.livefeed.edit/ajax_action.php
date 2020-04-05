<?
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if (check_bitrix_sessid() && CModule::IncludeModule("calendar"))
{
	if (isset($_REQUEST['bx_event_calendar_check_meeting_room']) && $_REQUEST['bx_event_calendar_check_meeting_room'] === 'Y')
	{
		$check = false;
		$settings = CCalendar::GetSettings();
		$from = CCalendar::Date(CCalendar::Timestamp($_REQUEST['from']));
		$to = CCalendar::Date(CCalendar::Timestamp($_REQUEST['to']));
		$loc_new = CCalendar::ParseLocation(trim($_REQUEST['location']));

		$params = array(
			'dateFrom' => $from,
			'dateTo' => $to,
			'regularity' => 'NONE',
			'members' => false,
		);

		$params['RMiblockId'] = $settings['rm_iblock_id'];
		$params['mrid'] = $loc_new['mrid'];
		$params['mrevid_old'] = 0;

		$check = CCalendar::CheckMeetingRoom($params);

		?><script>top.BXCRES_Check = <?= CUtil::PhpToJSObject($check)?>;</script><?
	}
	elseif (isset($_REQUEST['bx_event_calendar_update_planner']) && $_REQUEST['bx_event_calendar_update_planner'] === 'Y')
	{
		$curUserId = CCalendar::GetCurUserId();
		$result = array(
			'entries' => array(),
			'accessibility' => array()
		);
		$userIds = array();

		if (isset($_REQUEST['codes']) && is_array($_REQUEST['codes']))
		{
			$codes = array();
			foreach($_REQUEST['codes'] as $permCode)
			{
				if($permCode)
					$codes[] = $permCode;
			}

			if(count($codes) > 0)
				$codes[] = 'U'.$curUserId;
			$users = CCalendar::GetDestinationUsers($codes, true);

			foreach($users as $user)
			{
				$userSettings = CCalendarUserSettings::Get($user['USER_ID']);
				$userIds[] = $user['USER_ID'];
				$result['entries'][] = array(
					'type' => 'user',
					'id' => $user['USER_ID'],
					'name' => CCalendar::GetUserName($user),
					'status' => $user['USER_ID'] == $curUserId ? 'h' : '',
					'url' => CCalendar::GetUserUrl($user['USER_ID']),
					'avatar' => CCalendar::GetUserAvatarSrc($user),
					'strictStatus' => $userSettings['denyBusyInvitation']
				);
			}
		}
		elseif(isset($_REQUEST['entries']) && is_array($_REQUEST['entries']))
		{
			foreach($_REQUEST['entries'] as $userId)
			{
				$userIds[] = intval($userId);
			}
		}

		$from = CCalendar::Date(CCalendar::Timestamp($_REQUEST['from']), false);
		$to = CCalendar::Date(CCalendar::Timestamp($_REQUEST['to']), false);

		$accessibility = CCalendar::GetAccessibilityForUsers(array(
			'users' => $userIds,
			'from' => $from, // date or datetime in UTC
			'to' => $to, // date or datetime in UTC
			'getFromHR' => true
		));

		$result['accessibility'] = array();
		$deltaOffset = isset($_REQUEST['timezone']) ? (CCalendar::GetTimezoneOffset($_REQUEST['timezone']) - CCalendar::GetCurrentOffsetUTC($curUserId)) : 0;

		foreach($accessibility as $userId => $entries)
		{
			$result['accessibility'][$userId] = array();

			foreach($entries as $entry)
			{
				if (isset($entry['DT_FROM']) && !isset($entry['DATE_FROM']))
				{
					if ($deltaOffset != 0)
					{
						$entry['DT_FROM'] = CCalendar::Date(CCalendar::Timestamp($entry['DT_FROM']) + $deltaOffset);
						$entry['DT_TO'] = CCalendar::Date(CCalendar::Timestamp($entry['DT_TO']) + $deltaOffset);
					}

					$result['accessibility'][$userId][] = array(
						'id' => $entry['ID'],
						'dateFrom' => $entry['DT_FROM'],
						'dateTo' => $entry['DT_TO'],
						'type' => $entry['FROM_HR'] ? 'hr' : 'event'
					);
				}
				else
				{
					$fromTs = CCalendar::Timestamp($entry['DATE_FROM']);
					$toTs = CCalendar::Timestamp($entry['DATE_TO']);
					if ($entry['DT_SKIP_TIME'] !== "Y")
					{
						$fromTs -= $entry['~USER_OFFSET_FROM'];
						$toTs -= $entry['~USER_OFFSET_TO'];
						$fromTs += $deltaOffset;
						$toTs += $deltaOffset;
					}
					$result['accessibility'][$userId][] = array(
						'id' => $entry['ID'],
						'dateFrom' => CCalendar::Date($fromTs, $entry['DT_SKIP_TIME'] != 'Y'),
						'dateTo' => CCalendar::Date($toTs, $entry['DT_SKIP_TIME'] != 'Y'),
						'type' => $entry['FROM_HR'] ? 'hr' : 'event'
					);
				}
			}
		}

		// Meeting room selection
		$location = CCalendar::ParseLocation(trim($_REQUEST['location']));
		if($location['mrid'])
		{
			$mrid = 'MR_'.$location['mrid'];
			$entry = array(
					'type' => 'room',
					'id' => $mrid,
					'name' => 'meeting room'
			);

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
			$result['accessibility'][$mrid] = array();

			$meetingRoomRes = CCalendar::GetAccessibilityForMeetingRoom(array(
					'allowReserveMeeting' => true,
					'id' => $location['mrid'],
					'from' => $from,
					'to' => $to,
					'curEventId' => false
			));

			foreach($meetingRoomRes as $entry)
			{
				$result['accessibility'][$mrid][] = array(
						'id' => $entry['ID'],
						'dateFrom' => $entry['DT_FROM'],
						'dateTo' => $entry['DT_TO']
				);
			}
		}

		?><script>top.BXCRES_Planner = <?= CUtil::PhpToJSObject($result)?>;</script><?
	}
}
else
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SESSION_ERROR'));
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>