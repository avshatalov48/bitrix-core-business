import { Loc } from 'main.core';

import { Core } from 'im.v2.application.core';
import { UserManager } from 'im.v2.lib.user';
import { ChatType, UserRole } from 'im.v2.const';

const DEMO_DIALOG_ID = 'settings';
const demoChat = {
	dialogId: DEMO_DIALOG_ID,
	chatId: -1,
	type: ChatType.chat,
	inited: true,
	role: UserRole.guest,
};

const demoUser = {
	id: -1,
	name: Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_USER_NAME'),
};

const demoMessage1 = {
	id: -3,
	chatId: demoChat.chatId,
	authorId: demoUser.id,
	text: Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_MESSAGE_1'),
	viewedByOthers: true,
};
const demoMessage2 = {
	id: -2,
	chatId: demoChat.chatId,
	authorId: Core.getUserId(),
	replyId: demoMessage1.id,
	text: Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_MESSAGE_2'),
	viewedByOthers: true,
};
const demoMessage3 = {
	id: -1,
	chatId: demoChat.chatId,
	authorId: demoUser.id,
	text: Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_MESSAGE_3'),
	viewedByOthers: true,
};

export const DemoManager = {
	initModels()
	{
		Core.getStore().dispatch('chats/set', demoChat);

		const userManager = new UserManager();
		userManager.addUsersToModel([demoUser]);

		const messages = [demoMessage1, demoMessage2, demoMessage3];
		Core.getStore().dispatch('messages/setChatCollection', { messages });
	},
};
