import { Core } from 'im.v2.application.core';

export function isNotes(dialogId: string): boolean
{
	return Core.getStore().getters['chats/isNotes'](dialogId);
}
