import { Type } from 'main.core';
import { Core } from 'im.v2.application.core';

export class OnlinePullHandler
{
	constructor()
	{
		this.store = Core.getStore();
	}

	getModuleId(): string
	{
		return 'online';
	}

	getSubscriptionType(): string
	{
		return 'online';
	}

	handleUserStatus(params: {
		users: Object,
	})
	{
		const currentUserId = Core.getUserId();
		if (Type.isPlainObject(params.users[currentUserId]))
		{
			const { status } = params.users[currentUserId];
			this.store.dispatch('application/settings/set', { status });
		}

		Object.values(params.users).forEach((userInfo) => {
			this.store.dispatch('users/update', {
				id: userInfo.id,
				fields: {
					lastActivityDate: userInfo.last_activity_date,
				},
			});
		});
	}
}
