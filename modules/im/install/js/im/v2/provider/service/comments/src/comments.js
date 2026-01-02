import { Core } from 'im.v2.application.core';
import { runAction } from 'im.v2.lib.rest';
import { RestMethod } from 'im.v2.const';

import type { ImModelChat } from 'im.v2.model';

export const CommentsService = {
	subscribe(messageId: number): void
	{
		void Core.getStore().dispatch('messages/comments/subscribe', messageId);

		return runAction(RestMethod.imV2ChatCommentSubscribe, {
			data: {
				postId: messageId,
				createIfNotExists: true,
				autoJoin: true,
			},
		}).catch(([error]) => {
			console.error('CommentsService: subscribe error', error);
		});
	},

	unsubscribe(messageId: number): void
	{
		void Core.getStore().dispatch('messages/comments/unsubscribe', messageId);

		return runAction(RestMethod.imV2ChatCommentUnsubscribe, {
			data: {
				postId: messageId,
				createIfNotExists: true,
				autoJoin: true,
			},
		}).catch(([error]) => {
			console.error('CommentsService: unsubscribe error', error);
		});
	},

	readAllChannelComments(channelDialogId: string): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](channelDialogId, true);
		const currentChannelCounter: number = Core.getStore().getters['counters/getChannelCommentsCounter'](chat.chatId);
		if (currentChannelCounter === 0)
		{
			return Promise.resolve();
		}

		void Core.getStore().dispatch('counters/readAllChannelComments', chat.chatId);

		return runAction(RestMethod.imV2ChatCommentReadAll, {
			data: { dialogId: channelDialogId },
		}).catch(([error]) => {
			console.error('CommentsService: readAllChannelComments error', error);
		});
	},
};
