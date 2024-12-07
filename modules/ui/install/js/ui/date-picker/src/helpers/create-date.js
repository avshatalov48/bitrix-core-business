import { Type } from 'main.core';
import type { DateLike } from '../date-picker-options';
import { convertToDbFormat } from './convert-to-db-format';
import { parseDate } from './parse-date';

export function createDate(value: DateLike, formatDate: string = null): Date | null
{
	let date = null;
	if (Type.isStringFilled(value) && Type.isStringFilled(formatDate))
	{
		date = parseDate(value, convertToDbFormat(formatDate));
	}
	else if (Type.isNumber(value))
	{
		date = new Date(value);
		date = createUTC(date);
	}
	else if (Type.isDate(value))
	{
		date = value.__utc ? value : createUTC(value);
	}

	if (date === null)
	{
		console.warn(`DatePicker: invalid date or format (${value}).`);
	}
	else
	{
		date.__utc = true;
	}

	return date;
}

function createUTC(date: Date): Date
{
	return new Date(Date.UTC(
		date.getFullYear(),
		date.getMonth(),
		date.getDate(),
		date.getHours(),
		date.getMinutes(),
		date.getSeconds(),
		0,
	));
}
