import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';

import {
	AnalyticsCategory,
	AnalyticsEvent,
	AnalyticsSection, AnalyticsSubSection,
	AnalyticsTool,
} from '../const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { getChatType } from '../helpers/get-chat-type';

import type { ImModelChat } from 'im.v2.model';

export class ChatDelete
{
	onClick(dialogId: string)
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.clickDelete,
			type: getChatType(chat),
			c_section: AnalyticsSection.sidebar,
			c_sub_section: AnalyticsSubSection.contextMenu,
			p1: `chatType_${chat.type}`,
			p5: `chatId_${chat.chatId}`,
		});
	}

	onCancel(dialogId: string)
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.cancelDelete,
			type: getChatType(chat),
			c_section: AnalyticsSection.popup,
			p1: `chatType_${chat.type}`,
			p5: `chatId_${chat.chatId}`,
		});
	}

	onConfirm(dialogId: string)
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.delete,
			type: getChatType(chat),
			c_section: AnalyticsSection.popup,
			p1: `chatType_${chat.type}`,
			p5: `chatId_${chat.chatId}`,
		});
	}

	onChatDeletedNotification(dialogId: string)
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);
		const category = getCategoryByChatType(chat);

		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.chatPopup,
			event: AnalyticsEvent.view,
			type: `deleted_${category}`,
			c_section: AnalyticsSection.activeChat,
			p1: `chatType_${chat.type}`,
			p5: `chatId_${chat.chatId}`,
		});
	}
}
