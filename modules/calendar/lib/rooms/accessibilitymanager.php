<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Main\Config\Option;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;

class AccessibilityManager
{
	/** @var string  */
	private const TYPE = 'location';
	/** @var string  */
	public const ADDITIONAL_LOCATION_CONNECTION_OPTION = 'additional_location_connection';

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
	 * @param string $locationId
	 * @param array $params
	 *
	 * Checks if room is accessible for meeting
	 *
	 * @return bool
	 * @throws LoaderException
	 * @throws ObjectException
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

			if ($location['room_id'])
			{
				$sections = [$location['room_id']];
				$additionalLocationConnection = self::getAdditionalLocationAccessibilityConnection((int)$location['room_id']);
				if (!empty($additionalLocationConnection))
				{
					$sections = [
						...$sections,
						...$additionalLocationConnection,
					];
				}

				$entries = self::getRoomAccessibility($sections, $from, $to);
				$res = self::checkLocationAccessibility($entries, $fromTs, $toTs, $location['room_event_id'], $eventId);
			}

			/** @deprecated  */
			else if ($location['mrid'])
			{
				$res = self::checkIBlockAccessibility($location, $fromTs, $toTs);
			}
		}

		return $res;
	}


	/**
	 * @param array $entries
	 * @param int $fromTs
	 * @param int $toTs
	 * @param mixed $roomEventId
	 * @param mixed $eventId
	 * @return bool
	 * @throws ObjectException
	 */
	public static function checkLocationAccessibility(array $entries, int $fromTs, int $toTs, mixed $roomEventId, mixed $eventId): bool
	{
		$result = true;

		foreach ($entries as $entry)
		{
			if (
				(int)$entry['ID'] !== (int)$roomEventId
				&& (int)$entry['PARENT_ID'] !== $eventId
			)
			{
				$entryFromTs = \Bitrix\Calendar\Util::getDateTimestampUtc(
					new DateTime($entry['DATE_FROM']), $entry['TZ_FROM']
				);
				$entryToTs = \Bitrix\Calendar\Util::getDateTimestampUtc(
					new DateTime($entry['DATE_TO']), $entry['TZ_FROM']
				);
				if ($entry['DT_SKIP_TIME'] === 'Y')
				{
					$entryToTs += \CCalendar::GetDayLen();
				}

				if ($entryFromTs < $toTs && $entryToTs > $fromTs)
				{
					$result = false;

					break;
				}
			}
		}

		return $result;
	}


	/**
	 * @param int $roomId
	 * @return array
	 */
	public static function getAdditionalLocationAccessibilityConnection(int $roomId): array
	{
		$currentOption = Option::get('calendar', self::ADDITIONAL_LOCATION_CONNECTION_OPTION);
		$decodedOption = unserialize($currentOption, ['allowed_classes' => false]);

		return $decodedOption[$roomId] ?? [];
	}


	/**
	 * @param array $additionalRoomConnectionInfo
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function setFullAdditionalLocationAccessibilityConnection(array $additionalRoomConnectionInfo): void
	{
		$result = serialize($additionalRoomConnectionInfo);

		Option::set('calendar', self::ADDITIONAL_LOCATION_CONNECTION_OPTION, $result);
	}

	/**
	 * @param int $roomId
	 * @param array $additionalRoomId
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function setAdditionalLocationAccessibilityConnection(int $roomId, array $additionalRoomId): void
	{
		$currentOption = Option::get('calendar', self::ADDITIONAL_LOCATION_CONNECTION_OPTION);
		$decodedOption = unserialize($currentOption, ['allowed_classes' => false]);

		if (!is_array($decodedOption))
		{
			$decodedOption = [];
		}

		$decodedOption[$roomId] = $additionalRoomId;
		$result = serialize($decodedOption);

		Option::set('calendar', self::ADDITIONAL_LOCATION_CONNECTION_OPTION, $result);
	}

	/**
	 * @param array $location
	 * @param int $fromTs
	 * @param int $toTs
	 * @return bool
	 * @throws LoaderException
	 */
	public static function checkIBlockAccessibility(array $location, int $fromTs, int $toTs): bool
	{
		$result = true;

		$meetingRoomRes = IBlockMeetingRoom::getAccessibilityForMeetingRoom([
			'allowReserveMeeting' => true,
			'id' => $location['mrid'],
			'from' => \CCalendar::Date($fromTs - \CCalendar::DAY_LENGTH, false),
			'to' => \CCalendar::Date($toTs + \CCalendar::DAY_LENGTH, false),
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
					$result = false;

					break;
				}
			}
		}
		return $result;
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
			'arSelect' => \CCalendarEvent::$defaultSelectEvent,
			'parseRecursion' => true,
			'fetchSection' => true,
			'setDefaultLimit' => false,
			'fetchAttendees' => false,
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
