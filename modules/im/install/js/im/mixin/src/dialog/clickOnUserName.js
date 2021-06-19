import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

export const DialogClickOnUserName = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.clickOnUserName, this.onClickOnUserName);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnUserName, this.onClickOnUserName);
	},
	methods: {
		onClickOnUserName({data: event})
		{
			this.replyToUser(event.user.id, event.user);
		},
		replyToUser()
		{
			//TODO
		}
	}
};