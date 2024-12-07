import { Type, type JsonObject } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';

type RawUser = {
	id: number,
	[key: string]: any,
};
type PullUserActivityResult = {
	[userId: string]: number,
};

export class UserService
{
	async updateLastActivityDate(userId: number): Promise
	{
		if (this.isPullServerWithUserStatusSupport())
		{
			const lastActivityDate = await this.getUserActivityFromPull(userId);
			if (!lastActivityDate)
			{
				return Promise.resolve();
			}

			return this.updateUserModel(userId, { lastActivityDate });
		}

		const userData = await this.requestUserData(userId);

		return this.updateUserModel(userId, userData);
	}

	async getUserActivityFromPull(userId: number): Promise<null | Date>
	{
		const result: PullUserActivityResult = await Core.getPullClient().getUsersLastSeen([userId])
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('UserService: error getting user activity from P&P', error);
			});

		if (!Type.isNumber(result[userId]))
		{
			return null;
		}

		const activityDateAgo = result[userId] * 1000;

		return new Date(Date.now() - activityDateAgo);
	}

	async requestUserData(userId: number): Promise<RawUser>
	{
		Logger.warn(`UserService: get actual user data for - ${userId}`);
		const answer = await Core.getRestClient().callMethod(RestMethod.imUserGet, { ID: userId })
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('UserService: error getting user data', error);
			});

		return answer.data();
	}

	async updateUserModel(userId: number, userFields: JsonObject): Promise
	{
		Logger.warn('UserService: update user data', userFields);

		return Core.getStore().dispatch('users/update', {
			id: userId,
			fields: userFields,
		});
	}

	isPullServerWithUserStatusSupport(): boolean
	{
		return Core.getPullClient().isJsonRpc();
	}
}
