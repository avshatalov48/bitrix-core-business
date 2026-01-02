import { Text } from 'main.core';
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
	AnalyticsSubSection,
} from '../const';

const CopilotEntryPoint = Object.freeze({
	create_menu: 'create_menu',
	role_picker: 'role_picker',
});

export class Copilot
{
	onCreateChat(chatId: number): void
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

	onCreateDefaultChatInRecent(): void
	{
		this.#sendDataForCopilotCreation({ c_sub_section: CopilotEntryPoint.create_menu });
	}

	onSelectRoleInRecent(): void
	{
		this.#sendDataForCopilotCreation({ c_sub_section: CopilotEntryPoint.role_picker });
	}

	onOpenChat(dialogId: string): void
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

	onOpenTab({ isAvailable = true } = {}): void
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

	onUseAudioInput(): void
	{
		sendData({
			event: AnalyticsEvent.audioUse,
			tool: AnalyticsTool.ai,
			category: AnalyticsCategory.chatOperations,
			c_section: AnalyticsSection.copilotTab,
		});
	}

	#sendDataForCopilotCreation(params: { c_sub_section: string }): void
	{
		const currentLayout = Core.getStore().getters['application/getLayout'].name;

		sendData({
			event: AnalyticsEvent.clickCreateNew,
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.copilot,
			c_section: `${currentLayout}_tab`,
			type: AnalyticsType.copilot,
			...params,
		});
	}
}
