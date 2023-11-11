import { MessageComponent } from 'im.v2.const';

import type { AttachConfig } from 'im.v2.const';

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
	componentParams: Object,
	files: number[],
	attach: AttachConfig[] | boolean | string,
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
	params: RawMessageParams,
	replaces: Array,
	text: string,
	unread: boolean,
	uuid: string | null,
	viewed: boolean,
	viewedByOthers: boolean
};

export type RawMessageParams = {
	COMPONENT_ID?: string,
	COMPONENT_PARAMS?: Object,
	FILE_ID?: number[],
	IS_EDITED?: 'Y' | 'N',
	IS_DELETED?: 'Y' | 'N',
	REPLY_ID?: string,
	ATTACH?: AttachConfig[],
	LINK_ACTIVE: number[],
};

export type PreparedMessageParams = {
	componentId: string,
	componentParams: Object,
	extensionId: string,
	extensionParams: Object,
	files: number[],
	isEdited: boolean,
	isDeleted: boolean,
	replyId: number,
	attach: AttachConfig[]
};
