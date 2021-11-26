import type { ItemBadgeOptions } from '../item/item-badge-options';
import type { SearchFieldOptions } from '../search/search-field-options';
import type { EntityFilterOptions } from './entity-filter-options';

export type EntityOptions = {
	id: string,
	options?: { [key: string]: any },
	itemOptions?: { [key: string]: any },
	tagOptions?: { [key: string]: any },
	badgeOptions?: ItemBadgeOptions[],
	filters?: EntityFilterOptions[],
	searchable?: boolean,
	searchFields?: SearchFieldOptions[],
	searchCacheLimits?: string[],
	dynamicLoad?: boolean,
	dynamicSearch?: boolean
};