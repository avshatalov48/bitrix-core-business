import { MessageComponent } from 'im.v2.const';

import type { JsonObject } from 'main.core';
import type { AttachConfig, KeyboardButtonConfig } from 'im.v2.const';

export type Message = {
	id: number | string,
	chatId: number,
	authorId: number,
	date: Date,
	text: string,
	unread: boolean,
	viewed: boolean,
	viewedByOthers: boolean,
	sending: boolean,
	error: boolean,
	componentId: $Values<typeof MessageComponent>,
	componentParams: Object,
	forward: {userId: number, id: string},
	files: number[],
	attach: AttachConfig[] | boolean | string,
	keyboard: KeyboardButtonConfig[],
	isEdited: boolean,
	replyId: number,
	isDeleted: boolean,
	removeLinks: boolean
};

export type RawMessage = {
	authorId: number,
	author_id: number,
	chatId: number,
	chat_id: number,
	date: string,
	id: number,
	isSystem: boolean,
	params: JsonObject,
	replaces: Array,
	text: string,
	unread: boolean,
	uuid: string | null,
	viewed: boolean,
	viewedByOthers: boolean
};
