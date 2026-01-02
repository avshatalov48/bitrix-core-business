import { ajax } from 'main.core';
import { mapGroupDtoToModel } from './mappers';

class Api
{
	async validateGroup(groupId: number): Promise<boolean>
	{
		const result = await ajax.runAction('socialnetwork.collab.Converter.validateGroup', {
			data: {
				id: groupId,
			},
		});

		return {
			isValid: result.data.isValid,
			errors: result.errors,
		};
	}

	async getGroup(groupId: number): Promise<void>
	{
		const result = await ajax.runAction('socialnetwork.api.workgroup.get', {
			data: {
				params: {
					groupId,
					select: [
						'LIST_OF_MEMBERS',
						'AVATAR',
						'AVATAR_TYPES',
						'FEATURES',
					],
				},
			},
		});

		return mapGroupDtoToModel(result.data);
	}

	async convertToCollab(groupId: number): Promise<void>
	{
		const result = await ajax.runAction('socialnetwork.collab.Converter.convertToCollab', {
			data: {
				id: groupId,
			},
		});

		return result.data;
	}
}

export const api = new Api();
