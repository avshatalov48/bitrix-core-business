import { DialogScrollThreshold } from '../chat';

export type OnLayoutChangeEvent = {
	from: {
		name: string,
		entityId: string,
		contextId: number
	},
	to: {
		name: string,
		entityId: string,
		contextId: number
	}
};

export type OnDialogInitedEvent = {
	dialogId: string
};

export type InsertTextEvent = {
	text: string,
	withNewLine?: boolean,
	replace?: boolean,
	dialogId: string,
};

export type InsertMentionEvent = {
	mentionText: string,
	mentionReplacement: string,
	dialogId: string,
};

export type EditMessageEvent = {
	messageId: number
};

export type ScrollToBottomEvent = {
	chatId: number,
	threshold?: $Values<typeof DialogScrollThreshold>,
	animation?: boolean,
};
