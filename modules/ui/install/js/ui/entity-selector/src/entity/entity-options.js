import type SearchField from '../search/search-field';
import type { ItemBadgeOptions } from '../item/item-badge-options';

export type EntityOptions = {
	id: string,
	options?: { [key: string]: any },
	itemOptions?: { [key: string]: any },
	tagOptions?: { [key: string]: any },
	badgeOptions?: ItemBadgeOptions[],
	searchable?: boolean,
	searchFields?: SearchField[],
	searchCacheLimits?: [],
	dynamicLoad?: boolean,
	dynamicSearch?: boolean
};