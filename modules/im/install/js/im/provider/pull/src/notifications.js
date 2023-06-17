import {PullClient} from "pull.client";

export class ImNotificationsPullHandler
{
	static create(params = {})
	{
		return new this(params);
	}

	constructor(params = {})
	{
		if (typeof params.application === 'object' && params.application)
		{
			this.application = params.application;
		}
		if (typeof params.controller === 'object' && params.controller)
		{
			this.controller = params.controller;
		}
		if (typeof params.store === 'object' && params.store)
		{
			this.store = params.store;
		}

		this.option = typeof params.store === 'object' && params.store ? params.store : {};
	}

	getModuleId()
	{
		return 'im';
	}

	getSubscriptionType()
	{
		return PullClient.SubscriptionType.Server;
	}

	handleNotifyAdd(params, extra)
	{
		if (extra.server_time_ago > 30 || params.onlyFlash === true)
		{
			return false;
		}

		const user = this.store.getters['users/get'](params.userId);
		if (!user)
		{
			const users = [];
			users.push({
				id: params.userId,
				avatar: params.userAvatar,
				color: params.userColor,
				name: params.userName
			});

			this.store.dispatch('users/set', users);
		}

		this.store.dispatch('notifications/add', { data: params });
		this.store.dispatch('notifications/setCounter', { unreadTotal: params.counter });
		this.store.dispatch('recent/update', {
			id: "notify",
			fields: {
				message: {
					id: params.id,
					text: params.text,
					date: params.date
				},
				counter: params.counter
			}
		});
	}

	handleNotifyReadAll(params)
	{
		this.store.dispatch('notifications/readAll');
		this.store.dispatch('notifications/setCounter', {unreadTotal: 0});
		this.store.dispatch('recent/update', {
			id: 'notify',
			fields: {
				counter: 0
			}
		});
	}

	handleNotifyConfirm(params, extra)
	{
		if (extra.server_time_ago > 30)
		{
			return false;
		}

		this.store.dispatch('notifications/delete', {
			id: params.id,
		});

		this.store.dispatch('notifications/setCounter', {
			unreadTotal: params.counter
		});
		this.updateRecentListOnDelete(params.counter);
	}

	handleNotifyRead(params, extra)
	{
		if (extra.server_time_ago > 30)
		{
			return false;
		}

		params.list.forEach(id => {
			this.store.dispatch('notifications/read', { ids: [id], action: true });
		});

		this.store.dispatch('notifications/setCounter', {
			unreadTotal: params.counter
		});

		this.store.dispatch('recent/update', {
			id: "notify",
			fields: {
				counter: params.counter
			}
		});

	}

	handleNotifyUnread(params, extra)
	{
		if (extra.server_time_ago > 30)
		{
			return false;
		}

		params.list.forEach(id => {
			this.store.dispatch('notifications/read', { ids: [id], action: false });
		});

		this.store.dispatch('notifications/setCounter', {
			unreadTotal: params.counter
		});

		this.store.dispatch('recent/update', {
			id: "notify",
			fields: {
				counter: params.counter
			}
		});
	}

	handleNotifyDelete(params, extra)
	{
		if (extra.server_time_ago > 30)
		{
			return false;
		}

		const idsToDelete = Object.keys(params.id).map(id => parseInt(id, 10));

		idsToDelete.forEach(id => {
			this.store.dispatch('notifications/delete', { id: id });
		});

		this.updateRecentListOnDelete(params.counter)
		this.store.dispatch('notifications/setCounter', {
			unreadTotal: params.counter
		});
	}

	updateRecentListOnDelete(counterValue)
	{
		let message;
		const latestNotification = this.getLatest();
		if (latestNotification !== null)
		{
			message = {
				id: latestNotification.id,
				text: latestNotification.text,
				date: latestNotification.date
			};
		}
		else
		{
			const notificationChat = this.store.getters['recent/get']('notify');
			if (notificationChat === false)
			{
				return;
			}
			message = notificationChat.element.message;
			message.text = this.controller.localize['IM_NOTIFICATIONS_DELETED_ITEM_STUB'];
		}

		this.store.dispatch('recent/update', {
			id: "notify",
			fields: {
				message: message,
				counter: counterValue
			}
		});
	}

	getLatest()
	{
		let latestNotification = {
			id: 0
		};

		for (const notification of this.store.state.notifications.collection)
		{
			if (notification.id > latestNotification.id)
			{
				latestNotification = notification;
			}
		}

		if (latestNotification.id === 0)
		{
			return null;
		}

		return latestNotification;
	}
}
