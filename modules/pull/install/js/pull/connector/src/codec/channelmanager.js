export class ChannelManager
{
	constructor(params)
	{
		this.publicIds = {};
		this.restClient = params.restClient ?? BX.rest;
		this.getPublicListMethod = params.getPublicListMethod;
	}

	/**
	 *
	 * @param {Array} users Array of user ids.
	 * @return {Promise}
	 */
	getPublicIds(users): Promise
	{
		const now = new Date();
		const result = {};
		const unknownUsers = [];

		for (const userId of users)
		{
			if (this.publicIds[userId] && this.publicIds[userId].end > now)
			{
				result[userId] = this.publicIds[userId];
			}
			else
			{
				unknownUsers.push(userId);
			}
		}

		if (unknownUsers.length === 0)
		{
			return Promise.resolve(result);
		}

		return new Promise((resolve, reject) => {
			this.restClient.callMethod(this.getPublicListMethod, { users: unknownUsers }).then((response) => {
				if (response.error())
				{
					resolve({});
				}
				else
				{
					const data = response.data();
					this.setPublicIds(Object.values(data));
					for (const userId of unknownUsers)
					{
						result[userId] = this.publicIds[userId];
					}

					resolve(result);
				}
			}).catch((e) => reject(e));
		});
	}

	/**
	 *
	 * @param {object[]} publicIds
	 * @param {integer} publicIds.user_id
	 * @param {string} publicIds.public_id
	 * @param {string} publicIds.signature
	 * @param {Date} publicIds.start
	 * @param {Date} publicIds.end
	 */
	setPublicIds(publicIds)
	{
		for (const publicIdDescriptor of publicIds)
		{
			const userId = publicIdDescriptor.user_id;
			this.publicIds[userId] = {
				userId,
				publicId: publicIdDescriptor.public_id,
				signature: publicIdDescriptor.signature,
				start: new Date(publicIdDescriptor.start),
				end: new Date(publicIdDescriptor.end),
			};
		}
	}
}
