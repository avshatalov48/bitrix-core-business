<?php

namespace Bitrix\Calendar\Core\Event\Tools;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core;
use Bitrix\Main\Type\Date;
use DateTime;
use Exception;

class Recurrence
{
	public const PERIOD_TYPES = [
		'WEEKLY' => 'WEEKLY',
		'HOURLY' => 'HOURLY',
		'DAILY' => 'DAILY',
		'MONTHLY' => 'MONTHLY',
		'YEARLY' => 'YEARLY',
	];

	private const UNIT_MAP = [
		'WEEKLY' => 'week',
		// 'HOURLY' => 'hour',
		'DAILY' => 'day',
		'MONTHLY' => 'month',
		'YEARLY' => 'year',
	];

	private const WEEK_DAYS_SHORT = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

	private const WEEK_DAYS = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

	/**
	 * @param Event $event
	 * @param array $params = {
	 * limitCount: int, //
	 * limitDateFrom: DateTime //
	 * limitDateTo: DateTime //
	 * }
	 *
	 * @return DateTime[]
	 *
	 * @throws BaseException*@throws Exception
	 * @throws Exception
	 */
	public function getEventOccurenceDates(Event $event, array $params = []): array
	{
		$limits = $this->prepareLimits($event, $params);

		$result = [];
		$counter = 0;

		$pushToResult = function (DateTime $date) use (&$result, &$counter)
		{
			$result[$date->format('d.m.Y')] = $date;
			$counter++;
		};

		$start = clone $event->getStart();
		$start->resetTime();
		$date = $this->convertBitrixDateToPhpDate($start);

		while ($this->isDateWithinLimits($date, $limits, $counter))
		{
			if ($event->getRecurringRule()->getFrequency() === self::PERIOD_TYPES['WEEKLY'])
			{
				if (empty($started))
				{
					$started = true;
					// return array like [0, 2, 5] for sunday, tuesday, friday
					$weekDays = $weekDays ?? $this->prepareWeekDays($event->getRecurringRule()->getByday());
					$weekDay = (int)$date->format('w');
					if (!in_array($weekDay, $weekDays))
					{
						$this->moveToNextWeekDay($date, $weekDays);
					}
				}
				else
				{
					$pushToResult(clone $date);
					$this->moveToNextWeekDay($date, $weekDays);
				}
			}
			else
			{
				$pushToResult(clone $date);
				$unit = self::UNIT_MAP[$event->getRecurringRule()->getFrequency()] ?? null;
				if ($unit === null)
				{
					throw new BaseException(
						"Unsupported frequency type: " . $event->getRecurringRule()->getFrequency(),
						406,
						__FILE__,
						__LINE__
					);
				}

				$date->modify('+' . $event->getRecurringRule()->getInterval() . ' ' . $unit);
			}
		}

		$this->removeExcludedDates($event, $result);

		return $result;
	}

	/**
	 * @param array $getByday
	 *
	 * @return array
	 */
	private function prepareWeekDays(array $getByday): array
	{
		$days = array_flip(self::WEEK_DAYS_SHORT);
		return array_map(function ($val) use ($days) {
			return $days[$val];
		}, $getByday);
	}

	/**
	 * @param DateTime $date
	 * @param array $weekDays
	 *
	 * @return void
	 */
	private function moveToNextWeekDay(DateTime $date, array $weekDays): void
	{
		$current = (int)$date->format('w');
		foreach ($weekDays as $weekDay)
		{
			if ($weekDay > $current)
			{
				$nextDay = $weekDay;
				break;
			}
		}
		$nextDayIndex = $nextDay ?? reset($weekDays);
		$nextDayName = self::WEEK_DAYS[$nextDayIndex];
		$date->modify("next $nextDayName");
	}

	/**
	 * @param Event $event
	 * @param array $params
	 *
	 * @return array
	 * @throws Exception
	 */
	private function prepareLimits(Event $event, array $params): array
	{
		$getCount = static function (Event $event, array $params)
		{
			return $params['limitCount']
				?? $event->getRecurringRule()
					? $event->getRecurringRule()->getCount()
					: null
				;
		};

		$getFrom = function (Event $event, array $params)
		{
			if (!empty($params['limitDateFrom']))
			{
				return max(
					$this->convertBitrixDateToPhpDate($params['limitDateFrom']),
					$this->convertBitrixDateToPhpDate($event->getStart())
				);
			}
			else
			{
				return $this->convertBitrixDateToPhpDate($event->getStart());
			}
		};

		$getTo = function (Event $event, array $params)
		{
			$until = (!is_null($event->getRecurringRule()) && $event->getRecurringRule()->hasUntil())
				? $this->convertBitrixDateToPhpDate($event->getRecurringRule()->getUntil())
				: null;
			if (empty($params['limitDateTo']))
			{
				return $until;
			}
			elseif ($until)
			{
				return min(
					$this->convertBitrixDateToPhpDate($params['limitDateTo']),
					$until
				);
			}
			else
			{
				return $params['limitDateTo'];
			}
		};

		return [
			'count' => $getCount($event, $params),
			'from' => $getFrom($event, $params),
			'to' => $getTo($event, $params),
		];
	}

	/**
	 * @param Core\Base\Date|Date $date
	 *
	 * @return DateTime
	 * @throws Exception
	 */
	private function convertBitrixDateToPhpDate($date): DateTime
	{
		return new DateTime($date->format('c'));
	}

	/**
	 * @param DateTime $date
	 * @param array $limits
	 * @param int $counter
	 *
	 * @return bool
	 */
	private function isDateWithinLimits(DateTime $date, array $limits, int $counter): bool
	{
		return (empty($limits['count']) || $counter < $limits['count'])
			&& ($date->format('Ymd') >= $limits['from']->format('Ymd'))
			&& (empty($limits['to']) || $date->format('Ymd') <= $limits['to']->format('Ymd'))
			;
	}

	/**
	 * @param Event $event
	 * @param array &$result
	 *
	 * @return void
	 */
	private function removeExcludedDates(Event $event, array &$result): void
	{
		if ($event->getExcludedDateCollection() && $event->getExcludedDateCollection()->count())
		{
			/** @var Core\Base\Date $excludedDate */
			foreach ($event->getExcludedDateCollection() as $excludedDate)
			{
				$key = $excludedDate->format('d.m.Y');
				if (array_key_exists($key, $result))
				{
					unset($result[$key]);
				}
			}
		}
	}
}
