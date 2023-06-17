import {Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';

import {EventType} from 'im.old-chat-embedding.const';

export class EventHandler
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

		this.subscribeToEvents();
	}

	subscribeToEvents()
	{
		this.onSetCounterHandler = this.onSetCounter.bind(this);
		this.onSetMessageHandler = this.onSetMessage.bind(this);
		this.onHideChatHandler = this.onHideChat.bind(this);
		this.onLeaveChatHandler = this.onLeaveChat.bind(this);
		this.onClearLikeHandler = this.onClearLike.bind(this);
		this.onClearHistoryHandler = this.onClearHistory.bind(this);

		EventEmitter.subscribe(EventType.recent.setCounter, this.onSetCounterHandler);
		EventEmitter.subscribe(EventType.recent.setMessage, this.onSetMessageHandler);
		EventEmitter.subscribe(EventType.recent.hideChat, this.onHideChatHandler);
		EventEmitter.subscribe(EventType.recent.leaveChat, this.onLeaveChatHandler);
		EventEmitter.subscribe(EventType.recent.clearLike, this.onClearLikeHandler);
		EventEmitter.subscribe(EventType.dialog.clearHistory, this.onClearHistoryHandler);
	}

	onSetCounter({data: {dialogId, counter}})
	{
		const recentItem = this.store.getters['recent/get'](dialogId);
		const dialog = this.store.getters['dialogues/get'](dialogId);
		if (!recentItem || !dialog)
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: dialogId,
			fields: {
				counter: counter
			}
		});
	}

	onSetMessage({data: {id, dialogId, text, date}})
	{
		const recentItem = this.store.getters['recent/get'](dialogId);
		const dialog = this.store.getters['dialogues/get'](dialogId);
		if (!recentItem || !dialog)
		{
			return false;
		}

		if (id && !id.toString().startsWith('temp') && id !== recentItem.message.id)
		{
			return false;
		}

		this.store.dispatch('recent/update', {
			id: dialogId,
			fields: {
				message: {
					id: id || 0,
					text: text,
					senderId: this.getCurrentUserId(),
					status: recentItem.message.status,
					date: date || recentItem.message.date,
				}
			}
		});
	}

	onHideChat({data: {dialogId}})
	{
		const recentItem = this.store.getters['recent/get'](dialogId);
		if (!recentItem)
		{
			return false;
		}

		this.store.dispatch('recent/delete', {
			id: dialogId
		});
	}

	onLeaveChat({data: {dialogId}})
	{
		this.onHideChat({data: {dialogId}});
	}

	onClearLike({data: {dialogId}})
	{
		const recentItem = this.store.getters['recent/get'](dialogId);
		if (!recentItem || !recentItem.liked)
		{
			return false;
		}

		this.store.dispatch('recent/like', {
			id: dialogId,
			liked: false
		});
	}

	onClearHistory({data: {dialogId}})
	{
		const recentItem = this.store.getters['recent/get'](dialogId);
		if (!recentItem)
		{
			return false;
		}

		this.store.dispatch('recent/update', {
			id: dialogId,
			fields: {
				message: {
					...recentItem.message,
					text: Loc.getMessage('IM_RECENT_DELETED_MESSAGE'),
				}
			}
		});
	}

	getCurrentUserId(): number
	{
		return this.store.state.application.common.userId;
	}

	destroy()
	{
		this.unsubscribeEvents();
	}

	unsubscribeEvents()
	{
		EventEmitter.unsubscribe(EventType.recent.setCounter, this.onSetCounterHandler);
		EventEmitter.unsubscribe(EventType.recent.setMessage, this.onSetMessageHandler);
		EventEmitter.unsubscribe(EventType.recent.hideChat, this.onHideChatHandler);
		EventEmitter.unsubscribe(EventType.recent.leaveChat, this.onLeaveChatHandler);
		EventEmitter.unsubscribe(EventType.recent.clearLike, this.onClearLikeHandler);
	}
}