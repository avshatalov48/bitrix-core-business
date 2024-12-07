import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { MessageComponentManager } from 'im.v2.lib.message-component-manager';

import {
	AnalyticsCategory,
	AnalyticsEvent,
	AnalyticsSection,
	AnalyticsSubSection,
	AnalyticsTool,
	AnalyticsType,
} from '../const';

import type { ImModelMessage, ImModelChat, ImModelCommentInfo } from 'im.v2.model';

export class MessageDelete
{
	onClickDelete({ messageId, dialogId }: {messageId: string | number, dialogId: string})
	{
		const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);
		const type = new MessageComponentManager(message).getName();
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.message,
			event: AnalyticsEvent.clickDelete,
			type,
			c_sub_section: AnalyticsSubSection.contextMenu,
			p1: `chatType_${chat.type}`,
			p5: `chatId_${chat.chatId}`,
		});
	}

	onCancel({ messageId, dialogId }: {messageId: string | number, dialogId: string})
	{
		const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);
		const type = new MessageComponentManager(message).getName();
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.message,
			event: AnalyticsEvent.cancelDelete,
			type,
			c_section: AnalyticsSection.popup,
			c_sub_section: AnalyticsSubSection.contextMenu,
			p1: `chatType_${chat.type}`,
			p5: `chatId_${chat.chatId}`,
		});
	}

	onNotFoundNotification({ dialogId }: {dialogId: string})
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.chatPopup,
			event: AnalyticsEvent.view,
			type: AnalyticsType.deletedMessage,
			p1: `chatType_${chat.type}`,
			p5: `chatId_${chat.chatId}`,
		});
	}

	onDeletedPostNotification({ messageId, dialogId }: {messageId: string | number, dialogId: string})
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);
		const commentInfo: ImModelCommentInfo = Core.getStore().getters['messages/comments/getByMessageId'](messageId);

		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.chatPopup,
			event: AnalyticsEvent.view,
			type: AnalyticsType.deletedMessage,
			c_section: AnalyticsSection.comments,
			p1: `chatType_${chat.type}`,
			p4: `parentChatId_${chat.chatId}`,
			p5: `chatId_${commentInfo.chatId}`,
		});
	}
}
