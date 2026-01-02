import { Core } from 'im.v2.application.core';
import { UserRole } from 'im.v2.const';
import { DemoChatBuilder } from 'im.v2.lib.demo';
import { Loc } from 'main.core';

export const initDemoData = (): string => {
	const chatBuilder = new DemoChatBuilder();
	chatBuilder.addChat({
		role: UserRole.guest,
	});

	const demoUser = chatBuilder.addUser({
		name: Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_USER_NAME'),
	});

	const firstMessage = chatBuilder.addMessage({
		authorId: demoUser.id,
		text: Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_MESSAGE_1'),
		viewedByOthers: true,
	});

	chatBuilder.addMessage({
		authorId: Core.getUserId(),
		replyId: firstMessage.id,
		text: Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_MESSAGE_2'),
		viewedByOthers: true,
	});

	chatBuilder.addMessage({
		authorId: demoUser.id,
		text: Loc.getMessage('IM_CONTENT_SETTINGS_DEMO_CHAT_MESSAGE_3'),
		viewedByOthers: true,
	});

	chatBuilder.save();

	return chatBuilder.getChat().dialogId;
};
