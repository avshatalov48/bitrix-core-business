import type { JsonObject } from 'main.core';

import { CounterType } from 'im.v2.const';

import type { InputActionType } from 'im.v2.lib.input-action';
import type { RawUser } from './common';

type CounterTypeItem = $Values<typeof CounterType>;

export type ChatOwnerParams = {
	chatId: number,
	dialogId: string,
	userId: number
};

export type ChatManagersParams = {
	chatId: number,
	dialogId: string,
	list: number[]
};

export type ChatUserAddParams = {
	chatId: number,
	dialogId: string,
	chatTitle: string,
	chatOwner: number,
	chatExtranet: boolean,
	containsCollaber: boolean,
	users: {[userId: string]: RawUser},
	newUsers: number[],
	userCount: number,
	relations: Relation[],
};

export type ChatUserLeaveParams = {
	chatId: number,
	chatTitle: string,
	dialogId: string,
	message: string,
	userCount: number,
	userId: number,
	chatExtranet: boolean,
	containsCollaber: boolean,
	relations: Relation[],
};

export type StartWritingParams = {
	dialogId: string,
	userId: number,
	userName: string
};

export type InputActionNotifyParams = {
	dialogId: string,
	userId: number,
	userName: string,
	type: InputActionType,
	statusMessageCode: string | null,
	userFirstName: string | null,
	duration: number | null,
};

export type ChatUnreadParams = {
	chatId: number,
	dialogId: string,
	active: boolean,
	muted: boolean,
	counter: number,
	markedId: number | "0",
	lines: boolean,
	counterType: CounterTypeItem
};

export type ChatMuteNotifyParams = {
	chatId: number,
	dialogId: string,
	muted: boolean,
	mute: boolean,
	counter: number,
	lines: boolean,
	unread: boolean,
	counterType: CounterTypeItem
};

export type ChatRenameParams = {
	chatId: number,
	name: string
};

export type ChatAvatarParams = {
	chatId: number,
	avatar: string
};

export type ChatHideParams = {
	dialogId: string,
	chatId: string,
	lines: Object | false,
};

export type ChatConvertParams = {
	dialogId: string,
	oldType: string,
	newType: string,
	newPermissions: JsonObject,
	newTypeParams: ?JsonObject,
};

export type ChatDeleteParams = {
	dialogId: string,
	chatId: number,
	userId: string,
	type: string,
	parentChatId: number,
};

export type MessagesAutoDeleteDelayParams = {
	chatId: number,
	dialogId: string,
	delay: number,
};

export type Relation = {
	id: number,
	userId: number,
	chatId: number,
	isHidden: boolean,
	role: 'guest' | 'member' | 'admin' | 'owner',
};
