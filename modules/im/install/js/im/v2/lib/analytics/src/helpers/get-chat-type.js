import { ChatType } from 'im.v2.const';

import { isNotes } from './is-notes';
import { PSEUDO_CHAT_TYPE_FOR_NOTES } from '../const';

import type { ImModelChat } from 'im.v2.model';

const CUSTOM_CHAT_TYPE = 'custom';

export type ExtendedChatType = $Values<typeof ChatType>
	| typeof CUSTOM_CHAT_TYPE
	| typeof PSEUDO_CHAT_TYPE_FOR_NOTES;

export function getChatType(chat: ImModelChat): ExtendedChatType
{
	if (isNotes(chat.dialogId))
	{
		return PSEUDO_CHAT_TYPE_FOR_NOTES;
	}

	return ChatType[chat.type] ?? CUSTOM_CHAT_TYPE;
}
