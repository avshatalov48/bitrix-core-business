import { Core } from 'im.v2.application.core';
import { runAction } from 'im.v2.lib.rest';
import { RestMethod } from 'im.v2.const';

import type { ImModelChat } from 'im.v2.model';

export const CommentsService = {
	subscribe(messageId: number): Promise
	{
		Core.getStore().dispatch('messages/comments/subscribe', messageId);

		return runAction(RestMethod.imV2ChatCommentSubscribe, {
			data: {
				postId: messageId,
				createIfNotExists: true,
				autoJoin: true,
			},
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('CommentsService: subscribe error', error);
		});
	},

	unsubscribe(messageId: number): Promise
	{
		Core.getStore().dispatch('messages/comments/unsubscribe', messageId);

		return runAction(RestMethod.imV2ChatCommentUnsubscribe, {
			data: {
				postId: messageId,
				createIfNotExists: true,
				autoJoin: true,
			},
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('CommentsService: unsubscribe error', error);
		});
	},

	readAllChannelComments(channelDialogId: string): Promise
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](channelDialogId, true);
		const currentChannelCounter: number = Core.getStore().getters['counters/getChannelCommentsCounter'](chat.chatId);
		if (currentChannelCounter === 0)
		{
			return Promise.resolve();
		}

		Core.getStore().dispatch('counters/readAllChannelComments', chat.chatId);

		return runAction(RestMethod.imV2ChatCommentReadAll, {
			data: { dialogId: channelDialogId },
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('CommentsService: readAllChannelComments error', error);
		});
	},
};
