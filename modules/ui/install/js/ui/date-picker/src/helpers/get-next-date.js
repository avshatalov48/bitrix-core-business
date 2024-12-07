import { addDate } from './add-date';
import { cloneDate } from './clone-date';

export function getNextDate(date, unit, increment = 1, firstWeekDay = 0): Date
{
	let newDate = cloneDate(date);
	switch (unit)
	{
		case 'day':
			newDate.setUTCMinutes(0, 0, 0);
			newDate = addDate(newDate, 'day', increment);
			break;
		case 'week': {
			const dayOfWeek = newDate.getUTCDay();
			newDate = addDate(
				newDate,
				'day',
				(7 * (increment - 1))
				+ (dayOfWeek < firstWeekDay
					? (firstWeekDay - dayOfWeek)
					: (7 - dayOfWeek + firstWeekDay)
				),
			);

			break;
		}
		case 'month':
			newDate = addDate(newDate, 'month', increment);
			newDate.setUTCDate(1);
			break;
		case 'quarter':
			newDate = addDate(newDate, 'month', ((increment - 1) * 3) + (3 - (newDate.getUTCMonth() % 3)));
			break;
		case 'year':
			newDate = new Date(Date.UTC(newDate.getUTCFullYear() + increment, 0, 1));
			break;
		default:
			newDate = addDate(date, unit, increment);
	}

	if (date.__utc)
	{
		newDate.__utc = true;
	}

	return newDate;
}
