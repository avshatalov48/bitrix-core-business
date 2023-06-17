import {DialogScrollThreshold} from '../dialog';

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
	withNewLine?: boolean
};

export type InsertMentionEvent = {
	mentionText: string,
	mentionReplacement: string
};

export type EditMessageEvent = {
	messageId: number
};

export type ScrollToBottomEvent = {
	chatId: number,
	threshold?: $Values<typeof DialogScrollThreshold>
};