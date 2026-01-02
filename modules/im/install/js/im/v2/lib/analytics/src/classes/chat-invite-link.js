import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';

import { AnalyticsTool, AnalyticsEvent, AnalyticsSection, AnalyticsSubSection } from '../const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';

import type { ImModelChat } from 'im.v2.model';

export class ChatInviteLink
{
	onCopyMembersPanel(dialogId: string)
	{
		sendData(this.#buildAnalyticsData(dialogId, AnalyticsSubSection.membersPanel));
	}

	onCopyContextMenu(dialogId: string)
	{
		sendData(this.#buildAnalyticsData(dialogId, AnalyticsSubSection.contextMenu));
	}

	#buildAnalyticsData(dialogId: string, subSection: string): { [key: string]: string }
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		return {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.copyChatLink,
			c_section: AnalyticsSection.sidebar,
			c_sub_section: subSection,
		};
	}
}
