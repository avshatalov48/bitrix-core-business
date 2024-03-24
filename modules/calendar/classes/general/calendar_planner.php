<?php

use Bitrix\Calendar\Core\Managers\Accessibility;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Rooms;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\Util;

class CCalendarPlanner
{
	public static function Init($config = [], $initialParams = false)
	{
		self::InitJsCore($config, $initialParams);
	}

	public static function InitJsCore($config = [], $initialParams = [])
	{
		global $APPLICATION;
		\Bitrix\Main\UI\Extension::load(['ajax', 'window', 'popup', 'access', 'date', 'viewer', 'socnetlogdest']);
		\Bitrix\Main\UI\Extension::load(['calendar.planner', 'ui.fonts.opensans']);

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
		$parentId = (int)($params['parent_id'] ?? null);
		$entryId = (int)($params['entry_id'] ?? null);

		$skipEntryId = $parentId !== 0 ? $parentId : $entryId;
		$curUserId = (int)($params['user_id'] ?? null);
		$hostUserId = (int)($params['host_id'] ?? null);

		$isPlannerFeatureEnabled = Bitrix24Manager::isPlannerFeatureEnabled();

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

		$prevUsersId = (isset($params['prevUserList']) && is_array($params['prevUserList']))
			? array_unique(array_map('intval', $params['prevUserList']))
			: [];

		if (!empty($users))
		{
			foreach($users as $user)
			{
				if (!in_array((int)$user['USER_ID'], $prevUsersId, true))
				{
					$userIds[] = (int)$user['USER_ID'];
				}

				$status = (($hostUserId && $hostUserId === (int)$user['USER_ID'])
					|| (!$hostUserId && $curUserId === (int)$user['USER_ID']))
					? 'h'
					: '';

				$userSettings = \Bitrix\Calendar\UserSettings::get($user['USER_ID']);
				$result['entries'][] = [
					'type' => 'user',
					'id' => $user['USER_ID'],
					'name' => CCalendar::GetUserName($user),
					'status' => $status,
					'url' => CCalendar::GetUserUrl($user['USER_ID']),
					'avatar' => CCalendar::GetUserAvatarSrc($user),
					'strictStatus' => $userSettings['denyBusyInvitation'],
					'emailUser' => isset($user['EXTERNAL_AUTH_ID']) && ($user['EXTERNAL_AUTH_ID'] === 'email'),
					'sharingUser' => isset($user['EXTERNAL_AUTH_ID']) && ($user['EXTERNAL_AUTH_ID'] === 'calendar_sharing'),
					'timezoneName' => CCalendar::GetUserTimezoneName((int)$user['USER_ID']),
				];
			}
		}
		elseif(isset($params['entries']) && is_array($params['entries']))
		{
			foreach($params['entries'] as $userId)
			{
				$userIds[] = (int)$userId;
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

		$from = $params['date_from'] ?? null;
		$to = $params['date_to'] ?? null;

		if ($isPlannerFeatureEnabled)
		{
			$accessibility = (new Accessibility())
				->setCheckPermissions(false)
				->setSkipEventId($skipEntryId)
				->getAccessibility($userIds, CCalendar::TimestampUTC($from), CCalendar::TimestampUTC($to))
			;

			$timezoneName = \CCalendar::GetUserTimezoneName(\CCalendar::GetUserId());
			foreach ($accessibility as $userId => $entries)
			{
				$result['accessibility'][$userId] = [];
				foreach ($entries as $entry)
				{
					if (isset($entry['id']) && in_array($entry['id'], $skipEntryList))
					{
						continue;
					}

					if ($entry['isFullDay'])
					{
						$dateFrom = $entry['from'];
						$dateTo = $entry['to'];
					}
					else
					{
						$dateFrom = Util::formatDateTimeTimestamp(CCalendar::TimestampUTC($entry['from']), $timezoneName);
						$dateTo = Util::formatDateTimeTimestamp(CCalendar::TimestampUTC($entry['to']), $timezoneName);
					}

					$result['accessibility'][$userId][] = [
						'name' => $entry['name'],
						'dateFrom' => $dateFrom,
						'dateTo' => $dateTo,
						'isFullDay' => $entry['isFullDay'],
						'isVacation' => $entry['isVacation'] ?? false,
					];
				}
			}
		}

		if (isset($params['location']))
		{
			$location = \Bitrix\Calendar\Rooms\Util::parseLocation($params['location']);
			$entryLocation = \Bitrix\Calendar\Rooms\Util::parseLocation($params['entryLocation'] ?? null);
			$roomEventId = $entryLocation['room_event_id'] ?? null;

			if ($roomEventId && !in_array($roomEventId, $skipEntryList))
			{
				$skipEntryList[] = $roomEventId;
			}

			if ($location['mrid'] ?? null)
			{
				$mrid = 'MR_' . $location['mrid'];
				$entry = [
					'type' => 'room',
					'id' => $mrid,
					'name' => 'meeting room'
				];

				$roomList = Rooms\IBlockMeetingRoom::getMeetingRoomList();
				foreach ($roomList as $room)
				{
					if ((int)$room['ID'] === (int)$location['mrid'])
					{
						$entry['name'] = $room['NAME'];
						$entry['url'] = $room['URL'];
						break;
					}
				}

				$result['entries'][] = $entry;
				$result['accessibility'][$mrid] = [];

				if ($isPlannerFeatureEnabled)
				{
					$meetingRoomRes = Rooms\IBlockMeetingRoom::getAccessibilityForMeetingRoom([
						'allowReserveMeeting' => true,
						'id' => $location['mrid'],
						'from' => $from,
						'to' => $to,
						'curEventId' => $roomEventId
					]);

					foreach ($meetingRoomRes as $entry)
					{
						if (!in_array($entry['ID'], $skipEntryList))
						{
							$result['accessibility'][$mrid][] = [
								'id' => $entry['ID'],
								'dateFrom' => $entry['DT_FROM'],
								'dateTo' => $entry['DT_TO']
							];
						}
					}
				}
			}
			elseif ($location['room_id'])
			{
				$roomId = 'room_' . $location['room_id'];
				$entry = [
					'type' => 'room',
					'id' => $roomId,
					'roomId' => $location['room_id'],
					'name' => 'meeting room'
				];

				$sectionList = Rooms\Manager::getRoomsList();
				foreach($sectionList as $room)
				{
					if ((int)$room['ID'] === (int)$location['room_id'])
					{
						$entry['name'] = $room['NAME'];
						break;
					}
				}

				$result['entries'][] = $entry;
				$result['accessibility'][$roomId] = [];

				if ($isPlannerFeatureEnabled)
				{
					$meetingRoomRes = Rooms\AccessibilityManager::getRoomAccessibility(
						[$location['room_id']],
						$from,
						$to
					);

					foreach ($meetingRoomRes as $entry)
					{
						if (in_array((int)$entry['ID'], $skipEntryList))
						{
							continue;
						}

						$dateFrom = $entry['DATE_FROM'];
						if ($entry['DT_SKIP_TIME'] !== "Y")
						{
							$dateFrom = CCalendar::Date(
								CCalendar::Timestamp($entry['DATE_FROM']) - $entry['~USER_OFFSET_FROM']
							);
							$dateTo = CCalendar::Date(
								CCalendar::Timestamp($entry['DATE_TO']) - $entry['~USER_OFFSET_TO']
							);
						}
						else
						{
							$dateTo = CCalendar::Date(
								CCalendar::Timestamp($entry['DATE_TO']) + CCalendar::GetDayLen()
							);
						}

						$result['accessibility'][$roomId][] = [
							'id' => $entry['ID'],
							'name' => $entry['NAME'],
							'dateFrom' => $dateFrom,
							'dateTo' => $dateTo
						];
					}
				}
			}
		}

		if (!empty($resourceIdList) && $isPlannerFeatureEnabled)
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

		if (($params['initPullWatches'] ?? null) === true)
		{
			Util::initPlannerPullWatches(
				$curUserId,
				$userIds
			);
		}

		return $result;
	}

	private static function getUsersIdList($params = [])
	{

	}
}
