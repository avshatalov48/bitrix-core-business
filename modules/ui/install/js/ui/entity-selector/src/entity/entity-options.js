import type { ItemBadgeOptions } from '../item/item-badge-options';
import type { SearchFieldOptions } from '../search/search-field-options';

export type EntityOptions = {
	id: string,
	options?: { [key: string]: any },
	itemOptions?: { [key: string]: any },
	tagOptions?: { [key: string]: any },
	badgeOptions?: ItemBadgeOptions[],
	searchable?: boolean,
	searchFields?: SearchFieldOptions[],
	searchCacheLimits?: string[],
	dynamicLoad?: boolean,
	dynamicSearch?: boolean
};