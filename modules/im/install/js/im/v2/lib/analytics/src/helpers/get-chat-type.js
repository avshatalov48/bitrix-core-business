import { ChatType } from 'im.v2.const';

import type { ImModelChat } from 'im.v2.model';

const CUSTOM_CHAT_TYPE = 'custom';

export function getChatType(chat: ImModelChat): $Values<typeof ChatType>
{
	return ChatType[chat.type] ?? CUSTOM_CHAT_TYPE;
}
