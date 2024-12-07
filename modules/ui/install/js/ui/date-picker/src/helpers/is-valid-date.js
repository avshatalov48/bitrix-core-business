import { Type } from 'main.core';

export function isValidDate(date: Date): boolean
{
	if (!Type.isDate(date))
	{
		return false;
	}

	return !Number.isNaN(date.getTime());
}
