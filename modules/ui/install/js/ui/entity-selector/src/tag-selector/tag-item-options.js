import type { TextNodeOptions } from '../common/text-node-options';
import type { AvatarOptions } from '../item/avatar-options';

export type TagItemOptions = {
	id: string,
	entityId: number | string,
	entityType?: string,
	title?: string | TextNodeOptions,
	avatar?: string,
	avatarOptions?: AvatarOptions,
	textColor?: string,
	bgColor?: string,
	fontWeight?: string,
	link?: string,
	onclick?: Function,
	maxWidth?: number,
	deselectable?: boolean,
	animate?: boolean,
	customData?: { [key: string]: any }
};