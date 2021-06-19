import { EventEmitter } from 'main.core.events';
import { EventType, RestMethod } from "im.const";

export const DialogSetMessageReaction = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.setMessageReaction, this.onSetMessageReaction);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.setMessageReaction, this.onSetMessageReaction);
	},
	methods: {
		onSetMessageReaction({data: event})
		{
			this.reactMessage(event.message.id, event.reaction);
		},
		reactMessage(messageId, type = 'like', action = 'auto')
		{
			this.getRestClient().callMethod(RestMethod.imMessageLike, {
				'MESSAGE_ID': messageId,
				'ACTION': action === 'auto'? 'auto': (action === 'set'? 'plus': 'minus')
			});
		}
	}
};