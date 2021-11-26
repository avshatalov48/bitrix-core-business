import type { DialogOptions } from '../dialog/dialog-options';
import type { BaseEvent } from 'main.core.events';
import type { TagItemOptions } from './tag-item-options';
import type { AvatarOptions } from '../item/avatar-options';

export type TagSelectorOptions = {
	id?: string,
	items?: TagItemOptions[],
	dialogOptions?: DialogOptions,
	multiple?: boolean,
	readonly?: boolean,
	locked?: boolean,
	deselectable?: boolean,
	events?: { [eventName: string]: (event: BaseEvent) => void },
	showAddButton?: boolean,
	showCreateButton?: boolean,
	showTextBox?: boolean,
	addButtonCaption?: string,
	addButtonCaptionMore?: string,
	createButtonCaption?: string,
	placeholder?: string,
	maxHeight?: number,
	textBoxAutoHide?: boolean,
	textBoxWidth?: string | number,
	tagAvatar?: string,
	tagAvatarOptions?: AvatarOptions,
	tagMaxWidth?: number,
	tagTextColor?: string,
	tagBgColor?: string,
	tagFontWeight?: string
};