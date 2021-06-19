import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

export const DialogClickOnDialog = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.clickOnDialog, this.onClickOnDialog);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnDialog, this.onClickOnDialog);
	},
	methods: {
		onClickOnDialog({data: event})
		{
			return true;
		},
	}
};