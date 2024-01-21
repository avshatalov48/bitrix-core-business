import { Type } from 'main.core';
import { BaseCollection } from './base-collection';

export class IntegerCollection extends BaseCollection
{
	validateValues(rawValues: []): []
	{
		const result = [];
		rawValues.forEach((value): void => {
			if (!Type.isInteger(value))
			{
				return;
			}

			if (value > 0)
			{
				result.push(value);
			}
		});

		return result;
	}
}
