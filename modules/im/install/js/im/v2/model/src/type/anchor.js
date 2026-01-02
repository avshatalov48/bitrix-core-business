import { type AnchorType } from 'im.v2.const';

export type Anchor = {
	chatId: number;
	fromUserId: number;
	messageId: number;
	parentChatId: number;
	parentMessageId: number;
	type: AnchorType;
	subType: string | null;
	userId: number;
};
