import type { AccessRightItem } from '../../../store/model/access-rights-model';

export interface ValueType
{
	getComponentName(): string;
	getEmptyValue(item: AccessRightItem): Set<string>;
	getMinValue(item: AccessRightItem): ?Set<string>;
	getMaxValue(item: AccessRightItem): ?Set<string>;
	isRowValueConfigurable(): boolean;
}
