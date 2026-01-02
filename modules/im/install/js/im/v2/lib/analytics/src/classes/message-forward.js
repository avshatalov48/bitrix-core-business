import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { getUserType } from 'im.v2.lib.analytics';

import { AnalyticsEvent, AnalyticsSection, AnalyticsSubSection, AnalyticsTool } from '../const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { isNotes } from '../helpers/is-notes';

import type { ImModelChat } from 'im.v2.model';

const SelectRecipientSource = Object.freeze({
	recent: 'recent',
	searchResult: 'search_result',
	notes: 'notes',
});

type RecipientParams = {
	dialogId: string,
	position: number,
}

type RecipientParamsWithSource = RecipientParams & {
	source: $Values<typeof SelectRecipientSource>
};

export class MessageForward
{
	#hasSearchedBefore: boolean = false;

	onClickForward(dialogId: string): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.clickShare,
			c_section: AnalyticsSection.chatWindow,
			c_sub_section: AnalyticsSubSection.contextMenu,
			p1: `chatType_${chat.type}`,
			p2: getUserType(),
		});
	}

	onStartSearch({ dialogId }: { dialogId: string }): void
	{
		if (this.#hasSearchedBefore)
		{
			return;
		}
		this.#hasSearchedBefore = true;

		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.startSearch,
			c_section: AnalyticsSection.forward,
			p1: `chatType_${chat.type}`,
			p2: getUserType(),
		});
	}

	onSelectRecipientFromRecent({ dialogId, position }: RecipientParams): void
	{
		this.#onSelectRecipient({
			dialogId,
			position,
			source: SelectRecipientSource.recent,
		});
	}

	onSelectRecipientFromSearchResult({ dialogId, position }: RecipientParams): void
	{
		this.#onSelectRecipient({
			dialogId,
			position,
			source: SelectRecipientSource.searchResult,
		});
	}

	onClosePopup(): void
	{
		this.#hasSearchedBefore = false;
	}

	#onSelectRecipient({ dialogId, position, source }: RecipientParamsWithSource): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);
		const type = isNotes(dialogId) ? SelectRecipientSource.notes : source;

		sendData({
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.selectRecipient,
			type,
			c_section: AnalyticsSection.forward,
			p1: `chatType_${chat.type}`,
			p2: getUserType(),
			p3: `position_${position}`,
		});
	}
}
