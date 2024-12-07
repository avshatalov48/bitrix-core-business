import { Text, Type } from 'main.core';

export function normalizeTimeValue(timeValue: any): number
{
	if (Type.isDate(timeValue))
	{
		return getTimestampFromDate(timeValue);
	}

	return Text.toInteger(timeValue);
}

export function createDateFromTimestamp(timestampInSeconds: number): Date
{
	return new Date(timestampInSeconds * 1000);
}

export function getTimestampFromDate(date: Date): number
{
	return Math.floor(date.getTime() / 1000);
}
