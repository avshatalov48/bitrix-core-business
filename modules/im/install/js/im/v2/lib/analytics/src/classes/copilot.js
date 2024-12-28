import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';

import {
	AnalyticsCategory,
	AnalyticsEvent,
	AnalyticsSection,
	AnalyticsStatus,
	AnalyticsTool,
	CopilotChatType,
	AnalyticsType,
} from '../const';

export class Copilot
{
	onCreateChat(chatId: number)
	{
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

	onOpenChat(dialogId: string)
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

	onOpenTab({ isAvailable = true } = {})
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

	onUseAudioInput()
	{
		sendData({
			event: AnalyticsEvent.audioUse,
			tool: AnalyticsTool.ai,
			category: AnalyticsCategory.chatOperations,
			c_section: AnalyticsSection.copilotTab,
		});
	}
}
