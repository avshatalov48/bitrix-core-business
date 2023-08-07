import {EventEmitter, BaseEvent} from 'main.core.events';

import {Core} from 'im.v2.application.core';
import {EventType} from 'im.v2.const';

import type {OnDialogInitedEvent} from 'im.v2.const';

export class LikeManager
{
	store: Object;

	constructor()
	{
		this.store = Core.getStore();
	}

	init()
	{
		this.onDialogInitedHandler = this.onDialogInited.bind(this);
		EventEmitter.subscribe(EventType.dialog.onDialogInited, this.onDialogInitedHandler);
	}

	destroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.onDialogInited, this.onDialogInitedHandler);
	}

	onDialogInited(event: BaseEvent<OnDialogInitedEvent>)
	{
		const { dialogId } = event.getData();
		const recentItem = this.store.getters['recent/get'](dialogId);
		if (!recentItem || !recentItem.liked)
		{
			return;
		}

		this.store.dispatch('recent/like', {
			id: dialogId,
			liked: false,
		});
	}
}
