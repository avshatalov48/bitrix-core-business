import { cloneDate } from './clone-date';

export function setTime(
	date: Date,
	hours: number | null = 0,
	minutes: number | null = 0,
	seconds: number | null = 0,
): Date
{
	const newDate = cloneDate(date);
	if (hours !== null)
	{
		newDate.setUTCHours(hours);
	}

	if (minutes !== null)
	{
		newDate.setUTCMinutes(minutes);
	}

	if (seconds !== null)
	{
		newDate.setUTCSeconds(seconds);
	}

	return newDate;
}
