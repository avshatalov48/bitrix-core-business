<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\TypeAccessController;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Main\Access\Exception\UnknownActionException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\Emoji;
use CCalendar;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use CExtranet;
use CTimeZone;

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

		if (!$location || is_array($location))
		{
			$res['str'] = '';

			return $res;
		}

		if (is_string($location))
		{
			$res['str'] = Emoji::decode($location);
		}

		if (
			mb_strlen($location) > 5
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
		else if (
			mb_strlen($location) > 9
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
				if (isset($parsedLocation[2]) && (int)$parsedLocation[2] > 0)
				{
					$res['room_event_id'] = (int)$parsedLocation[2];
				}
			}
		}

		return $res;
	}

	/**
	 * @param $loc
	 * @return array|string[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function unParseTextLocation($loc = ''): array
	{
		$result = ['NEW' => $loc];
		if ($loc != '')
		{
			$location = self::parseLocation($loc);
			if ($location['mrid'] === false && $location['room_id'] === false)
			{
				$MRList = IBlockMeetingRoom::getMeetingRoomList();
				$loc_ = mb_strtolower(trim($loc));
				foreach($MRList as $MR)
				{
					if (mb_strtolower(trim($MR['NAME'])) == $loc_)
					{
						$result['NEW'] = 'ECMR_'.$MR['ID'];
						break;
					}
				}

				if (Bitrix24Manager::isFeatureEnabled('calendar_location'))
				{
					$locationList = Manager::getRoomsList();
					foreach($locationList as $room)
					{
						if (mb_strtolower(trim($room['NAME'])) == $loc_)
						{
							$result['NEW'] = 'calendar_'.$room['ID'];
						}
					}
				}

			}
		}

		return $result;
	}

	/**
	 * @param $loc
	 *
	 * @return mixed|string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getTextLocation($loc = '')
	{
		$result = $loc;
		if ($loc)
		{
			$location = self::parseLocation($loc);

			if ($location['mrid'] === false && $location['room_id'] === false)
			{
				return $location['str'];
			}

			if ($location['room_id'] > 0)
			{
				$room = Manager::getRoomById($location['room_id']);
				return $room ? ($room[0]['NAME'] ?? null) : '';
			}

			$MRList = IBlockMeetingRoom::getMeetingRoomList();
			foreach ($MRList as $MR)
			{
				if ((int)$MR['ID'] === (int)$location['mrid'])
				{
					return $MR['NAME'];
				}
			}
		}

		return Emoji::decode($result);
	}

	/**
	 * @param $old
	 * @param $new
	 * @param array $params
	 * @return mixed|string
	 * @throws LoaderException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function setLocation($old = '', $new = '', array $params = [])
	{
		$params = [
			'bRecreateReserveMeetings' => $params['bRecreateReserveMeetings'] ?? null,
			'dateFrom' => $params['dateFrom'] ?? null,
			'dateTo' => $params['dateTo'] ?? null,
			'name' => $params['name'] ?? null,
			'persons' => $params['persons'] ?? null,
			'attendees' => $params['attendees'] ?? null,
			'parentParams' => $params['parentParams'] ?? null,
			'checkPermission' => $params['checkPermission'] ?? null,
		];

		$tzEnabled = CTimeZone::Enabled();
		if ($tzEnabled)
		{
			CTimeZone::Disable();
		}

		// *** ADD MEETING ROOM ***
		$locOld = self::parseLocation($old);
		$locNew = self::parseLocation($new);
		$res = $locNew['mrid'] ? $locNew['str'] : $new;
		$settings = CCalendar::GetSettings(['request' => false]);
		$RMiblockId = $settings['rm_iblock_id'] ?? null;
		// If not allowed
		if ($RMiblockId && $locOld['mrid'] !== false && $locOld['mrevid'] !== false) // Release MR
		{
			Util::releaseLocation($locOld);
		}

		if ($locNew['mrid'] !== false) // Reserve MR
		{
			$mrevid = false;
			if ($params['bRecreateReserveMeetings'])
			{
				$mrevid = IBlockMeetingRoom::reserveMeetingRoom([
	                'RMiblockId' => $RMiblockId,
	                'mrid' => $locNew['mrid'],
	                'dateFrom' => $params['dateFrom'],
	                'dateTo' => $params['dateTo'],
	                'name' => $params['name'],
	                'description' => Loc::getMessage('EC_RESERVE_FOR_EVENT').': '.$params['name'],
	                'persons' => $params['persons'],
	                'members' => $params['attendees']
                ]);
			}

			else if($locNew['mrevid'] !== false)
			{
				$mrevid = $locNew['mrevid'];
			}

			$locNew =
				($mrevid && $mrevid !== 'reserved' && $mrevid !== 'expire' && $mrevid > 0)
					? 'ECMR_'.$locNew['mrid'].'_'.$mrevid
					: ''
			;
		}

		// Release room
		if (
			$locOld['room_id'] !== false
			&& $locOld['room_event_id'] !== false
			&& $locNew['room_id'] === false
		)
		{
			Util::releaseLocation($locOld);

			$locNew = $locNew['str'];
		}
		//Reserve room if it hasn't reserved before
		else if($locNew['room_id'] && $locOld['room_id'] === false)
		{
			$roomEventId = Manager::reserveRoom([
				'room_id' => $locNew['room_id'],
				'room_event_id' => false,
				'parentParams' => $params['parentParams']
			]);

			$locNew = $roomEventId ? 'calendar_'.$locNew['room_id'].'_'.$roomEventId : '';
		}
		//Update room event if it has been reserved before
		else if (
			$locNew['room_id']
			&& $locOld['room_id']
			&& $locOld['room_event_id']
		)
		{
			$roomEventId = Manager::reserveRoom([
				'room_id' => $locNew['room_id'],
				'room_event_id' => $locOld['room_event_id'],
				'parentParams' => $params['parentParams'],
				'checkPermission' => $params['checkPermission'],
			]);

			$locNew = $roomEventId ? 'calendar_' . $locNew['room_id'] . '_' . $roomEventId : '';
		}
		//String value for location field
		else
		{
			$locNew = $locNew['str'];
		}

		if ($locNew)
		{
			$res = $locNew;
		}

		if ($tzEnabled)
		{
			CTimeZone::Enable();
		}

		return $res;
	}

	/**
	 * @param $loc
	 *
	 * @return void
	 */
	public static function releaseLocation($loc)
	{
		$loc = [
			'room_id' => $loc['room_id'] ?? false,
			'room_event_id' => $loc['room_event_id'] ?? false,
			'mrevid' => $loc['mrevid'] ?? false,
			'mrid' => $loc['mrid'] ?? false,
		];

		if ($loc['room_id'] && $loc['room_event_id'] !== false)
		{
			Manager::releaseRoom([
				'room_id' => $loc['room_id'],
				'room_event_id' => $loc['room_event_id']
			]);
		}

		// Old reserve meeting based on iblock module
		if($loc['mrevid'] && $loc['mrid'])
		{
			$set = CCalendar::GetSettings(['request' => false]);
			if ($set['rm_iblock_id'] ?? null)
			{
				IBlockMeetingRoom::releaseMeetingRoom([
					'mrevid' => $loc['mrevid'],
					'mrid' => $loc['mrid'],
					'RMiblockId' => $set['rm_iblock_id']
				]);
			}
		}
	}

	/**
	 * @param $userId
	 *
	 * @return bool
	 * @throws UnknownActionException
	 * @throws LoaderException
	 */
	public static function getLocationAccess($userId): bool
	{
		$typeModel = TypeModel::createFromXmlId(Dictionary::CALENDAR_TYPE['location']);

		$access = (new TypeAccessController($userId))->check(ActionDictionary::ACTION_TYPE_EDIT, $typeModel, []);

		if (Loader::includeModule('extranet'))
		{
			$access = $access && CExtranet::IsIntranetUser(SITE_ID, $userId);
		}

		return $access;
	}
}