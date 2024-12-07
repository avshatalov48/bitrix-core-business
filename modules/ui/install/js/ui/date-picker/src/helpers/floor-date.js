import { addDate } from './add-date';
import { cloneDate } from './clone-date';

export function floorDate(date, unit, firstWeekDay): Date
{
	let newDate = cloneDate(date);
	switch (unit)
	{
		case 'day':
			newDate.setUTCHours(0, 0, 0, 0);
			break;
		case 'week': {
			const day = newDate.getUTCDay();
			newDate.setUTCHours(0, 0, 0, 0);
			if (day !== firstWeekDay)
			{
				newDate = addDate(
					newDate,
					'day',
					-(day > firstWeekDay ? (day - firstWeekDay) : (7 - day - firstWeekDay)),
				);
			}

			break;
		}
		case 'month':
			newDate.setUTCHours(0, 0, 0, 0);
			newDate.setUTCDate(1);
			break;
		case 'hour':
			newDate.setUTCMinutes(0, 0, 0);
			break;
		case 'minute':
			newDate.setUTCSeconds(0);
			newDate.setUTCMilliseconds(0);
			break;
		case 'second':
			newDate.setUTCMilliseconds(0);
			break;
		case 'year':
			newDate = new Date(Date.UTC(date.getUTCFullYear(), 0, 1));
			break;
		case 'quarter': {
			newDate.setUTCHours(0, 0, 0, 0);
			newDate.setUTCDate(1);
			newDate = addDate(newDate, 'month', -(newDate.getUTCMonth() % 3));

			break;
		}
		default:
			// No default
	}

	if (date.__utc)
	{
		newDate.__utc = true;
	}

	return newDate;
}
