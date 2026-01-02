import { Type } from 'main.core';
import { BaseCollection } from './base-collection';

export class IntegerCollection extends BaseCollection
{
	validateValue(value): boolean
	{
		return Type.isInteger(value) && value > 0;
	}
}
