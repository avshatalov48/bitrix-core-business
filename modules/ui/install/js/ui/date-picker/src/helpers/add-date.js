import { cloneDate } from './clone-date';
import { getDaysInMonth } from './get-days-in-month';

export function addDate(date, unit, increment): Date
{
	let newDate = cloneDate(date);
	if (!unit || increment === 0)
	{
		return newDate;
	}

	switch (unit.toLowerCase())
	{
		case 'milli':
			newDate = new Date(date.getTime() + increment);
			break;
		case 'second':
			newDate = new Date(date.getTime() + (increment * 1000));
			break;
		case 'minute':
			newDate = new Date(date.getTime() + (increment * 60000));
			break;
		case 'hour':
			newDate = new Date(date.getTime() + (increment * 3_600_000));
			break;
		case 'day':
			newDate.setUTCDate(date.getUTCDate() + increment);
			break;
		case 'week':
			newDate.setUTCDate(date.getUTCDate() + increment * 7);
			break;
		case 'month': {
			let day = date.getUTCDate();
			if (day > 28)
			{
				const firstDayOfMonth = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), 1));
				day = Math.min(day, getDaysInMonth(addDate(firstDayOfMonth, 'month', increment)));
			}

			newDate.setUTCDate(day);
			newDate.setUTCMonth(newDate.getUTCMonth() + increment);
			break;
		}
		case 'quarter':
			newDate = addDate(date, 'month', increment * 3);
			break;
		case 'year':
			newDate.setUTCFullYear(date.getUTCFullYear() + increment);
			break;
		default:
			// nothing
	}

	if (date.__utc)
	{
		newDate.__utc = true;
	}

	return newDate;
}
