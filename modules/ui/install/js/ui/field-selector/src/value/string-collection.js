import { Type } from 'main.core';
import { BaseCollection } from './base-collection';

export class StringCollection extends BaseCollection
{
	validateValue(value): boolean
	{
		return Type.isString(value) && value.length > 0;
	}
}
