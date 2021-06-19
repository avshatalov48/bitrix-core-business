import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

export const DialogClickOnReadList = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.clickOnReadList, this.onClickOnReadList);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnReadList, this.onClickOnReadList);
	},
	methods: {
		onClickOnReadList({data: event})
		{
			this.openReadList(event.list);
		},
		openReadList()
		{
			//TODO
		}
	}
};