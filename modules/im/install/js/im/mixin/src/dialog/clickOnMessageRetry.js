import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

export const DialogClickOnMessageRetry = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.clickOnMessageRetry, this.onClickOnMessageRetry);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnMessageRetry, this.onClickOnMessageRetry);
	},
	methods: {
		onClickOnMessageRetry({data: event})
		{
			this.retrySendMessage(event.message);
		},
		retrySendMessage()
		{
			//TODO
		}
	}
};