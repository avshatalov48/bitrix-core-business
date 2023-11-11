<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CPHPCache;

class IBlockMeetingRoom
{
	private static $meetingRoomList;
	private static $allowReserveMeeting = true;

	/**
	 * @param array $params
	 *
	 * @return array
	 * @throws LoaderException
	 */
	public static function getMeetingRoomList(array $params = []): array
	{
		if (\COption::GetOptionString('calendar', 'eventWithLocationConverted', 'N') === 'Y')
		{
			$meetingRoomList = [];
			self::$meetingRoomList = $meetingRoomList;

			return $meetingRoomList;
		}
		if (isset(self::$meetingRoomList))
		{
			$meetingRoomList = self::$meetingRoomList;
		}
		else
		{
			$meetingRoomList = [];
			if (!\CCalendar::IsBitrix24() && Loader::includeModule('iblock'))
			{
				if (!isset($params['RMiblockId']) && !isset($params['VMiblockId']))
				{
					$settings = \CCalendar::GetSettings();
					$pathsForSite = \CCalendar::GetSettings([
						'forseGetSitePathes' => true,
						'site' => \CCalendar::GetSiteId()
                    ]);;
					$RMiblockId = $settings['rm_iblock_id'];
					$pathToMR = $pathsForSite['path_to_rm'];
				}
				else
				{
					$RMiblockId = $params['RMiblockId'];
					$pathToMR = $params['pathToMR'];
				}
				
				if (self::$allowReserveMeeting && !\CCalendar::IsAdmin() && (\CIBlock::GetPermission($RMiblockId) < 'R'))
				{
					self::$allowReserveMeeting = false;
				}
				
				if ((int)$RMiblockId > 0 && \CIBlock::GetPermission($RMiblockId) >= 'R' && self::$allowReserveMeeting)
				{
					$orderBy = [
						'NAME' => 'ASC',
						'ID' => 'DESC'
					];
					$filter = [
						'IBLOCK_ID' => $RMiblockId,
						'ACTIVE' => 'Y'
					];
					$selectFields = [
						'IBLOCK_ID',
						'ID',
						'NAME',
						'DESCRIPTION',
						'UF_FLOOR',
						'UF_PLACE',
						'UF_PHONE'
					];
					$res = \CIBlockSection::GetList($orderBy, $filter, false, $selectFields );
					while ($arMeeting = $res->GetNext())
					{
						$meetingRoomList[] = [
							'ID' => $arMeeting['ID'],
							'NAME' => $arMeeting['~NAME'],
							'DESCRIPTION' => $arMeeting['~DESCRIPTION'],
							'UF_PLACE' => $arMeeting['UF_PLACE'],
							'UF_PHONE' => $arMeeting['UF_PHONE'],
							'URL' => str_replace(
								['#id#', '#ID#'],
								$arMeeting['ID'],
								$pathToMR
							)
						];
					}
				}
			}
			self::$meetingRoomList = $meetingRoomList;
		}
		
		return $meetingRoomList;
	}

	/**
	 * @param $params
	 *
	 * @return array|false
	 * @throws LoaderException
	 */
	public static function getMeetingRoomById($params)
	{
		if (!Loader::includeModule('iblock'))
		{
			return false;
		}

		if ((int)$params['RMiblockId'] > 0 && \CIBlock::GetPermission($params['RMiblockId']) >= 'R')
		{
			$filter = [
				'IBLOCK_ID' => $params['RMiblockId'],
				'ACTIVE' => 'Y',
				'ID' => $params['id']
			];
			$selectFields = ['NAME'];
			$res = \CIBlockSection::GetList([], $filter, false, $selectFields);
			if ($meeting = $res->GetNext())
			{
				return $meeting;
			}
		}
		
		if ((int)$params['VMiblockId'] > 0 && \CIBlock::GetPermission($params['VMiblockId']) >= 'R')
		{
			$filter = [
				'IBLOCK_ID' => $params['VMiblockId'],
				'ACTIVE' => 'Y'
			];
			$selectFields = [
				'ID',
				'NAME',
				'DESCRIPTION',
				'IBLOCK_ID'
			];
			$res = \CIBlockSection::GetList([], $filter, false, $selectFields);
			if ($meeting = $res->GetNext())
			{
				return [
					'ID' => $params['VMiblockId'],
					'NAME' => $meeting['NAME'],
					'DESCRIPTION' => $meeting['DESCRIPTION'],
				];
			}
		}
		
		return false;
	}

	/**
	 * @param array $params
	 *
	 * @return int
	 * @throws LoaderException
	 */
	public static function reserveMeetingRoom(array $params)
	{
		if (!Loader::includeModule('iblock'))
		{
			return false;
		}

		$tst = MakeTimeStamp($params['dateTo']);
		if (date('H:i', $tst) === '00:00')
		{
			$params['dateTo'] = \CIBlockFormatProperties::DateFormat(
				\CCalendar::DFormat(true),
				$tst + (23 * 60 + 59) * 60
			);
		}
		
		$settings = \CCalendar::GetSettings(['request' => false]);
		$params['RMiblockId'] = $settings['rm_iblock_id'];
		
		$check = self::checkMeetingRoom($params);
		if ($check !== true)
		{
			return $check;
		}
		
		$fields = [
			'IBLOCK_ID' => $params['RMiblockId'],
			'IBLOCK_SECTION_ID' => $params['mrid'],
			'NAME' => $params['name'],
			'DATE_ACTIVE_FROM' => $params['dateFrom'],
			'DATE_ACTIVE_TO' => $params['dateTo'],
			'CREATED_BY' => \CCalendar::GetCurUserId(),
			'DETAIL_TEXT' => $params['description'],
			'PROPERTY_VALUES' => [
				'UF_PERSONS' => $params['persons'],
				'PERIOD_TYPE' => 'NONE'
			],
			'ACTIVE' => 'Y'
		];
		
		$iBlockElem= new \CIBlockElement;
		$id = $iBlockElem->Add($fields);
		
		// Hack: reserve meeting calendar based on old calendar's cache
		$cache = new CPHPCache;
		$cache->CleanDir('event_calendar/');
		$cache->CleanDir('event_calendar/events/');
		$cache->CleanDir('event_calendar/events/'.$params['RMiblockId']);
		
		return (int)$id;
	}

	/**
	 * @param array $params
	 * @throws LoaderException
	 */
	public static function releaseMeetingRoom(array $params): void
	{
		if (!Loader::includeModule('iblock'))
		{
			return;
		}

		$settings = \CCalendar::GetSettings(['request' => false]);
		$params['RMiblockId'] = $settings['rm_iblock_id'];
		
		$filter = [
			'ID' => $params['mrevid'],
			'IBLOCK_ID' => $params['RMiblockId'],
			'IBLOCK_SECTION_ID' => $params['mrid'],
			'SECTION_ID' => [$params['mrid']]
		];
		
		$res = \CIBlockElement::GetList([], $filter, false, false, ['ID']);
		if ($res->Fetch())
		{
			$iBlockElem = new \CIBlockElement;
			$iBlockElem::Delete($params['mrevid']);
		}
		
		// Hack: reserve meeting calendar based on old calendar's cache
		$cache = new CPHPCache;
		$cache->CleanDir('event_calendar/');
		$cache->CleanDir('event_calendar/events/');
		$cache->CleanDir('event_calendar/events/'.$params['RMiblockId']);
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 * @throws LoaderException
	 */
	public static function getAccessibilityForMeetingRoom(array $params): array
	{
		if (!Loader::includeModule('iblock'))
		{
			return [];
		}

		$allowReserveMeeting =
			$params['allowReserveMeeting'] ?? self::$allowReserveMeeting
		;
		$settings = \CCalendar::GetSettings(['request' => false]);
		$RMiblockId = $settings['rm_iblock_id'];
		$curEventId = $params['curEventId'] ?? false;
		$result = [];
		$offset = \CCalendar::GetOffset();
		
		if ($allowReserveMeeting)
		{
			$select = [
				'ID',
				'NAME',
				'IBLOCK_SECTION_ID',
				'IBLOCK_ID',
				'ACTIVE_FROM',
				'ACTIVE_TO'
			];
			$filter = [
				'IBLOCK_ID' => $RMiblockId,
				'SECTION_ID' => $params['id'],
				'INCLUDE_SUBSECTIONS' => 'Y',
				'ACTIVE' => 'Y',
				'CHECK_PERMISSIONS' => 'N',
				'>=DATE_ACTIVE_TO' => $params['from'],
				'<=DATE_ACTIVE_FROM' => $params['to']
			];
			if ((int)$curEventId > 0)
			{
				$filter['!ID'] = (int)$curEventId;
			}
			
			$rsElement = \CIBlockElement::GetList(['ACTIVE_FROM' => 'ASC'], $filter, false, false, $select);
			while($iBlockElem = $rsElement->GetNextElement())
			{
				$item = $iBlockElem->GetFields();
				$item['DISPLAY_ACTIVE_FROM'] = \CIBlockFormatProperties::DateFormat(
					\CCalendar::DFormat(true), MakeTimeStamp($item['ACTIVE_FROM'])
				);
				$item['DISPLAY_ACTIVE_TO'] = \CIBlockFormatProperties::DateFormat(
					\CCalendar::DFormat(true), MakeTimeStamp($item['ACTIVE_TO'])
				);
				
				$result[] = [
					'ID' => (int)$item['ID'],
					'NAME' => $item['~NAME'],
					'DT_FROM' => \CCalendar::CutZeroTime($item['DISPLAY_ACTIVE_FROM']),
					'DT_TO' => \CCalendar::CutZeroTime($item['DISPLAY_ACTIVE_TO']),
					'DT_FROM_TS' => (\CCalendar::Timestamp($item['DISPLAY_ACTIVE_FROM']) - $offset) * 1000,
					'DT_TO_TS' => (\CCalendar::Timestamp($item['DISPLAY_ACTIVE_TO']) - $offset) * 1000
				];
			}
		}
		
		return $result;
	}

	/**
	 * @param $params
	 *
	 * @return bool|string
	 * @throws LoaderException
	 */
	public static function checkMeetingRoom($params)
	{
		if (!Loader::includeModule('iblock'))
		{
			return false;
		}

		$fromDateTime = MakeTimeStamp($params['dateFrom']);
		$toDateTime = MakeTimeStamp($params['dateTo']);
		$filter = [
			'ACTIVE' => 'Y',
			'IBLOCK_ID' => $params['RMiblockId'],
			'SECTION_ID' => $params['mrid'],
			'<DATE_ACTIVE_FROM' => $params['dateTo'],
			'>DATE_ACTIVE_TO' => $params['dateFrom'],
			'PROPERTY_PERIOD_TYPE' => 'NONE',
		];
		
		if ($params['mrevid_old'] > 0)
		{
			$filter['!=ID'] = $params['mrevid_old'];
		}

		$dbElements = \CIBlockElement::GetList(
			['DATE_ACTIVE_FROM' => 'ASC'],
			$filter,
			false,
			false,
			['ID']
		);
		if ($elements = $dbElements->GetNext())
		{
			return 'reserved';
		}
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/intranet.reserve_meeting/init.php');
		$periodicElements = __IRM_SearchPeriodic($fromDateTime, $toDateTime, $params['RMiblockId'], $params['mrid']);

		foreach ($periodicElements as $element)
		{
			if (!$params['mrevid_old'] || (int)$element['ID'] !== (int)$params['mrevid_old'])
			{
				return 'reserved';
			}
		}

		return true;
	}
}