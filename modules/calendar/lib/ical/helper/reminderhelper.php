<?php

namespace Bitrix\Calendar\ICal\Helper;

use Bitrix\Calendar\Core\Event\Properties\Remind;
use Bitrix\Calendar\ICal\IcsBuilder;
use Bitrix\Calendar\Util;

final class ReminderHelper
{
	/**
	 * @param Remind $remind
	 * @param bool $isFullDay
	 *
	 * @return array|null[]
	 * @throws \Bitrix\Calendar\Core\Event\Tools\PropertyException
	 */
	public static function prepareReminderValue(
		Remind $remind,
		bool $isFullDay
	): array
	{
		$valueType = '';
		$value = '';

		if ($remind->getUnits() === 'minutes')
		{
			$valueType = 'DURATION';
			if ($remind->getTime() === 60 || $remind->getTime() === 120)
			{
				$value = '-PT' . $remind->getTime() / 60 . 'H';
			}
			else if ($remind->getTime() === 0)
			{
				$value = 'PT' . $remind->getTime() . 'S';
			}
			else
			{
				$value = '-PT' . $remind->getTime() . 'M';
			}
		}
		else if ($remind->getSpecificTime() && $remind->getDaysBefore() !== null)
		{
			$valueType = 'DURATION';
			$diff = $remind->getTimeBeforeStartInMinutes();
			$parsedDiff = Util::minutesToDayHoursMinutes(abs($diff));
			if ($isFullDay  && $remind->getDaysBefore() === 0)
			{
				$value = 'PT' . $parsedDiff['hours'] . 'H';
			}
			else if (
				($remind->getDaysBefore() === 0 && !$isFullDay && $diff > 0)
				|| ($remind->getDaysBefore() === 1 && $parsedDiff['days'] === 0)
			)
			{
				$hours = '';
				$minutes = '';
				if ($parsedDiff['hours'])
				{
					$hours = $parsedDiff['hours'] . 'H';
				}
				if ($parsedDiff['minutes'])
				{
					$minutes = $parsedDiff['minutes'] . 'M';
				}
				$value = '-PT' . $hours . $minutes;
			}
			else if ($parsedDiff['days'] > 0)
			{
				$hours = '';
				$minutes = '';
				$value = '-P' . $parsedDiff['days'] . 'D';
				if ($parsedDiff['hours'])
				{
					$hours = $parsedDiff['hours'] . 'H';
				}
				if ($parsedDiff['minutes'])
				{
					$minutes = $parsedDiff['minutes'] . 'M';
				}
				if ($hours || $minutes)
				{
					$value .= 'T' . $hours . $minutes;
				}
			}
			else
			{
				return [null, null];
			}
		}
		else if ($remind->getSpecificTime())
		{
			$valueType = 'DATE-TIME';
			$value = date(IcsBuilder::UTC_DATETIME_FORMAT, $remind->getSpecificTime()->getTimestamp());
		}

		return [$value, $valueType];
	}
}
