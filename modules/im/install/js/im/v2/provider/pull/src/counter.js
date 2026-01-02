import { Type } from 'main.core';

import { Logger } from 'im.v2.lib.logger';
import { NewMessageManager } from 'im.v2.provider.pull';
import { Core } from 'im.v2.application.core';
import { CounterType, RecentType } from 'im.v2.const';

import type { MessageAddParams, PullExtraParams, ReadMessageParams, UnreadMessageParams } from 'im.v2.provider.pull';
import type { ChatMuteNotifyParams, ChatUnreadParams } from './types/chat';
import type { MessageDeleteCompleteParams } from './types/message';

type RecentTypeItem = $Values<typeof RecentType>;

type CounterParams = {
	dialogId: string,
	chatId: number,
	parentChatId: number,
	counter: number,
	muted: boolean,
	unread: boolean,
	counterType: $Values<typeof CounterType>,
	recentConfig: {
		chatId: number,
		sections: RecentTypeItem[],
	},
};

export class CounterPullHandler
{
	constructor()
	{
		this.store = Core.getStore();
	}

	getModuleId(): string
	{
		return 'im';
	}

	handleMessage(params: MessageAddParams, extra: PullExtraParams)
	{
		this.handleMessageAdd(params, extra);
	}

	handleMessageChat(params: MessageAddParams, extra: PullExtraParams)
	{
		this.handleMessageAdd(params, extra);
	}

	handleMessageAdd(params: MessageAddParams, extra: PullExtraParams)
	{
		const manager = new NewMessageManager(params, extra);
		if (!manager.isCommentChat())
		{
			return;
		}

		this.#updateCommentCounter({
			channelChatId: manager.getParentChatId(),
			commentChatId: manager.getChatId(),
			commentCounter: params.counter,
		});
	}

	handleMessageDeleteComplete(params: MessageDeleteCompleteParams)
	{
		this.#handleCounters(params);
	}

	handleReadMessage(params: ReadMessageParams)
	{
		this.#handleCounters(params);
	}

	handleReadMessageChat(params: ReadMessageParams)
	{
		this.#handleCounters(params);
	}

	handleUnreadMessage(params: UnreadMessageParams)
	{
		this.#handleCounters(params);
	}

	handleUnreadMessageChat(params: UnreadMessageParams)
	{
		this.#handleCounters(params);
	}

	handleChatUnread(params: ChatUnreadParams)
	{
		this.#handleCounters({
			...params,
			unread: params.active,
		});
	}

	handleChatMuteNotify(params: ChatMuteNotifyParams)
	{
		this.#handleCounters(params);
	}

	handleReadAllChats()
	{
		Logger.warn('CounterPullHandler: handleReadAllChats');
		void Core.getStore().dispatch('counters/clear');
	}

	#handleCounters(params: CounterParams)
	{
		const {
			chatId,
			counter,
			counterType = CounterType.chat,
			parentChatId = 0,
		} = params;

		if (counterType === CounterType.openline)
		{
			return;
		}

		Logger.warn('CounterPullHandler: handleCounters:', params);

		if (counterType === CounterType.comment)
		{
			this.#updateCommentCounter({
				channelChatId: parentChatId,
				commentChatId: chatId,
				commentCounter: counter,
			});

			return;
		}

		this.#handleUnloadedChatCounters(params);
	}

	#getNewCounter(params: CounterParams): number
	{
		const { counter, muted, unread } = params;

		let newCounter = 0;
		if (muted)
		{
			newCounter = 0;
		}
		else if (unread && counter === 0)
		{
			newCounter = 1;
		}
		else if (unread && counter > 0)
		{
			newCounter = counter;
		}
		else if (!unread)
		{
			newCounter = counter;
		}

		return newCounter;
	}

	#updateCommentCounter(payload: { channelChatId: number, commentChatId: number, commentCounter: number })
	{
		const { channelChatId, commentChatId, commentCounter } = payload;
		if (Type.isUndefined(commentCounter))
		{
			return;
		}

		const counters = {
			[channelChatId]: {
				[commentChatId]: commentCounter,
			},
		};

		Core.getStore().dispatch('counters/setCommentCounters', counters);
	}

	#handleUnloadedChatCounters(params: CounterParams)
	{
		const { chatId, recentConfig, dialogId } = params;
		const newCounter = this.#getNewCounter(params);

		recentConfig.sections.forEach((recentSection) => {
			const sectionConfig = this.#getRecentSectionConfig(recentSection);
			if (!sectionConfig)
			{
				return;
			}

			const isInRecentCollection = this.store.getters[sectionConfig.collectionGetter](dialogId);
			if (isInRecentCollection)
			{
				return;
			}

			void Core.getStore().dispatch(sectionConfig.setUnloadedCounterAction, { [chatId]: newCounter });
		});
	}

	#getRecentSectionConfig(section: RecentTypeItem): ?{ setUnloadedCounterAction: string, collectionGetter: string }
	{
		const handlers = {
			[RecentType.default]: {
				setUnloadedCounterAction: 'counters/setUnloadedChatCounters',
				collectionGetter: 'recent/isInRecentCollection',
			},
			[RecentType.collab]: {
				setUnloadedCounterAction: 'counters/setUnloadedCollabCounters',
				collectionGetter: 'recent/isInCollabCollection',
			},
			[RecentType.copilot]: {
				setUnloadedCounterAction: 'counters/setUnloadedCopilotCounters',
				collectionGetter: 'recent/isInCopilotCollection',
			},
			[RecentType.taskComments]: {
				setUnloadedCounterAction: 'counters/setUnloadedTaskCounters',
				collectionGetter: 'recent/isInTaskCollection',
			},
		};

		return handlers[section];
	}
}
