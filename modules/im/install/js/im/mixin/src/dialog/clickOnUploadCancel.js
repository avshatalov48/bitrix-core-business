import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

export const DialogClickOnUploadCancel = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.clickOnUploadCancel, this.onClickOnUploadCancel);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnUploadCancel, this.onClickOnUploadCancel);
	},
	methods: {
		onClickOnUploadCancel({data: event})
		{
			this.cancelUploadFile(event.file.id);
		},
		cancelUploadFile()
		{
			//TODO
		}
	}
};