<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Main\Type\DateTime;

class AccessibilityManager
{
	private const TYPE = 'location';
	
	/** @var ?string $dateFrom format : dd.mm.yyyy*/
	private ?string $dateFrom = null;
	/** @var ?string $dateTo format : dd.mm.yyyy*/
	private ?string $dateTo = null;
	/** @var ?array $datesRange*/
	private ?array $datesRange = null;
	/** @var ?array $locationList */
	private ?array $locationList = null;
	
	protected function __construct()
	{
	}
	
	public static function createInstance(): AccessibilityManager
	{
		return new self();
	}
	
	public function setLocationList(?array $locationList = []): AccessibilityManager
	{
		$this->locationList = $locationList;
		
		return $this;
	}
	
	public function setDateFrom(?string $dateFrom = ''): AccessibilityManager
	{
		$this->dateFrom = $dateFrom;
		
		return $this;
	}
	
	public function setDateTo(?string $dateTo = ''): AccessibilityManager
	{
		$this->dateTo = $dateTo;
		
		return $this;
	}
	
	public function setDatesRange(?array $range = []): AccessibilityManager
	{
		$this->datesRange = $range;
		
		return $this;
	}
	
	public function getLocationList(): ?array
	{
		return $this->locationList;
	}
	
	public function getDateFrom(): ?string
	{
		return $this->dateFrom;
	}
	
	public function getDateTo(): ?string
	{
		return $this->dateTo;
	}
	
	public function getDatesRange(): ?array
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
	public static function checkAccessibility(string $locationId = '', array $params = []): bool
	{
		$location = Util::parseLocation($locationId);
		
		$res = true;
		if ($location['room_id'] || $location['mrid'])
		{
			$dateFrom = DateTime::createFromTimestamp(\CCalendar::TimestampUTC($params['fields']['DATE_FROM']))
				->setTimeZone(new \DateTimeZone('UTC'));
			$dateTo = DateTime::createFromTimestamp(\CCalendar::TimestampUTC($params['fields']['DATE_TO']))
				->setTimeZone(new \DateTimeZone('UTC'));

			$fromTs = \Bitrix\Calendar\Util::getDateTimestampUtc($dateFrom, $params['fields']['TZ_FROM']);
			$toTs = \Bitrix\Calendar\Util::getDateTimestampUtc($dateTo, $params['fields']['TZ_FROM']);
			if ($params['fields']['SKIP_TIME'])
			{
				$toTs += \CCalendar::GetDayLen();
			}
			
			$eventId = (int)$params['fields']['ID'];
			
			$from = \Bitrix\Calendar\Util::formatDateTimestampUTC($fromTs);
			$to = \Bitrix\Calendar\Util::formatDateTimestampUTC($toTs);
			
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
					if ((int)$entry['ID'] !== (int)$location['mrevid'])
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
				$entries = self::getRoomAccessibility([$location['room_id']], $from, $to);
				foreach ($entries as $entry)
				{
					if ((int)$entry['ID'] !== (int)$location['room_event_id']
						&& (int)$entry['PARENT_ID'] !== $eventId)
					{
						$entryFromTs = \Bitrix\Calendar\Util::getDateTimestampUtc(new DateTime($entry['DATE_FROM']), $entry['TZ_FROM']);
						$entryToTs = \Bitrix\Calendar\Util::getDateTimestampUtc(new DateTime($entry['DATE_TO']), $entry['TZ_FROM']);
						if ($entry['DT_SKIP_TIME'] === 'Y')
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
	 * @param array $roomIds
	 * @param $from
	 * @param $to
	 *
	 * @return array room accessibility for creating event
	 */
	public static function getRoomAccessibility(array $roomIds, $from, $to): array
	{
		return \CCalendarEvent::GetList([
			'arFilter' => [
				'FROM_LIMIT' => $from,
				'TO_LIMIT' => $to,
				'CAL_TYPE' => self::TYPE,
				'ACTIVE_SECTION' => 'Y',
				'SECTION' => $roomIds,
			],
			'parseRecursion' => true,
			'fetchSection' => true,
			'setDefaultLimit' => false,
		]);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function getLocationAccessibility(): array
	{
		if (!is_array($this->datesRange) || !is_array($this->locationList) || empty($this->datesRange))
		{
			return [];
		}

		$roomIds = array_map(static fn($room) => (int)$room['ID'], $this->locationList);
		$from = $this->datesRange[0];
		$to = $this->datesRange[count($this->datesRange) - 1];

		$entries = self::getRoomAccessibility($roomIds, $from, $to);

		$result = [];

		foreach ($this->datesRange as $date)
		{
			$result[$date] = [];
		}

		foreach ($entries as $entry)
		{
			$roomId = (int)$entry['SECTION_ID'];

			$dateStart = new DateTime($entry['DATE_FROM']);
			$dateEnd = new DateTime($entry['DATE_TO']);
			while ($dateStart->getTimestamp() <= $dateEnd->getTimestamp())
			{
				$date = $dateStart->format('d.m.Y');
				$dateStart->add('1 day');
				if (!isset($result[$date]))
				{
					continue;
				}

				$result[$date][$roomId] ??= [];
				$result[$date][$roomId][] = $entry;
			}
		}

		return $result;
	}
}