import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

export const DialogClickOnMessageMenu = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.clickOnMessageMenu, this.onClickOnMessageMenu);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnMessageMenu, this.onClickOnMessageMenu);
	},
	methods: {
		onClickOnMessageMenu({data: event})
		{
			this.openMessageMenu(event.message);
		},
		openMessageMenu()
		{
			//TODO
		}
	}
};