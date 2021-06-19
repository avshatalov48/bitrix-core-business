import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

export const DialogOpenMessageReactionList = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.openMessageReactionList, this.onOpenMessageReactionList);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.openMessageReactionList, this.onOpenMessageReactionList);
	},
	methods: {
		onOpenMessageReactionList({data: event})
		{
			this.openMessageReactionList(event.message.id, event.values);
		},
		openMessageReactionList()
		{
			//TODO
		}
	}
};