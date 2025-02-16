import { Type } from 'main.core';
import type { AccessRightItem } from '../../../store/model/access-rights-model';
import { type ValueType } from './value-type';

/**
 * @abstract
 */
export class Base implements ValueType
{
	/*
	 * @abstract
	 */
	getComponentName(): string
	{
		throw new Error('not implemented');
	}

	getEmptyValue(item: AccessRightItem): Set<string>
	{
		return item.emptyValue ?? new Set();
	}

	getMinValue(item: AccessRightItem): ?Set<string>
	{
		if (!Type.isNil(item.minValue))
		{
			return item.minValue;
		}

		return null;
	}

	getMaxValue(item: AccessRightItem): ?Set<string>
	{
		if (!Type.isNil(item.maxValue))
		{
			return item.maxValue;
		}

		return null;
	}

	isRowValueConfigurable(): boolean
	{
		return true;
	}
}
