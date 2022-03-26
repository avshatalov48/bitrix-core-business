<?php
	
namespace Bitrix\Calendar\Rooms;

use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\Type\DateTime;

class AccessibilityManager
{
	const TYPE = 'location';
	
	/** @var string $dateFrom format : dd.mm.yyyy*/
	private $dateFrom;
	/** @var string $dateTo format : dd.mm.yyyy*/
	private $dateTo;
	/** @var array $datesRange*/
	private $datesRange;
	/** @var array $locationList */
	private $locationList;
	
	protected function __construct()
	{
	}
	
	public static function createInstance(): AccessibilityManager
	{
		return new self();
	}
	
	public function setLocationList($locationList): AccessibilityManager
	{
		$this->locationList = $locationList;
		
		return $this;
	}
	
	public function setDateFrom($dateFrom): AccessibilityManager
	{
		$this->dateFrom = $dateFrom;
		
		return $this;
	}
	
	public function setDateTo($dateTo): AccessibilityManager
	{
		$this->dateTo = $dateTo;
		
		return $this;
	}
	
	public function setDatesRange($range): AccessibilityManager
	{
		$this->datesRange = $range;
		
		return $this;
	}
	
	public function getLocationList(): array
	{
		return $this->locationList;
	}
	
	public function getDateFrom(): string
	{
		return $this->dateFrom;
	}
	
	public function getDateTo(): string
	{
		return $this->dateTo;
	}
	
	public function getDatesRange(): array
	{
		return $this->datesRange;
	}
	
	/**
	 * @param string $location
	 * @param array $params
	 *
	 * Checks if room is accessible for meeting
	 *
	 * @return bool
	 */
	public static function checkAccessibility(string $location = '', array $params = []): bool
	{
		$location = Util::parseLocation($location);
		
		$res = true;
		if ($location['room_id'] || $location['mrid'])
		{
			$fromTs = \CCalendar::Timestamp($params['fields']['DATE_FROM']);
			$toTs = \CCalendar::Timestamp($params['fields']['DATE_TO']);
			if ($params['fields']['SKIP_TIME'])
			{
				$toTs += \CCalendar::GetDayLen();
			}
			
			$eventId = (int)$params['fields']['ID'];
			
			$from = \CCalendar::Date($fromTs, false);
			$to = \CCalendar::Date($toTs, false);
			
			$curUserId = \CCalendar::GetCurUserId();
			$deltaOffset =
				isset($params['timezone'])
					? (\CCalendar::GetTimezoneOffset($params['timezone']) - \CCalendar::GetCurrentOffsetUTC($curUserId))
					: 0
			;
			
			if ($location['mrid'])
			{
				$meetingRoomRes = IBlockMeetingRoom::getAccessibilityForMeetingRoom([
					'allowReserveMeeting' => true,
					'id' => $location['mrid'],
					'from' => \CCalendar::Date(
						$fromTs - \CCalendar::DAY_LENGTH,
						false
					),
					'to' => \CCalendar::Date(
						$toTs + \CCalendar::DAY_LENGTH,
						false
					),
					'curEventId' => $location['mrevid'],
				]);
				
				foreach ($meetingRoomRes as $entry)
				{
					if ($entry['ID'] != $location['mrevid'])
					{
						$entryFromTs = \CCalendar::Timestamp($entry['DT_FROM']);
						$entryToTs = \CCalendar::Timestamp($entry['DT_TO']);
						
						if ($entryFromTs < $toTs && $entryToTs > $fromTs)
						{
							$res = false;
							break;
						}
					}
				}
			}
			elseif ($location['room_id'])
			{
				$entries = AccessibilityManager::getRoomAccessibility($location['room_id'], $from, $to);
				foreach ($entries as $entry)
				{
					if ((int)$entry['ID'] !== (int)$location['room_event_id']
						&& (int)$entry['PARENT_ID'] !== $eventId)
					{
						$entryFromTs = \CCalendar::Timestamp($entry['DATE_FROM']);
						$entryToTs = \CCalendar::Timestamp($entry['DATE_TO']);
						if ($entry['DT_SKIP_TIME'] !== 'Y')
						{
							$entryFromTs -= $entry['~USER_OFFSET_FROM'];
							$entryToTs -= $entry['~USER_OFFSET_TO'];
							$entryFromTs += $deltaOffset;
							$entryToTs += $deltaOffset;
						}
						else
						{
							$entryToTs += \CCalendar::GetDayLen();
						}
						
						if ($entryFromTs < $toTs && $entryToTs > $fromTs)
						{
							$res = false;
							break;
						}
					}
				}
			}
		}
		
		return $res;
	}
	
	/**
	 * @param $roomId
	 * @param $from
	 * @param $to
	 *
	 * @return array room accessibility for creating event
	 */
	public static function getRoomAccessibility($roomId, $from, $to): array
	{
		$accessibility = [];
		
		$roomEvents = \CCalendarEvent::GetList([
			'arFilter' => [
				'FROM_LIMIT' => $from,
				'TO_LIMIT' => $to,
				'CAL_TYPE' => self::TYPE,
				'ACTIVE_SECTION' => 'Y',
				'SECTION' => $roomId,
			],
			'parseRecursion' => true,
			'fetchSection' => true,
			'setDefaultLimit' => false,
		]);
		
		
		foreach ($roomEvents as $roomEvent)
		{
			$accessibility[] = [
				'ID' => $roomEvent['ID'],
				'PARENT_ID' => $roomEvent['PARENT_ID'],
				'NAME' => $roomEvent['NAME'],
				'DATE_FROM' => $roomEvent['DATE_FROM'],
				'DATE_TO' => $roomEvent['DATE_TO'],
				'~USER_OFFSET_FROM' => $roomEvent['~USER_OFFSET_FROM'],
				'~USER_OFFSET_TO' => $roomEvent['~USER_OFFSET_TO'],
				'DT_SKIP_TIME' => $roomEvent['DT_SKIP_TIME'],
				'TZ_FROM' => $roomEvent['TZ_FROM'],
				'TZ_TO' => $roomEvent['TZ_TO'],
				'ACCESSIBILITY' => $roomEvent['ACCESSIBILITY'],
				'IMPORTANCE' => $roomEvent['IMPORTANCE'],
				'EVENT_TYPE' => $roomEvent['EVENT_TYPE'],
			];
		}
		
		return $accessibility;
	}
	
	/**
	 * @return array
	 */
	public function getLocationAccessibility(): array
	{
		$result = [];
		$datesLength = count($this->datesRange);
		if (!$datesLength)
		{
			return $result;
		}
		
		foreach ($this->datesRange as $date)
		{
			$result[$date] = [];
		}
		
		foreach ($this->locationList as $location)
		{
			$roomId = (int)$location['ID'];
			$entries = AccessibilityManager::getRoomAccessibility(
				$roomId,
				$this->datesRange[0],
				$this->datesRange[$datesLength - 1]
			);
			foreach ($entries as $entry)
			{
				$date = new DateTime($entry['DATE_FROM']);
				$date = $date->format('d.m.Y');
				if (!isset($result[$date]))
				{
					continue;
				}
				if (!isset($result[$date][$roomId]))
				{
					$result[$date][$roomId] = [];
				}
				
				$result[$date][$roomId][] = $entry;
			}
		}
		
		return $result;
	}
}