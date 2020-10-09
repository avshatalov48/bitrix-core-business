import type { RenderMode } from './item-node'
import type { ItemBadgeOptions } from './item-badge-options';

export type ItemNodeOptions = {
	itemOrder?: {[key: string]: 'asc' | 'desc'},
	open?: boolean,
	dynamic?: boolean,

	title?: string,
	subtitle?: string,
	supertitle?: string,
	caption?: string,
	avatar?: string,
	textColor?: string,
	link?: string,
	linkTitle?: string,
	badges?: ItemBadgeOptions[],
	renderMode?: RenderMode,
};