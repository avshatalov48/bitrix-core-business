import { sendData } from 'ui.analytics';
import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';

import {
	AnalyticsEvent,
	AnalyticsTool,
	AnalyticsCategory,
	AnalyticsType,
	AnalyticsSection,
	CopilotChatType,
} from './const';

type DialogId = string;

export class Analytics
{
	#createdChats: Set<DialogId> = new Set();

	static #instance: Analytics;

	static getInstance(): Analytics
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	createChat({ chatId, dialogId })
	{
		this.#createdChats.add(dialogId);

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

	openCopilotChat(dialogId: string)
	{
		if (!Type.isStringFilled(dialogId))
		{
			return;
		}

		if (this.#createdChats.has(dialogId))
		{
			this.#createdChats.delete(dialogId);

			return;
		}

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

	openCopilotTab()
	{
		sendData({
			event: AnalyticsEvent.openTab,
			tool: AnalyticsTool.ai,
			category: AnalyticsCategory.chatOperations,
			c_section: AnalyticsSection.copilotTab,
		});
	}

	useAudioInput()
	{
		sendData({
			event: AnalyticsEvent.audioUse,
			tool: AnalyticsTool.ai,
			category: AnalyticsCategory.chatOperations,
			c_section: AnalyticsSection.copilotTab,
		});
	}
}
