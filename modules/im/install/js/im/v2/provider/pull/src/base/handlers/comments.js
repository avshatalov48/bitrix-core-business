import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';

import { CommentSubscribeParams, ReadAllChannelCommentsParams } from '../../types/comments';

export class CommentsPullHandler
{
	handleCommentSubscribe(params: CommentSubscribeParams)
	{
		const { messageId, subscribe } = params;
		Logger.warn('CommentsPullHandler: handleCommentSubscribe', params);
		if (subscribe)
		{
			Core.getStore().dispatch('messages/comments/subscribe', messageId);

			return;
		}

		Core.getStore().dispatch('messages/comments/unsubscribe', messageId);
	}

	handleReadAllChannelComments(params: ReadAllChannelCommentsParams)
	{
		Core.getStore().dispatch('counters/readAllChannelComments', params.chatId);
	}
}
