<?php

namespace Bitrix\Calendar\Sharing;

use Bitrix\Main\Type\DateTime;

class SharingAccessibilityManager
{
	/** @var int  */
	private int $userId;
	/** @var int  */
	private int $timestampFrom;
	/** @var int  */
	private int $timestampTo;
	/** @var string|null  */
	private ?string $timezone;

	/**
	 * @param $options
	 */
	public function __construct($options)
	{
		$this->userId = $options['userId'];
		$this->timestampFrom = $options['timestampFrom'];
		$this->timestampTo = $options['timestampTo'];
		$this->timezone = $options['timezone'] ?? null;
	}

	/**
	 * @return bool
	 */
	public function checkUserAccessibility(): bool
	{
		$this->timestampFrom -= \CCalendar::GetTimezoneOffset($this->timezone, $this->timestampFrom);
		$this->timestampTo -= \CCalendar::GetTimezoneOffset($this->timezone, $this->timestampTo);

		$accessibility = \CCalendar::GetAccessibilityForUsers([
			'users' => [$this->userId],
			'from' => \CCalendar::Date($this->timestampFrom, false),
			'to' => \CCalendar::Date($this->timestampTo, false),
			'checkPermissions' => false
		]);
		$events = $accessibility[$this->userId];

		foreach ($events as $event)
		{
			$eventTsFrom = \CCalendar::Timestamp($event['DATE_FROM']);
			$eventTsTo = \CCalendar::Timestamp($event['DATE_TO']);

			if ($event['DT_SKIP_TIME'] === 'Y')
			{
				$eventTsTo += \CCalendar::GetDayLen();
			}

			$eventTsFrom -= \CCalendar::GetTimezoneOffset($event['TZ_FROM'], $eventTsFrom);
			$eventTsTo -= \CCalendar::GetTimezoneOffset($event['TZ_TO'], $eventTsTo);

			if ($eventTsFrom < $this->timestampTo && $eventTsTo > $this->timestampFrom)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function getUserAccessibilitySegmentsInUtc(): array
	{
		$result = [];

		$accessibility = \CCalendar::GetAccessibilityForUsers([
			'users' => [$this->userId],
			'from' => \CCalendar::Date($this->timestampFrom, false),
			'to' => \CCalendar::Date($this->timestampTo, false),
			'checkPermissions' => false
		]);

		$userEvents = $accessibility[$this->userId];

		foreach ($userEvents as $event)
		{
			$eventTsFromUTC = Helper::getEventTimestampUTC(new DateTime($event['DATE_FROM']), $event['TZ_FROM']);
			$eventTsToUTC = Helper::getEventTimestampUTC(new DateTime($event['DATE_TO']), $event['TZ_TO']);

			if ($event['DT_SKIP_TIME'] === 'Y')
			{
				$eventTsToUTC += \CCalendar::GetDayLen();
			}

			$result[] = [
				'timestampFromUTC' => $eventTsFromUTC,
				'timestampToUTC' => $eventTsToUTC,
				'accessibility' => $event['ACCESSIBILITY'],
			];
		}

		return $result;
	}
}