import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { RestMethod, UserStatus } from 'im.v2.const';

export class StatusService
{
	store: Object = null;
	restClient: Object = null;

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
	}

	changeStatus(status: string): Promise
	{
		if (!UserStatus[status])
		{
			return false;
		}

		Logger.warn(`StatusService: change current user status to ${status}`);
		this.store.dispatch('users/setStatus', { status });
		this.store.dispatch('application/settings/set', { status });

		return this.restClient.callMethod(RestMethod.imUserStatusSet, {
			STATUS: status
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('StatusService: changeStatus error', error);
		});
	}
}