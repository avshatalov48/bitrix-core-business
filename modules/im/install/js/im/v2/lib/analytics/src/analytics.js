import { sendData } from 'ui.analytics';

import { ChatType, Layout, UserRole } from 'im.v2.const';
import { Core } from 'im.v2.application.core';

import {
	AnalyticsEvent,
	AnalyticsTool,
	AnalyticsCategory,
	AnalyticsType,
	AnalyticsSection,
	AnalyticsStatus,
	CopilotChatType,
	AnalyticsSubSection,
	AnalyticsElement,
} from './const';

import { getCategoryByChatType } from './helpers/get-category-by-chat-type';
import { getChatType } from './helpers/get-chat-type';
import { ChatDelete } from './classes/chat-delete';
import { MessageDelete } from './classes/message-delete';
import { HistoryLimit } from './classes/history-limit';

import type { ImModelChat } from 'im.v2.model';

type DialogId = string;

export class Analytics
{
	#excludedChats: Set<DialogId> = new Set();
	#currentTab: string = Layout.chat.name;
	chatDelete: ChatDelete = new ChatDelete();
	messageDelete: MessageDelete = new MessageDelete();
	historyLimit: HistoryLimit = new HistoryLimit();

	static #instance: Analytics;

	static AnalyticsType = AnalyticsType;
	static AnalyticsSection = AnalyticsSection;
	static AnalyticsSubSection = AnalyticsSubSection;
	static AnalyticsElement = AnalyticsElement;

	static getInstance(): Analytics
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	onOpenMessenger()
	{
		sendData({
			event: AnalyticsEvent.openMessenger,
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.messenger,
		});
	}

	onCreateCopilotChat({ chatId, dialogId })
	{
		this.#excludedChats.add(dialogId);

		sendData({
			event: AnalyticsEvent.createNewChat,
			tool: AnalyticsTool.ai,
			category: AnalyticsCategory.chatOperations,
			c_section: AnalyticsSection.copilotTab,
			type: AnalyticsType.ai,
			p3: CopilotChatType.private,
			p5: `chatId_${chatId}`,
		});
	}

	onOpenCopilotChat(dialogId: string)
	{
		const dialog = Core.getStore().getters['chats/get'](dialogId);
		const copilotChatType = dialog.userCounter <= 2 ? CopilotChatType.private : CopilotChatType.multiuser;

		sendData({
			event: AnalyticsEvent.openChat,
			tool: AnalyticsTool.ai,
			category: AnalyticsCategory.chatOperations,
			c_section: AnalyticsSection.copilotTab,
			type: AnalyticsType.ai,
			p3: copilotChatType,
			p5: `chatId_${dialog.chatId}`,
		});
	}

	onOpenCopilotTab({ isAvailable = true } = {})
	{
		const payload = {
			event: AnalyticsEvent.openTab,
			tool: AnalyticsTool.ai,
			category: AnalyticsCategory.chatOperations,
			c_section: AnalyticsSection.copilotTab,
			status: isAvailable ? AnalyticsStatus.success : AnalyticsStatus.errorTurnedOff,
		};

		sendData(payload);
	}

	onOpenTab(tabName: string)
	{
		const existingTabs = [
			Layout.chat.name,
			Layout.copilot.name,
			Layout.channel.name,
			Layout.notification.name,
			Layout.settings.name,
			Layout.openlines.name,
		];

		if (!existingTabs.includes(tabName))
		{
			return;
		}

		if (this.#currentTab === tabName)
		{
			return;
		}

		this.#currentTab = tabName;

		sendData({
			event: AnalyticsEvent.openTab,
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.messenger,
			type: tabName,
		});
	}

	onUseCopilotAudioInput()
	{
		sendData({
			event: AnalyticsEvent.audioUse,
			tool: AnalyticsTool.ai,
			category: AnalyticsCategory.chatOperations,
			c_section: AnalyticsSection.copilotTab,
		});
	}

	onOpenCheckInPopup()
	{
		sendData({
			event: AnalyticsEvent.popupOpen,
			tool: AnalyticsTool.checkin,
			category: AnalyticsCategory.shift,
			c_section: AnalyticsSection.chat,
		});
	}

	onOpenPriceTable(featureId: string)
	{
		sendData({
			tool: AnalyticsTool.infoHelper,
			category: AnalyticsCategory.limit,
			event: AnalyticsEvent.openPrices,
			type: featureId,
			c_section: AnalyticsSection.chat,
		});
	}

	onOpenToolsSettings(toolId: string)
	{
		sendData({
			tool: AnalyticsTool.infoHelper,
			category: AnalyticsCategory.toolOff,
			event: AnalyticsEvent.openSettings,
			type: toolId,
			c_section: AnalyticsSection.chat,
		});
	}

	onStartCreateNewChat(type: $Values<typeof ChatType>)
	{
		const currentLayout = Core.getStore().getters['application/getLayout'].name;

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(type),
			event: AnalyticsEvent.clickCreateNew,
			type,
			c_section: `${currentLayout}_tab`,
		});
	}

	onCreateChat(dialogId: string)
	{
		this.#excludedChats.add(dialogId);
	}

	onOpenChat(dialog: ImModelChat)
	{
		if (this.#excludedChats.has(dialog.dialogId))
		{
			this.#excludedChats.delete(dialog.dialogId);

			return;
		}

		const chatType = getChatType(dialog);

		if (chatType === ChatType.copilot)
		{
			this.onOpenCopilotChat(dialog.dialogId);
		}

		const currentLayout = Core.getStore().getters['application/getLayout'].name;
		const isMember = dialog.role === UserRole.guest ? 'N' : 'Y';

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chatType),
			event: AnalyticsEvent.openExisting,
			type: chatType,
			c_section: `${currentLayout}_tab`,
			p3: `isMember_${isMember}`,
			p5: `chatId_${dialog.chatId}`,
		};

		if (chatType === ChatType.comment)
		{
			const parentChat = Core.getStore().getters['chats/getByChatId'](dialog.parentChatId);
			params.p1 = `chatType_${parentChat.type}`;
			params.p4 = `parentChatId_${dialog.parentChatId}`;
		}

		sendData(params);
	}

	onOpenChatEditForm(dialogId: string)
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.clickEdit,
			c_section: AnalyticsSection.sidebar,
			c_sub_section: AnalyticsSubSection.contextMenu,
			p1: `chatType_${chat.type}`,
			p5: `chatId_${chat.chatId}`,
		});
	}

	onSubmitChatEditForm(dialogId: string)
	{
		this.#excludedChats.add(dialogId);

		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.submitEdit,
			p1: `chatType_${chat.type}`,
			p5: `chatId_${chat.chatId}`,
		});
	}

	onCancelChatEditForm(dialogId: string)
	{
		this.#excludedChats.add(dialogId);
	}

	onStartCallClick(params)
	{
		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.messenger,
			event: AnalyticsEvent.clickCallButton,
			type: params.type,
			c_section: params.section,
			c_sub_section: params.subSection,
			c_element: params.element,
			p5: `chatId_${params.chatId}`,
		});
	}

	onStartConferenceClick(params)
	{
		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.call,
			event: AnalyticsEvent.clickStartConf,
			type: AnalyticsType.videoconf,
			c_section: AnalyticsSection.chatWindow,
			c_element: params.element,
			p5: `chatId_${params.chatId}`,
		});
	}

	onJoinConferenceClick(params)
	{
		sendData({
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.call,
			event: AnalyticsEvent.clickJoin,
			type: AnalyticsType.videoconf,
			c_section: AnalyticsSection.chatList,
			p5: `callId_${params.callId}`,
		});
	}
}
