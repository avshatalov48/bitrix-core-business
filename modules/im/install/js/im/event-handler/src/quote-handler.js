import { EventEmitter } from "main.core.events";
import { EventType } from "im.const";

export class QuoteHandler
{
	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;

		this.onQuoteMessageHandler = this.onQuoteMessage.bind(this);
		this.onQuotePanelCloseHandler = this.onQuotePanelClose.bind(this);
		EventEmitter.subscribe(EventType.dialog.quoteMessage, this.onQuoteMessageHandler);
		EventEmitter.subscribe(EventType.dialog.quotePanelClose, this.onQuotePanelCloseHandler);
	}

	onQuoteMessage({data})
	{
		this.quoteMessage(data.message.id);
	}

	onQuotePanelClose()
	{
		this.clearQuote();
	}

	quoteMessage(messageId)
	{
		this.store.dispatch('dialogues/update', {
			dialogId: this.getDialogId(),
			fields: {
				quoteId: messageId
			}
		});
	}

	clearQuote()
	{
		this.store.dispatch('dialogues/update', {
			dialogId: this.getDialogId(),
			fields: {
				quoteId: 0
			}
		});
	}

	getDialogId(): number | string
	{
		return this.store.state.application.dialog.dialogId;
	}

	destroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.quoteMessage, this.onQuoteMessageHandler);
		EventEmitter.unsubscribe(EventType.dialog.quotePanelClose, this.onQuotePanelCloseHandler);
	}
}