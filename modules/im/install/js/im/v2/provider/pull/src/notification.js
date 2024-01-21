import {Runtime} from 'main.core';

import {Core} from 'im.v2.application.core';
import {UserManager} from 'im.v2.lib.user';

import type {NotifyAddParams} from './types/notification';

export class NotificationPullHandler
{
	constructor()
	{
		this.store = Core.getStore();
		this.userManager = new UserManager();

		this.updateCounterDebounced = Runtime.debounce(this.updateCounter, 1500, this);
	}

	getModuleId(): string
	{
		return 'im';
	}

	getSubscriptionType(): string
	{
		return 'server';
	}

	handleNotifyAdd(params: NotifyAddParams)
	{
		if (params.onlyFlash === true)
		{
			return;
		}

		this.userManager.setUsersToModel(params.users);
		this.store.dispatch('notifications/set', params);

		this.updateCounterDebounced(params.counter);
	}

	handleNotifyConfirm(params)
	{
		this.store.dispatch('notifications/delete', {
			id: params.id,
		});

		this.updateCounterDebounced(params.counter);
	}

	handleNotifyRead(params)
	{
		params.list.forEach(id => {
			this.store.dispatch('notifications/read', {ids: [id], read: true});
		});

		this.updateCounterDebounced(params.counter);
	}

	handleNotifyUnread(params)
	{
		params.list.forEach(id => {
			this.store.dispatch('notifications/read', {ids: [id], read: false});
		});

		this.updateCounterDebounced(params.counter);
	}

	handleNotifyDelete(params)
	{
		const idsToDelete = Object.keys(params.id).map(id => Number.parseInt(id, 10));

		idsToDelete.forEach(id => {
			this.store.dispatch('notifications/delete', {id});
		});

		this.updateCounterDebounced(params.counter);
	}

	updateCounter(counter: number)
	{
		this.store.dispatch('notifications/setCounter', counter);
	}
}
