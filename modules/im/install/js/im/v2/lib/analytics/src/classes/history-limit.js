import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { SidebarDetailBlock } from 'im.v2.const';

import { AnalyticsCategory, AnalyticsEvent, AnalyticsSection, AnalyticsTool, AnalyticsType } from '../const';
import { getChatType } from '../helpers/get-chat-type';

export class HistoryLimit
{
	onDialogLimitExceeded({ dialogId, noMessages }: { dialogId: string, noMessages: boolean })
	{
		const sectionValue = noMessages ? AnalyticsSection.chatStart : AnalyticsSection.chatHistory;
		const dialog = Core.getStore().getters['chats/get'](dialogId);
		const chatType = getChatType(dialog);

		const params = {
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.limitBanner,
			event: AnalyticsEvent.view,
			type: AnalyticsType.limitOfficeChatingHistory,
			c_section: sectionValue,
			p1: `chatType_${chatType}`,
		};

		sendData(params);
	}

	onSidebarLimitExceeded({ dialogId, panel }: { dialogId: string, panel: $Values<typeof SidebarDetailBlock> })
	{
		const dialog = Core.getStore().getters['chats/get'](dialogId);
		const chatType = getChatType(dialog);

		const params = {
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.limitBanner,
			event: AnalyticsEvent.view,
			type: AnalyticsType.limitOfficeChatingHistory,
			c_section: AnalyticsSection.sidebar,
			c_element: this.#getSidebarPanelNameForAnalytics(panel),
			p1: `chatType_${chatType}`,
		};

		sendData(params);
	}

	onDialogBannerClick({ dialogId }: {dialogId: string})
	{
		const section = AnalyticsSection.chatWindow;
		this.#onBannerClick({ dialogId, section });
	}

	onSidebarBannerClick({ dialogId, panel }: { dialogId: string, panel: $Values<typeof SidebarDetailBlock> })
	{
		const section = AnalyticsSection.sidebar;
		const element = this.#getSidebarPanelNameForAnalytics(panel);
		this.#onBannerClick({ dialogId, section, element });
	}

	onGoToContextLimitExceeded({ dialogId }: { dialogId: string })
	{
		const section = AnalyticsSection.messageLink;
		this.#onBannerClick({ dialogId, section });
	}

	#onBannerClick({ dialogId, section, element }: { dialogId: string, section: string, element?: string })
	{
		const dialog = Core.getStore().getters['chats/get'](dialogId);
		const chatType = getChatType(dialog);

		const params = {
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.limitBanner,
			event: AnalyticsEvent.click,
			type: AnalyticsType.limitOfficeChatingHistory,
			c_section: section,
			p1: `chatType_${chatType}`,
		};

		if (element)
		{
			params.c_element = element;
		}

		sendData(params);
	}

	#getSidebarPanelNameForAnalytics(panel: $Values<typeof SidebarDetailBlock>): string
	{
		switch (panel)
		{
			case SidebarDetailBlock.main:
				return 'main';
			case SidebarDetailBlock.file:
			case SidebarDetailBlock.fileUnsorted:
			case SidebarDetailBlock.audio:
			case SidebarDetailBlock.brief:
			case SidebarDetailBlock.document:
			case SidebarDetailBlock.media:
			case SidebarDetailBlock.other:
				return 'docs';
			case SidebarDetailBlock.messageSearch:
				return 'message_search';
			case SidebarDetailBlock.favorite:
				return 'favs';
			case SidebarDetailBlock.link:
				return 'links';
			case SidebarDetailBlock.task:
				return 'task';
			case SidebarDetailBlock.meeting:
				return 'event';
			default:
				return 'unknown';
		}
	}
}
