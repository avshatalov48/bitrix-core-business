import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

export const DialogQuoteMessage = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.quoteMessage, this.onQuoteMessage);
		EventEmitter.subscribe(EventType.dialog.quotePanelClose, this.onQuotePanelClose);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.quoteMessage, this.onQuoteMessage);
		EventEmitter.unsubscribe(EventType.dialog.quotePanelClose, this.onQuotePanelClose);
	},
	methods: {
		onQuoteMessage({data: event})
		{
			this.quoteMessage({id: event.message.id});
		},
		onQuotePanelClose()
		{
			this.quoteMessageClear();
		},
		quoteMessage({id})
		{
			this.$store.dispatch('dialogues/update', {
				dialogId: this.dialogId,
				fields: {
					quoteId: id
				}
			});
		},
		quoteMessageClear()
		{
			this.$store.dispatch('dialogues/update', {
				dialogId: this.dialogId,
				fields: {
					quoteId: 0
				}
			});
		}
	}
};