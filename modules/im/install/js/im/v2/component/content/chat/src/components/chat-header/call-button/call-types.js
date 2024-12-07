import { Core } from 'im.v2.application.core';
import { Messenger } from 'im.public';
import { CallManager } from 'im.v2.lib.call';

import type { ImModelChat } from 'im.v2.model';

export const CallTypes = {
	video: {
		id: 'video',
		locCode: 'IM_CONTENT_CHAT_HEADER_VIDEOCALL',
		start: (dialogId: string) => {
			Messenger.startVideoCall(dialogId);
		},
	},
	audio: {
		id: 'audio',
		locCode: 'IM_CONTENT_CHAT_HEADER_CALL_MENU_AUDIO',
		start: (dialogId: string) => {
			Messenger.startVideoCall(dialogId, false);
		},
	},
	beta: {
		id: 'beta',
		locCode: 'IM_CONTENT_CHAT_HEADER_CALL_MENU_BETA_2',
		start: (dialogId: string) => {
			const dialog: ImModelChat = Core.getStore().getters['chats/get'](dialogId);
			CallManager.getInstance().createBetaCallRoom(dialog.chatId);
		},
	},
};
