import 'ui.info-helper';
import { UserSelector } from 'ui.form-elements.view';
import { Type } from 'main.core';

export class FieldFactory
{
	static createUserSelector(userSelectorParams): UserSelector
	{
		// eslint-disable-next-line no-param-reassign
		userSelectorParams.encodeValue = (value) => {
			if (!Type.isNil(value.id))
			{
				return value.id === 'all-users' ? 'UA' : value.type + value.id.toString().split(':')[0];
			}

			return null;
		};

		// eslint-disable-next-line no-param-reassign
		userSelectorParams.decodeValue = (value) => {
			if (value === 'UA')
			{
				return {
					type: 'AU',
					id: '',
				};
			}

			const arr = userSelectorParams.enableDepartments
				? value.match(/^(U|DR|D)(\d+)/)
				: value.match(/^(U)(\d+)/);

			if (!Type.isArray(arr))
			{
				return {
					type: null,
					id: null,
				};
			}

			return {
				type: arr[1],
				id: arr[2],
			};
		};

		if (Type.isObject(userSelectorParams.values))
		{
			// eslint-disable-next-line no-param-reassign
			userSelectorParams.values = Object.values(userSelectorParams.values);
		}

		return new UserSelector(userSelectorParams);
	}
}
