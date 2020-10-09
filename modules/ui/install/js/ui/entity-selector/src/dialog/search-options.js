import type { ItemOptions } from '../item/item-options';

export type SearchOptions = {
	allowCreateItem?: boolean,
	draftItemOptions?: ItemOptions,
	draftItemRender?: Function
};