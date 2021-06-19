import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

//openDialog is in dialogCore
export const DialogClickOnMention = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.clickOnMention, this.onClickOnMention);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnMention, this.onClickOnMention);
	},
	methods: {
		onClickOnMention({data: event})
		{
			if (event.type === 'USER')
			{
				this.openProfile(event.value);
			}
			else if (event.type === 'CHAT')
			{
				this.openDialog(event.value);
			}
			else if (event.type === 'CALL')
			{
				this.openPhoneMenu(event.value);
			}
		},
		openProfile()
		{
			//TODO
		},
		openPhoneMenu()
		{
			//TODO
		}
	}
};