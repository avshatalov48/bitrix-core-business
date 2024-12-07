import { Type } from 'main.core';

import type { FieldsConfig } from 'im.v2.model';

export const messagesFieldsConfig: FieldsConfig = [
	{
		fieldName: 'id',
		targetFieldName: 'id',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'role',
		targetFieldName: 'roleCode',
		checkFunction: Type.isString,
	},
];
