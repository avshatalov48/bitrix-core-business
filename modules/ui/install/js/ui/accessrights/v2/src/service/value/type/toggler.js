import { Type } from 'main.core';
import type { AccessRightItem } from '../../../store/model/access-rights-model';
import { Base } from './base';

export class Toggler extends Base
{
	getComponentName(): string
	{
		return 'Toggler';
	}

	getEmptyValue(item: AccessRightItem): Set<string>
	{
		const isFalsy = !item.emptyValue || !item.emptyValue[0];
		if (isFalsy)
		{
			// use explicit '0' for correctly identify modifications
			return new Set(['0']);
		}

		return super.getEmptyValue(item);
	}

	getMinValue(item: AccessRightItem): ?Set<string>
	{
		const explicit = super.getMinValue(item);
		if (!Type.isNull(explicit))
		{
			return explicit;
		}

		return new Set(['0']);
	}

	getMaxValue(item: AccessRightItem): ?Set<string>
	{
		const explicit = super.getMaxValue(item);
		if (!Type.isNull(explicit))
		{
			return explicit;
		}

		return new Set(['1']);
	}

	isRowValueConfigurable(): boolean
	{
		return false;
	}
}
