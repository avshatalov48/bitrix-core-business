import { Core } from 'im.v2.application.core';
import { runAction } from 'im.v2.lib.rest';
import { RestMethod } from 'im.v2.const';

export const CommentsService = {
	subscribe(dialogId: string): Promise
	{
		Core.getStore().dispatch('chats/unmute', { dialogId });

		return runAction(RestMethod.imV2ChatCommentSubscribe, {
			data: { dialogId },
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('CommentsService: subscribe error', error);
		});
	},

	unsubscribe(dialogId: string): Promise
	{
		Core.getStore().dispatch('chats/mute', { dialogId });

		return runAction(RestMethod.imV2ChatCommentUnsubscribe, {
			data: { dialogId },
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('CommentsService: unsubscribe error', error);
		});
	},
};
