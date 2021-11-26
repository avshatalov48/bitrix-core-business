import type { RenderMode } from './item-node'
import type { ItemBadgeOptions } from './item-badge-options';
import type { TextNodeOptions } from '../common/text-node-options';
import type { CaptionOptions } from './caption-options';
import type { BadgesOptions } from './badges-options';
import type { AvatarOptions } from './avatar-options';

export type ItemNodeOptions = {
	itemOrder?: ItemNodeOrder,
	open?: boolean,
	dynamic?: boolean,

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
	renderMode?: RenderMode,
};

export type ItemNodeOrder =
	{
		[key: string]:
			'asc' | 'desc' |
			'asc nulls first' | 'asc nulls last' |
			'desc nulls first' | 'desc nulls last'
	}
	|
	(a: T, b: T) => number
;