import { Core } from 'im.v2.application.core';
import { runAction } from 'im.v2.lib.rest';
import { RestMethod } from 'im.v2.const';

const DEBOUNCE_TIME = 50;

export class AnchorService
{
	#store: Store;
	messagesToRead: Set<number> = new Set();
	#timerBeforeSendRequest = null;

	constructor() {
		this.#store = Core.getStore();
	}

	readChatAnchors(chatId: number): void
	{
		runAction(RestMethod.imV2ChatAnchorRead, {
			data: {
				chatId,
			},
		})
			.then(() => {
				const userId = Core.getUserId();

				this.#store.dispatch('messages/anchors/removeChatAnchors', { userId, chatId });
			})
			.catch((error) => {
				console.error('AnchorService: read chat anchors error', error);
			})
		;
	}

	debouncedReadMessageAnchors(messageId: number): void
	{
		const userId = Core.getUserId();

		this.messagesToRead.add(messageId);
		this.#store.dispatch('messages/anchors/removeUserAnchorsFromMessage', { userId, messageId });

		if (this.#timerBeforeSendRequest)
		{
			clearTimeout(this.#timerBeforeSendRequest);
			this.#timerBeforeSendRequest = null;
		}

		this.#timerBeforeSendRequest = setTimeout(() => {
			return this.#batchReadMessageAnchors();
		}, DEBOUNCE_TIME);

		return Promise.resolve();
	}

	#batchReadMessageAnchors(): void
	{
		if (this.messagesToRead.size === 0)
		{
			return;
		}

		runAction(RestMethod.imV2AnchorRead, {
			data: {
				messageIds: [...this.messagesToRead],
			},
		})
			.catch((error) => {
				console.error('AnchorService: read anchor error', error);
			})
		;

		this.messagesToRead.clear();
	}
}
