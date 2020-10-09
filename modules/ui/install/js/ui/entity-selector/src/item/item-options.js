import type { ItemNodeOptions } from './item-node-options';
import type { ItemBadgeOptions } from './item-badge-options';

export type ItemOptions = {
	id: string,
	entityId: number | string,
	entityType?: string,

	title?: string,
	subtitle?: string,
	supertitle?: string,
	caption?: string,
	avatar?: string,
	textColor?: string,
	link?: string,
	linkTitle?: string,
	badges?: ItemBadgeOptions[],
	tagOptions?: { [key: string]: any },

	tabs?: string[],
	searchable?: boolean,
	saveable?: boolean,
	deselectable?: boolean,
	selected?: boolean,
	hidden?: boolean,
	children?: ItemOptions[],
	nodeOptions?: ItemNodeOptions,
	customData?: { [key: string]: any },
	contextSort?: number,
	globalSort?: number,
	sort?: number,
};