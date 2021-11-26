import type { ItemNodeOptions } from './item-node-options';
import type { ItemBadgeOptions } from './item-badge-options';
import type { TextNodeOptions } from '../common/text-node-options';
import type { CaptionOptions } from './caption-options';
import type { BadgesOptions } from './badges-options';
import type { AvatarOptions } from './avatar-options';

export type ItemOptions = {
	id: number | string,
	entityId: string,
	entityType?: string,

	title?: string | TextNodeOptions,
	subtitle?: string | TextNodeOptions,
	supertitle?: string | TextNodeOptions,
	caption?: string | TextNodeOptions,
	captionOptions?: CaptionOptions,
	avatar?: string,
	avatarOptions?: AvatarOptions,
	textColor?: string,
	link?: string,
	linkTitle?: string | TextNodeOptions,
	badges?: ItemBadgeOptions[],
	badgesOptions?: BadgesOptions,
	tagOptions?: { [key: string]: any },

	tabs?: string | string[],
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