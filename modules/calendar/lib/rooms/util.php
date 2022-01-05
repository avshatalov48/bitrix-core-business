<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Main\Loader;
use CCalendar;
use CExtranet;

class Util
{
	/**
	 * @param $location
	 *
	 * Preparing location name for event
	 *
	 * @return array
	 */
	public static function parseLocation($location): array
	{
		$res = [
			'mrid' => false,
			'mrevid' => false,
			'room_id' => false,
			'room_event_id' => false,
			'str' => $location
		];

		if (
			is_string($location)
			&& mb_strlen($location) > 5
			&& mb_strpos($location, 'ECMR_') === 0
		)
		{
			$parsedLocation = explode('_', $location);
			if (count($parsedLocation) >= 2)
			{
				if ((int)$parsedLocation[1] > 0)
				{
					$res['mrid'] = (int)$parsedLocation[1];
				}
				if ((int)$parsedLocation[2] > 0)
				{
					$res['mrevid'] = (int)$parsedLocation[2];
				}
			}
		}
		elseif (
			is_string($location)
			&& mb_strlen($location) > 9
			&& mb_strpos($location, 'calendar_') === 0
		)
		{
			$parsedLocation = explode('_', $location);
			if (count($parsedLocation) >= 2)
			{
				if ((int)$parsedLocation[1] > 0)
				{
					$res['room_id'] = (int)$parsedLocation[1];
				}
				if ((int)$parsedLocation[2] > 0)
				{
					$res['room_event_id'] = (int)$parsedLocation[2];
				}
			}
		}

		return $res;
	}

	/**
	 * @param $loc
	 * Getting location name if empty
	 *
	 * @return mixed|string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getTextLocation($loc)
	{
		$result = $loc;
		if ($loc !== '')
		{
			$location = self::parseLocation($loc);

			if ($location['mrid'] === false && $location['room_id'] === false)
			{
				return $location['str'];
			}
			elseif ($location['room_id'] > 0)
			{
				$room = Manager::getRoomById($location['room_id']);
				return $room ? $room[0]['NAME'] : '';
			}
			else
			{
				$MRList = CCalendar::GetMeetingRoomList();
				foreach ($MRList as $MR)
				{
					if ($MR['ID'] == $location['mrid'])
					{
						return $MR['NAME'];
					}
				}
			}
		}

		return $result;
	}
	
	public static function getLocationAccess($userId): bool
	{
		if (Loader::includeModule('extranet'))
		{
			return \CCalendarType::CanDo('calendar_type_edit', 'location')
				&& CExtranet::IsIntranetUser(SITE_ID, $userId);
		}
		
		return \CCalendarType::CanDo('calendar_type_edit', 'location');
	}
}