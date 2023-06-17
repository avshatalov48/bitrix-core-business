import {EventEmitter} from 'main.core.events';

import {EventType} from 'im.old-chat-embedding.const';

export class DraftManager
{
	static instance = null;
	store: Object = null;

	static init($Bitrix): void
	{
		if (this.instance)
		{
			return;
		}

		this.instance = new this($Bitrix);
	}

	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;

		this.initDraftHistory();

		this.onSetDraftHandler = this.onSetDraft.bind(this);
		EventEmitter.subscribe(EventType.recent.setDraftMessage, this.onSetDraftHandler);
	}

	initDraftHistory()
	{
		if (!BX.MessengerProxy)
		{
			return false;
		}
		const history = BX.MessengerProxy.getTextareaHistory();
		Object.entries(history).forEach(([dialogId, text]) => {
			this.setDraftMessage(dialogId, text);
		});
	}

	onSetDraft({data: {dialogId, text}})
	{
		this.setDraftMessage(dialogId, text);
	}

	setDraftMessage(dialogId, text)
	{
		this.store.dispatch('recent/draft', {
			id: dialogId,
			text
		});
	}

	destroy()
	{
		EventEmitter.unsubscribe(EventType.recent.setDraftMessage, this.onSetDraftHandler);
	}
}