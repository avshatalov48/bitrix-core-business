import { Type } from 'main.core';
import { isDateAfter } from './is-date-after';
import { isDateBefore } from './is-date-before';
import { isDatesEqual } from './is-dates-equal';
import { copyTime } from './copy-time';

type DateRange = [] | [Date] | [Date, Date];

export function addToRange(date, range: DateRange = []): DateRange
{
	const [from = null, to = null] = Type.isArray(range) ? range : [];
	if (from !== null && to !== null)
	{
		if (isDatesEqual(to, date) && isDatesEqual(from, date))
		{
			return [];
		}

		if (isDatesEqual(to, date))
		{
			return [to];
		}

		if (isDatesEqual(from, date))
		{
			// return [from];
			return [];
		}

		if (isDateAfter(from, date))
		{
			copyTime(from, date);

			return [date, to];
		}

		copyTime(to, date);

		return [from, date];
	}

	if (to !== null)
	{
		if (isDateAfter(date, to))
		{
			return [to, date];
		}

		return [date, to];
	}

	if (from !== null)
	{
		if (isDateBefore(date, from))
		{
			return [date, from];
		}

		return [from, date];
	}

	return [date];
}
