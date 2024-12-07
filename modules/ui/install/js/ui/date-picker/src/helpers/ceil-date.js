import { addDate } from './add-date';
import { cloneDate } from './clone-date';
import { floorDate } from './floor-date';
import { getNextDate } from './get-next-date';

export function ceilDate(date, unit, increment, firstWeekDay): Date
{
	const newDate = cloneDate(date);
	if (unit === 'week')
	{
		newDate.setUTCHours(0, 0, 0, 0);

		return addDate(floorDate(newDate, unit, firstWeekDay), unit, 1);
	}

	switch (unit)
	{
		case 'hour':
			newDate.setUTCMinutes(0, 0, 0);
			break;
		case 'minute':
			newDate.setUTCSeconds(0, 0);
			break;
		case 'second':
			newDate.setUTCMilliseconds(0);
			break;
		default:
			newDate.setUTCHours(0, 0, 0, 0);
	}

	return getNextDate(newDate, unit, increment);
}
