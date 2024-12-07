import { Type, Text } from 'main.core';
import { DateTimeFormat } from 'main.date';
import { createDate } from './create-date';
import { createUtcDate } from './create-utc-date';
import { getDate } from './get-date';

const WORD_REGEX = /[^\p{L}\p{N}\u0600-\u06FF_]/u;
const YEAR_REGEX = /^[1-9]\d{3}$/;
const DAY_REGEX = /^(0?[1-9]|[12]\d|3[01])$/;
const MONTH_REGEX = /^(0?[1-9]|1[0-2])$/;
const HOURS24_REGEX = /^(\d|0\d|1\d|2[0-3])$/;
// const HOURS12_REGEX = /^(1[0-2]|0?[1-9])$/;
const MINUTES_REGEX = /^(\d|[0-5]\d)$/;
const SECONDS_REGEX = /^(\d|[0-5]\d)$/;

export function parseDate(dateValue: string, format: string): Date | null
{
	const tokens: string = format.split(WORD_REGEX);
	const values = dateValue.split(WORD_REGEX);

	const parts = {};
	const errors = new Map();
	for (const [i, token] of tokens.entries())
	{
		const valuePart = getDatePart(token, values[i]);
		if (valuePart !== null)
		{
			const [part, value, initialValue] = valuePart;
			if (value === 'error')
			{
				errors.set(part, initialValue);

				continue;
			}

			parts[part] = value;
		}
	}

	const hasDay = Type.isNumber(parts.day);
	const hasMonth = Type.isNumber(parts.month);
	const hasYear = Type.isNumber(parts.year);

	if (errors.size > 0)
	{
		const hasDate = hasYear && hasMonth && hasDay;
		const emptyTime = (
			errors.has('hours')
			&& errors.has('minutes')
			&& Type.isUndefined(errors.get('hours'))
			&& Type.isUndefined(errors.get('minutes'))
			&& (
				(errors.has('seconds') && Type.isUndefined(errors.get('seconds')))
				|| !errors.has('seconds')
			)
		);

		if (!hasDate || !emptyTime)
		{
			return null;
		}
	}

	const today = createDate(new Date());
	const { day: currentDay, month: currentMonth, year: currentYear } = getDate(today);
	const defaultYear = currentYear;
	const defaultMonth = hasYear ? 0 : currentMonth;
	const defaultDay = hasYear || hasMonth ? 1 : currentDay;

	const { meridiem } = parts;
	const is12Hours = tokens.includes('H') || tokens.includes('G');
	const isPM = Type.isStringFilled(meridiem) && meridiem.toLowerCase() === 'pm';

	let { hours } = parts;
	if (is12Hours)
	{
		if (isPM)
		{
			hours += hours === 12 ? 0 : 12;
		}
		else
		{
			hours = hours < 12 ? hours : 0;
		}
	}

	const {
		year = defaultYear,
		month = defaultMonth,
		day = defaultDay,
		minutes = 0,
		seconds = 0,
	} = parts;

	return createUtcDate(year, month, day, hours, minutes, seconds);
}

function getDatePart(token, value): Array | null
{
	// DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G
	switch (token)
	{
		case 'YYYY':
		{
			if (!YEAR_REGEX.test(value))
			{
				return ['year', 'error', value];
			}

			const year = Text.toInteger(value);

			return ['year', year, value];
		}
		case 'MMMM':
		case 'MMM':
		{
			const monthIndex = DateTimeFormat.getMonthIndex(value);
			if (Type.isNumber(monthIndex))
			{
				return ['month', monthIndex - 1, value];
			}

			return ['month', 'error', value];
		}
		case 'MM':
		case 'M': {
			if (!MONTH_REGEX.test(value))
			{
				return ['month', 'error', value];
			}

			const monthIndex = Text.toInteger(value);

			return ['month', monthIndex === 0 ? monthIndex : Math.min(Math.max(monthIndex, 1), 12) - 1, value];
		}
		case 'DD':
		case 'D':
		{
			if (!DAY_REGEX.test(value))
			{
				return ['day', 'error', value];
			}

			const day = Text.toInteger(value);

			return ['day', Math.min(Math.max(day, 1), 31), value];
		}
		case 'HH':
		case 'GG':
		{
			if (!HOURS24_REGEX.test(value))
			{
				return ['hours', 'error', value];
			}

			const hours = Text.toInteger(value);

			return ['hours', Math.min(Math.max(hours, 0), 23), value];
		}
		case 'H':
		case 'G':
		{
			if (!HOURS24_REGEX.test(value))
			{
				return ['hours', 'error', value];
			}

			const hours = Text.toInteger(value);

			return ['hours', hours > 12 ? hours - 12 : hours, value];
		}

		case 'MI':
		{
			if (!MINUTES_REGEX.test(value))
			{
				return ['minutes', 'error', value];
			}

			const minutes = Text.toInteger(value);

			return ['minutes', Math.min(Math.max(minutes, 0), 59), value];
		}

		case 'SS':
		{
			if (Type.isStringFilled(value) && ['am', 'pm'].includes(value.toLowerCase()))
			{
				return ['meridiem', value, value];
			}

			if (Type.isStringFilled(value) && !SECONDS_REGEX.test(value))
			{
				return ['seconds', 'error', value];
			}

			const seconds = Text.toInteger(value);

			return ['seconds', Math.min(Math.max(seconds, 0), 59), value];
		}
		case 'T':
		case 'TT':
			if (Type.isStringFilled(value))
			{
				return ['meridiem', value, value];
			}

			return null;
		default:
			return null;
	}
}
