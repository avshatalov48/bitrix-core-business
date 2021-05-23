import type { ItemBadgeOptions } from '../item/item-badge-options';

export type EntityBadgeOptions = ItemBadgeOptions & {
	conditions?: { [key: string]: any }
};