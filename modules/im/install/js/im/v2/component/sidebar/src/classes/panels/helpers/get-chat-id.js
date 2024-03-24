import { Core } from 'im.v2.application.core';

export function getChatId(dialogId: string): number
{
	const dialog = Core.getStore().getters['chats/get'](dialogId, true);

	return dialog.chatId;
}
