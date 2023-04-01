import type { SearchFieldOptions } from '../search/search-field-options';
import type { EntityFilterOptions } from './entity-filter-options';
import type { ItemOptions } from '../item/item-options';
import { EntityBadgeOptions } from './entity-badge-options';

export type EntityOptions = {
	id: string,
	options?: { [key: string]: any },
	itemOptions?: { [key: string]: ItemOptions },
	tagOptions?: { [key: string]: any },
	badgeOptions?: EntityBadgeOptions[],
	filters?: EntityFilterOptions[],
	searchable?: boolean,
	searchFields?: SearchFieldOptions[],
	searchCacheLimits?: string[],
	dynamicLoad?: boolean,
	dynamicSearch?: boolean
};