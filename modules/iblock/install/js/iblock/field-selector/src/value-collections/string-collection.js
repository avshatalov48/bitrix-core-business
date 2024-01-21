import { Type } from 'main.core';
import { BaseCollection } from './base-collection';

export class StringCollection extends BaseCollection
{
	validateValues(rawValues: []): []
	{
		const result = [];
		rawValues.forEach((value): void => {
			if (!Type.isString(value))
			{
				return;
			}

			const trimValue = value.trim();
			if (trimValue !== '')
			{
				result.push(trimValue);
			}
		});

		return result;
	}
}
