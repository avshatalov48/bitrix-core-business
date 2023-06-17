import {MessageComponent} from 'im.v2.const';

import type {AttachConfig} from 'im.v2.const';

export type Message = {
	id: number | string,
	chatId: number,
	authorId: number,
	date: Date,
	text: string,
	replaces: Object[],
	unread: boolean,
	viewed: boolean,
	viewedByOthers: boolean,
	sending: boolean,
	error: boolean,
	retry: boolean,
	componentId: $Values<typeof MessageComponent>,
	files: number[],
	attach: AttachConfig[] | boolean | string,
	isEdited: boolean,
	isDeleted: boolean,
	removeLinks: boolean
};