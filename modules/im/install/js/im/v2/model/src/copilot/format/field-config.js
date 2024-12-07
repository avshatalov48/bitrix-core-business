import { Type } from 'main.core';

import { formatFieldsWithConfig, type FieldsConfig } from '../../utils/validate';

export const copilotFieldsConfig: FieldsConfig = [
	{
		fieldName: 'recommendedRoles',
		targetFieldName: 'recommendedRoles',
		checkFunction: Type.isArray,
	},
	{
		fieldName: 'roles',
		targetFieldName: 'roles',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => {
			return Object.values(target).map((role) => {
				return formatFieldsWithConfig(role, rolesFieldsConfig);
			});
		},
	},
];




