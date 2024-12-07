import { Type } from 'main.core';
import { getDate } from './get-date';

export function isDatesEqual(dateA, dateB, precision: 'day' | 'datetime' | 'month' | 'year' = 'day'): boolean
{
	if (!Type.isDate(dateA) || !Type.isDate(dateB))
	{
		return false;
	}

	const {
		day: dayA,
		month: monthA,
		year: yearA,
		hours: hoursA,
		minutes: minutesA,
		seconds: secondsA,
	} = getDate(dateA);

	const {
		day: dayB,
		month: monthB,
		year: yearB,
		hours: hoursB,
		minutes: minutesB,
		seconds: secondsB,
	} = getDate(dateB);

	if (precision === 'day')
	{
		return dayA === dayB && monthA === monthB && yearA === yearB;
	}

	if (precision === 'datetime')
	{
		return (
			dayA === dayB
			&& monthA === monthB
			&& yearA === yearB
			&& hoursA === hoursB
			&& minutesA === minutesB
			&& secondsA === secondsB
		);
	}

	if (precision === 'month')
	{
		return monthA === monthB && yearA === yearB;
	}

	if (precision === 'year')
	{
		return yearA === yearB;
	}

	return false;
}
