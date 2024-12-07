import { MessageComponent, ChatType } from 'im.v2.const';

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
	forward: {
		userId: number,
		id: string,
		chatTitle: string | null,
		chatType: $Values<typeof ChatType>,
	},
	files: number[],
	attach: AttachConfig[] | boolean | string,
	keyboard: KeyboardButtonConfig[],
	isEdited: boolean,
	replyId: number,
	isDeleted: boolean,
	removeLinks: boolean,
	copilotRole?: string,
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

export type CommentInfo = {
	chatId: number,
	lastUserIds: number[],
	messageCount: 0,
	messageId: number,
	isUserSubscribed: boolean,
};
